#!/usr/bin/env bash
# server-setup.sh
# Automated setup for Ubuntu 24.04 server with Docker:
# - create user "tsv" with sudo + docker group
# - create /srv/projects and a hello-world docker-compose app on 127.0.0.1:8080
# - install nginx, snapd + certbot
# - add UFW rules for SSH/HTTP/HTTPS
# - configure nginx reverse-proxy for DOMAIN and obtain Let's Encrypt cert (non-interactive)
#
# USAGE:
# 1) Edit the variables below (DOMAIN, SERVER_IP, CERTBOT_EMAIL, SSH_PUBKEY) before running,
#    OR set them in the environment before invoking the script:
#      DOMAIN=tsv-bandenwerbung.de CERTBOT_EMAIL=you@example.com SSH_PUBKEY="ssh-rsa AAAA..." sudo ./server-setup.sh
# 2) Run as root (sudo) on the server.
#
# CAUTION: This script will make system changes. Read it and adapt to your needs.
#set -euo pipefail

# ---------------------------
# Configuration - customize!
# ---------------------------
DOMAIN="${DOMAIN:-tsv-foerdertafel.de}"
WWW_DOMAIN="www.${DOMAIN}"
SERVER_IP="${SERVER_IP:-46.224.208.148}"   # your server IP
NEW_USER="${NEW_USER:-tsv}"
CERTBOT_EMAIL="${CERTBOT_EMAIL:-webmaster@tsv-foerdertafel.de}"          # required for non-interactive certbot; set before running or edit here
SSH_PUBKEY=""                # optional: put your public SSH key here or export SSH_PUBKEY env var
PROJECT_ROOT="${PROJECT_ROOT:-/srv/projects}"
HELLO_DIR="${PROJECT_ROOT}/hello-world"
DOCKER_COMPOSE_BIN="${DOCKER_COMPOSE_BIN:-docker}" # use `docker compose` (v2) via docker CLI
MAX_DNS_CHECKS=30
DNS_WAIT_SEC=10

# ---------------------------
# Basic checks
# ---------------------------
if [[ $EUID -ne 0 ]]; then
  echo "Please run as root (sudo)."
  exit 1
fi

echo "Starting automated setup for domain: ${DOMAIN}"
echo "Server IP expected: ${SERVER_IP}"
echo "New system user to create: ${NEW_USER}"
if [[ -z "${CERTBOT_EMAIL}" ]]; then
  echo "WARNING: CERTBOT_EMAIL is empty. certbot non-interactive mode needs an email. Set CERTBOT_EMAIL env var or edit the script."
fi

# ---------------------------
# Create user & groups
# ---------------------------
# create docker group if missing
if ! getent group docker >/dev/null; then
  echo "Creating docker group"
  groupadd docker || true
fi

if id -u "${NEW_USER}" >/dev/null 2>&1; then
  echo "User ${NEW_USER} already exists; skipping useradd."
else
  echo "Creating user ${NEW_USER} with home and adding to sudo,docker groups"
  useradd -m -s /bin/bash -G sudo,docker "${NEW_USER}"
  # no password set; administrator can set password or use SSH key
  passwd -l "${NEW_USER}" || true
fi

# If SSH public key provided, install it
if [[ -n "${SSH_PUBKEY}" ]]; then
  echo "Installing provided SSH public key for ${NEW_USER}"
  userhome="$(eval echo ~${NEW_USER})"
  mkdir -p "${userhome}/.ssh"
  echo "${SSH_PUBKEY}" > "${userhome}/.ssh/authorized_keys"
  chown -R "${NEW_USER}:${NEW_USER}" "${userhome}/.ssh"
  chmod 700 "${userhome}/.ssh"
  chmod 600 "${userhome}/.ssh/authorized_keys"
else
  echo "No SSH public key supplied. Make sure you can log in via password or add a key to /home/${NEW_USER}/.ssh/authorized_keys later."
fi

