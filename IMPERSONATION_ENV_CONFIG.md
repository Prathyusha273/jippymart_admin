# 🔧 Impersonation System Environment Configuration

## Required Environment Variables

Add these variables to your `.env` file for the impersonation system to work properly:

### **Core Configuration**
```env
# Restaurant Panel URL (for impersonation redirects)
RESTAURANT_PANEL_URL=http://127.0.0.1:8001

# Firebase Project ID
FIREBASE_PROJECT_ID=jippymart-27c08
```

### **Security Settings**
```env
# Enable/disable security alerts
IMPERSONATION_SECURITY_ALERTS=true

# Security team email for alerts
IMPERSONATION_SECURITY_EMAIL=security@jippymart.in
```

### **Rate Limiting Configuration**
```env
# Maximum impersonation attempts per admin per hour
IMPERSONATION_ADMIN_LIMIT=10

# Maximum impersonation attempts per IP per hour
IMPERSONATION_IP_LIMIT=20

# Maximum global impersonation attempts per hour
IMPERSONATION_GLOBAL_LIMIT=100
```

### **Token Configuration**
```env
# Maximum token expiration time (minutes)
IMPERSONATION_MAX_EXPIRATION=30

# Default token expiration time (minutes)
IMPERSONATION_DEFAULT_EXPIRATION=5

# Token cache duration (minutes)
IMPERSONATION_CACHE_DURATION=10
```

### **Business Hours Monitoring**
```env
# Business hours start (24-hour format)
IMPERSONATION_BUSINESS_START=6

# Business hours end (24-hour format)
IMPERSONATION_BUSINESS_END=22
```

### **Security Monitoring Thresholds**
```env
# Maximum failed attempts before alert
IMPERSONATION_MAX_FAILED_ATTEMPTS=5

# Maximum IP addresses per admin before alert
IMPERSONATION_MAX_IPS_PER_ADMIN=3

# Maximum user agents per admin before alert
IMPERSONATION_MAX_USER_AGENTS_PER_ADMIN=2

# Maximum impersonations of same restaurant per admin per hour
IMPERSONATION_MAX_RESTAURANT_IMPERSONATIONS=10
```

### **Performance Settings**
```env
# Enable query caching
IMPERSONATION_QUERY_CACHE=true

# Cache TTL for restaurant data (seconds)
IMPERSONATION_RESTAURANT_CACHE_TTL=300

# Cache TTL for owner data (seconds)
IMPERSONATION_OWNER_CACHE_TTL=600

# Enable batch operations
IMPERSONATION_BATCH_OPERATIONS=true
```

### **Fallback Settings**
```env
# Enable fallback mechanisms
IMPERSONATION_FALLBACK_ENABLED=true

# Fallback timeout (seconds)
IMPERSONATION_FALLBACK_TIMEOUT=10

# Maximum retry attempts
IMPERSONATION_MAX_RETRIES=3

# Retry delay (seconds)
IMPERSONATION_RETRY_DELAY=1
```

### **Development Settings**
```env
# Enable debug mode for impersonation (development only)
IMPERSONATION_DEBUG=false

# Enable verbose logging (development only)
IMPERSONATION_VERBOSE_LOGGING=false
```

### **Production Settings**
```env
# Enable production security features
IMPERSONATION_PRODUCTION_MODE=true

# Enable strict origin validation
IMPERSONATION_STRICT_ORIGIN=true

# Enable IP whitelist (comma-separated)
IMPERSONATION_IP_WHITELIST=
```

## 🔧 Configuration by Environment

### **Local Development (.env.local)**
```env
RESTAURANT_PANEL_URL=http://127.0.0.1:8001
IMPERSONATION_DEBUG=true
IMPERSONATION_VERBOSE_LOGGING=true
IMPERSONATION_PRODUCTION_MODE=false
IMPERSONATION_STRICT_ORIGIN=false
```

### **Staging (.env.staging)**
```env
RESTAURANT_PANEL_URL=https://staging-restaurant.jippymart.in
IMPERSONATION_DEBUG=false
IMPERSONATION_PRODUCTION_MODE=true
IMPERSONATION_STRICT_ORIGIN=true
```

### **Production (.env.production)**
```env
RESTAURANT_PANEL_URL=https://restaurant.jippymart.in
IMPERSONATION_DEBUG=false
IMPERSONATION_PRODUCTION_MODE=true
IMPERSONATION_STRICT_ORIGIN=true
IMPERSONATION_SECURITY_ALERTS=true
IMPERSONATION_SECURITY_EMAIL=security@jippymart.in
```

## 🚨 Critical Security Variables

These are the **MOST IMPORTANT** variables for security:

```env
# CRITICAL: Rate limiting
IMPERSONATION_ADMIN_LIMIT=10
IMPERSONATION_IP_LIMIT=20
IMPERSONATION_GLOBAL_LIMIT=100

# CRITICAL: Security monitoring
IMPERSONATION_SECURITY_ALERTS=true
IMPERSONATION_SECURITY_EMAIL=security@jippymart.in
IMPERSONATION_MAX_FAILED_ATTEMPTS=5

# CRITICAL: Token security
IMPERSONATION_MAX_EXPIRATION=30
IMPERSONATION_DEFAULT_EXPIRATION=5
```

## 📋 Quick Setup Checklist

- [ ] Add `RESTAURANT_PANEL_URL` for your environment
- [ ] Set `IMPERSONATION_SECURITY_EMAIL` to your security team
- [ ] Configure rate limits based on your needs
- [ ] Set business hours for monitoring
- [ ] Enable security alerts in production
- [ ] Configure cache settings for performance
- [ ] Set fallback timeouts and retry limits

## 🔍 Testing Your Configuration

After adding these variables, test with:

```bash
# Check if configuration is loaded
php artisan tinker
>>> config('impersonation.security_alerts_enabled')
>>> config('impersonation.rate_limits.admin_per_hour')

# Test impersonation endpoint
curl -X POST http://127.0.0.1:8000/admin/impersonate/generate-token \
  -H "Content-Type: application/json" \
  -d '{"restaurant_id":"test","_token":"your_csrf_token"}'
```

## ⚠️ Security Notes

1. **Never commit sensitive values** to version control
2. **Use different values** for each environment
3. **Monitor security alerts** regularly
4. **Review rate limits** based on usage patterns
5. **Update security email** to your team's address
6. **Test fallback mechanisms** in staging environment
