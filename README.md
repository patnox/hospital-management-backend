# Hospital Management System
A comprehensive hospital management system built with Laravel 8, Vue.js 3, and MySQL.

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Installation](#installation)
   - [Debian-based Systems](#debian-based-systems)
   - [RedHat-based Systems](#redhat-based-systems)
3. [Configuration](#configuration)
   - [Apache Configuration](#apache-configuration)
   - [MySQL Configuration](#mysql-configuration)
   - [PHP Configuration](#php-configuration)
4. [Deployment Steps](#deployment-steps)
5. [Security Considerations](#security-considerations)
6. [Troubleshooting](#troubleshooting)

## System Requirements
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache 2.4 or higher
- Node.js 14 or higher
- npm 6 or higher

## Installation
### Debian-based Systems
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and required extensions
sudo apt install -y php8.0-fpm php8.0-mysql php8.0-curl php8.0-gd php8.0-mbstring php8.0-xml php8.0-zip

# Install MySQL
sudo apt install -y mysql-server

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs

# Install npm packages
npm install
```

### RedHat-based Systems
```bash
# Update system
sudo dnf update -y

# Install PHP and required extensions
sudo dnf install -y php-fpm php-mysqlnd php-opcache php-gd php-mbstring php-xml php-zip

# Install MySQL
sudo dnf install -y mysql-server

# Install Node.js
curl -fsSL https://rpm.nodesource.com/setup_lts.x | sudo -E bash -
sudo dnf install -y nodejs

# Install npm packages
npm install
```

## Configuration
### Apache Configuration
Create a new Apache configuration file:

```bash
sudo nano /etc/apache2/sites-available/hospital.conf
```

Add the following configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/hospital/public

    <Directory /var/www/hospital/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hospital_error.log
    CustomLog ${APACHE_LOG_DIR}/hospital_access.log combined
</VirtualHost>
```

Enable the configuration:

```bash
sudo a2ensite hospital.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

### MySQL Configuration
Configure MySQL:

```bash
sudo mysql_secure_installation
```

Create database and user:

```sql
CREATE DATABASE hospital_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hospital_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON hospital_management.* TO 'hospital_user'@'localhost';
FLUSH PRIVILEGES;
```

### PHP Configuration
Update PHP configuration:

```bash
sudo nano /etc/php/8.0/fpm/php.ini
```

Adjust these settings:

```ini
upload_max_filesize = 40M
post_max_size = 40M
memory_limit = 512M
max_execution_time = 300
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.0-fpm
```

## Deployment Steps
1. Clone the repository:
```bash
git clone https://your-repository-url.com/hospital-management.git
cd hospital-management
```

2. Install dependencies:
```bash
composer install --optimize-autoloader --no-dev
npm install --production
npm run build
```

3. Configure environment:
```bash
cp .env.example .env
nano .env
```

Update environment variables:
```makefile
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hospital_management
DB_USERNAME=hospital_user
DB_PASSWORD=your_strong_password

SESSION_DRIVER=cookie
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

4. Run migrations:
```bash
php artisan migrate --force
php artisan db:seed --class=HospitalDatabaseSeeder
```

5. Set permissions:
```bash
sudo chown -R www-data:www-data /var/www/hospital
sudo chmod -R 755 /var/www/hospital/storage
```

## Security Considerations
1. Enable SSL:
```bash
sudo certbot --apache
```

2. Configure firewall:
```bash
sudo ufw allow http
sudo ufw allow https
sudo ufw enable
```

3. Set up backup:
```bash
sudo crontab -e
```

Add daily backup:
```bash
0 2 * * * mysqldump -u hospital_user -p'your_strong_password' hospital_management > /var/backups/hospital_$(date +\%Y-\%m-\%d).sql
```

## Troubleshooting
### Common Issues
1. Permission errors:
```bash
sudo chown -R www-data:www-data /var/www/hospital
sudo chmod -R 755 /var/www/hospital/storage
```

2. PHP errors:
```bash
sudo tail -f /var/log/apache2/hospital_error.log
```

3. Database connection:
```bash
mysql -u hospital_user -p hospital_management
```

4. Queue worker:
```bash
php artisan queue:work --tries=3
```

## Maintenance
1. Update dependencies:
```bash
composer update --optimize-autoloader --no-dev
npm update --production
```

2. Clear cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

3. Monitor logs:
```bash
sudo tail -f /var/log/apache2/hospital_error.log
sudo tail -f /var/log/mysql/error.log
```