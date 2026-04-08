# TrustProxies und HTTPS in Docker-Umgebungen

## Problem: Datei-Uploads schlagen fehl bei HTTPS hinter Reverse Proxy

Wenn Sie Laravel in einer Docker-Umgebung mit einem Reverse Proxy (z.B. Nginx, Traefik, oder einem Load Balancer) betreiben und HTTPS verwenden, können verschiedene Probleme auftreten:

- **Mixed Content Warnings**: Die Anwendung generiert HTTP-URLs statt HTTPS-URLs
- **Datei-Uploads schlagen fehl**: Laravel erkennt die Verbindung als unsicher
- **CSRF-Token-Fehler**: Token-Validierung schlägt fehl
- **Redirect-Loops**: Die Anwendung leitet endlos um
- **Session-Probleme**: Sessions werden nicht korrekt gespeichert

## Ursache

In einer typischen Docker-Setup mit Reverse Proxy:

```
Internet (HTTPS) → Nginx/Traefik → Docker Container (HTTP)
```

Das Problem: Laravel läuft intern auf HTTP, weiß aber nicht, dass die Verbindung von außen über HTTPS erfolgt. Der Reverse Proxy muss diese Information über spezielle Header weitergeben.

## Lösung

### 1. TrustProxies Middleware konfigurieren

Die `TrustProxies` Middleware teilt Laravel mit, welchen Proxies es vertrauen soll und welche Header es für die Erkennung der echten Client-IP und des Protokolls verwenden soll.

