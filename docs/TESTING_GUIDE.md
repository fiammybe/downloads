# Testing Guide - Downloads Module 2.0

This guide provides comprehensive testing procedures for the Downloads module to ensure all functionality works correctly after installation or updates.

## Table of Contents

1. [Pre-Testing Setup](#pre-testing-setup)
2. [Functional Testing](#functional-testing)
3. [Security Testing](#security-testing)
4. [Performance Testing](#performance-testing)
5. [Compatibility Testing](#compatibility-testing)
6. [Regression Testing](#regression-testing)
7. [User Acceptance Testing](#user-acceptance-testing)

---

## Pre-Testing Setup

### Test Environment

Ensure you have a proper test environment:

- [ ] Separate test installation (not production)
- [ ] PHP 8.2+ installed
- [ ] ImpressCMS 2.0+ installed
- [ ] Test database with sample data
- [ ] Multiple user accounts with different permission levels
- [ ] Error logging enabled

### Test Users

Create test users with different roles:

1. **Admin User** - Full administrative access
2. **Registered User** - Can submit downloads (if enabled)
3. **Guest User** - Anonymous visitor
4. **Restricted User** - Limited permissions

### Sample Data

Create test data:

- 3-5 categories (including nested categories)
- 10-15 downloads across categories
- Various file types (PDF, ZIP, images, etc.)
- Some downloads with reviews
- Some downloads with mirrors
- Mix of approved and unapproved items

---

## Functional Testing

### 1. Category Management

#### Admin: Create Category

- [ ] Navigate to **Downloads Admin > Categories**
- [ ] Click "Add Category"
- [ ] Fill in all fields:
  - Title: "Test Category"
  - Description: "Test description"
  - Parent: (select parent or leave as root)
  - Image: Upload test image
- [ ] Click "Submit"
- [ ] Verify category appears in list
- [ ] Verify category appears on front-end

#### Admin: Edit Category

- [ ] Click edit icon for a category
- [ ] Modify title and description
- [ ] Click "Submit"
- [ ] Verify changes are saved
- [ ] Verify changes appear on front-end

#### Admin: Delete Category

- [ ] Click delete icon for a category
- [ ] Confirm deletion
- [ ] Verify category is removed
- [ ] Verify downloads in category are handled correctly

#### Admin: Toggle Category Visibility

- [ ] Click visibility icon
- [ ] Verify category becomes hidden/visible
- [ ] Check front-end reflects change

#### Admin: Change Category Weight

- [ ] Modify weight values
- [ ] Click "Submit"
- [ ] Verify category order changes

### 2. Download Management

#### Admin: Add Download

- [ ] Navigate to **Downloads Admin > Downloads**
- [ ] Click "Add Download"
- [ ] Fill in required fields:
  - Title
  - Category
  - Description
  - File upload or URL
- [ ] Fill in optional fields:
  - Teaser
  - Key features
  - Requirements
  - Version
  - License
  - Platform
  - Screenshots
  - Mirror URL
- [ ] Click "Submit"
- [ ] Verify download appears in list
- [ ] Verify download appears on front-end

#### Admin: Edit Download

- [ ] Click edit icon for a download
- [ ] Modify various fields
- [ ] Click "Submit"
- [ ] Verify changes are saved
- [ ] Verify changes appear on front-end

#### Admin: Delete Download

- [ ] Click delete icon for a download
- [ ] Confirm deletion
- [ ] Verify download is removed
- [ ] Verify file is handled correctly

#### Admin: Toggle Download Status

Test all toggle operations:
- [ ] Toggle visibility (active/inactive)
- [ ] Toggle "show in blocks"
- [ ] Toggle approval status
- [ ] Toggle broken status
- [ ] Toggle mirror approval
- [ ] Verify each toggle works correctly
- [ ] Verify front-end reflects changes

#### Admin: Change Download Weight

- [ ] Modify weight values for multiple downloads
- [ ] Click "Submit"
- [ ] Verify download order changes

### 3. Front-End Browsing

#### View Index Page

- [ ] Navigate to Downloads module
- [ ] Verify categories are displayed
- [ ] Verify recent downloads are shown
- [ ] Verify layout is correct
- [ ] Test pagination (if applicable)

#### Browse Categories

- [ ] Click on a category
- [ ] Verify downloads in category are shown
- [ ] Verify breadcrumb navigation
- [ ] Test nested categories
- [ ] Test empty categories

#### View Single Download

- [ ] Click on a download
- [ ] Verify all information is displayed:
  - Title, description, teaser
  - Screenshots
  - Version information
  - Requirements
  - License, platform
  - Download counter
  - Reviews
- [ ] Verify related downloads (if any)
- [ ] Verify integration with other modules (Album, Sprockets, Catalogue)

#### Download File

- [ ] Click "Download" button
- [ ] Verify file download starts
- [ ] Verify download counter increments
- [ ] Test with different file types
- [ ] Test mirror downloads (if configured)

### 4. User Submissions

#### Submit Download (Registered User)

- [ ] Log in as registered user
- [ ] Navigate to Downloads module
- [ ] Click "Submit Download"
- [ ] Fill in form
- [ ] Upload file
- [ ] Click "Submit"
- [ ] Verify submission success message
- [ ] Verify download appears in admin for approval

#### Admin: Approve Submission

- [ ] Log in as admin
- [ ] Navigate to **Downloads Admin > Downloads**
- [ ] Find unapproved download
- [ ] Click approval toggle
- [ ] Verify download becomes visible on front-end

### 5. Review System

#### Add Review (Registered User)

- [ ] Log in as registered user
- [ ] Navigate to a download
- [ ] Fill in review form:
  - Name (if not auto-filled)
  - Email (if not auto-filled)
  - Message
- [ ] Click "Submit"
- [ ] Verify review is added
- [ ] Verify review appears on download page

#### Admin: Manage Reviews

- [ ] Navigate to **Downloads Admin > Reviews**
- [ ] Verify all reviews are listed
- [ ] Click edit icon
- [ ] Modify review
- [ ] Click "Submit"
- [ ] Verify changes are saved
- [ ] Test delete review

### 6. Search Functionality

#### Search Downloads

- [ ] Use search box
- [ ] Enter search term
- [ ] Click "Search"
- [ ] Verify relevant results are shown
- [ ] Test various search terms
- [ ] Test empty search
- [ ] Test special characters

### 7. Permissions

#### View Permissions

- [ ] Navigate to **Downloads Admin > Permissions**
- [ ] Select "View Downloads"
- [ ] Modify group permissions
- [ ] Click "Submit"
- [ ] Log in as user in modified group
- [ ] Verify permissions are enforced

#### Submit Permissions

- [ ] Select "Submit Downloads"
- [ ] Modify group permissions
- [ ] Click "Submit"
- [ ] Log in as user in modified group
- [ ] Verify submit button appears/disappears correctly

### 8. Activity Log

#### View Log

- [ ] Navigate to **Downloads Admin > Log**
- [ ] Verify recent activities are logged
- [ ] Verify log entries are accurate
- [ ] Test log filtering (if available)

### 9. Index Page Management

#### Edit Index Page

- [ ] Navigate to **Downloads Admin > Index Page**
- [ ] Modify index page content
- [ ] Upload index image
- [ ] Click "Submit"
- [ ] Verify changes appear on front-end index

### 10. Blocks

#### Recent Downloads Block

- [ ] Go to **System Admin > Blocks**
- [ ] Find "Recent Downloads" block
- [ ] Configure and activate
- [ ] Verify block displays correctly
- [ ] Test block options

#### Category Menu Block

- [ ] Find "Category Menu" block
- [ ] Configure and activate
- [ ] Verify categories are displayed
- [ ] Test nested category display

#### Most Popular Block

- [ ] Find "Most Popular Downloads" block
- [ ] Configure and activate
- [ ] Verify popular downloads are shown
- [ ] Verify counter is accurate

### 11. RSS Feeds

#### Test RSS Feed

- [ ] Navigate to `rss.php`
- [ ] Verify valid RSS XML is generated
- [ ] Verify recent downloads are included
- [ ] Test RSS feed in feed reader

### 12. AJAX Functionality

#### AJAX Reviews

- [ ] Submit review via AJAX
- [ ] Verify review is added without page reload
- [ ] Verify error handling

#### AJAX Tags (if Sprockets installed)

- [ ] Add tag via AJAX
- [ ] Verify tag is added without page reload
- [ ] Verify error handling

---

## Security Testing

### 1. CSRF Protection

- [ ] Try to submit form without security token
- [ ] Verify request is rejected
- [ ] Try to submit with invalid token
- [ ] Verify request is rejected

### 2. SQL Injection

- [ ] Try SQL injection in search: `' OR '1'='1`
- [ ] Try SQL injection in form fields
- [ ] Verify all inputs are properly sanitized
- [ ] Verify no SQL errors are displayed

### 3. XSS (Cross-Site Scripting)

- [ ] Try XSS in download title: `<script>alert('XSS')</script>`
- [ ] Try XSS in description
- [ ] Try XSS in review
- [ ] Verify all output is properly escaped

### 4. File Upload Security

- [ ] Try to upload PHP file
- [ ] Try to upload executable
- [ ] Try to upload oversized file
- [ ] Verify file type validation works
- [ ] Verify file size limits are enforced

### 5. Permission Bypass

- [ ] Try to access admin pages as non-admin
- [ ] Try to edit others' downloads
- [ ] Try to delete others' downloads
- [ ] Verify all permission checks work

### 6. Direct Access

- [ ] Try to access handler files directly
- [ ] Try to access class files directly
- [ ] Verify proper access control

---

## Performance Testing

### 1. Page Load Times

- [ ] Measure index page load time
- [ ] Measure category page load time
- [ ] Measure single download page load time
- [ ] Verify acceptable performance (< 2 seconds)

### 2. Database Queries

- [ ] Enable query logging
- [ ] Count queries per page
- [ ] Identify slow queries
- [ ] Optimize if needed

### 3. Large Dataset

- [ ] Test with 100+ downloads
- [ ] Test with 50+ categories
- [ ] Test pagination performance
- [ ] Verify no performance degradation

### 4. Concurrent Users

- [ ] Simulate multiple users browsing
- [ ] Simulate multiple downloads
- [ ] Verify no conflicts or errors

---

## Compatibility Testing

### 1. Browser Compatibility

Test in multiple browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers (iOS Safari, Chrome Mobile)

### 2. PHP Versions

- [ ] Test on PHP 8.2
- [ ] Test on PHP 8.3 (if available)
- [ ] Verify no deprecation warnings

### 3. Database Compatibility

- [ ] Test on MySQL 5.7
- [ ] Test on MySQL 8.0
- [ ] Test on MariaDB 10.2+

### 4. Module Integration

If other modules are installed:
- [ ] Test Sprockets integration (tags)
- [ ] Test Album integration (galleries)
- [ ] Test Catalogue integration (selling)
- [ ] Verify no conflicts

---

## Regression Testing

After any code changes, re-test:

- [ ] All CRUD operations (Create, Read, Update, Delete)
- [ ] Permission system
- [ ] File downloads
- [ ] Search functionality
- [ ] Blocks display
- [ ] RSS feeds
- [ ] AJAX operations

---

## User Acceptance Testing

### End-User Scenarios

#### Scenario 1: Visitor Downloads File

1. Visitor browses to Downloads module
2. Browses categories
3. Finds desired download
4. Views download details
5. Clicks download button
6. File downloads successfully

**Expected Result**: Smooth, intuitive process

#### Scenario 2: User Submits Download

1. Registered user logs in
2. Navigates to Downloads
3. Clicks "Submit Download"
4. Fills in form
5. Uploads file
6. Submits
7. Receives confirmation

**Expected Result**: Easy submission process

#### Scenario 3: Admin Manages Content

1. Admin logs in
2. Adds new category
3. Adds new download
4. Approves user submission
5. Edits existing download
6. Views activity log

**Expected Result**: Efficient admin workflow

---

## Test Report Template

```
Test Date: _______________
Tester: _______________
Environment: _______________
PHP Version: _______________
ImpressCMS Version: _______________

Test Results:
[ ] All tests passed
[ ] Some tests failed (see details below)
[ ] Critical issues found

Failed Tests:
1. _______________________________
2. _______________________________
3. _______________________________

Issues Found:
1. _______________________________
   Severity: [ ] Critical [ ] High [ ] Medium [ ] Low
   
2. _______________________________
   Severity: [ ] Critical [ ] High [ ] Medium [ ] Low

Recommendations:
_________________________________
_________________________________
_________________________________

Overall Assessment:
[ ] Ready for production
[ ] Needs minor fixes
[ ] Needs major fixes
[ ] Not ready for production
```

---

## Automated Testing (Future)

The module's controller-based architecture enables automated testing:

### PHPUnit Tests

```php
// Example test structure
class DownloadControllerTest extends TestCase
{
    public function testListDownloads()
    {
        // Test logic
    }
    
    public function testCreateDownload()
    {
        // Test logic
    }
}
```

### Integration Tests

- Test database operations
- Test file uploads
- Test permission checks
- Test AJAX endpoints

---

## Continuous Testing

Establish a testing routine:

1. **Before Release** - Full test suite
2. **After Updates** - Regression testing
3. **Monthly** - Security audit
4. **Quarterly** - Performance review

---

## Reporting Issues

If you find bugs during testing:

1. **Document the issue**:
   - Steps to reproduce
   - Expected behavior
   - Actual behavior
   - Screenshots (if applicable)
   - Error messages
   - Environment details

2. **Report on GitHub**:
   - [GitHub Issues](https://github.com/ImpressCMS/downloads/issues)

3. **Include**:
   - PHP version
   - ImpressCMS version
   - Module version
   - Browser (if front-end issue)

---

**Happy Testing!** 🧪

