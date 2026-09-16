User-agent: Mediapartners-Google
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Applebot
Allow: /

User-agent: Applebot-Extended
Allow: /

User-agent: OAI-SearchBot
Allow: /

User-agent: *
Allow: /
Disallow: /yonetim/
Disallow: /profile
Disallow: /dashboard
Disallow: /dolabim
Disallow: /favoriler
Disallow: /pisir/

# Geo-targeting: Kıbrıs / Cyprus targeting
<IfModule mod_headers.c>
    Header set X-Geo-Region "TR"
    Header set X-Geo-Country "CY"
</IfModule>

# Kıbrıs mutfak içeriği için bölgesel indeksleme
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^mutfaklar/kibris-mutfagi - [E=GEO_REGION:TR,E=GEO_COUNTRY:CY]
    RewriteRule ^kibris-yemek - [E=GEO_REGION:TR,E=GEO_COUNTRY:CY]
</IfModule>

Sitemap: {{ route('sitemap') }}

# Kıbrıs tarifleri için bölgesel sitemap
<IfModule mod_rewrite.c>
    RewriteRule ^sitemaps/recipes\.xml - [E=GEO_REGION:TR,E=GEO_COUNTRY:CY]
</IfModule>