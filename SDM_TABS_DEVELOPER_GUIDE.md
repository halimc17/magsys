# SDM Bootstrap 5 Tabs - Developer Quick Reference

## Quick Start Guide

### For Developers Working with SDM Tab Files

---

## Tab Implementation Pattern

All SDM files now use this standard Bootstrap 5 pattern:

```php
// 1. Define tab headers
$hfrm[0] = 'Tab 1 Name';
$hfrm[1] = 'Tab 2 Name';
$hfrm[2] = 'Tab 3 Name';

// 2. Define tab content
$frm[0] = "<fieldset>Content for Tab 1</fieldset>";
$frm[1] = "<fieldset>Content for Tab 2</fieldset>";
$frm[2] = "<fieldset>Content for Tab 3</fieldset>";

// 3. Generate Bootstrap 5 tabs
echo "<ul class='nav nav-tabs' id='FRMTab' role='tablist'>";
for($i=0; $i<count($hfrm); $i++) {
    $active = ($i==0) ? "active" : "";
    $ariaSelected = ($i==0) ? "true" : "false";
    echo "<li class='nav-item' role='presentation'>
            <button class='nav-link $active' id='FRM".$i."-tab'
                    data-bs-toggle='tab' data-bs-target='#FRM".$i."'
                    type='button' role='tab' aria-controls='FRM".$i."'
                    aria-selected='$ariaSelected'>
                ".$hfrm[$i]."
            </button>
          </li>";
}
echo "</ul>";

echo "<div class='tab-content mt-3' id='FRMTabContent'>";
for($i=0; $i<count($frm); $i++) {
    $active = ($i==0) ? "show active" : "";
    echo "<div class='tab-pane fade $active' id='FRM".$i."'
              role='tabpanel' aria-labelledby='FRM".$i."-tab'>
            ".$frm[$i]."
          </div>";
}
echo "</div>";
```

---

## File Reference Matrix

| File | Tabs | Purpose | JavaScript File |
|------|------|---------|----------------|
| `sdm_data_karyawan.php` | 7 | Employee master data | `js/datakaryawan.js` |
| `sdm_penggantianTransport.php` | 3 | Transport reimbursement | `js/sdm_jatahBBM.js` |
| `sdm_promosi.php` | 2 | Promotion/demotion | `js/sdm_promosi.js` |
| `sdm_suratPeringatan.php` | 2 | Warning letters | `js/sdm_sp.js` |
| `sdm_3revisipjd.php` | 1 | PJD revision | `js/sdm_3revisipjd.js` |
| `sdm_2summarykaryawan.php` | 1 | Employee summary | `js/sdm_2summarykaryawan.js` |
| `sdm_2totalkomponengaji.php` | 2 | Salary component total | N/A |
| `sdm_2laporanKehadiranHO.php` | 3 | HO attendance report | `js/sdm_2rekapabsenho.js` |

---

## Common Operations

### 1. Adding a New Tab

```php
// Add to existing arrays
$hfrm[3] = 'New Tab Name';  // Add header
$frm[3] = "<fieldset>New tab content...</fieldset>";  // Add content

// Tab generation code automatically includes it
```

### 2. Removing a Tab

```php
// Simply don't define the array element
// For example, to remove tab 2:
$hfrm[0] = 'Tab 1';
$hfrm[1] = 'Tab 2';
// $hfrm[2] = 'Tab 3';  // Comment out
$hfrm[3] = 'Tab 4';

// Re-index array
$hfrm = array_values($hfrm);
$frm = array_values($frm);
```

### 3. Changing Tab Order

```php
// Reorder array elements
$hfrm[0] = 'Second Tab';  // Was first
$hfrm[1] = 'First Tab';   // Was second
// Order changes automatically
```

### 4. Programmatically Switching Tabs

```javascript
// Using Bootstrap 5 JavaScript
var tabEl = document.querySelector('#FRM1-tab');
var tab = new bootstrap.Tab(tabEl);
tab.show();

// Or using jQuery
$('#FRM1-tab').tab('show');
```

---

## Form Controls Best Practices

### Within Tab Content

Use Bootstrap 5 classes for all form elements:

```php
$frm[0] = "
<fieldset>
    <legend>Form Title</legend>
    <table class='table-sm'>
        <tr>
            <td>Label:</td>
            <td>
                <!-- Text Input -->
                <input type='text' id='myField'
                       class='form-control form-control-sm' />
            </td>
        </tr>
        <tr>
            <td>Select:</td>
            <td>
                <!-- Select Dropdown -->
                <select id='mySelect'
                        class='form-select form-select-sm'>
                    <option>Option 1</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <!-- Buttons -->
                <button class='btn btn-primary btn-sm'
                        onclick='saveData()'>Save</button>
                <button class='btn btn-secondary btn-sm ms-2'
                        onclick='clearForm()'>Cancel</button>
            </td>
        </tr>
    </table>
</fieldset>
";
```

---

## Bootstrap 5 Classes Reference

### Tab Navigation Classes
```css
.nav                /* Base navigation */
.nav-tabs          /* Tab styling */
.nav-item          /* Tab item wrapper */
.nav-link          /* Tab button */
.active            /* Active tab */
```

### Tab Content Classes
```css
.tab-content       /* Content container */
.tab-pane          /* Individual pane */
.fade              /* Fade transition */
.show              /* Visible state */
.active            /* Active pane */
```

### Form Control Classes
```css
.form-control      /* Text inputs, textareas */
.form-control-sm   /* Small size */
.form-select       /* Select dropdowns */
.form-select-sm    /* Small size */
.btn               /* Buttons */
.btn-primary       /* Primary button */
.btn-secondary     /* Secondary button */
.btn-sm            /* Small button */
```

