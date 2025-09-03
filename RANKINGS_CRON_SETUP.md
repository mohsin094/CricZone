# ICC Rankings Cron Job Setup

## Overview
This document explains the automated ICC rankings update system that runs every 3 days to fetch fresh data from the Cricbuzz API and store it in the database.

## System Architecture

### 1. Cron Job Schedule
- **Frequency**: Every 3 days at 2:00 AM
- **Command**: `php artisan rankings:update`
- **Schedule**: `0 2 */3 * *` (cron expression)

### 2. Data Flow
```
Cricbuzz API → RankingService → Database → Frontend Display
```

### 3. Components

#### A. Console Command
- **File**: `app/Console/Commands/UpdateRankingsCommand.php`
- **Signature**: `rankings:update`
- **Options**:
  - `--force`: Force update even if not needed
  - `--category`: Update specific category (men/women)
  - `--type`: Update specific type (team/batter/bowler/all_rounder)
  - `--format`: Update specific format (odi/t20/test)

#### B. Ranking Service
- **File**: `app/Services/RankingService.php`
- **Methods**:
  - `updateAllRankings()`: Updates all rankings
  - `updateTeamRankingsForFormat()`: Updates team rankings for specific format
  - `updatePlayerRankingsForType()`: Updates player rankings for specific type

#### C. Database Tables
- **Team Rankings**: `team_rankings`
- **Player Rankings**: `player_rankings`

## Setup Instructions

### 1. Verify Cron Job is Scheduled
Check `routes/console.php`:
```php
// Schedule the rankings update command to run every 3 days at 2 AM
Schedule::command('rankings:update')->cron('0 2 */3 * *');
```

### 2. Ensure Laravel Scheduler is Running
Add to your server's crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Test the Command Manually
```bash
# Update all rankings
php artisan rankings:update

# Update specific rankings
php artisan rankings:update --category=men --type=batter --format=odi

# Force update
php artisan rankings:update --force
```

## API Endpoints

### 1. Manual Update (for testing)
- **URL**: `POST /rankings/update`
- **Parameters**:
  - `force` (optional): Force update
  - `category` (optional): men/women
  - `type` (optional): team/batter/bowler/all_rounder
  - `format` (optional): odi/t20/test

### 2. Get Rankings Data
- **URL**: `GET /rankings/data`
- **Parameters**:
  - `category`: men/women
  - `type`: team/batter/bowler/all_rounder



## Data Storage

### Team Rankings Table
```sql
CREATE TABLE team_rankings (
    id BIGINT PRIMARY KEY,
    category VARCHAR(10),
    format VARCHAR(10),
    team_name VARCHAR(100),
    team_code VARCHAR(10),
    team_flag_url VARCHAR(255),
    rank INT,
    rating INT,
    points INT,
    metadata JSON,
    last_updated TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Player Rankings Table
```sql
CREATE TABLE player_rankings (
    id BIGINT PRIMARY KEY,
    category VARCHAR(10),
    type VARCHAR(20),
    format VARCHAR(10),
    player_name VARCHAR(100),
    team_name VARCHAR(100),
    team_code VARCHAR(10),
    player_image_url VARCHAR(255),
    rank INT,
    rating INT,
    points INT,
    statistics JSON,
    metadata JSON,
    last_updated TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Frontend Integration

### 1. Data Source Priority
1. **Database**: Primary source (updated every 3 days)
2. **Mock Data**: Fallback when database is empty

### 2. View Implementation
The frontend automatically:
- Fetches data from database via `RankingService`
- Falls back to mock data if database is empty
- Displays rankings in 3-column layout (ODI, T20, Test)
- Supports expand/collapse functionality

## Monitoring and Logging

### 1. Log Files
- **Location**: `storage/logs/laravel.log`
- **Events Logged**:
  - Successful updates
  - API errors
  - Database errors
  - Command execution

### 2. Monitoring Commands
```bash
# Check last update time
php artisan rankings:stats

# View logs
tail -f storage/logs/laravel.log | grep "ranking"

# Test API connection
php artisan rankings:test
```

## Error Handling

### 1. API Failures
- Logs error and continues with other updates
- Falls back to existing database data
- Uses mock data if database is empty

### 2. Database Errors
- Logs error details
- Continues with other ranking updates
- Maintains data integrity

### 3. Network Issues
- Retries failed requests
- Logs network errors
- Graceful degradation

## Performance Optimization

### 1. Batch Processing
- Updates all rankings in single command
- Processes categories/types in parallel
- Efficient database operations

### 2. Performance
- Database queries are optimized
- API responses are fetched directly when needed
- Reduces server load

### 3. Data Cleanup
- Old rankings are replaced (not duplicated)
- Efficient storage usage
- Regular cleanup of expired data

## Troubleshooting

### 1. Cron Job Not Running
```bash
# Check if Laravel scheduler is running
php artisan schedule:list

# Test command manually
php artisan rankings:update --force

# Check server crontab
crontab -l
```

### 2. API Issues
```bash
# Test API connection
php artisan rankings:test

# Check API credentials
# Verify in .env file
```

### 3. Database Issues
```bash
# Check database connection
php artisan migrate:status

# Verify tables exist
php artisan tinker
>>> \App\Models\TeamRanking::count()
>>> \App\Models\PlayerRanking::count()
```

## Security Considerations

### 1. API Keys
- Store in `.env` file
- Never commit to version control
- Rotate regularly

### 2. Rate Limiting
- Respects API rate limits
- Implements backoff strategies
- Monitors usage

### 3. Data Validation
- Validates all incoming data
- Sanitizes before database storage
- Prevents injection attacks

## Maintenance

### 1. Regular Tasks
- Monitor log files
- Check API quota usage
- Verify data accuracy
- Update API credentials

### 2. Backup Strategy
- Database backups before updates
- API response caching
- Error recovery procedures

### 3. Updates
- Keep Laravel updated
- Monitor API changes
- Update service as needed

## Support

For issues or questions:
1. Check log files first
2. Test commands manually
3. Verify API connectivity
4. Check database status
5. Review this documentation

---

**Last Updated**: {{ date('Y-m-d H:i:s') }}
**Version**: 1.0
