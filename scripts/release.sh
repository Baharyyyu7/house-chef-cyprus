#!/usr/bin/env bash
set -euo pipefail

# Uctan uca surum yayinlama.
#
# Bu script elle yurutulen dokuz adimlik bir prosedurun yerini aliyor. O
# prosedurun her adimi bir gun icinde en az bir kez yanlis yapildi:
#
#   1. Detached HEAD'de commit atildi; `git push` "Everything up-to-date"
#      dedi, deploy "unable to read tree" ile durdu.
#   2. RELEASE_COMMIT elle yazildi ve uc kez yanlis girildi.
#   3. Artefakt alfabetik siralamayla secildi, yanlis dosya yuklendi.
#   4. Taze worktree'de `.env` yoktu; on uc test ortam yuzunden kirmizi
#      verdi ve kod hatasi sanildi.
#   5. Basarisiz denemenin biraktigi release dizini bir sonraki denemeyi
#      reddettirdi.
#
# Hicbiri kod hatasi degildi; hepsi prosedur hatasiydi. Prosedur artik
# burada.
#
# Kullanim:
#   scripts/release.sh                 # tam yayin
#   scripts/release.sh --dry-run       # hicbir sey degistirmeden dogrula
#   scripts/release.sh --skip-tests    # yalniz suit baska yerde kosulduysa

DRY_RUN=false
SKIP_TESTS=false
for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=true ;;
        --skip-tests) SKIP_TESTS=true ;;
        *) printf 'Bilinmeyen secenek: %s\n' "$arg" >&2; exit 2 ;;
    esac
done

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BRANCH="${BRANCH:-visual-redesign-v2}"
SSH_TARGET="${SSH_TARGET:-u584361532@147.93.93.105}"
SSH_PORT="${SSH_PORT:-65002}"
APP_HOME="${APP_HOME:-~/domains/housechefcyprus.com}"
REPOSITORY_URL="${REPOSITORY_URL:-git@github-housechef:Baharyyyu7/house-chef-cyprus.git}"
PHP_BIN="${PHP_BIN:-/opt/alt/php84/usr/bin/php}"

adim() { printf '\n[surum] %s\n' "$1"; }
hata() { printf '\n[surum] HATA: %s\n' "$1" >&2; exit 1; }
uzak() { ssh -p "$SSH_PORT" "$SSH_TARGET" "$@"; }

cd "$ROOT"

# --- 1. Dal denetimi -------------------------------------------------------
# Detached HEAD'de atilan commit hicbir dala bagli olmaz: push sessizce
# hicbir sey yapmaz ve deploy commit'i bulamaz.
adim 'Dal durumu denetleniyor.'
MEVCUT_DAL="$(git branch --show-current)"
[[ -n "$MEVCUT_DAL" ]] || hata "Detached HEAD. Once dala don: git checkout $BRANCH"
[[ "$MEVCUT_DAL" == "$BRANCH" ]] || hata "Yanlis dal: $MEVCUT_DAL (beklenen $BRANCH)"

# SHA hicbir zaman elle yazilmiyor.
SHA="$(git rev-parse HEAD)"
KISA="$(git rev-parse --short=7 HEAD)"
printf '  dal=%s commit=%s\n' "$MEVCUT_DAL" "$KISA"

# --- 2. Uzak ucla karsilastirma --------------------------------------------
# Iki ajan ayni dala calisiyor. Kendi commit'inden deploy etmek, sonradan
# gelen commit'leri uretimden dusurur - bir kez oldu ve bir ajanin tasarim
# isi canlidan silindi.
adim 'Uzak dal ucu kontrol ediliyor.'
git fetch --quiet origin "$BRANCH"
UZAK="$(git rev-parse "origin/$BRANCH")"

if [[ "$UZAK" != "$SHA" ]]; then
    if git merge-base --is-ancestor "$UZAK" "$SHA"; then
        printf '  yerel uctan ileride, gonderilecek\n'
    else
        hata "Uzak dalda sizde olmayan commit var. Once birlestirin: git pull --ff-only"
    fi
fi

# --- 3. Temiz kopya --------------------------------------------------------
# Artefakt calisma agacindan paketleniyor, commit'ten degil: kirli agacta
# commit edilmemis kod, onu icermeyen bir commit adiyla uretime gider.
WORKTREE="${WORKTREE_DIR:-/tmp/hc-release-$KISA}"
adim "Temiz kopya hazirlaniyor: $WORKTREE"
if "$DRY_RUN"; then
    printf '  [dry-run] worktree kurulumu atlandi\n'
else
    git worktree prune
    [[ -d "$WORKTREE" ]] && git worktree remove --force "$WORKTREE" 2>/dev/null || true
    git worktree add --quiet --detach "$WORKTREE" "$SHA"

    # Taze kopyada .env ve bagimliliklar yok. Bu atlandiginda on uc test
    # ortam yuzunden kirmizi verdi ve kod hatasi sanildi.
    [[ -f "$ROOT/.env" ]] || hata "$ROOT/.env yok; testler APP_KEY olmadan kosamaz."
    cp "$ROOT/.env" "$WORKTREE/.env"
    ( cd "$WORKTREE" && composer install --no-interaction --prefer-dist --quiet )
    ( cd "$WORKTREE" && npm ci --silent )
    # Varliklar da derlenmeli: testler sayfa render ediyor ve Vite manifesti
    # yoksa 350 test "manifest not found" ile 500 doner. Bu, .env eksikligiyle
    # birlikte Codex'in deploy'da durmasinin ikinci sebebiydi.
    ( cd "$WORKTREE" && npm run build --silent >/dev/null )
