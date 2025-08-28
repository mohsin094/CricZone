# 🚀 CricZone.pk Installation Guide

## Environment Setup

Create a `.env` file in your project root with the following variables:

```env
APP_NAME="CricZone.pk"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=criczone
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Cricket API Configuration
CRICKET_API_KEY=3d561c5a8927413649b8f4edd0cbbb9003e6cd0efce493c98c1211d9660aeaf4
CRICKET_BASE_URL=https://apiv2.api-cricket.com/cricket/
CRICKET_CACHE_TTL=60

# Google AdSense (replace with your actual publisher ID)
ADSENSE_PUBLISHER_ID=ca-pub-XXXXXXXXXX
```

## Quick Installation Steps

1. **Copy environment file**
   ```bash
   cp .env.example .env
   # OR manually create .env with the content above
   ```

2. **Generate application key**
   ```bash
   php artisan key:generate
   ```

3. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

4. **Create database**
   ```bash
   # Create a database named 'criczone' in your MySQL/PostgreSQL
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Test the API**
   ```bash
   php artisan cricket:sync
   ```

8. **Start the server**
   ```bash
   php artisan serve
   ```

## Database Setup

The application will create the following tables:
- `cricket_matches` - Match data from API
- `leagues` - League information
- `teams` - Team details
- `news` - Cricket news articles
- `users` - User management (Laravel default)

## API Testing

Test if your cricket API is working:

```bash
php artisan tinker
>>> app('App\Services\CricketApiService')->getLeagues();
```

This should return an array of leagues if the API is working correctly.

## Troubleshooting

### Common Issues

1. **API Key Error**: Make sure your cricket API key is correct
2. **Database Connection**: Verify database credentials in `.env`
3. **Cache Issues**: Run `php artisan cache:clear`
4. **Route Issues**: Run `php artisan route:clear`

### Performance Tips

1. **Enable Redis caching** for better performance
2. **Run optimizations**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Set up a cron job** for data sync:
   ```bash
   # Add to crontab
   */5 * * * * cd /path/to/criczone && php artisan cricket:sync
   ```

## Next Steps

After installation:
1. Customize the design in `resources/views/`
2. Set up your Google AdSense account
3. Configure your domain and SSL
4. Set up monitoring and logging
5. Deploy to production

## Support

If you encounter issues:
1. Check the Laravel logs in `storage/logs/`
2. Verify your API key is working
3. Check database connectivity
4. Review the README.md for more details

