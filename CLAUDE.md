# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

ERP Mill is a legacy PHP-based Enterprise Resource Planning system for palm oil mill and plantation management (Medco Agro System). The system has been modernized with Bootstrap 5 while maintaining backward compatibility with legacy code.

## Technology Stack

- **Backend**: PHP (legacy version using mysql_* functions, not PDO)
- **Database**: MySQL (database: `erpmill`)
- **Frontend**: Bootstrap 5.3.0, jQuery, custom JavaScript
- **Server**: XAMPP (Apache + MySQL on Windows)
- **Architecture**: Traditional PHP with frameset-based navigation (uses frames in index.php)

## Database Connection

Database configuration is in `config/connection.php`:
- Default credentials: root (no password)
- Database: `erpmill`
- Uses deprecated `mysql_connect()` functions (not `mysqli_*` or PDO)

## Project Structure

### Key Directories
- `lib/` - Core PHP libraries and framework functions
  - `nangkoelib.php` - Main template/UI functions (OPEN_BODY, OPEN_BOX, etc.)
  - `zFunction.php`, `zTable.php`, `zGrid.php` - Utility libraries
  - `fpdf.php` - PDF generation
- `config/` - Database and system configuration
- `js/` - JavaScript files (generic.js, calendar.js, drag.js, etc.)
- `style/` - CSS files including Bootstrap customizations
- `master_*.php` - Core system files (menu, footer, validation)
- `images/` - Image assets

### Module Naming Convention
Files are prefixed by module:
- `bgt_*` - Budget management
- `kebun_*` - Plantation/estate operations
- `keu_*` - Finance/accounting
- `sdm_*` - Human resources
- `lbm_*` - Materials/logistics
- `main_*` - User management and system settings
- `*_slave_*` - AJAX handlers and data processing endpoints

### File Naming Patterns
- `*_laporan_*` - Reports
- `*_5*.php` - Master data/settings
- `*_2*.php` - Data entry forms
- `*_3*.php` - Processing/posting functions
- `*_pdf.php` or `*_excel.php` - Export functions

## Core Template System (NangkoelFramework)

Located in `lib/nangkoelib.php`, provides:

### HTML Generation Functions
```php
OPEN_BODY($title)        // Opens HTML with Bootstrap headers
OPEN_BODY_BI($title)     // Opens HTML for BI/Reports
CLOSE_BODY()             // Closes HTML with footer and Bootstrap scripts
OPEN_BOX($style, $title, $id, $contentId)  // Creates Bootstrap card
CLOSE_BOX()              // Closes Bootstrap card
OPEN_THEME($caption, $width, $text_align)  // Legacy theme opener
drawTab($tabId, $arrHeader, $arrContent, $tabLength, $contentLength)  // Tab system
```

### Standard Page Structure
```php
<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
session_start();

OPEN_BODY('Page Title');
require_once('master_mainMenu.php');  // Top navigation

OPEN_BOX('', 'Card Title');
// Page content here
CLOSE_BOX();

CLOSE_BODY();
?>
```

## Bootstrap 5 Integration

The system uses Bootstrap 5.3.0 (integrated in January 2025):

### Key Files
- `style/bootstrap-custom.css` - Custom Bootstrap theme with ERP color scheme
- `js/bootstrap-init.js` - Auto-converts legacy classes to Bootstrap classes

### Legacy to Bootstrap Class Mapping
The system auto-converts legacy classes:
- `.mybutton` → `.btn .btn-primary .btn-sm`
- `.myinputtext` → `.form-control .form-control-sm`
- `.mytextbox` → `.form-control .form-control-sm`
- `table.sortable` → `.table .table-striped .table-hover`

### Color Scheme
```css
--primary-color: #1E3A8A       /* Navy blue - navbar, headers */
--primary-light: #1E40AF       /* Blue 700 - borders, dropdowns */
--accent-color: #EA580C        /* Orange - hover states */
--success-color: #16A34A       /* Green - success messages */
--warning-color: #F59E0B       /* Amber - warnings */
--error-color: #DC2626         /* Red - errors */
--bg-main: #F9FAFB             /* Gray background */
```

## Menu System

Dynamic menu structure stored in database (`erpmill.menu` table):
- `master_mainMenu.php` - Renders Bootstrap navbar from database
- Menu items support nesting (master → child → grandchild)
- Access control via session variables (`$_SESSION['security']`, `$_SESSION['access_type']`)
- Multilingual support (ID, EN, MY) via `caption`, `caption2`, `caption3` fields

## Session Management

