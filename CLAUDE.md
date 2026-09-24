# CLAUDE.md - Coding Profile
# Best for: dev projects, code review, debugging, refactoring
# Extends: Universal CLAUDE.md rules

---

## Output
- Return code first. Explanation after, only if non-obvious.
- No inline prose. Use comments sparingly - only where logic is unclear.
- No boilerplate unless explicitly requested.

## Code Rules
- Simplest working solution. No over-engineering.
- No abstractions for single-use operations.
- No speculative features or "you might also want..."
- Read the file before modifying it. Never edit blind.
- No docstrings or type annotations on code not being changed.
- No error handling for scenarios that cannot happen.
- Three similar lines is better than a premature abstraction.

## Review Rules
- State the bug. Show the fix. Stop.
- No suggestions beyond the scope of the review.
- No compliments on the code before or after the review.

## Debugging Rules
- Never speculate about a bug without reading the relevant code first.
- State what you found, where, and the fix. One pass.
- If cause is unclear: say so. Do not guess.

## Simple Formatting
- No em dashes, smart quotes, or decorative Unicode symbols.
- Plain hyphens and straight quotes only.
- Natural language characters (accented letters, CJK, etc.) are fine when the content requires them.
- Code output must be copy-paste safe.

---

## Nerede durulur, nerede devam edilir

- Bir adım için girdime ihtiyaç yoksa devam et. Durum notunu ayrı bir
  mesaja değil, bir sonraki eylemin yanına yaz.
- Yalnız iki durumda dur ve sor: bensiz devam edemiyorsan, ya da geri
  alınması zor bir şeyin eşiğindeysen - üretim verisi silmek, force-push,
  bu deponun dışına çıkan değişiklik, para harcayan bir adım.
- "Devam edeyim mi?" diye sorma. Seçenekleri sıralayıp beklemek de bir
  duraktır; iş devam ederken sıralanabiliyorsa durak değildir.
- Kapsamın bir kısmı tıkandıysa geri kalanını bitir, sonra neyi neden
  bırakdığını söyle. İşi küçültme kararı bana ait.

Her turun sonunda üç başlık:

- **Bende kalan**: benim yapmam gereken, senin yapamadığın şeyler
- **Değişen**: bu turda gerçekten değişen ve doğrulanan şeyler
- **Bulunan**: ölçtüğün ama henüz düzeltilmemiş kusurlar

Uzun bir işte görev listesini `.claude/TASKS.md` dosyasında tut ve
ilerledikçe güncelle. Oturum uzayınca bağlam özetleniyor; dosyadaki liste
o özetten sağ çıkar, sohbet geçmişi çıkmaz.

---

## Ajanlar arası kurallar

Bu depoda Codex ve Antigravity de çalışıyor. Dosya üretmeden önce
[AGENTS.md](AGENTS.md) ve [docs/AJAN_CALISMA_KURALLARI.md](docs/AJAN_CALISMA_KURALLARI.md)
geçerlidir: üretilen dosyanın nereye yazılacağı, hangi komutun yerelde
çalıştırılmayacağı ve silme sırası orada tanımlı.

---

## Session Start Protocol ⚡

**MANDATORY** at start of each session:

```bash
# Load essential docs (~800 tokens - 2 min read)
✓ .claude/COMMON_MISTAKES.md      # ⚠️ CRITICAL - Read FIRST
✓ .claude/QUICK_START.md          # Essential commands
✓ .claude/ARCHITECTURE_MAP.md     # File locations
```

**At task completion:**
- Create completion doc in `.claude/completions/YYYY-MM-DD-task-name.md`
- Move session file to `.claude/sessions/archive/` (if created)

**⚠️ NEVER auto-load:**
- Files in `.claude/completions/` (0 token cost)
- Files in `.claude/sessions/` (0 token cost)
- Files in `docs/archive/` (0 token cost)

---

**Last Updated**: 2026-08-12
**Optimized with**: [Claude Token Optimizer](https://github.com/nadimtuhin/claude-token-optimizer)
