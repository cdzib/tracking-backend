
docker compose exec vagonetas_backend-php bash
php artisan reverb:start

docker compose exec vagonetas_backend-php bash
php artisan app:gps-socket-server  --port=5055


ngrok start --all --config="ngrok.yml"