# Nginx Upload Limit konfigurieren

## Problem: 413 Request Entity Too Large

Wenn Sie beim Hochladen von Dateien (z.B. Preview-Bilder) den Fehler `413 Request Entity Too Large` erhalten, bedeutet dies, dass die maximale Upload-Größe in Nginx überschritten wurde.

> **Hinweis für HTTPS/Docker-Umgebungen:** Wenn Sie zusätzlich Probleme mit Datei-Uploads in HTTPS-Umgebungen hinter einem Reverse Proxy haben, siehe [TrustProxies und HTTPS in Docker-Umgebungen](TRUSTPROXIES_HTTPS_DOCKER.md).

## Lösung

### 1. Aktuelles Limit prüfen

#### Nginx-Konfiguration prüfen
```bash
# In der Docker-Umgebung
docker exec -it <nginx-container-name> cat /etc/nginx/nginx.conf | grep client_max_body_size
docker exec -it <nginx-container-name> cat /etc/nginx/conf.d/default.conf | grep client_max_body_size

# Auf dem Server direkt
cat /etc/nginx/nginx.conf | grep client_max_body_size
```

#### PHP-Limits prüfen
```bash
# In der Docker-Umgebung
docker exec -it <laravel-container-name> php -i | grep -E "upload_max_filesize|post_max_size|memory_limit"

# Auf dem Server direkt
php -i | grep -E "upload_max_filesize|post_max_size|memory_limit"
```

**Standardwerte:**
- Nginx: `client_max_body_size` = `1M` (wenn nicht gesetzt)
- PHP: `upload_max_filesize` = `2M`
- PHP: `post_max_size` = `8M`

### 2. Nginx `client_max_body_size` erhöhen

#### Option A: In der Hauptkonfiguration (empfohlen)

Bearbeiten Sie `/etc/nginx/nginx.conf`:

```nginx
http {
    # ... andere Einstellungen
    
    client_max_body_size 100M;
    
    # ... rest der Konfiguration
}
```

#### Option B: In der Server-Block-Konfiguration

Bearbeiten Sie Ihre Site-Konfiguration (z.B. `/etc/nginx/sites-available/default` oder `/etc/nginx/conf.d/default.conf`):

```nginx
server {
    listen 80;
    server_name example.com;
    
    client_max_body_size 100M;
    
    root /var/www/html/public;
    index index.php index.html;
    
    # ... rest der Server-Konfiguration
}
```

#### Option C: Für spezifische Routen

Falls Sie nur für bestimmte Upload-Routen erhöhen möchten:

```nginx
location /admin {
    client_max_body_size 100M;
    
    try_files $uri $uri/ /index.php?$query_string;
}

location /api/upload {
    client_max_body_size 100M;
    
    # ... rest der Location-Konfiguration
}
```

#### Option D: In Reverse Proxy Konfiguration

Wenn Nginx als Reverse Proxy vor Ihrer Laravel-Anwendung läuft:

```nginx
server {
    listen 443 ssl http2;
    server_name ihre-domain.de;
    
    client_max_body_size 100M;
    
    location / {
        proxy_pass http://laravel-app:80;
        
        # Wichtig für große Uploads
        proxy_request_buffering off;
        
        # Timeouts erhöhen
        proxy_read_timeout 600;
        proxy_connect_timeout 600;
        proxy_send_timeout 600;
        client_body_timeout 600;
        
        # Proxy-Header
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

> **Wichtig:** `proxy_request_buffering off;` verhindert, dass Nginx die gesamte Datei puffert, bevor sie an Laravel weitergeleitet wird. Das spart Speicher und ermöglicht größere Uploads.

### 3. PHP-Limits anpassen

Bearbeiten Sie Ihre `php.ini` oder erstellen Sie eine eigene Konfigurationsdatei:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
```

**Wichtig:** `post_max_size` sollte größer oder gleich `upload_max_filesize` sein.

### 4. Docker-spezifische Konfiguration

#### Docker Compose anpassen

Fügen Sie in Ihrer `docker-compose.yml` oder `docker-compose-prod.yml` ein Volume für die Nginx-Konfiguration hinzu:

```yaml
services:
  nginx:
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
```

Oder mounten Sie eine PHP-Konfiguration:

```yaml
services:
  php:
    volumes:
      - ./docker/php/php.ini:/usr/local/etc/php/php.ini
```

### 5. Konfiguration testen und anwenden

#### Nginx-Syntax prüfen
```bash
# Auf dem Server
sudo nginx -t

# In Docker
docker exec -it <nginx-container-name> nginx -t
```

#### Nginx neu laden
```bash
# Auf dem Server (ohne Verbindungsunterbrechung)
sudo systemctl reload nginx

# Oder vollständiger Neustart
sudo systemctl restart nginx

# In Docker
docker-compose restart nginx

# Oder spezifischer Container
docker restart <nginx-container-name>
```

#### Status überprüfen
```bash
sudo systemctl status nginx
```

### 6. Überprüfung

Nach der Anpassung sollten Sie testen, ob die Änderungen wirksam sind:

1. Erstellen Sie eine temporäre PHP-Datei `public/test-upload-limits.php`:

```php
<?php
echo 'PHP upload_max_filesize: ' . ini_get('upload_max_filesize') . '<br>';
echo 'PHP post_max_size: ' . ini_get('post_max_size') . '<br>';
echo 'PHP memory_limit: ' . ini_get('memory_limit') . '<br>';
```

2. Rufen Sie die Datei im Browser auf: `http://your-domain.com/test-upload-limits.php`
3. Löschen Sie die Datei nach der Überprüfung wieder

### Troubleshooting

#### Problem: Änderungen werden nicht übernommen

- Stellen Sie sicher, dass Sie die richtige Konfigurationsdatei bearbeitet haben
- Prüfen Sie, ob andere Konfigurationsdateien den Wert überschreiben
- Leeren Sie den Browser-Cache
- Starten Sie alle beteiligten Container/Services neu:

```bash
docker-compose restart
```

#### Problem: Immer noch 413 Fehler

1. Prüfen Sie die Nginx-Logs:
```bash
docker logs <nginx-container-name>
# oder
tail -f /var/log/nginx/error.log
```

2. Prüfen Sie, ob ein Load Balancer oder Proxy vor Nginx steht, der ebenfalls ein Limit hat

3. Stellen Sie sicher, dass sowohl Nginx als auch PHP korrekt konfiguriert sind

4. Bei HTTPS/Reverse Proxy: Siehe [TrustProxies und HTTPS Dokumentation](TRUSTPROXIES_HTTPS_DOCKER.md)

## Empfohlene Werte

Für eine Laravel/Filament-Anwendung mit Bild-Uploads:

- **Kleine Bilder (Profilbilder, Icons):** 10M
- **Mittelgroße Bilder (Banner, Produktfotos):** 50M
- **Große Dateien (Dokumente, Videos):** 100M - 500M

```nginx
# In nginx.conf oder server-block
client_max_body_size 100M;
```

```ini
# In php.ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
```

## Weitere Ressourcen

- [TrustProxies und HTTPS in Docker-Umgebungen](TRUSTPROXIES_HTTPS_DOCKER.md) - Lösung für Upload-Probleme mit HTTPS/Reverse Proxy
- [Nginx Documentation - client_max_body_size](http://nginx.org/en/docs/http/ngx_http_core_module.html#client_max_body_size)
- [PHP Documentation - upload_max_filesize](https://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize)
