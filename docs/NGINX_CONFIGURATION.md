# Nginx yapılandırma örneği

Bu örneği domain, PHP socket ve sertifika yollarına göre uyarlayın. Uygulamanın kökü değil yalnız `public/` dizini servis edilir.

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name example.com www.example.com;
    return 301 https://example.com$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name example.com;

    root /var/www/house-chef/current/public;
    index index.php;
    charset utf-8;
    client_max_body_size 12m;

    ssl_certificate /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/example.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location = /storage/app/private { deny all; }
    location ~ /\.(?!well-known).* { deny all; }
    location ~* \.(?:env|log|sqlite|bak|sql)$ { deny all; }

    location ~ \.php$ {
        try_files $uri =404;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 60s;
    }
}
```

Cloudflare kullanılıyorsa yalnız Cloudflare'ın güncel IP aralıklarını Nginx `real_ip` güvenilir proxy listesine ekleyin; rastgele `X-Forwarded-*` header'larını güvenilir kabul etmeyin. TLS doğrulaması ve HTTP→HTTPS yönlendirmesi tamamlanmadan `APP_URL` değiştirilmez.