# ---------------------------
# Create project directory
# ---------------------------
echo "Creating project root at ${PROJECT_ROOT} and setting ownership to ${NEW_USER}"
mkdir -p "${PROJECT_ROOT}"
chown "${NEW_USER}:${NEW_USER}" "${PROJECT_ROOT}"
chmod 755 "${PROJECT_ROOT}"

# ---------------------------
# Basic package install & services
# ---------------------------
echo "Updating apt and installing required packages (ufw, nginx, curl, snapd)"
apt update
apt upgrade -y
apt install -y ufw nginx curl snapd

# Ensure snapd is active
systemctl enable --now snapd.socket || true

# install certbot via snap
if ! snap list certbot >/dev/null 2>&1; then
  echo "Installing certbot via snap"
  snap install core && snap refresh core
  snap install --classic certbot
  ln -sf /snap/bin/certbot /usr/bin/certbot
else
  echo "certbot (snap) already installed"
fi

# Enable and start nginx
systemctl enable --now nginx

# ---------------------------
# UFW configuration
# ---------------------------
echo "Configuring UFW: allowing OpenSSH, 80, 443"
apt install -y ufw
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
ufw status verbose

# ---------------------------
# Nginx site configuration (HTTP -> reverse proxy to 127.0.0.1:8080)
# ---------------------------
NGINX_SITE="/etc/nginx/sites-available/${DOMAIN}"
if [[ -f "${NGINX_SITE}" ]]; then
  echo "Nginx site ${NGINX_SITE} already exists; backing up and overwriting."
  cp "${NGINX_SITE}" "${NGINX_SITE}.bak.$(date +%s)"
fi

cat > "${NGINX_SITE}" <<'NGCONF'
server {
    listen 80;
    server_name REPLACE_DOMAIN REPLACE_WWW;

    # Proxy requests to local docker app at 127.0.0.1:8080
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
NGCONF

# replace placeholders
sed -i "s|REPLACE_DOMAIN|${DOMAIN}|g" "${NGINX_SITE}"
sed -i "s|REPLACE_WWW|${WWW_DOMAIN}|g" "${NGINX_SITE}"

ln -sf "${NGINX_SITE}" "/etc/nginx/sites-enabled/${DOMAIN}"
# remove default site to avoid conflicts
if [[ -f /etc/nginx/sites-enabled/default ]]; then
  rm -f /etc/nginx/sites-enabled/default
fi

nginx -t
systemctl reload nginx

# ---------------------------
# Create hello-world Docker project
# ---------------------------
echo "Creating hello-world docker project in ${HELLO_DIR}"
if [[ ! -d "${HELLO_DIR}" ]]; then
  mkdir -p "${HELLO_DIR}/html"
  chown -R "${NEW_USER}:${NEW_USER}" "${HELLO_DIR}"
fi

cat > "${HELLO_DIR}/Dockerfile" <<'DOCKERFILE'
FROM nginx:stable-alpine
COPY ./html /usr/share/nginx/html:ro
DOCKERFILE

cat > "${HELLO_DIR}/html/index.html" <<'INDEX'
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Hello World</title>
</head>
<body>
  <h1>Hello World from Docker on your server</h1>
  <p>This response comes from the Docker container bound to 127.0.0.1:8080 and proxied by host nginx.</p>
</body>
</html>
INDEX

cat > "${HELLO_DIR}/docker-compose.yml" <<'DCOMPOSE'
version: "3.8"
services:
  hello:
    build: .
    container_name: hello-world
    restart: unless-stopped
    ports:
      - "127.0.0.1:8080:80"
DCOMPOSE

chown -R "${NEW_USER}:${NEW_USER}" "${HELLO_DIR}"
chmod -R 755 "${HELLO_DIR}"

# Build and start the app using docker compose
echo "Starting docker compose for hello-world (may require docker to be installed and running)"
cd "${HELLO_DIR}"
# Use the docker CLI compose command
${DOCKER_COMPOSE_BIN} compose up -d --build || {
  echo "Warning: 'docker compose' failed. Ensure Docker Engine 27.x is installed and current user has permissions. You can run this manually later as ${NEW_USER}."
}

# Check that the container is up (best effort)
if command -v docker >/dev/null 2>&1; then
  echo "docker ps (showing containers):"
  docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}"
