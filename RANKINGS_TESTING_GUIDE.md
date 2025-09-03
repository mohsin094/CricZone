# ICC Rankings System - Testing & Usage Guide

## 🚀 Quick Start

### 1. **Access the Rankings Page**
```
http://your-domain.com/rankings
```

### 2. **Navigate Rankings**
- **Category Tabs**: Men / Women
- **Type Tabs**: Team / Batter / Bowler / All Rounder
- **Format Columns**: ODI / T20 / Test (displayed side by side)

---

## 🧪 Testing the System

### **A. Manual Testing via Browser**

#### 1. **Test All Ranking Types**
```
# Team Rankings
http://your-domain.com/rankings?category=men&type=team
http://your-domain.com/rankings?category=women&type=team

# Player Rankings
http://your-domain.com/rankings?category=men&type=batter
http://your-domain.com/rankings?category=men&type=bowler
http://your-domain.com/rankings?category=men&type=all_rounder

http://your-domain.com/rankings?category=women&type=batter
http://your-domain.com/rankings?category=women&type=bowler
http://your-domain.com/rankings?category=women&type=all_rounder
```

#### 2. **Test Expand/Collapse Functionality**
- Click "View Full List >" on any format card
- Verify it shows all rankings (up to 50)
- Click "Show Less <" to collapse back to top 10

#### 3. **Test Responsive Design**
- Test on desktop (3 columns)
- Test on mobile (1 column)
- Verify all data displays correctly

---

### **B. Command Line Testing**

#### 1. **Test Rankings Update Command**
```bash
# Navigate to project directory
cd /path/to/your/project

# Test manual update (all rankings)
php artisan rankings:update

# Test specific category/type/format
php artisan rankings:update --category=men --type=batter --format=odi

# Force update (even if not needed)
php artisan rankings:update --force

# Test specific team rankings
php artisan rankings:update --category=women --type=team --format=t20
```

#### 2. **Test API Integration**
```bash
# Test API connection and data fetching
php artisan rankings:test
```

#### 3. **Check Database Status**
```bash
# Check if tables exist and have data
php artisan tinker

# In tinker, run:
>>> \App\Models\TeamRanking::count()
>>> \App\Models\PlayerRanking::count()
>>> \App\Models\TeamRanking::where('category', 'men')->where('format', 'odi')->count()
>>> \App\Models\PlayerRanking::where('category', 'men')->where('type', 'batter')->where('format', 'odi')->count()
```

---

### **C. API Endpoint Testing**

#### 1. **Manual Update Endpoint**
```bash
# Update all rankings
curl -X POST http://your-domain.com/rankings/update

# Update specific rankings
curl -X POST http://your-domain.com/rankings/update \
  -d "category=men&type=batter&format=odi"

# Force update
curl -X POST http://your-domain.com/rankings/update \
  -d "force=true"
```

#### 2. **Statistics Endpoint**
```bash
# Get ranking statistics
curl http://your-domain.com/rankings/stats
```

---

## 🔧 Development & Debugging

### **A. Check Logs**
```bash
# View recent logs
tail -f storage/logs/laravel.log

# Filter ranking-related logs
tail -f storage/logs/laravel.log | grep -i ranking

# Check for errors
grep -i "error\|exception" storage/logs/laravel.log | tail -20
```

### **B. Database Inspection**
```bash
# Check database connection
php artisan migrate:status

# View table structure
php artisan tinker
>>> Schema::getColumnListing('team_rankings')
>>> Schema::getColumnListing('player_rankings')

# Check recent data
>>> \App\Models\TeamRanking::latest()->take(5)->get()
>>> \App\Models\PlayerRanking::latest()->take(5)->get()
```

### **C. Environment Configuration**
```bash
# Check API configuration
php artisan tinker
>>> config('services.cricbuzz.api_key')
>>> config('services.cricbuzz.base_url')

# Verify .env file has correct settings
cat .env | grep CRICBUZZ
```

---

## 📊 Monitoring & Maintenance

### **A. Cron Job Monitoring**

#### 1. **Check if Cron is Running**
```bash
# Check server crontab
crontab -l

# Check Laravel scheduler
php artisan schedule:list

# Test scheduler manually
php artisan schedule:run
```

#### 2. **Monitor Cron Execution**
```bash
# Check cron logs (varies by system)
tail -f /var/log/cron
# or
tail -f /var/log/syslog | grep CRON
```

### **B. Data Quality Checks**

#### 1. **Verify Data Completeness**
```bash
php artisan tinker

# Check if all categories/types/formats have data
>>> $categories = ['men', 'women'];
>>> $formats = ['odi', 't20', 'test'];
>>> $types = ['batter', 'bowler', 'all_rounder'];

# Team rankings
foreach($categories as $cat) {
    foreach($formats as $format) {
        $count = \App\Models\TeamRanking::where('category', $cat)->where('format', $format)->count();
        echo "{$cat} {$format}: {$count} teams\n";
    }
}

# Player rankings
foreach($categories as $cat) {
    foreach($types as $type) {
        foreach($formats as $format) {
            $count = \App\Models\PlayerRanking::where('category', $cat)->where('type', $type)->where('format', $format)->count();
            echo "{$cat} {$type} {$format}: {$count} players\n";
        }
    }
}
```

