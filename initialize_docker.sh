
# 2. Build and start all Docker containers
docker-compose up --build -d

# 3. Install PHP dependencies
docker-compose run --rm composer install

# 4. Install npm packages
docker-compose run --rm npm install

# 5. Generate the Laravel app key
docker-compose run --rm artisan key:generate

# 6. Run the database migrations and refresh them
docker-compose run --rm artisan migrate:refresh

# 7. Add some initial data using seeders
docker-compose run --rm artisan db:seed

docker-compose run --rm artisan db:seed --class=TestDataSeeder

# 8. Run Vite server
docker-compose run --rm --publish 5173:5173 npm run dev --host

echo "Setup complete."