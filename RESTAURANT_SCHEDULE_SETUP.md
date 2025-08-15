# Restaurant Auto Schedule Setup Guide

## Overview
This guide explains how to set up reliable server-side auto-scheduling for restaurant opening/closing times.

## Why Server-Side Scheduling?
- **Reliable**: Works even when browser is closed
- **Accurate**: Server time is more precise than client time
- **Scalable**: Handles multiple users without conflicts
- **Persistent**: Continues working after page refreshes

## Setup Instructions

### 1. Set up Cron Job (Required)
Add this to your server's crontab to run Laravel scheduler every minute:

```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /path/to/your/laravel/project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Test the Command
Test the scheduling command manually:

```bash
# Check current schedule
php artisan restaurants:auto-schedule check

# Manually open all restaurants
php artisan restaurants:auto-schedule open

# Manually close all restaurants
php artisan restaurants:auto-schedule close
```

### 3. Configure Schedule Times
The default schedule is:
- **Open**: 9:30 AM
- **Close**: 10:30 PM
- **Timezone**: Asia/Kolkata (IST)

To change these times, edit the `getSchedule()` method in:
`app/Console/Commands/RestaurantAutoSchedule.php`

### 4. Monitor Logs
Check the schedule logs:
```bash
tail -f storage/logs/restaurant-schedule.log
```

## How It Works

### Server-Side Process
1. **Cron Job** runs every minute
2. **Laravel Scheduler** executes the command
3. **Command checks** current time vs schedule
4. **Firestore updates** restaurant status
5. **Logs** all actions

### Frontend Integration
- **UI Updates**: Shows next scheduled action
- **Real-time Status**: Displays current schedule state
- **Manual Override**: Can manually trigger open/close
- **Configuration**: Easy schedule setup through modal

## Troubleshooting

### Schedule Not Working?
1. **Check Cron**: Verify cron job is running
   ```bash
   crontab -l
   ```

2. **Check Logs**: Look for errors
   ```bash
   tail -f storage/logs/restaurant-schedule.log
   ```

3. **Test Command**: Run manually
   ```bash
   php artisan restaurants:auto-schedule check
   ```

4. **Check Permissions**: Ensure Laravel can write logs
   ```bash
   chmod -R 775 storage/logs
   ```

### Timezone Issues?
1. **Server Timezone**: Check server timezone
   ```bash
   date
   ```

2. **Laravel Timezone**: Verify in `config/app.php`
   ```php
   'timezone' => 'Asia/Kolkata',
   ```

3. **Schedule Timezone**: Check in command
   ```php
   ->timezone('Asia/Kolkata')
   ```

## API Endpoints

### Get Schedule
```
GET /restaurants/schedule
```

### Update Schedule
```
POST /restaurants/schedule
{
    "enabled": true,
    "open_time": "09:30",
    "close_time": "22:30",
    "timezone": "Asia/Kolkata"
}
```

### Get Next Action
```
GET /restaurants/schedule/next-action
```

### Manual Trigger
```
POST /restaurants/schedule/trigger
{
    "action": "open" // or "close"
}
```

### Get Status
```
GET /restaurants/schedule/status
```

## Security Notes

1. **Authentication**: All endpoints require restaurant permissions
2. **Validation**: Input is validated server-side
3. **Logging**: All actions are logged for audit
4. **Rate Limiting**: Consider adding rate limiting for manual triggers

## Performance Considerations

1. **Batch Updates**: Firestore updates are batched (500 per batch)
2. **Background Processing**: Commands run in background
3. **Caching**: Schedule config is cached
4. **Log Rotation**: Logs are automatically rotated

## Customization

### Different Times for Different Days
Modify the `getSchedule()` method to return different times based on day:

```php
private function getSchedule()
{
    $dayOfWeek = now()->dayOfWeek;
    
    if ($dayOfWeek == 0) { // Sunday
        return [
            'open_time' => '10:00',
            'close_time' => '21:00',
            'timezone' => 'Asia/Kolkata'
        ];
    }
    
    return [
        'open_time' => '09:30',
        'close_time' => '22:30',
        'timezone' => 'Asia/Kolkata'
    ];
}
```

### Database Storage
Store schedule in database instead of config:

```php
private function getSchedule()
{
    return DB::table('restaurant_schedules')
        ->where('active', true)
        ->first()
        ->toArray();
}
```

## Support

If you encounter issues:
1. Check the logs first
2. Test commands manually
3. Verify cron job is running
4. Check server timezone settings
