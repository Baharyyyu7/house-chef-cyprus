# Web Root and Server Configuration

The only public web root is `public/`. `deploy/apache/housechef.htaccess.example` and `deploy/nginx/housechef.conf.example` are templates, not active production configuration. They deny dotfiles and app internals, disable directory listing, require HTTPS/canonical redirects, preserve Laravel routing and apply safe static caching/security headers. Configure HSTS only after valid HTTPS is verified.