### Utility Classes
```css
.mt-3              /* Margin top */
.ms-2              /* Margin start (left) */
.me-2              /* Margin end (right) */
.mb-3              /* Margin bottom */
.d-inline-block    /* Inline block display */
```

---

## JavaScript Integration

### Accessing Tab Elements

```javascript
// Get active tab
var activeTab = document.querySelector('.nav-link.active');

// Get specific tab
var tab1 = document.querySelector('#FRM0-tab');

// Get tab content
var content1 = document.querySelector('#FRM0');
```

### Tab Events

```javascript
// Listen for tab show event
var tabEl = document.querySelector('#FRM1-tab');
tabEl.addEventListener('shown.bs.tab', function (event) {
    console.log('Tab shown:', event.target);
    // Load data, refresh content, etc.
});

// Listen for tab hide event
tabEl.addEventListener('hidden.bs.tab', function (event) {
    console.log('Tab hidden:', event.target);
    // Save data, cleanup, etc.
});
```

### Common Patterns

```javascript
// Load data when tab is shown
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tabEl) {
    tabEl.addEventListener('shown.bs.tab', function (event) {
        var tabId = event.target.getAttribute('data-bs-target');
        loadTabData(tabId);
    });
});

// Validate before switching tabs
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tabEl) {
    tabEl.addEventListener('show.bs.tab', function (event) {
        if (!validateCurrentTab()) {
            event.preventDefault();
            alert('Please complete the current tab first');
        }
    });
});
```

---

## AJAX Integration

### Loading Tab Content Dynamically

```javascript
function loadTabData(tabId) {
    // Show loading indicator
    document.querySelector(tabId).innerHTML =
        '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

    // Load via AJAX
    fetch('sdm_slave_data.php?tab=' + tabId)
        .then(response => response.text())
        .then(data => {
            document.querySelector(tabId).innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading tab:', error);
        });
}
```

---

## Troubleshooting

### Tab Not Switching
**Problem:** Clicking tab doesn't switch content

**Solution:**
1. Check Bootstrap JavaScript is loaded
2. Verify `data-bs-toggle="tab"` attribute
3. Ensure `data-bs-target` matches tab pane ID
4. Check for JavaScript errors in console

### Content Not Visible
**Problem:** Tab content hidden or not displaying

**Solution:**
1. Verify first tab has `show active` classes
2. Check `tab-pane` has correct ID
3. Ensure `fade` class is present
4. Verify tab content is inside `tab-content` div

### Forms Not Submitting
**Problem:** Form submission in tab doesn't work

**Solution:**
1. Check form is inside `tab-pane` div
2. Verify button onclick handlers are working
3. Check for JavaScript errors
4. Ensure AJAX endpoints are correct

### Styling Issues
**Problem:** Tabs don't look right

**Solution:**
1. Clear browser cache (Ctrl+Shift+R)
2. Verify Bootstrap CSS is loaded
3. Check for conflicting custom CSS
4. Inspect elements in browser dev tools

---

## Performance Tips

1. **Lazy Load Tab Content**
   - Only load data when tab is shown
   - Use `shown.bs.tab` event

2. **Cache Tab Data**
   - Store loaded data in JavaScript variables
   - Avoid reloading on every tab switch

3. **Minimize DOM Manipulation**
   - Build HTML strings, insert once
   - Use document fragments for multiple elements

4. **Optimize Table Rendering**
   - Use pagination for large datasets
   - Implement virtual scrolling

---

## Security Considerations

1. **Input Validation**
   ```javascript
   function saveData() {
       var input = document.getElementById('myField').value;
       if (!validateInput(input)) {
           alert('Invalid input');
           return false;
       }
       // Proceed with save
   }
   ```

2. **CSRF Protection**
   ```php
   // Include CSRF token in forms
   echo "<input type='hidden' name='csrf_token' value='".$_SESSION['csrf_token']."'>";
   ```

3. **XSS Prevention**
   ```php
   // Escape output
   echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
   ```

---

## Testing Checklist

Before deploying changes:

- [ ] All tabs render correctly
- [ ] Tab switching works smoothly
- [ ] Forms within tabs submit properly
- [ ] AJAX calls load data correctly
- [ ] Tables display and sort properly
- [ ] Date pickers function in all tabs
- [ ] Validation works across tabs
- [ ] Save/Cancel buttons work
- [ ] No JavaScript console errors
- [ ] Tested in Chrome, Firefox, Edge
- [ ] Mobile responsive (test at 768px, 992px)
- [ ] Print layout works

---

## Support & Resources

### Internal Documentation
- `BOOTSTRAP_IMPLEMENTATION.md` - Overall Bootstrap guide
- `SDM_BOOTSTRAP_TABS_IMPLEMENTATION_PART1.md` - Implementation details
- `SDM_TABS_BEFORE_AFTER.md` - Migration examples

### External Resources
- [Bootstrap 5 Tabs Documentation](https://getbootstrap.com/docs/5.3/components/navs-tabs/)
- [Bootstrap 5 Forms](https://getbootstrap.com/docs/5.3/forms/overview/)
- [Bootstrap 5 JavaScript](https://getbootstrap.com/docs/5.3/getting-started/javascript/)

### Contact
For questions or issues:
1. Check this documentation first
2. Review existing implementations
3. Test in browser dev tools
4. Consult team lead if needed

---

**Last Updated:** January 2025
**Version:** 1.0
**Maintainer:** ERP Development Team
