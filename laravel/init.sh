#!/bin/bash

# install dependencies
echo "➡️ Installing dependencies"
composer install --ignore-platform-reqs
yarn install
echo "✅ Installing dependencies complete"

# create .env file
echo "➡️ Create .env file"
cp .env.example .env
echo "✅ Create .env file complete"

# create APP_KEY
echo "➡️ Create APP_KEY 'php artisan key:generate'"
php artisan key:generate
echo "✅ APP_KEY creation Complete"

# run migrations
echo "➡️ Run migrations"
php artisan migrate
echo "✅ Run migrations complete"

# run migrations
echo "➡️ Create Admin User"
php artisan make:filament-user --name=admin --email=admin@example.com --password=secret
echo "✅ Create Admin User complete"