#### 2. **Check Data Freshness**
```bash
php artisan tinker

# Check last update times
>>> \App\Models\TeamRanking::latest('last_updated')->first()->last_updated
>>> \App\Models\PlayerRanking::latest('last_updated')->first()->last_updated

# Check if data is older than 3 days
>>> \App\Models\TeamRanking::where('last_updated', '<', now()->subDays(3))->count()
```

---

## 🐛 Troubleshooting

### **A. Common Issues**

#### 1. **No Data Showing**
```bash
# Check if database has data
php artisan tinker
>>> \App\Models\TeamRanking::count()
>>> \App\Models\PlayerRanking::count()

# If no data, run manual update
php artisan rankings:update --force
```

#### 2. **API Errors**
```bash
# Check API credentials
cat .env | grep CRICBUZZ

# Test API connection
php artisan rankings:test

# Check logs for API errors
tail -f storage/logs/laravel.log | grep -i "api\|cricbuzz"
```

#### 3. **Cron Job Not Running**
```bash
# Check if Laravel scheduler is in crontab
crontab -l | grep "schedule:run"

# If not present, add it:
# * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# Test scheduler manually
php artisan schedule:run
```

#### 4. **Database Connection Issues**
```bash
# Test database connection
php artisan migrate:status

# Check database configuration
php artisan tinker
>>> config('database.default')
>>> config('database.connections.mysql')
```

### **B. Performance Issues**

#### 1. **Slow Page Loading**
```bash
# Check if there's too much data
php artisan tinker
>>> \App\Models\TeamRanking::count()
>>> \App\Models\PlayerRanking::count()

# If too much data, consider cleanup
>>> \App\Models\TeamRanking::where('last_updated', '<', now()->subDays(30))->delete()
```

#### 2. **Memory Issues**
```bash
# Check PHP memory limit
php -i | grep memory_limit

# Increase if needed in php.ini
memory_limit = 256M
```

---

## 🔄 Workflow for Daily Operations

### **A. Morning Checklist**
1. ✅ Check if rankings updated overnight
2. ✅ Verify no errors in logs
3. ✅ Test a few ranking pages manually
4. ✅ Check API quota usage

### **B. Weekly Maintenance**
1. ✅ Review logs for any recurring issues
2. ✅ Check database size and cleanup if needed
3. ✅ Verify cron job is still running
4. ✅ Test all ranking types and formats

### **C. Monthly Tasks**
1. ✅ Update API credentials if needed
2. ✅ Review and optimize database queries
3. ✅ Check for Laravel/PHP updates
4. ✅ Backup database

---

## 📱 User Experience Testing

### **A. Frontend Testing Checklist**
- [ ] All category tabs work (Men/Women)
- [ ] All type tabs work (Team/Batter/Bowler/All Rounder)
- [ ] All format columns display data (ODI/T20/Test)
- [ ] Expand/collapse functionality works
- [ ] Responsive design works on mobile
- [ ] Images and flags load correctly
- [ ] No JavaScript errors in console
- [ ] Page loads quickly

### **B. Data Validation**
- [ ] Rankings are in correct order (1, 2, 3...)
- [ ] Team/player names are correct
- [ ] Ratings are reasonable numbers
- [ ] Flags and images display properly
- [ ] No missing or broken data

---

## 🚨 Emergency Procedures

### **A. If Rankings Stop Updating**
1. Check cron job status
2. Run manual update: `php artisan rankings:update --force`
3. Check API credentials and quota
4. Review error logs

### **B. If Website Shows Errors**
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Verify database connection
3. Check if tables exist
4. Run migrations if needed: `php artisan migrate`

### **C. If API Quota Exceeded**
1. Check API usage dashboard
2. Wait for quota reset or upgrade plan
3. Use existing database data until quota resets
4. Consider reducing update frequency

---

## 📞 Support & Resources

### **A. Useful Commands**
```bash
# Clear Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check system status
php artisan about

# Database operations
php artisan migrate:status
php artisan db:show
```

### **B. Log Locations**
- **Laravel Logs**: `storage/logs/laravel.log`
- **Cron Logs**: `/var/log/cron` or `/var/log/syslog`
- **Web Server Logs**: `/var/log/apache2/` or `/var/log/nginx/`

### **C. Configuration Files**
- **Environment**: `.env`
- **Database**: `config/database.php`
- **Services**: `config/services.php`
- **Scheduler**: `routes/console.php`

---

**Last Updated**: {{ date('Y-m-d H:i:s') }}
**Version**: 1.0
