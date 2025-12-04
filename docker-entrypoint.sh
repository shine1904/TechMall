#!/bin/bash
set -e

# Install/update Composer dependencies if needed
if [ ! -d "vendor" ] || [ "composer.json" -nt "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Update database configuration from environment variables
if [ -f "config/database.php" ]; then
    echo "Updating database configuration..."
    sed -i "s/define('DB_HOST', '.*');/define('DB_HOST', '${DB_HOST:-db}');/" config/database.php
    sed -i "s/define('DB_NAME', '.*');/define('DB_NAME', '${DB_NAME:-ecommercedb}');/" config/database.php
    sed -i "s/define('DB_USER', '.*');/define('DB_USER', '${DB_USER:-ecommerce_user}');/" config/database.php
    sed -i "s/define('DB_PASS', '.*');/define('DB_PASS', '${DB_PASS:-ecommerce_pass}');/" config/database.php
    echo "Database configuration updated!"
fi

# Set permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/public/images

# Start Apache
exec apache2-foreground

