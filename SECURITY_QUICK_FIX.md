# 🚨 SECURITY QUICK FIX GUIDE

## ⚡ IMMEDIATE ACTIONS (Do Right Now!)

### Step 1: Remove Sensitive Files from Git
```bash
# Remove .env files from git tracking
git rm --cached .env
git rm --cached .env.docker

# Commit the removal
git commit -m "Security: Remove sensitive environment files"

# Push to remote
git push origin main
```

### Step 2: Regenerate Application Key
```bash
# Generate new APP_KEY
php artisan key:generate

# This will update your .env file automatically
```

### Step 3: Change Database Password
```sql
-- Connect to MySQL
mysql -u root -p

-- Change password
ALTER USER 'bernand'@'localhost' IDENTIFIED BY 'NewSecurePassword123!@#';
ALTER USER 'bernand'@'%' IDENTIFIED BY 'NewSecurePassword123!@#';
FLUSH PRIVILEGES;
```

Then update your `.env` file:
```env
DB_PASSWORD=NewSecurePassword123!@#
```

### Step 4: Disable Debug Mode
Update your production `.env`:
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
```

### Step 5: Clear and Optimize Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
```

---

## ✅ VERIFICATION CHECKLIST

After applying fixes, verify:

- [ ] `.env` and `.env.docker` are in `.gitignore`
- [ ] `.env` files are not in git history
- [ ] New APP_KEY is generated
- [ ] Database password is changed
- [ ] APP_DEBUG=false in production
- [ ] Security headers are added
- [ ] Rate limiting is enabled on login
- [ ] .htaccess blocks .env access
- [ ] Nginx blocks sensitive files
- [ ] HTTPS is enforced

---

## 🧪 SECURITY TESTS

### Test 1: Check .env Access
```bash
# Should return 403 or 404
curl https://app-staging.eurokars.co.id/.env
curl https://app-staging.eurokars.co.id/.env.docker
```

### Test 2: Check Security Headers
```bash
curl -I https://app-staging.eurokars.co.id/
```

Should see:
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Strict-Transport-Security: max-age=31536000`
- `Referrer-Policy: strict-origin-when-cross-origin`

### Test 3: Check Rate Limiting
Try logging in with wrong password 6 times - should be blocked.

### Test 4: Check Debug Mode
Visit a non-existent page - should NOT show stack trace.

---

## 📝 DEPLOYMENT CHECKLIST

Before deploying to production:

1. **Environment**
   - [ ] APP_ENV=production
   - [ ] APP_DEBUG=false
   - [ ] APP_KEY is set and unique
   - [ ] Strong database password
   - [ ] SESSION_ENCRYPT=true
   - [ ] SESSION_SECURE_COOKIE=true

2. **Security**
   - [ ] HTTPS enabled
   - [ ] Security headers configured
   - [ ] Rate limiting enabled
   - [ ] .env files protected
   - [ ] File upload validation

3. **Infrastructure**
   - [ ] Firewall configured
   - [ ] Database access restricted
   - [ ] Backups configured
   - [ ] Monitoring enabled
   - [ ] Logs configured

4. **Code**
   - [ ] Dependencies updated
   - [ ] No hardcoded secrets
   - [ ] SQL injection prevention
   - [ ] XSS prevention
   - [ ] CSRF protection enabled

---

## 🔄 REGULAR MAINTENANCE

### Weekly
- Review application logs
- Check for failed login attempts
- Monitor disk space

### Monthly
- Update dependencies: `composer update && npm update`
- Review user permissions
- Check security advisories

### Quarterly
- Rotate APP_KEY
- Security audit
- Penetration testing
- Disaster recovery test

---

## 🆘 INCIDENT RESPONSE

If you suspect a security breach:

1. **Immediate**
   - Take application offline
   - Change all passwords
   - Rotate APP_KEY
   - Review logs

2. **Investigation**
   - Check access logs
   - Review database changes
   - Identify attack vector
   - Document findings

3. **Recovery**
   - Restore from backup if needed
   - Apply security patches
   - Update credentials
   - Notify affected users

4. **Prevention**
   - Fix vulnerability
   - Update security measures
   - Train team
   - Document lessons learned

---

## 📞 SUPPORT

For security issues:
- Email: security@eurokars.co.id
- Emergency: Contact IT Team immediately

**Remember: Security is not a one-time task, it's an ongoing process!**
