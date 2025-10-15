# Migration Guide: Downloads 1.x to 2.0

This guide will help you upgrade from Downloads module version 1.x to version 2.0.

## ⚠️ Important Notice

**Version 2.0 is a major release with significant changes:**

- **PHP 8.2+ Required** - The module no longer supports PHP 7.x or earlier
- **ImpressCMS 2.0+ Required** - Requires ImpressCMS 2.0 or higher
- **Architecture Changed** - Complete refactoring to controller-based MVC
- **Data Compatible** - Your existing data will be preserved
- **Custom Modifications** - May need updating for new architecture

## 📋 Pre-Migration Checklist

Before starting the migration, ensure you have:

- [ ] **Full backup** of your database
- [ ] **Full backup** of your files (especially `/modules/downloads/`)
- [ ] **PHP 8.2 or higher** installed on your server
- [ ] **ImpressCMS 2.0 or higher** installed
- [ ] **Write permissions** on modules directory
- [ ] **Admin access** to your ImpressCMS installation
- [ ] **Maintenance mode** enabled (recommended)
- [ ] **List of custom modifications** (if any)

## 🔍 Compatibility Check

### Server Requirements

```bash
# Check PHP version
php -v
# Should show: PHP 8.2.x or higher

# Check required PHP extensions
php -m | grep -E 'mysqli|gd|mbstring|json'
```

### ImpressCMS Version

1. Log in to admin panel
2. Go to **System Admin > System Information**
3. Verify ImpressCMS version is 2.0 or higher

### Database Compatibility

The module uses the same database structure, so your data will be preserved. However, ensure:

- MySQL 5.7+ or MariaDB 10.2+
- InnoDB storage engine
- UTF-8 character set

## 📦 Migration Steps

### Step 1: Backup Everything

```bash
# Backup database
mysqldump -u username -p database_name > downloads_backup_$(date +%Y%m%d).sql

# Backup module files
cd /path/to/impresscms/modules/
tar -czf downloads_backup_$(date +%Y%m%d).tar.gz downloads/

# Backup uploads
tar -czf downloads_uploads_$(date +%Y%m%d).tar.gz ../uploads/downloads/
```

### Step 2: Document Custom Modifications

If you have custom modifications, document them:

```bash
# List modified files
cd /path/to/impresscms/modules/downloads/
find . -type f -name "*.php" -mtime -365 > modified_files.txt
```

Review each file and note:
- What was changed
- Why it was changed
- Where the logic should go in the new architecture

### Step 3: Enable Maintenance Mode

1. Log in to ImpressCMS admin
2. Go to **System Admin > Preferences > General Settings**
3. Enable "Turn your site off"
4. Save changes

### Step 4: Deactivate Module (Optional)

For a safer migration:

1. Go to **System Admin > Modules**
2. Find "Downloads" module
3. Click "Deactivate" (do NOT uninstall)

### Step 5: Replace Module Files

```bash
# Remove old module files (keep backup!)
cd /path/to/impresscms/modules/
mv downloads downloads_old

# Extract new version
unzip downloads-2.0.0.zip
# or
tar -xzf downloads-2.0.0.tar.gz

# Set permissions
chmod -R 755 downloads/
chown -R www-data:www-data downloads/
```

### Step 6: Update Module

1. Log in to ImpressCMS admin
2. Go to **System Admin > Modules**
3. Find "Downloads" module
4. Click the **Update** icon
5. Follow on-screen instructions
6. Wait for update to complete

### Step 7: Clear Caches

```bash
# Clear ImpressCMS caches
cd /path/to/impresscms/
rm -rf cache/*
rm -rf templates_c/*

# Or via admin panel:
# System Admin > Maintenance > Clear Cache
```

### Step 8: Verify Installation

1. Go to **Downloads Admin > Dashboard**
2. Check statistics are correct
3. Verify categories are displayed
4. Verify downloads are displayed
5. Test file downloads
6. Test user submissions (if enabled)
7. Test reviews (if enabled)

### Step 9: Update Permissions

The permission system is unchanged, but verify:

1. Go to **Downloads Admin > Permissions**
2. Check "View Downloads" permissions
3. Check "View Categories" permissions
4. Check "Submit Downloads" permissions
5. Adjust as needed

### Step 10: Test Functionality

Test all major features:

- [ ] Browse categories
- [ ] View downloads
- [ ] Download files
- [ ] Submit download (if enabled)
- [ ] Add review (if enabled)
- [ ] Admin: Add category
- [ ] Admin: Add download
- [ ] Admin: Edit download
- [ ] Admin: Delete download
- [ ] Search functionality
- [ ] RSS feeds
- [ ] Blocks display correctly

### Step 11: Disable Maintenance Mode

1. Go to **System Admin > Preferences > General Settings**
2. Disable "Turn your site off"
3. Save changes

## 🔧 Migrating Custom Modifications

### Understanding the New Architecture

Version 2.0 uses a controller-based MVC architecture:

```
Old (1.x):                    New (2.0):
┌─────────────────┐          ┌─────────────────┐
│  index.php      │          │  index.php      │
│  (200+ lines)   │   →      │  (38 lines)     │
│  - All logic    │          │  - Routes only  │
│  - Forms        │          └────────┬────────┘
│  - Display      │                   │
└─────────────────┘                   ↓
                              ┌─────────────────┐
                              │  Controller     │
                              │  - Handle logic │
                              │  - Call handler │
                              │  - Render view  │
                              └────────┬────────┘
                                       │
                              ┌────────┴────────┐
                              ↓                 ↓
                         ┌─────────┐      ┌─────────┐
                         │ Handler │      │Template │
                         │ (Model) │      │ (View)  │
                         └─────────┘      └─────────┘
```