Key session variables:
- `$_SESSION['namauser']` - Username
- `$_SESSION['language']` - Current language (ID/EN/MY)
- `$_SESSION['access_type']` - Access control type (detail/standard)
- `$_SESSION['access_level']` - User access level
- `$_SESSION['allpriv']` - Comma-separated list of menu IDs user can access
- `$_SESSION['theme']` - UI theme (skyblue/black)

## Common Development Commands

### Starting XAMPP Services
```bash
# Start Apache
"C:\XAMPP\xampp\apache_start.bat"

# Start MySQL
net start MySQL

# Check Apache status
sc query Apache2.4

# Restart Apache (if needed)
"C:\XAMPP\xampp\apache\bin\httpd.exe" -k restart
```

### Database Operations
```bash
# Connect to MySQL
"C:\XAMPP\xampp\mysql\bin\mysql.exe" -u root -e "USE erpmill; SELECT * FROM user;"

# Check user logged status
"C:\XAMPP\xampp\mysql\bin\mysql.exe" -u root -e "SELECT namauser, logged, status FROM erpmill.user WHERE namauser='username';"
```

### Common URLs
- Login: http://localhost/erpmill/
- Main system: http://localhost/erpmill/index.php

## Important Patterns

### AJAX Pattern
Most `*_slave_*` files handle AJAX requests:
```php
<?
require_once('config/connection.php');
session_start();

// Get POST data
$action = $_POST['action'];

// Process and return JSON or HTML
echo json_encode($result);
?>
```

### Report Generation Pattern
Reports typically:
1. Accept date range parameters
2. Query database with complex JOINs
3. Generate HTML table or call FPDF
4. Offer PDF/Excel export options

### Database Query Pattern
```php
$str = "SELECT * FROM ".$dbname.".tablename WHERE condition";
$res = mysql_query($str);
while($row = mysql_fetch_object($res)) {
    // Process row
}
```

## Security Notes

- System uses legacy `mysql_*` functions (vulnerable to SQL injection)
- Session-based authentication (check `$_SESSION['namauser']`)
- User login tracking via `erpmill.user.logged` field
- Password stored as MD5 hash (legacy, not bcrypt)
- Most pages require session validation

## Code Style Guidelines

- Use `<?` short tags (not `<?php`)
- Database queries use `$dbname` variable for schema
- Echo HTML directly (no template engine)
- JavaScript functions often defined inline in PHP files
- Table class names: `sortable`, `data` for main data tables

## Testing Workflow

1. Clear browser cache (Ctrl+Shift+R) after CSS/JS changes
2. Check browser console for JavaScript errors
3. Monitor Apache error logs: `C:\XAMPP\xampp\apache\logs\error.log`
4. Test on Chrome, Firefox, Edge for compatibility
5. Test responsive design at breakpoints: 768px, 992px, 1200px

## Module-Specific Notes

### Budget Module (`bgt_*`)
- Master data setup: `bgt_2pt.php`, `bgt_departemen.php`
- Budget allocation: `bgt_budget_sebaran.php`
- Reports: `bgt_laporan_*` files with PDF/Excel export

### Plantation Module (`kebun_*`)
- Production tracking: `kebun_produksi.php`, `kebun_operasional_*.php`
- Rainfall data: `kebun_curahHujan.php`
- Harvest planning: `kebun_rencanapanen.php`
- Uses complex block/estate hierarchy

### Finance Module (`keu_*`)
- Chart of accounts: `keu_5daftarperkiraan_*.php`
- General journal: `keu_jurnal.php`
- Financial statements: `keu_2neraca.php`, `keu_2rugilaba.php`
- Period closing: `keu_3tutupbulan.php`

## Documentation References

- Bootstrap implementation: `BOOTSTRAP_IMPLEMENTATION.md`
- Implementation summary: `IMPLEMENTATION_SUMMARY.md`
- Color scheme: `COLOR_SCHEME.md`
- Menu fix notes: `MENU_FIX.md`
- Font updates: `FONT_UPDATE.md`

## Common Issues

### Login Issues
- Check `erpmill.user.logged` field (should be 0 before login)
- Session cookies may need clearing
- MD5 password hash must match

### Display Issues
- Clear browser cache (Bootstrap CSS may be cached)
- Check if Bootstrap JS loaded (needed for dropdowns, modals)
- Verify `bootstrap-init.js` runs after DOM loads

### Database Issues
- Default connection assumes root with no password
- Check XAMPP MySQL service is running
- Legacy `mysql_*` functions require old PHP extension

## Backward Compatibility

When making changes:
- Legacy class names still work (auto-converted by JS)
- All PHP functions maintain original signatures
- Database schema must not break existing queries
- Frame-based navigation must continue to work
- Session variable names are hardcoded throughout system