fi

temizle() {
    [[ "$DRY_RUN" == "true" ]] && return 0
    git worktree remove --force "$WORKTREE" 2>/dev/null || true
}
trap temizle EXIT

# --- 4. Testler ------------------------------------------------------------
if "$SKIP_TESTS"; then
    adim 'Testler atlandi (--skip-tests).'
elif "$DRY_RUN"; then
    adim '[dry-run] testler atlandi.'
else
    adim 'Tam test paketi kosuluyor (temiz kopyada).'
    ( cd "$WORKTREE" && php artisan test ) || hata 'Testler gecmedi; yayin durduruldu.'
fi

# --- 5. Artefakt -----------------------------------------------------------
adim 'Artefakt uretiliyor.'
ARTEFAKT="$WORKTREE/build-artifacts/house-chef-cyprus-full-$KISA.tar.gz"
if "$DRY_RUN"; then
    printf '  [dry-run] %s\n' "$ARTEFAKT"
else
    ( cd "$WORKTREE" && ./scripts/build-production-artifact.sh >/dev/null )
    [[ -f "$ARTEFAKT" ]] || hata "Artefakt beklenen adta uretilmedi: $ARTEFAKT"
fi

# --- 6. Gonderim -----------------------------------------------------------
adim 'Commit gonderiliyor.'
if "$DRY_RUN"; then
    printf '  [dry-run] git push origin %s\n' "$BRANCH"
else
    git push --quiet origin "$BRANCH"
    # Deploy sunucuda `git clone` yapiyor; commit uzakta yoksa "unable to
    # read tree" ile durur.
    git fetch --quiet origin "$BRANCH"
    [[ "$(git rev-parse "origin/$BRANCH")" == "$SHA" ]] || hata 'Commit uzak dala ulasmadi.'
fi

# --- 7. Yukleme ------------------------------------------------------------
adim 'Artefakt sunucuya yukleniyor.'
if "$DRY_RUN"; then
    printf '  [dry-run] scp %s\n' "$(basename "$ARTEFAKT")"
else
    COPYFILE_DISABLE=1 scp -q -P "$SSH_PORT" "$ARTEFAKT" "$ARTEFAKT.sha256" "$SSH_TARGET:~/"
    uzak "cd ~ && sha256sum -c $(basename "$ARTEFAKT").sha256" >/dev/null || hata 'Checksum tutmadi.'
fi

# --- 8. Bayat release dizini ------------------------------------------------
# Basarisiz bir deneme dizini birakir ve script tekrar kullanmayi reddeder.
# Silmeden once aktif olmadigini dogrulamak sart.
adim 'Bayat release dizini kontrol ediliyor.'
if ! "$DRY_RUN"; then
    uzak "D=$APP_HOME/releases/$SHA; A=\$(readlink -f $APP_HOME/current); \
        if [ -d \"\$D\" ]; then \
            if [ \"\$D\" = \"\$A\" ]; then echo '  dizin aktif surum, dokunulmadi'; \
            else rm -rf \"\$D\" && echo '  bayat dizin silindi'; fi; \
        else echo '  bayat dizin yok'; fi"
fi

# --- 9. Deploy -------------------------------------------------------------
adim 'Deploy calistiriliyor.'
if "$DRY_RUN"; then
    printf '  [dry-run] RELEASE_COMMIT=%s\n' "$SHA"
else
    uzak "cd $APP_HOME/current && \
        APP_HOME=$APP_HOME \
        REPOSITORY_URL=$REPOSITORY_URL \
        RELEASE_COMMIT=$SHA \
        ASSET_ARTIFACT=~/$(basename "$ARTEFAKT") \
        BRANCH=$BRANCH \
        PHP_BIN=$PHP_BIN \
        bash scripts/deploy-hostinger-release.sh" 2>&1 | grep -v 'LIBARCHIVE.xattr' | tail -20
fi

# --- 10. lsphp ve dogrulama -------------------------------------------------
# LiteSpeed eski kodu opcache'te tutuyor; yeniden baslatilmadan yeni surum
# servis edilmiyor.
adim 'lsphp yeniden baslatiliyor ve canli dogrulaniyor.'
if ! "$DRY_RUN"; then
    uzak 'pkill -f lsphp' 2>/dev/null || true
    sleep 5

    AKTIF="$(uzak "readlink $APP_HOME/current | xargs basename")"
    [[ "$AKTIF" == "$SHA" ]] || hata "Surum degismedi: $AKTIF (beklenen $KISA)"

    for yol in '' 'tarifler' 'blog' 'health'; do
        KOD="$(curl -s -o /dev/null -w '%{http_code}' "https://housechefcyprus.com/$yol")"
        printf '  /%-10s %s\n' "$yol" "$KOD"
        [[ "$KOD" == "200" ]] || hata "/$yol beklenen 200 yerine $KOD dondu."
    done

    # --- 11. Artefakt temizligi --------------------------------------------
    # AGENTS.md: dogrulanmis deploy sonrasi ayni oturumda silinir.
    uzak "cd ~ && ls -1t house-chef-cyprus-full-*.tar.gz* 2>/dev/null | tail -n +5 | xargs -r rm -f"
fi

adim "Yayin tamamlandi: $KISA"