**Datei:** `app/Http/Middleware/TrustProxies.php`

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Die vertrauenswürdigen Proxies für diese Anwendung.
     * 
     * Optionen:
     * - '*' = Alle Proxies vertrauen (für Docker/Kubernetes)
     * - '192.168.1.1' = Spezifische IP-Adresse
     * - ['192.168.1.1', '10.0.0.0/8'] = Array von IPs/Subnets
     */
    protected $proxies = '*';

    /**
     * Die Header, die verwendet werden sollen, um Proxies zu erkennen.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |      // Ursprüngliche Client-IP
        Request::HEADER_X_FORWARDED_HOST |     // Original Host
        Request::HEADER_X_FORWARDED_PORT |     // Original Port (443 für HTTPS)
        Request::HEADER_X_FORWARDED_PROTO |    // Original Protokoll (https)
        Request::HEADER_X_FORWARDED_AWS_ELB;   // AWS Elastic Load Balancer
}
```

#### Sicherheitshinweis zu `$proxies = '*'`

⚠️ **Wichtig:** `$proxies = '*'` vertraut allen Proxies. Das ist in Docker-Umgebungen üblich, da die Container-IPs dynamisch sind. 

**Alternativen für mehr Sicherheit:**

```php
// Nur spezifische Docker-Netzwerke vertrauen
protected $proxies = ['172.16.0.0/12', '192.168.0.0/16'];

// Aus Umgebungsvariablen laden
protected $proxies = [
    env('TRUSTED_PROXIES', '172.16.0.0/12')
];
```

### 2. Middleware registrieren

Die `TrustProxies` Middleware muss in Ihrem Panel-Provider registriert sein:

**Datei:** `app/Providers/Filament/AdminPanelProvider.php`

```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ... andere Konfigurationen
        ->middleware([
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
            TrustProxies::class, // ← Wichtig!
        ]);
}
```

### 3. APP_SECURE Konfiguration

Die `APP_SECURE` Umgebungsvariable steuert, ob Laravel HTTPS-URLs erzwingen soll.

**Datei:** `config/app.php`

```php
'secure' => env('APP_SECURE', false),
```

**In Ihrer `.env` Datei:**

```env
# Für Produktionsumgebung mit HTTPS
APP_SECURE=true
APP_URL=https://ihre-domain.de

# Für lokale Entwicklung
APP_SECURE=false
APP_URL=http://localhost
```

### 4. URL-Forcing im Provider

Wenn `APP_SECURE=true`, wird Laravel gezwungen, HTTPS-URLs zu generieren:

**Datei:** `app/Providers/Filament/AdminPanelProvider.php`

```php
public function boot(): void
{
    FilamentView::registerRenderHook(
        PanelsRenderHook::BODY_END,
        fn(): View => view('filament.admin.components.template-modal'),
    );

    if (config('app.secure', false)) {
        // Erzwinge HTTPS für alle generierten URLs
        URL::forceScheme('https');
        
        // Verwende die konfigurierte APP_URL als Basis
        URL::useOrigin(config('app.url'));

        // Zusätzliche Absicherung für Docker/Proxy-Umgebungen
        // Wenn der X-Forwarded-Proto Header gesetzt ist, setze HTTPS
        if (request()->server->has('HTTP_X_FORWARDED_PROTO')) {
            request()->server->set('HTTPS', 'on');
        }
    }
}
```

**Was macht dieser Code?**

1. `URL::forceScheme('https')` - Alle generierten URLs verwenden HTTPS
2. `URL::useOrigin(config('app.url'))` - Verwendet die konfigurierte Domain
3. `request()->server->set('HTTPS', 'on')` - Teilt PHP mit, dass die Verbindung sicher ist

### 5. Nginx Reverse Proxy Konfiguration

Ihr Nginx-Proxy muss die richtigen Header setzen:

```nginx
server {
    listen 443 ssl http2;
    server_name ihre-domain.de;

    # SSL-Konfiguration
    ssl_certificate /etc/ssl/certs/your-cert.pem;
    ssl_certificate_key /etc/ssl/private/your-key.pem;

    location / {
        proxy_pass http://laravel-app:80;
        
        # Wichtige Proxy-Header für Laravel
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        
        # Für Websockets (falls verwendet)
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        
        # Timeouts für große Uploads
        proxy_read_timeout 300;
        proxy_connect_timeout 300;
        proxy_send_timeout 300;
    }
}
```

### 6. Docker Compose Konfiguration

**Beispiel `docker-compose.yml`:**

```yaml
version: '3.8'

services:
  nginx-proxy:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/ssl:/etc/ssl
    depends_on:
      - laravel
    networks:
      - app-network

  laravel:
    build:
      context: .
      dockerfile: docker/runtimes/8.4/Dockerfile
    environment:
      - APP_SECURE=true
      - APP_URL=https://ihre-domain.de
    volumes:
      - ./laravel:/var/www/html
    networks:
      - app-network

networks:
  app-network:
    driver: bridge
```

## Umgebungsvariablen Übersicht

### `.env` für Produktion mit HTTPS:

```env
APP_NAME="Ihre Anwendung"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ihre-domain.de
APP_SECURE=true

# Wenn Sie spezifische Proxies vertrauen möchten
TRUSTED_PROXIES=172.16.0.0/12,192.168.0.0/16
```

### `.env` für lokale Entwicklung:

```env
APP_NAME="Ihre Anwendung"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_SECURE=false
```

## Troubleshooting

### Problem: Immer noch HTTP-URLs in der Anwendung

**Lösung 1:** Cache leeren

```bash
docker exec -it laravel-container php artisan config:clear
docker exec -it laravel-container php artisan cache:clear
docker exec -it laravel-container php artisan route:clear
docker exec -it laravel-container php artisan view:clear
```

**Lösung 2:** Überprüfen Sie die Nginx-Header

```bash
# Prüfen Sie, ob die Header ankommen
docker exec -it laravel-container php artisan tinker
>>> request()->server->all()
>>> request()->server->get('HTTP_X_FORWARDED_PROTO')
```

**Lösung 3:** Debugging aktivieren

```php
// Temporär in AdminPanelProvider.php zum Debuggen
public function boot(): void
{
    if (config('app.secure', false)) {
        \Log::info('X-Forwarded-Proto: ' . request()->header('X-Forwarded-Proto'));
        \Log::info('X-Forwarded-Host: ' . request()->header('X-Forwarded-Host'));
        \Log::info('Is Secure: ' . (request()->isSecure() ? 'yes' : 'no'));
        
        // ... restlicher Code
    }
}
```

### Problem: CSRF-Token-Fehler bei HTTPS

**Ursache:** Session-Cookie wird nicht korrekt gesetzt.

**Lösung:** Überprüfen Sie `config/session.php`:

```php
'secure' => env('SESSION_SECURE_COOKIE', env('APP_SECURE', false)),
'same_site' => 'lax',
```

Und in `.env`:

```env
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.ihre-domain.de
```

### Problem: Mixed Content Warnings

**Ursache:** Einige Assets werden noch über HTTP geladen.

**Lösung 1:** Content Security Policy Header in Nginx:

```nginx
add_header Content-Security-Policy "upgrade-insecure-requests;";
```

**Lösung 2:** In Laravel erzwingen:

```php
// In AppServiceProvider.php boot()
if ($this->app->environment('production')) {
    URL::forceScheme('https');
}
```

### Problem: File Upload schlägt fehl mit "The file exceeds the allowed size"

**Ursache:** Nginx oder PHP Limit ist zu niedrig.

**Lösung:** Siehe [NGINX_UPLOAD_LIMIT.md](NGINX_UPLOAD_LIMIT.md) für Details.

Zusätzlich in Nginx für Proxy:

```nginx
# In der proxy location
client_max_body_size 100M;
proxy_request_buffering off;
```

### Problem: Datei-Upload bricht nach einiger Zeit ab

**Ursache:** Proxy-Timeouts sind zu kurz.

**Lösung:** Erhöhen Sie die Timeouts in Nginx:

```nginx
location / {
    proxy_pass http://laravel-app:80;
    
    proxy_read_timeout 600;
    proxy_connect_timeout 600;
    proxy_send_timeout 600;
    
    # Für große Uploads
    client_body_timeout 600;
    send_timeout 600;
}
```

## Best Practices

### 1. Unterschiedliche Konfigurationen für Umgebungen

```php
// In AdminPanelProvider.php
public function boot(): void
{
    // Nur in Produktion HTTPS erzwingen
    if ($this->app->environment('production') && config('app.secure')) {
        URL::forceScheme('https');
        URL::useOrigin(config('app.url'));
    }
}
```

### 2. Health Check Endpoint ohne HTTPS-Redirect

```php
// In routes/web.php
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
})->withoutMiddleware([TrustProxies::class]);
```

### 3. Logging für Debugging

```php
// In TrustProxies.php für Debugging
public function handle(Request $request, Closure $next): Response
{
    if (config('app.debug')) {
        \Log::debug('TrustProxies', [
            'remote_addr' => $request->server->get('REMOTE_ADDR'),
            'x_forwarded_for' => $request->header('X-Forwarded-For'),
            'x_forwarded_proto' => $request->header('X-Forwarded-Proto'),
            'is_secure' => $request->isSecure(),
        ]);
    }
    
    return parent::handle($request, $next);
}
```

### 4. Sicherheit: Spezifische Proxies vertrauen

Für Produktionsumgebungen mit bekannten Proxy-IPs:

```php
// In TrustProxies.php
protected $proxies = [
    '192.168.1.1',      // Ihr Load Balancer
    '10.0.0.0/8',       // Internes Netzwerk
    '172.16.0.0/12',    // Docker Netzwerk
];
```

## Checkliste für HTTPS in Docker

- [ ] `TrustProxies` Middleware ist konfiguriert und registriert
- [ ] `APP_SECURE=true` in Produktions-`.env`
- [ ] `APP_URL` ist mit `https://` konfiguriert
- [ ] Nginx setzt alle `X-Forwarded-*` Header
- [ ] `URL::forceScheme('https')` wird in Produktion aufgerufen
- [ ] `client_max_body_size` ist in Nginx gesetzt (siehe [NGINX_UPLOAD_LIMIT.md](NGINX_UPLOAD_LIMIT.md))
- [ ] Proxy-Timeouts sind für große Uploads angepasst
- [ ] SSL-Zertifikate sind gültig und korrekt konfiguriert
- [ ] Session-Cookies sind für HTTPS konfiguriert
- [ ] Cache wurde nach Änderungen geleert

## Testen der Konfiguration

### 1. Header-Test

```bash
# Von außen testen
curl -I https://ihre-domain.de

# Im Container testen
docker exec -it laravel-container php artisan tinker
>>> request()->isSecure()
>>> request()->getScheme()
>>> request()->server->get('HTTP_X_FORWARDED_PROTO')
```

### 2. URL-Generierung testen

```bash
docker exec -it laravel-container php artisan tinker
>>> route('admin')
>>> url('admin')
>>> asset('css/app.css')
# Alle sollten mit https:// beginnen
```

### 3. File-Upload testen

1. Öffnen Sie die Browser-Entwicklertools (F12)
2. Gehen Sie zum Network-Tab
3. Laden Sie eine Datei hoch
4. Überprüfen Sie:
   - Request-URL beginnt mit `https://`
   - Status ist `200` oder `201`
   - Keine Mixed Content Warnings in der Console

## Weitere Ressourcen

- [Laravel TrustProxies Dokumentation](https://laravel.com/docs/11.x/requests#configuring-trusted-proxies)
- [Nginx Proxy Headers](http://nginx.org/en/docs/http/ngx_http_proxy_module.html#proxy_set_header)
- [Laravel HTTPS Configuration](https://laravel.com/docs/11.x/urls#forcing-https)
- [Nginx Upload Limit konfigurieren](NGINX_UPLOAD_LIMIT.md)
