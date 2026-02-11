web: sh -c 'rm -rf public/storage; php artisan storage:link || true; heroku-php-apache2 public/'

# Run database migrations on deploy. Optionally create/ensure an admin account
# if ADMIN_USERNAME and ADMIN_PASSWORD config vars are provided.
release: php artisan migrate --force && php artisan db:fix-pg-sequences && rm -rf public/storage && php artisan storage:link && if [ -n "$ADMIN_USERNAME" ] && [ -n "$ADMIN_PASSWORD" ]; then php artisan auth:ensure-admin --username="$ADMIN_USERNAME" --password="$ADMIN_PASSWORD"; else echo "ADMIN_USERNAME/ADMIN_PASSWORD not set; skipping admin init"; fi && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
