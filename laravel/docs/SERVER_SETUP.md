# 🖥️ Server-Einrichtung – Schritt-für-Schritt-Anleitung

> **Zielumgebung:** Ubuntu 24.04 LTS  
> **Skript:** `server-setup.sh` (Projekt-Root)

---

## 📋 Inhaltsverzeichnis

1. [Voraussetzungen](#1-voraussetzungen)
2. [Skript konfigurieren](#2-skript-konfigurieren)
3. [server-setup.sh ausführen](#3-server-setupsh-ausführen)
4. [Docker installieren](#4-docker-installieren)
5. [GitHub-Verknüpfung via SSH-Key](#5-github-verknüpfung-via-ssh-key)
6. [Projekt klonen & git pull](#6-projekt-klonen--git-pull)
7. [Häufige Probleme](#7-häufige-probleme)

---

## 1. Voraussetzungen

Bevor du anfängst, stelle sicher dass:

- Du einen frischen **Ubuntu 24.04**-Server hast (z. B. bei Hetzner, Netcup, etc.)
- Du per **Root-SSH-Zugang** auf den Server kommst
- Die **DNS-A-Records** deiner Domain auf die Server-IP zeigen
  ```
  A    tsv-foerdertafel.de      →  46.224.208.148
  A    www.tsv-foerdertafel.de  →  46.224.208.148
  ```
- Du deinen **lokalen SSH Public Key** kennst (für den neuen Server-User)

> ⏳ DNS-Änderungen können bis zu 24 Stunden dauern. Du kannst mit `dig tsv-foerdertafel.de` prüfen, ob sie schon aktiv sind.

---

## 2. Skript konfigurieren

Öffne `server-setup.sh` und passe diese Variablen oben im Skript an:

```bash
DOMAIN="${DOMAIN:-tsv-foerdertafel.de}"        # Deine Domain
SERVER_IP="${SERVER_IP:-46.224.208.148}"        # IP deines Servers
NEW_USER="${NEW_USER:-tsv}"                     # Neuer Linux-User
CERTBOT_EMAIL="${CERTBOT_EMAIL:-webmaster@tsv-foerdertafel.de}"  # Deine E-Mail für Let's Encrypt
SSH_PUBKEY="ssh-rsa AAAA..."                    # Dein lokaler Public Key (id_rsa.pub / id_ed25519.pub)
```

**Deinen lokalen Public Key ermitteln:**
```bash
# Auf deinem lokalen Rechner (Windows Terminal / Git Bash):
cat ~/.ssh/id_ed25519.pub
# oder
cat ~/.ssh/id_rsa.pub
```
Den kompletten Inhalt (eine Zeile) in `SSH_PUBKEY` eintragen.

---

## 3. server-setup.sh ausführen

### Skript auf den Server übertragen

```bash
# Von deinem lokalen Rechner:
scp server-setup.sh root@46.224.208.148:/root/
```

### Skript auf dem Server starten

```bash
# Per SSH mit Root einloggen:
ssh root@46.224.208.148

# Ausführbar machen und starten:
chmod +x /root/server-setup.sh
sudo bash /root/server-setup.sh
```

### Was das Skript macht

| Schritt | Aktion |
|---|---|
| 👤 User anlegen | User `tsv` mit `sudo` & `docker` Gruppe |
| 🔑 SSH-Key einrichten | Public Key in `~/.ssh/authorized_keys` |
| 📁 Projektverzeichnis | `/srv/projects` erstellen |
| 📦 Pakete installieren | `nginx`, `ufw`, `curl`, `snapd`, `certbot` |
| 🔥 Firewall | UFW: SSH (22), HTTP (80), HTTPS (443) |
| 🌐 Nginx | Reverse-Proxy auf `127.0.0.1:8080` |
| 🐳 Hello-World | Demo-Docker-App zum Testen |
| 🔒 TLS-Zertifikat | Let's Encrypt via Certbot (wartet auf DNS) |

Nach dem Durchlauf kannst du dich als neuer User einloggen:
```bash
ssh tsv@46.224.208.148
```

---

## 4. Docker installieren

> Das Skript installiert **kein** Docker automatisch – das muss vorher oder danach manuell erfolgen.

```bash
# Als root auf dem Server:
curl -fsSL https://get.docker.com | sh

# User "tsv" zur docker-Gruppe hinzufügen (falls noch nicht passiert):
usermod -aG docker tsv

# Testen:
docker --version
docker compose version
```

> 🔄 Damit die Gruppe greift, musst du dich neu einloggen (`exit` + erneut `ssh tsv@...`).

---

## 5. GitHub-Verknüpfung via SSH-Key

Damit der Server von einem **privaten GitHub-Repository** pullen kann, muss auf dem Server ein SSH-Schlüsselpaar erstellt und der Public Key bei GitHub hinterlegt werden.

### 5.1 SSH-Schlüssel auf dem Server generieren

Einloggen als der Deploy-User (`tsv`):

```bash
ssh tsv@46.224.208.148
```

Schlüsselpaar erstellen (Ed25519 ist modern und empfohlen):

```bash
ssh-keygen -t ed25519 -C "deploy@tsv-foerdertafel.de"
```

Eingaben:
- **Dateipfad:** einfach `Enter` drücken → speichert in `~/.ssh/id_ed25519`
- **Passphrase:** leer lassen (Enter) für automatisches `git pull` ohne Passwort-Eingabe

Ergebnis:
```
~/.ssh/id_ed25519        ← Privater Schlüssel (NIEMALS weitergeben!)
~/.ssh/id_ed25519.pub    ← Öffentlicher Schlüssel → bei GitHub eintragen
```

### 5.2 Public Key anzeigen

```bash
cat ~/.ssh/id_ed25519.pub
```

Ausgabe sieht z. B. so aus:
```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAI... deploy@tsv-foerdertafel.de
```

Diese gesamte Zeile kopieren.

### 5.3 Deploy Key bei GitHub hinterlegen

Du hast zwei Möglichkeiten:

#### Option A: Deploy Key (empfohlen – nur für ein Repo)

1. GitHub → dein Repository öffnen
2. **Settings** → **Deploy keys** → **Add deploy key**
3. Titel: z. B. `Server tsv-foerdertafel.de`
4. Key: den kopierten Public Key einfügen
5. **Allow write access:** ❌ Nein (nur lesen reicht für `git pull`)
6. **Add key** klicken

#### Option B: SSH Key am Account (für alle Repos)

1. GitHub → **Profil-Icon** → **Settings**
2. **SSH and GPG keys** → **New SSH key**
3. Titel: z. B. `Server Fördertafel`
4. Key: Public Key einfügen
5. **Add SSH key** klicken

### 5.4 Verbindung zu GitHub testen

```bash
ssh -T git@github.com
```

Erwartete Ausgabe (trotz "Permission denied" kein Fehler):
```
Hi dein-github-username! You've successfully authenticated, but GitHub does not provide shell access.
```

Falls eine Fehlermeldung erscheint:
```bash
# Debug-Modus für mehr Details:
ssh -vT git@github.com
```

---

## 6. Projekt klonen & git pull

### 6.1 Repository klonen

```bash
# Als User "tsv" auf dem Server:
cd /srv/projects

# SSH-URL des privaten Repos verwenden (nicht HTTPS!):
git clone git@github.com:DEIN-USERNAME/DEIN-REPO.git

# Beispiel:
git clone git@github.com:cooolinho/laravel-filament-foerdertafel.git
```

> ⚠️ **Wichtig:** Immer die **SSH-URL** (`git@github.com:...`) verwenden, nicht die HTTPS-URL. Die SSH-URL findest du auf GitHub unter **Code** → **SSH**.

### 6.2 Aktuelles Repository updaten (git pull)

```bash
cd /srv/projects/laravel-filament-foerdertafel
git pull
```

Oder kombiniert:
```bash
cd /srv/projects/laravel-filament-foerdertafel && git pull origin main
```

### 6.3 Deployment-Workflow (nach git pull)

Nach einem Pull müssen je nach Änderungen weitere Schritte ausgeführt werden:

```bash
cd /srv/projects/laravel-filament-foerdertafel

# 1. Abhängigkeiten updaten (falls composer.json geändert):
docker compose exec app composer install --no-dev --optimize-autoloader

# 2. Migrationen ausführen (falls neue Migrations):
docker compose exec app php artisan migrate --force

# 3. Cache leeren:
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 4. Container neu starten (falls docker-compose.yml geändert):
docker compose up -d --build
```

### 6.4 Automatisches Deployment (optional)

Für einen einfachen Deploy-Script kannst du eine Datei anlegen:

```bash
cat > /srv/projects/deploy.sh << 'EOF'
#!/bin/bash
set -e

PROJECT_DIR="/srv/projects/laravel-filament-foerdertafel"

echo "🚀 Deployment gestartet..."

cd "$PROJECT_DIR"

echo "📥 Code aktualisieren..."
git pull origin main

echo "📦 Composer-Pakete installieren..."
docker compose exec -T app composer install --no-dev --optimize-autoloader

echo "🗄️ Migrationen ausführen..."
docker compose exec -T app php artisan migrate --force

echo "🧹 Cache leeren..."
docker compose exec -T app php artisan optimize

echo "✅ Deployment abgeschlossen!"
EOF

chmod +x /srv/projects/deploy.sh
```

Deployment starten:
```bash
bash /srv/projects/deploy.sh
```

---

## 7. Häufige Probleme

### ❌ `git@github.com: Permission denied (publickey)`

```bash
# Key vorhanden?
ls -la ~/.ssh/

# SSH-Agent starten und Key hinzufügen:
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519

# Erneut testen:
ssh -T git@github.com
```

→ Prüfe, ob der Public Key korrekt bei GitHub hinterlegt wurde (kein Leerzeichen, vollständige Zeile).

### ❌ `Host key verification failed`

```bash
# GitHub zu known_hosts hinzufügen:
ssh-keyscan github.com >> ~/.ssh/known_hosts
```

### ❌ Docker Compose schlägt fehl

```bash
# Docker-Daemon läuft?
systemctl status docker

# Docker starten:
sudo systemctl start docker
sudo systemctl enable docker

# User in docker-Gruppe?
groups tsv
# Falls nicht: sudo usermod -aG docker tsv
# Dann neu einloggen!
```

### ❌ Certbot schlägt fehl (DNS noch nicht aktiv)

Das Skript wartet automatisch bis zu 5 Minuten auf die DNS-Auflösung. Falls der Timeout abläuft, Zertifikat manuell beantragen:

```bash
sudo certbot --nginx \
  -d tsv-foerdertafel.de \
  -d www.tsv-foerdertafel.de \
  --email webmaster@tsv-foerdertafel.de \
  --agree-tos \
  --no-eff-email \
  --redirect \
  --non-interactive
```

### ❌ Nginx-Fehler nach Konfigurationsänderung

```bash
# Konfiguration prüfen:
sudo nginx -t

# Nginx neu laden:
sudo systemctl reload nginx
```

---

## ✅ Checkliste

- [ ] DNS-A-Records gesetzt und propagiert
- [ ] `server-setup.sh` konfiguriert (Domain, IP, E-Mail, SSH-Key)
- [ ] Skript als Root ausgeführt
- [ ] Docker installiert (`curl -fsSL https://get.docker.com | sh`)
- [ ] Als User `tsv` eingeloggt
- [ ] SSH-Schlüssel auf dem Server generiert (`ssh-keygen -t ed25519`)
- [ ] Deploy Key bei GitHub hinterlegt
- [ ] `ssh -T git@github.com` erfolgreich
- [ ] Repository geklont (`git clone git@github.com:...`)
- [ ] `git pull` funktioniert
- [ ] Laravel-App läuft (`docker compose up -d`)

---

**Letzte Aktualisierung:** 2026-04-06  
**Getestet mit:** Ubuntu 24.04 LTS, Docker 27.x, Git 2.x