### Migration Patterns

#### Pattern 1: Custom Display Logic

**Old (1.x):**
```php
// In index.php
if ($op == 'custom') {
    $downloads = $downloads_download_handler->getObjects();
    // Custom display logic
    $xoopsTpl->assign('downloads', $downloads);
}
```

**New (2.0):**
```php
// In class/Controllers/IndexController.php
public function custom(): void
{
    $downloads = $this->downloadHandler->getObjects();
    // Custom display logic
    $this->render('downloads_custom.html', [
        'downloads' => $downloads
    ]);
}

// In index.php - add route
$controller->handle(); // Will call custom() if op=custom
```

#### Pattern 2: Custom Form Processing

**Old (1.x):**
```php
// In download.php
if ($op == 'customsave') {
    $downloadObj = $downloads_download_handler->get($_POST['id']);
    $downloadObj->setVar('custom_field', $_POST['custom_field']);
    $downloads_download_handler->insert($downloadObj);
}
```

**New (2.0):**
```php
// In class/Controllers/DownloadController.php
protected function customSave(): void
{
    if (!$this->validateToken()) {
        $this->redirect('download.php', 2, 'Invalid token');
        return;
    }
    
    $id = $this->postInt('id');
    $downloadObj = $this->downloadHandler->get($id);
    $downloadObj->setVar('custom_field', $this->postString('custom_field'));
    $this->downloadHandler->insert($downloadObj);
    
    $this->redirect('download.php', 2, 'Saved successfully');
}
```

#### Pattern 3: Custom Queries

**Old (1.x):**
```php
$criteria = new icms_db_criteria_Compo();
$criteria->add(new icms_db_criteria_Item('custom_field', 'value'));
$downloads = $downloads_download_handler->getObjects($criteria);
```

**New (2.0) - Fluent Interface:**
```php
$downloads = $this->downloadHandler
    ->where('custom_field', 'value')
    ->orderBy('download_title')
    ->get();
```

### Custom Template Modifications

Templates are largely unchanged, but if you modified them:

1. **Backup your custom templates**
2. **Compare with new templates** to see what changed
3. **Merge your changes** into new templates
4. **Test thoroughly**

### Custom Block Modifications

Blocks are unchanged in structure. If you have custom blocks:

1. Ensure they use modern PHP syntax (if needed)
2. Update any handler calls to use fluent interface
3. Test block display

## 🐛 Troubleshooting

### Issue: "PHP version not supported"

**Solution:** Upgrade to PHP 8.2 or higher

```bash
# On Ubuntu/Debian
sudo apt-get update
sudo apt-get install php8.2

# On CentOS/RHEL
sudo yum install php82
```

### Issue: "Module update fails"

**Solution:**

1. Check PHP error logs: `/var/log/php/error.log`
2. Check ImpressCMS logs: `uploads/logs/`
3. Ensure database user has ALTER permissions
4. Try manual SQL update if needed

### Issue: "White screen after update"

**Solution:**

1. Enable PHP error display:
   ```php
   // In mainfile.php (temporarily)
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. Check for PHP 8.2 compatibility issues
3. Clear all caches
4. Check file permissions

### Issue: "Downloads not displaying"

**Solution:**

1. Check permissions: **Downloads Admin > Permissions**
2. Verify categories are active
3. Verify downloads are approved
4. Clear template cache
5. Check block settings

### Issue: "Custom modifications not working"

**Solution:**

1. Review migration patterns above
2. Update code to use controllers
3. Use fluent query builder for database operations
4. Ensure CSRF tokens are validated
5. Check error logs for specific issues

## 🔄 Rollback Procedure

If you need to rollback to version 1.x:

```bash
# Stop web server
sudo systemctl stop apache2  # or nginx

# Restore database
mysql -u username -p database_name < downloads_backup_YYYYMMDD.sql

# Restore files
cd /path/to/impresscms/modules/
rm -rf downloads/
tar -xzf downloads_backup_YYYYMMDD.tar.gz

# Restore uploads (if needed)
cd /path/to/impresscms/uploads/
rm -rf downloads/
tar -xzf downloads_uploads_YYYYMMDD.tar.gz

# Clear caches
rm -rf /path/to/impresscms/cache/*
rm -rf /path/to/impresscms/templates_c/*

# Start web server
sudo systemctl start apache2  # or nginx
```

## 📞 Getting Help

If you encounter issues during migration:

1. **Check Documentation**:
   - [README.md](README.md)
   - [CHANGELOG.md](CHANGELOG.md)
   - [DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)

2. **Community Support**:
   - [ImpressCMS Forums](https://community.impresscms.org/)
   - [GitHub Issues](https://github.com/ImpressCMS/downloads/issues)

3. **Report Bugs**:
   - Include PHP version
   - Include ImpressCMS version
   - Include error messages
   - Include steps to reproduce

## ✅ Post-Migration Checklist

After successful migration:

- [ ] All downloads are accessible
- [ ] All categories are displayed correctly
- [ ] File downloads work
- [ ] User submissions work (if enabled)
- [ ] Reviews work (if enabled)
- [ ] Admin functions work
- [ ] Permissions are correct
- [ ] Blocks display correctly
- [ ] Search works
- [ ] RSS feeds work
- [ ] Custom modifications migrated (if any)
- [ ] Performance is acceptable
- [ ] No PHP errors in logs
- [ ] Backup of new version created

## 🎉 Success!

Congratulations on successfully migrating to Downloads 2.0! You now have:

- Modern PHP 8.2+ codebase
- Controller-based MVC architecture
- Enhanced security
- Better performance
- Improved maintainability
- Future-proof code

Enjoy your modernized Downloads module!