fi

# Local test curl to 127.0.0.1:8080
echo "Testing local HTTP access to 127.0.0.1:8080"
if command -v curl >/dev/null 2>&1; then
  curl -I --silent --max-time 5 http://127.0.0.1:8080 || echo "No response from 127.0.0.1:8080 yet."
fi

# ---------------------------
# DNS check before attempting certbot
# ---------------------------
echo "Checking DNS A record for ${DOMAIN} resolves to ${SERVER_IP}"
checks=0
resolved_ok=0
while [[ ${checks} -lt ${MAX_DNS_CHECKS} ]]; do
  checks=$((checks+1))
  # prefer dig if present, fallback to host
  if command -v dig >/dev/null 2>&1; then
    ip_found="$(dig +short ${DOMAIN} | grep -E '^[0-9.]+' | head -n1 || true)"
  elif command -v host >/dev/null 2>&1; then
    ip_found="$(host ${DOMAIN} | awk '/has address/ {print $4; exit}' || true)"
  else
    ip_found=""
  fi

  if [[ "${ip_found}" == "${SERVER_IP}" ]]; then
    resolved_ok=1
    echo "DNS points correctly (${ip_found})."
    break
  fi

  echo "Attempt ${checks}/${MAX_DNS_CHECKS}: DNS points to '${ip_found:-<none>}' (waiting ${DNS_WAIT_SEC}s). If you haven't set the A record at your registrar, do so now (A ${DOMAIN} -> ${SERVER_IP})."
  sleep "${DNS_WAIT_SEC}"
done

if [[ "${resolved_ok}" -ne 1 ]]; then
  echo "DNS did not resolve to ${SERVER_IP} after $((MAX_DNS_CHECKS*DNS_WAIT_SEC)) seconds. Skipping automatic certbot. You can run certbot manually later when DNS is ready."
  echo "To obtain certs later run (as root): certbot --nginx -d ${DOMAIN} -d ${WWW_DOMAIN} --email you@example.com --agree-tos --no-eff-email --redirect --non-interactive"
  exit 0
fi

# ---------------------------
# Obtain TLS certificate (non-interactive)
# ---------------------------
if [[ -z "${CERTBOT_EMAIL}" ]]; then
  echo "CERTBOT_EMAIL not set; cannot run certbot non-interactively. Exiting before certificate issuance."
  exit 1
fi

echo "Requesting Let's Encrypt certificate for ${DOMAIN} and ${WWW_DOMAIN} (non-interactive)"
certbot --nginx -d "${DOMAIN}" -d "${WWW_DOMAIN}" --email "${CERTBOT_EMAIL}" --agree-tos --no-eff-email --redirect --non-interactive

echo "Testing certbot renewal (dry-run)"
certbot renew --dry-run || echo "certbot renew --dry-run returned non-zero (this may still be fine)."

# ---------------------------
# Final status
# ---------------------------
echo "Setup complete."
echo "Nginx site file: ${NGINX_SITE}"
echo "Hello-world project: ${HELLO_DIR}"
echo "To manage the hello-world app as ${NEW_USER}:"
echo "  sudo -u ${NEW_USER} -H bash -c 'cd ${HELLO_DIR} && ${DOCKER_COMPOSE_BIN} compose up -d --build'"
echo
echo "Useful commands:"
echo "  nginx -t && systemctl reload nginx"
echo "  systemctl status nginx"
echo "  docker ps"
echo "  sudo ufw status"
echo
echo "If you want passwordless sudo for ${NEW_USER}, edit /etc/sudoers.d/ (not done automatically)."
echo "Remember to add your SSH public key to /home/${NEW_USER}/.ssh/authorized_keys if you didn't provide one."
