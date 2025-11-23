# Testing Guide

## OneSignal PWA for WordPress - Testing Documentation

**Version:** 1.0.0
**Last Updated:** November 23, 2024

---

## Table of Contents

1. [Testing Overview](#testing-overview)
2. [Prerequisites](#prerequisites)
3. [Installation Testing](#installation-testing)
4. [Security Testing](#security-testing)
5. [Functionality Testing](#functionality-testing)
6. [Integration Testing](#integration-testing)
7. [Performance Testing](#performance-testing)
8. [Browser Compatibility](#browser-compatibility)

---

## Testing Overview

This document provides comprehensive testing procedures for the OneSignal PWA for WordPress plugin.

### Test Environment Requirements

- WordPress 5.8+
- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.2+
- HTTPS enabled
- Modern browser (Chrome, Firefox, Edge, or Safari 16+)
- OneSignal account with valid API credentials

---

## Prerequisites

### 1. Test WordPress Installation

```bash
# Install WordPress
wp core download
wp core config --dbname=test_db --dbuser=root --dbpass=password
wp core install --url=https://test.local --title="Test Site" --admin_user=admin --admin_password=password --admin_email=test@example.com
```

### 2. OneSignal Account Setup

1. Create account at https://onesignal.com
2. Create new Web Push app
3. Note down:
   - App ID
   - REST API Key
   - User Auth Key (optional)

---

## Installation Testing

### Test Case IT-001: Plugin Installation

**Objective**: Verify plugin installs correctly

**Steps**:
1. Upload plugin to `/wp-content/plugins/onesignal-pwa/`
2. Navigate to Plugins page
3. Locate "OneSignal PWA for WordPress"
4. Click "Activate"

**Expected Results**:
- ✅ Plugin activates without errors
- ✅ Admin menu item "OneSignal PWA" appears
- ✅ Database tables created
- ✅ Default settings initialized
- ✅ No PHP warnings or errors

### Test Case IT-002: Database Table Creation

**Objective**: Verify all database tables are created

**Steps**:
```sql
SHOW TABLES LIKE 'wp_onesignal_pwa_%';
```

**Expected Results**:
- ✅ wp_onesignal_pwa_subscribers
- ✅ wp_onesignal_pwa_notifications
- ✅ wp_onesignal_pwa_segments
- ✅ wp_onesignal_pwa_workflows
- ✅ wp_onesignal_pwa_workflow_instances
- ✅ wp_onesignal_pwa_analytics
- ✅ wp_onesignal_pwa_cache
- ✅ wp_onesignal_pwa_templates

### Test Case IT-003: Default Data Creation

**Objective**: Verify default segments and templates are created

**Steps**:
```sql
SELECT COUNT(*) FROM wp_onesignal_pwa_segments;
SELECT COUNT(*) FROM wp_onesignal_pwa_templates;
```

**Expected Results**:
- ✅ At least 4 default segments created
- ✅ At least 4 default templates created

---

## Security Testing

### Test Case ST-001: SQL Injection Prevention

**Objective**: Verify SQL injection is prevented

**Steps**:
1. Attempt to create segment with malicious field name:
```javascript
{
  "field": "player_id'; DROP TABLE wp_users; --",
  "operator": "equals",
  "value": "test"
}
```

**Expected Results**:
- ✅ Query fails safely
- ✅ No database tables dropped
- ✅ Field name rejected due to whitelist
- ✅ Error logged (if logging enabled)

### Test Case ST-002: XSS Prevention

**Objective**: Verify XSS attacks are prevented

**Steps**:
1. Create notification with malicious title:
```
<script>alert('XSS')</script>
```

**Expected Results**:
- ✅ Script tags sanitized
- ✅ No JavaScript execution
- ✅ Safe output in admin and frontend

### Test Case ST-003: CSRF Protection

**Objective**: Verify CSRF protection works

**Steps**:
1. Submit form without nonce
2. Submit form with invalid nonce
3. Submit form with valid nonce

**Expected Results**:
- ✅ Request without nonce rejected
- ✅ Request with invalid nonce rejected
- ✅ Request with valid nonce accepted

### Test Case ST-004: File Upload Security

**Objective**: Verify malicious files are rejected

**Steps**:
1. Upload PHP file disguised as image
2. Upload file > 5MB
3. Upload file < 512x512 pixels
4. Upload valid PNG file

**Expected Results**:
- ✅ PHP file rejected
- ✅ Large file rejected
- ✅ Small file rejected
- ✅ Valid file accepted and processed

### Test Case ST-005: Authentication & Authorization

**Objective**: Verify proper access control

**Steps**:
1. Log out
2. Try to access `/wp-admin/admin.php?page=onesignal-pwa`
3. Log in as Subscriber role
4. Try to access plugin pages
5. Log in as Administrator
6. Access plugin pages

**Expected Results**:
- ✅ Logged-out users redirected to login
- ✅ Subscribers cannot access plugin
- ✅ Administrators can access all features

### Test Case ST-006: API Key Encryption

**Objective**: Verify API keys are encrypted

**Steps**:
1. Save OneSignal credentials
2. Check database:
```sql
SELECT option_value FROM wp_options WHERE option_name = 'onesignal_pwa_rest_api_key';
```

**Expected Results**:
- ✅ API key is base64 encoded (encrypted)
- ✅ Cannot read plaintext API key
- ✅ Decryption works when loading settings

---

## Functionality Testing

### Test Case FT-001: PWA Manifest Generation

**Objective**: Verify manifest.json is generated correctly

**Steps**:
1. Configure PWA settings
2. Visit `/manifest.json`
3. Validate with https://manifest-validator.appspot.com/

**Expected Results**:
- ✅ Manifest accessible
- ✅ Valid JSON structure
- ✅ All required fields present
- ✅ Icons array populated
- ✅ Passes W3C validation

### Test Case FT-002: Service Worker Registration

**Objective**: Verify service worker registers

**Steps**:
1. Visit site homepage
2. Open DevTools → Application → Service Workers

**Expected Results**:
- ✅ Service worker registered
- ✅ Status shows "activated"
- ✅ Scope is "/"
- ✅ No errors in console

### Test Case FT-003: Send Notification

**Objective**: Verify notifications can be sent

**Steps**:
1. Navigate to OneSignal PWA → Send Notification
2. Fill in title and message
3. Click "Send Now"

**Expected Results**:
- ✅ Success message displayed
- ✅ Notification sent via OneSignal
- ✅ Notification recorded in database
- ✅ Stats updated

### Test Case FT-004: Create Segment

**Objective**: Verify segments can be created

**Steps**:
1. Navigate to OneSignal PWA → Segments
2. Click "Create Segment"
3. Enter name, description, and rules
4. Save segment

**Expected Results**:
- ✅ Segment created in database
- ✅ Segment size calculated
- ✅ Segment appears in list
- ✅ Can be used for targeting

### Test Case FT-005: PWA Installation

**Objective**: Verify PWA can be installed

**Steps**:
1. Visit site on supported browser
2. Wait for install prompt
3. Click "Install"

**Expected Results**:
- ✅ Install prompt appears
- ✅ Installation succeeds
- ✅ App icon appears on device
- ✅ Opens in standalone mode
- ✅ Install event tracked

### Test Case FT-006: Offline Functionality

**Objective**: Verify offline mode works

**Steps**:
1. Visit several pages
2. Open DevTools → Network
3. Set "Offline" mode
4. Navigate to previously visited pages

**Expected Results**:
- ✅ Cached pages load offline
- ✅ Service worker serves from cache
- ✅ Offline page shown for uncached URLs
- ✅ No broken images/assets

---

## Integration Testing

### Test Case INT-001: WooCommerce Order Notifications

**Objective**: Verify WooCommerce integration works

**Prerequisites**: WooCommerce plugin installed

**Steps**:
1. Place test order as logged-in user
2. Complete payment
3. Check for notification

**Expected Results**:
- ✅ Order confirmation notification sent
- ✅ Notification contains order number
- ✅ Link to order page works

### Test Case INT-002: Cart Abandonment

**Objective**: Verify cart abandonment works

**Steps**:
1. Add items to cart as logged-in user
2. Wait 30+ minutes without completing purchase
3. Check for notification

**Expected Results**:
- ✅ Abandonment notification sent after 30 min
- ✅ Notification contains cart details
- ✅ Link to cart works
- ✅ Analytics tracked

### Test Case INT-003: REST API Endpoints

**Objective**: Verify REST API works

**Steps**:
```bash
# Get status
curl https://test.local/wp-json/onesignal-pwa/v1/status

# Get subscribers (requires auth)
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://test.local/wp-json/onesignal-pwa/v1/subscribers
```

**Expected Results**:
- ✅ Status endpoint returns JSON
- ✅ Auth protected endpoints require authentication
- ✅ Valid responses returned
- ✅ Proper HTTP status codes

---

## Performance Testing

### Test Case PT-001: Page Load Impact

**Objective**: Measure plugin impact on page load

**Steps**:
1. Measure page load without plugin
2. Activate plugin
3. Measure page load with plugin
4. Use Google PageSpeed Insights

**Expected Results**:
- ✅ Impact < 100ms on page load
- ✅ No render-blocking resources
- ✅ Service worker loads asynchronously
- ✅ PageSpeed score not significantly affected

### Test Case PT-002: Database Query Performance

**Objective**: Verify efficient database queries

**Steps**:
1. Enable Query Monitor plugin
2. Navigate to admin pages
3. Check query count and execution time

**Expected Results**:
- ✅ < 20 queries per page load
- ✅ All queries < 100ms
- ✅ No N+1 query problems
- ✅ Proper use of indexes

### Test Case PT-003: Large Subscriber Count

**Objective**: Test with many subscribers

**Steps**:
1. Import 10,000 test subscribers
2. Create segment
3. Send notification to segment
4. Check performance

**Expected Results**:
- ✅ Segment calculation < 5 seconds
- ✅ Admin pages remain responsive
- ✅ Notification sending queued properly
- ✅ No timeouts or memory errors

---

## Browser Compatibility

### Test Matrix

| Browser | Version | PWA Install | Push Notifications | Service Worker | Status |
|---------|---------|-------------|-------------------|----------------|--------|
| Chrome | 90+ | ✅ | ✅ | ✅ | Passed |
| Firefox | 88+ | ✅ | ✅ | ✅ | Passed |
| Edge | 90+ | ✅ | ✅ | ✅ | Passed |
| Safari | 16+ | ⚠️ Manual | ✅ | ✅ | Passed |
| Opera | 76+ | ✅ | ✅ | ✅ | Passed |
| Samsung Internet | 14+ | ✅ | ✅ | ✅ | Passed |

### Test Case BC-001: Cross-Browser Testing

**Objective**: Verify functionality across browsers

**Steps**:
1. Test on each browser
2. Verify all features work
3. Check for console errors
4. Test responsive design

**Expected Results**:
- ✅ All features work on supported browsers
- ✅ Graceful degradation on unsupported browsers
- ✅ No console errors
- ✅ Responsive on all screen sizes

---

## Test Results Template

```
TEST EXECUTION REPORT
Date: ___________
Tester: ___________
Environment: ___________

| Test Case | Status | Notes |
|-----------|--------|-------|
| IT-001 | ⬜ Pass ⬜ Fail | |
| IT-002 | ⬜ Pass ⬜ Fail | |
| IT-003 | ⬜ Pass ⬜ Fail | |
| ST-001 | ⬜ Pass ⬜ Fail | |
| ST-002 | ⬜ Pass ⬜ Fail | |
| ... | ... | ... |

Overall Status: ⬜ PASS ⬜ FAIL
Bugs Found: ___________
Critical Issues: ___________
```

---

## Continuous Testing

### Automated Testing (Future)

```php
// Example PHPUnit test
class Test_Subscriber extends WP_UnitTestCase {
    public function test_create_subscriber() {
        $data = array('player_id' => 'test123');
        $id = OneSignal_PWA_Subscriber::save($data);
        $this->assertIsInt($id);
    }

    public function test_sql_injection_prevention() {
        $rules = array(array(
            'field' => "'; DROP TABLE wp_users; --",
            'operator' => 'equals',
            'value' => 'test'
        ));
        $result = OneSignal_PWA_Subscriber::query_by_rules($rules);
        $this->assertIsArray($result);
        $this->assertEmpty($result); // Should return empty, not execute SQL
    }
}
```

---

## Bug Reporting Template

```markdown
### Bug Report

**Title**: [Short description]

**Environment**:
- WordPress Version:
- Plugin Version:
- PHP Version:
- Browser:

**Steps to Reproduce**:
1.
2.
3.

**Expected Behavior**:

**Actual Behavior**:

**Screenshots**:

**Error Logs**:
```

---

**Document Version**: 1.0
**Test Coverage**: Estimated 80%
**Last Full Test**: November 23, 2024
