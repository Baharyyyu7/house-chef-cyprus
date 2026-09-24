{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{{ $channelTitle ?? 'House Chef Cyprus' }}</title>
        <link>{{ route('home') }}</link>
        <description>{{ $channelDescription ?? 'Doğrulanmış Kıbrıs ve Akdeniz yemek tarifleri, mutfak rehberleri ve gastronomi yazıları.' }}</description>
        <language>{{ $language ?? 'tr-CY' }}</language>
        <lastBuildDate>{{ $lastBuildDate }}</lastBuildDate>
        <atom:link href="{{ $feedUrl ?? route('feed') }}" rel="self" type="application/rss+xml" />
        <image>
            <url>{{ url(config('brand.assets.mark_light')) }}</url>
            <title>{{ $channelTitle ?? 'House Chef Cyprus' }}</title>
            <link>{{ route('home') }}</link>
        </image>
        @foreach($items as $item)
            <item>
                <title><![CDATA[{{ $item['title'] }}]]></title>
                <link>{{ $item['link'] }}</link>
                <guid isPermaLink="true">{{ $item['guid'] }}</guid>
                <description><![CDATA[{{ $item['description'] }}]]></description>
                <category>{{ $item['category'] }}</category>
                <dc:creator>House Chef Cyprus</dc:creator>
                <pubDate>{{ $item['pubDate'] }}</pubDate>
                @if(!empty($item['image']))
                    <enclosure url="{{ $item['image'] }}" length="0" type="image/jpeg" />
                @endif
            </item>
        @endforeach
    </channel>
</rss>
