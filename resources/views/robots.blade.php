User-agent: *
Allow: /
Disallow: /yonetim/
Disallow: /profile
Disallow: /dashboard
Disallow: /dolabim
Disallow: /favoriler
Disallow: /pisir/

Sitemap: {{ route('sitemap') }}
Sitemap: {{ route('sitemap.recipes') }}
Sitemap: {{ route('sitemap.blog') }}
Sitemap: {{ route('sitemap.images') }}
Sitemap: {{ route('sitemap.pages') }}

IndexNow: https://api.indexnow.org/indexnow
Host: housechefcyprus.com
Key: c032a1e8e8214371b658ef9c7e0b57e1
Key-Location: https://housechefcyprus.com/.well-known/key.txt

User-agent: GPTBot
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: Bingbot
Allow: /

User-agent: Mediapartners-Google
Allow: /

User-agent: Applebot
Allow: /

User-agent: Applebot-Extended
Allow: /

User-agent: OAI-SearchBot
Allow: /