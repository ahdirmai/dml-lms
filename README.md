# DML LMS (Learning Management System)

A robust and modern Learning Management System built with Laravel 12.

## 🛠 Tech Stack

-   **Framework:** [Laravel 12](https://laravel.com)
-   **Frontend:** [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev), [Vite](https://vitejs.dev)
-   **Database:** MySQL / MariaDB / SQLite
-   **PDF Generation:** [dompdf](https://github.com/barryvdh/laravel-dompdf)
-   **Excel:** [Laravel Excel](https://laravel-excel.com)
-   **Permissions:** [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

## 📋 Requirements

Ensure your server meets the following requirements:

-   **PHP**: ^8.2
-   **Composer**
-   **Node.js** & **NPM**
-   **Database**: MySQL, MariaDB, or SQLite

## 🚀 Installation (Local Development)

Follow these steps to set up the project locally:

1.  **Clone the Repository**

    ```bash
    git clone https://github.com/yourusername/dml-lms-fix.git
    cd dml-lms-fix
    ```

2.  **Install PHP Dependencies**

    ```bash
    composer install
    ```

3.  **Install NPM Dependencies**

    ```bash
    npm install
    ```

4.  **Environment Configuration**
    Copy the example environment file and configure your database settings.

    ```bash
    cp .env.example .env
    ```

    Open `.env` and update your database credentials:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

5.  **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

6.  **Run Migrations & Seeders**
    Create the database tables and populate them with initial data.

    ```bash
    php artisan migrate --seed
    ```

7.  **Build Assets**

    ```bash
    npm run build
    ```

    Or for development with hot reload:

    ```bash
    npm run dev
    ```

8.  **Serve the Application**
    ```bash
    php artisan serve
    ```
    Visit `http://localhost:8000` in your browser.

---

## 🌍 Deployment Guide (Production)

This guide assumes you are deploying to a Linux server (Ubuntu/Debian) with **Nginx**, **PHP-FPM**, and **MySQL/MariaDB**.

### 1. Server Setup

Ensure your server has the required software installed:

```bash
sudo apt update
sudo apt install nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl php8.2-xml php8.2-mbstring php8.2-zip unzip git supervisor
```

### 2. Clone & Install

Navigate to your web directory and clone the project:

```bash
cd /var/www
sudo git clone https://github.com/yourusername/dml-lms-fix.git
cd dml-lms-fix
```

Install dependencies (optimize for production):

```bash
sudo composer install --optimize-autoloader --no-dev
sudo npm install
sudo npm run build
```

### 3. Permissions

Set proper permissions for the web server user (`www-data`):

```bash
sudo chown -R www-data:www-data /var/www/dml-lms-fix
sudo chmod -R 775 /var/www/dml-lms-fix/storage
sudo chmod -R 775 /var/www/dml-lms-fix/bootstrap/cache
```

### 4. Environment Configuration

```bash
sudo cp .env.example .env
sudo nano .env
```

Update the following for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
...
```

Generate the key:

```bash
sudo php artisan key:generate
```

### 5. Database Migration

```bash
sudo php artisan migrate --force
```

_(Note: Use `--seed` only if this is a fresh install and you need initial data)_

### 6. Nginx Configuration

Create a new Nginx site configuration:

```bash
sudo nano /etc/nginx/sites-available/dml-lms
```

Paste the following configuration (adjust `server_name` and paths):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/dml-lms-fix/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/dml-lms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. Optimization

Run these commands to cache configuration and routes for better performance:

```bash
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

### 8. Supervisor (Optional - For Queues)

If your application uses queues, set up Supervisor to keep the queue worker running.

```bash
sudo nano /etc/supervisor/conf.d/dml-lms-worker.conf
```

Content:

```ini
[program:dml-lms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/dml-lms-fix/artisan queue:work sqs --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/dml-lms-fix/storage/logs/worker.log
stopwaitsecs=3600
```

Start Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start dml-lms-worker:*
```

## 🤝 Contributing

Contributions are welcome! Please fork the repository and submit a pull request.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
