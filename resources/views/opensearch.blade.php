{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
    <ShortName>{{ ($locale ?? 'tr') === 'en' ? 'House Chef (EN)' : 'House Chef' }}</ShortName>
    <Description>{{ ($locale ?? 'tr') === 'en' ? 'Search verified Cypriot and Mediterranean recipes' : 'Kıbrıs ve Akdeniz yemek tarifleri ara' }}</Description>
    <InputEncoding>UTF-8</InputEncoding>
    <Image width="16" height="16" type="image/x-icon">{{ asset('favicon.ico') }}</Image>
    <Url type="text/html" method="get" template="{{ route('recipes.index') }}?q={searchTerms}"/>
</OpenSearchDescription>
