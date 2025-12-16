# Laravel Docker Deployment Guide

This directory contains the Docker configuration for the Online Shop Laravel application. This setup is optimized for production deployment on a server.

## 📦 What's Included

- **Dockerfile**: Multi-stage build for optimized production images
- **docker-compose.yml**: Complete stack orchestration
- **nginx/**: Web server configuration
- **php/**: PHP-FPM configuration and optimizations
- **mysql/**: Database configuration
- **entrypoint.sh**: Application startup script

## 🚀 Quick Start

### 1. Prerequisites

Make sure your server has Docker and Docker Compose installed:

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### 2. Environment Configuration

Create your `.env` file from the example:

```bash
cp .env.example .env
```

Edit `.env` and configure the following important variables:

```env
APP_NAME="Online Shop"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=onlineshop
DB_USERNAME=onlineshop_user
DB_PASSWORD=your_secure_password

# Add your other configurations (reCAPTCHA, etc.)
```

### 3. Build and Deploy

Build the Docker images:

```bash
docker-compose build
```

Start all services:

```bash
docker-compose up -d
```

Check container status:

```bash
docker-compose ps
```

### 4. Initial Setup

Run migrations and seeders (first time only):

```bash
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

Create storage symlink (if not already done by entrypoint):

```bash
docker-compose exec app php artisan storage:link
```

## 🔧 Common Commands

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Run Artisan Commands

```bash
docker-compose exec app php artisan [command]

# Examples:
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan queue:work
```

### Access MySQL

```bash
docker-compose exec mysql mysql -u root -p
```

### Restart Services

```bash
# Restart all services
docker-compose restart

# Restart specific service
docker-compose restart app
docker-compose restart nginx
```

### Stop and Remove

```bash
# Stop services
docker-compose stop

# Stop and remove containers (data volumes are preserved)
docker-compose down

# Remove everything including volumes (⚠️ WARNING: This deletes the database!)
docker-compose down -v
```

## 🌐 SSL/HTTPS Configuration

For production, you should set up SSL certificates. Here's how to use Let's Encrypt with Certbot:

### Option 1: Using Certbot with Docker Nginx

1. First, start your services without SSL:

```bash
docker-compose up -d
```

2. Install Certbot on your host machine:

```bash
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx
```

3. Stop nginx container temporarily:

```bash
docker-compose stop nginx
```

4. Generate SSL certificate:

```bash
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com
```

5. Copy certificates to Docker volume:

```bash
sudo mkdir -p docker/nginx/ssl
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/
```

6. Update nginx configuration (`docker/nginx/conf.d/default.conf`) to include SSL:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /var/www/html/public;
    index index.php index.html;

    # ... rest of your configuration
}
```

7. Restart nginx:

```bash
docker-compose up -d nginx
```

### Option 2: Using Reverse Proxy (Recommended)

For production, it's recommended to use a reverse proxy like Nginx or Traefik on the host machine to handle SSL and proxy to Docker containers.

## 📊 Monitoring and Maintenance

### View Resource Usage

```bash
docker stats
```

### Database Backup

```bash
# Backup
docker-compose exec mysql mysqldump -u root -p onlineshop > backup_$(date +%Y%m%d).sql

# Restore
docker-compose exec -T mysql mysql -u root -p onlineshop < backup_20231217.sql
```

### Clear Laravel Caches

```bash
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan optimize
```

## 🔍 Troubleshooting

### Container won't start

Check logs:
```bash
docker-compose logs app
```

### Database connection failed

Ensure database is ready:
```bash
docker-compose exec app php artisan db:show
```

### Permission errors

Fix storage permissions:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Out of memory

Increase Docker memory limit in Docker settings or adjust PHP memory_limit in `docker/php/php.ini`.

## 📁 Directory Structure

```
onlineshop/
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf          # Main nginx config
│   │   ├── conf.d/
│   │   │   └── default.conf    # Virtual host config
│   │   └── ssl/                # SSL certificates
│   ├── php/
│   │   ├── php.ini             # PHP configuration
│   │   └── opcache.ini         # OPcache settings
│   ├── mysql/
│   │   └── my.cnf              # MySQL configuration
│   ├── entrypoint.sh           # Container startup script
│   └── README.md               # This file
├── Dockerfile
├── docker-compose.yml
└── .dockerignore
```

## 🔐 Security Considerations

1. **Environment Variables**: Never commit `.env` file to version control
2. **Database Password**: Use strong passwords for production
3. **APP_KEY**: Generate using `php artisan key:generate`
4. **Debug Mode**: Always set `APP_DEBUG=false` in production
5. **File Permissions**: Storage and cache directories should be writable by www-data
6. **SSL**: Always use HTTPS in production
7. **Firewall**: Only expose ports 80 and 443 to the internet

## 📝 Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper `APP_URL`
- [ ] Set strong database password
- [ ] Generate `APP_KEY`
- [ ] Configure email settings
- [ ] Set up SSL certificates
- [ ] Configure backups
- [ ] Set up monitoring
- [ ] Review and optimize `docker/php/php.ini`
- [ ] Review and optimize `docker/mysql/my.cnf`
- [ ] Test all critical features
- [ ] Set up log rotation

## 🆘 Support

For issues related to:
- **Docker**: Check Docker logs and documentation
- **Laravel**: Check `storage/logs/laravel.log`
- **Nginx**: Check `/var/log/nginx/error.log` in container
- **MySQL**: Check MySQL logs with `docker-compose logs mysql`

---

**Note**: This setup is designed for server deployment only. For local development, continue using your current setup without Docker.
