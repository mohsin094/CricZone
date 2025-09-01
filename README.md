# CricZone - Cricket Live Scores & Updates

A comprehensive cricket application built with Laravel that provides live scores, match details, fixtures, results, and team information.

## 🏗️ Project Structure

### Controllers Organization

The application follows a modular controller structure for better maintainability and separation of concerns:

#### Cricket Controllers (`app/Http/Controllers/Cricket/`)

- **`HomeController`** - Handles the main cricket home page with live matches, today's matches, and upcoming matches
- **`LiveScoreController`** - Manages live score functionality and filtering
- **`FixtureController`** - Handles fixtures display with pagination and filtering
- **`ResultController`** - Manages completed match results
- **`TeamController`** - Handles team listings, details, and synchronization with API
- **`MatchController`** - Manages individual match details and live updates
- **`SearchController`** - Handles search functionality across teams and matches

### Views Organization

#### Partials (`resources/views/partials/`)

- **`navbar.blade.php`** - Reusable navigation bar with mobile responsiveness
- **`footer.blade.php`** - Comprehensive footer with links and social media
- **`page-loader.blade.php`** - Animated page loading component

#### Cricket Views (`resources/views/cricket/`)

- **`index.blade.php`** - Home page with live scores and upcoming matches
- **`live-scores.blade.php`** - Dedicated live scores page
- **`fixtures.blade.php`** - Fixtures with filtering and pagination
- **`results.blade.php`** - Completed match results
- **`teams.blade.php`** - Team listings and information
- **`team-detail.blade.php`** - Individual team details and matches
- **`match-detail.blade.php`** - Comprehensive match information
- **`search.blade.php`** - Search results display

## 🚀 Features

### Core Functionality
- **Live Scores** - Real-time cricket match updates
- **Fixtures** - Upcoming matches with advanced filtering
- **Results** - Completed match results and statistics
- **Teams** - Comprehensive team information and rankings
- **Match Details** - Detailed match analysis and commentary
- **Search** - Search across teams, matches, and leagues

### Technical Features
- **Responsive Design** - Mobile-first approach with Tailwind CSS
- **Real-time Updates** - Live score updates and match progress
- **Advanced Filtering** - Filter matches by league, team, date, and more
- **Pagination** - Efficient data loading for large datasets
- **Caching** - API response caching for better performance
- **Error Handling** - Comprehensive error handling and logging

## 🛠️ Installation

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

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start the application**
   ```bash
   php artisan serve
   ```

## 📱 API Integration

The application integrates with external cricket APIs to provide:
- Live match scores and updates
- Team and player information
- Match statistics and commentary
- Historical data and results

## 🎨 UI/UX Features

- **Modern Design** - Clean, professional cricket-themed interface
- **Responsive Layout** - Optimized for all device sizes
- **Loading States** - Smooth loading animations and transitions
- **Interactive Elements** - Hover effects and smooth transitions
- **Accessibility** - Screen reader friendly with proper ARIA labels

## 🔧 Development

### Code Standards
- **PSR-12** coding standards
- **Laravel Best Practices** for controller and model organization
- **Proper Error Handling** with logging and user-friendly messages
- **Documentation** for all public methods and complex logic

### Testing
- Unit tests for controllers and services
- Feature tests for critical user flows
- API integration tests

## 📊 Performance

- **Caching Strategy** - API responses cached to reduce external calls
- **Database Optimization** - Efficient queries with proper indexing
- **Asset Optimization** - Minified CSS and JavaScript for production
- **Lazy Loading** - Images and content loaded as needed

## 🚀 Deployment

### Production Requirements
- PHP 8.1+
- Laravel 10+
- MySQL 8.0+ or PostgreSQL 13+
- Redis for caching (optional)
- Node.js 16+ for asset compilation

### Environment Variables
```env
APP_NAME=CricZone
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=criczone
DB_USERNAME=username
DB_PASSWORD=password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Contact: info@criczone.pk
- Documentation: [Wiki](link-to-wiki)

## 🙏 Acknowledgments

- Cricket API providers for data
- Laravel community for the excellent framework
- Tailwind CSS for the beautiful UI components
- All contributors and supporters
