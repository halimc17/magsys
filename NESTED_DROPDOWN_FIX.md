# Nested Dropdown Fix - Bootstrap 5

## Masalah
Nested dropdown (submenu) tampil **di bawah** parent menu instead of **di sebelah kanan**.

## Solusi

### CSS Key Rules (`style/bootstrap-custom.css`)

```css
/* Parent menggunakan position: relative */
.dropend {
    position: relative !important;
}

/* Submenu di sebelah kanan dengan left: 100% */
.dropend > .dropdown-menu {
    position: absolute !important;
    top: 0 !important;
    left: 100% !important;
    margin-top: -6px !important;
    z-index: 9999 !important;
}

/* First level dropdown HARUS overflow: visible */
.navbar .nav-item > .dropdown-menu {
    overflow: visible !important;
    max-height: none !important;
}
```

### HTML Structure (`master_mainMenu.php`)

```html
<li class="dropdown dropend">
    <a class="dropdown-item dropdown-toggle">Transaksi</a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item">Sales Order</a></li>
    </ul>
</li>
```

## Testing
- Test file: `test_nested_simple.html` ✓
- Manual: PEMASARAN → Transaksi → Submenu muncul di kanan

## Date
Fixed: January 2025
Status: ✓ Working
