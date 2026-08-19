@php
    /**
     * E-kitap şablonu.
     *
     * PDF motoru tarayıcı değil: flexbox, grid ve CSS değişkenleri okunmaz.
     * Bu yüzden sayfa düzeni bilerek tablo ve blok tabanlı, renkler sabit.
     * Türkçe karakterler için DejaVu Sans şart - varsayılan font ğ, ş, İ
     * karakterlerini boş kutu olarak basar.
     */
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Kıbrıs Mutfağı Seçmeler</title>
    <style>
        @page { margin: 28mm 18mm; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10.5pt; line-height: 1.6; color: #2b2b2b; }
        h1 { font-size: 26pt; line-height: 1.25; margin: 0 0 6pt; color: #111; }
        h2 { font-size: 15pt; line-height: 1.35; margin: 0 0 4pt; color: #111; }
        h3 { font-size: 11pt; margin: 14pt 0 5pt; color: #c2410c; text-transform: uppercase; letter-spacing: 0.6pt; }
        .kapak { text-align: center; padding-top: 60mm; }
        .kapak .marka { font-size: 11pt; letter-spacing: 3pt; color: #c2410c; margin-bottom: 12pt; }
        .kapak .alt { font-size: 11pt; color: #666; margin-top: 10pt; }
        .kunye { font-size: 9pt; color: #777; margin-top: 40mm; line-height: 1.7; }
        .tarif { page-break-before: always; }
        .ust { font-size: 9pt; color: #777; margin-bottom: 3pt; letter-spacing: 0.5pt; }
        .ozet { color: #555; margin: 6pt 0 10pt; }
        .kunye-satiri { font-size: 9pt; color: #666; border-top: 1px solid #e5e0d8; border-bottom: 1px solid #e5e0d8; padding: 5pt 0; margin-bottom: 10pt; }
        ul, ol { margin: 0; padding-left: 16pt; }
        li { margin-bottom: 3pt; }
        .icindekiler td { padding: 2.5pt 0; font-size: 10pt; border-bottom: 1px dotted #ddd; }
        .not { font-size: 8.5pt; color: #888; margin-top: 12pt; font-style: italic; }
    </style>
</head>
<body>

<div class="kapak">
    <div class="marka">HOUSE CHEF CYPRUS</div>
    <h1>Kıbrıs Mutfağı<br>Seçmeler</h1>
    <div class="alt">{{ $tarifler->count() }} doğrulanmış tarif · ölçüleriyle, adımlarıyla</div>
    <div class="kunye">
        housechefcyprus.com<br>
        Derlenme tarihi: {{ $uretimTarihi->format('d.m.Y') }}<br><br>
        Bu kitaptaki tarifler sitede yayımlanan ve editoryal doğrulamadan geçmiş
        tariflerden derlenmiştir. Kişisel kullanım içindir; çoğaltılamaz ve
        yeniden satılamaz.
    </div>
</div>

<div class="tarif">
    <h2>İçindekiler</h2>
    <table class="icindekiler" width="100%">
        @foreach ($tarifler as $sira => $tarif)
            <tr>
                <td>{{ $sira + 1 }}. {{ $tarif->title }}</td>
                <td align="right">{{ $tarif->cuisine?->name }}</td>
            </tr>
        @endforeach
    </table>
</div>

@foreach ($tarifler as $tarif)
    @php($satirlar = $olculer->forRecipe($tarif->recipeIngredients, (int) $tarif->servings, (int) $tarif->servings))
    <div class="tarif">
        <div class="ust">{{ $tarif->cuisine?->name }}@if($tarif->category) · {{ $tarif->category->name }}@endif</div>
        <h2>{{ $tarif->title }}</h2>

        @if ($tarif->excerpt)
            <p class="ozet">{{ $tarif->excerpt }}</p>
        @endif

        <div class="kunye-satiri">
            {{ $tarif->servings }} porsiyon
            @if ($tarif->totalMinutes()) · toplam {{ $tarif->totalMinutes() }} dakika @endif
            @if ($tarif->difficulty) · {{ $tarif->difficulty }} @endif
        </div>

        <h3>Malzemeler</h3>
        <ul>
            @foreach ($satirlar as $satir)
                <li>{{ $satir['formatted'] }}</li>
            @endforeach
        </ul>

        <h3>Hazırlanışı</h3>
        <ol>
            @foreach ($tarif->steps->sortBy('position') as $adim)
                <li>{{ $adim->instruction }}</li>
            @endforeach
        </ol>

        <p class="not">housechefcyprus.com/tarifler/{{ $tarif->slug }}</p>
    </div>
@endforeach

</body>
</html>
