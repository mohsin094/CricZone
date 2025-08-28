# 🏏 CricZone.pk - All Cricket in One Zone

A modern, mobile-responsive Laravel web application delivering live cricket scores and related content using API-Cricket.com. Built with Laravel 12, Blade templates, and Tailwind CSS.

## ✨ Features

- **Live Cricket Scores**: Real-time updates from matches around the world
- **Match Details**: Comprehensive scorecards, commentary, and statistics
- **Fixtures & Results**: Upcoming matches and completed game outcomes
- **Teams & Leagues**: Detailed information about cricket teams and competitions
- **Mobile Responsive**: Optimized for all devices
- **SEO Optimized**: Search engine friendly with meta tags and clean URLs
- **AdSense Ready**: Integrated Google AdSense support
- **Performance Optimized**: Redis caching and Laravel optimization

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL
- Redis (optional, for caching)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd CricZone
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your environment variables**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=criczone
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   CRICKET_API_KEY=3d561c5a8927413649b8f4edd0cbbb9003e6cd0efce493c98c1211d9660aeaf4
   CRICKET_BASE_URL=https://apiv2.api-cricket.com/cricket/
   CRICKET_CACHE_TTL=60
   
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

5. **Run database migrations**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## 🔧 Configuration

### Cricket API

The application uses API-Cricket.com for live cricket data. Your API key is already configured in the services config.

**API Endpoints Available:**
- Leagues
- Fixtures
- Live Scores
- Match Details
- Teams
- Standings
- Head-to-Head Statistics
- Odds

### Caching Strategy

- **Live Scores**: 60 seconds (configurable)
- **Match Details**: 5 minutes
- **Teams & Leagues**: 1 hour
- **Standings**: 30 minutes

### Performance Optimization

The application includes several performance optimizations:

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## 📱 Available Routes

| Route | Description |
|-------|-------------|
| `/` | Home page with live matches and quick actions |
| `/cricket/live-scores` | All live cricket matches |
| `/cricket/fixtures` | Upcoming matches |
| `/cricket/results` | Completed matches |
| `/cricket/teams` | All cricket teams |
| `/cricket/leagues` | Cricket leagues and tournaments |
| `/cricket/match/{id}` | Detailed match information |
| `/cricket/search` | Search teams, matches, and leagues |

## 🗄️ Database Structure

### Tables

- **cricket_matches**: Match data from API
- **leagues**: League information
- **teams**: Team details
- **news**: Cricket news articles
- **users**: User management (Laravel default)

### Data Sync

Sync cricket data from the API:

```bash
# Sync all data
php artisan cricket:sync

# Sync specific data types
php artisan cricket:sync --type=leagues
php artisan cricket:sync --type=teams
php artisan cricket:sync --type=matches
```

## 🎨 Customization

### Styling

The application uses Tailwind CSS. Customize the design by modifying:

- `resources/css/app.css`
- `resources/views/layouts/app.blade.php`
- Individual view files in `resources/views/cricket/`

### AdSense Integration

Replace the placeholder in `resources/views/layouts/app.blade.php`:

```html
<!-- Replace ca-pub-XXXXXXXXXX with your actual AdSense publisher ID -->
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXXXX" crossorigin="anonymous"></script>
```

## 📊 Monitoring & Maintenance

### Cache Management

```bash
# Clear all caches
php artisan cache:clear

# Clear specific cricket API caches
php artisan tinker
>>> app('App\Services\CricketApiService')->clearCache();
```

### Logs

Monitor API calls and errors in:
- `storage/logs/laravel.log`

### Performance Monitoring

Track cache hit rates and API usage through Laravel's built-in monitoring tools.

## 🔒 Security

- CSRF protection enabled
- Input validation and sanitization
- Secure API key storage
- Rate limiting (can be configured)

## 🌐 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Configure production database
- [ ] Set up Redis for caching
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up SSL certificate
- [ ] Configure CDN for static assets
- [ ] Set up monitoring and logging

### Server Requirements

- PHP 8.2+
- MySQL 8.0+ or PostgreSQL 13+
- Redis 6.0+ (recommended)
- Nginx or Apache
- SSL certificate

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:

- Create an issue in the repository
- Check the Laravel documentation
- Review API-Cricket.com documentation

## 🚀 Future Enhancements

- [ ] Mobile app development
- [ ] Push notifications
- [ ] User accounts and favorites
- [ ] Advanced statistics and analytics
- [ ] Social media integration
- [ ] Multi-language support

---

**Built with ❤️ using Laravel, Blade, and Tailwind CSS**

*CricZone.pk - All cricket in one zone*
