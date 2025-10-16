# Bootstrap 5 Migration Patterns - Setup Modules
Quick reference guide for converting legacy HTML to Bootstrap 5

## Pattern 1: Fieldset to Card

### BEFORE (Legacy)
```html
<fieldset style='float:left;'>
    <legend>Form Title</legend>
    <table border=0 cellpadding=1 cellspacing=1>
        <tr>
            <td>Label</td>
            <td>:</td>
            <td><input type=text class=myinputtext id=field></td>
        </tr>
    </table>
</fieldset>
```

### AFTER (Bootstrap 5)
```html
<div class='card mb-3'>
    <div class='card-header'>
        <h6 class='mb-0'>Form Title</h6>
    </div>
    <div class='card-body'>
        <div class='row mb-3'>
            <label class='col-sm-3 col-form-label'>Label</label>
            <div class='col-sm-9'>
                <input type='text' class='form-control form-control-sm' id='field'>
            </div>
        </div>
    </div>
</div>
```

## Pattern 2: Data Table

### BEFORE (Legacy)
```html
<fieldset>
    <legend>List</legend>
    <table class=sortable cellspacing=1 border=0>
        <thead>
            <tr class=rowheader>
                <td>Column 1</td>
                <td>Column 2</td>
            </tr>
        </thead>
        <tbody>
            <tr class=rowcontent>
                <td>Data 1</td>
                <td>Data 2</td>
            </tr>
        </tbody>
    </table>
</fieldset>
```

### AFTER (Bootstrap 5)
```html
<div class='card'>
    <div class='card-header'>
        <h6 class='mb-0'>List</h6>
    </div>
    <div class='card-body'>
        <div class='table-responsive'>
            <table class='table table-striped table-hover table-sm sortable'>
                <thead class='table-light'>
                    <tr>
                        <th>Column 1</th>
                        <th>Column 2</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Data 1</td>
                        <td>Data 2</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
```

## Pattern 3: Input with Add-on (Percentage)

### BEFORE (Legacy)
```html
<input type=text id=field class=myinputtextnumber>%
```

### AFTER (Bootstrap 5)
```html
<div class='input-group' style='width:150px;'>
    <input type='text' id='field' class='form-control form-control-sm'>
    <span class='input-group-text'>%</span>
</div>
```

## Pattern 4: Buttons

### BEFORE (Legacy)
```html
<button class=mybutton onclick=save()>Save</button>
<button class=mybutton onclick=cancel()>Cancel</button>
```

### AFTER (Bootstrap 5)
```html
<button class='btn btn-primary btn-sm' onclick='save()'>Save</button>
<button class='btn btn-secondary btn-sm' onclick='cancel()'>Cancel</button>
```

## Pattern 5: Checkbox

### BEFORE (Legacy)
```html
<input type='checkbox' id=check name=check />Label Text
```

### AFTER (Bootstrap 5)
```html
<div class='form-check'>
    <input type='checkbox' class='form-check-input' id='check' name='check' />
    <label class='form-check-label' for='check'>Label Text</label>
</div>
```

## Pattern 6: Select Dropdown

### BEFORE (Legacy)
```html
<select id=dropdown style="width:100px;">
    <option value="1">Option 1</option>
</select>
```

### AFTER (Bootstrap 5)
```html
<select id='dropdown' class='form-select form-select-sm' style='width:180px;'>
    <option value="1">Option 1</option>
</select>
```

## Pattern 7: Search Button with Input

### BEFORE (Legacy)
```html
<input type=text id=field class=myinputtext disabled />
<img src=images/search.png class=dellicon onclick=search()>
```

### AFTER (Bootstrap 5)
```html
<div class='input-group' style='width:400px;'>
    <input type='text' id='field' class='form-control form-control-sm' disabled />
    <button class='btn btn-outline-secondary btn-sm' type='button' onclick='search()'>
        <img src='images/search.png' style='width:16px;height:16px;' alt='Search'>
    </button>
</div>
```

## Pattern 8: Textarea

### BEFORE (Legacy)
```html
<textarea id=text name=text></textarea>
```

### AFTER (Bootstrap 5)
```html
<textarea id='text' name='text' class='form-control form-control-sm' rows='3' style='width:350px;'></textarea>
```

## Pattern 9: Form Button Alignment

### BEFORE (Legacy)
```html
<tr>
    <td></td>
    <td></td>
    <td>
        <button class=mybutton>Save</button>
    </td>
</tr>
```

### AFTER (Bootstrap 5)
```html
<div class='row'>
    <div class='col-sm-9 offset-sm-3'>
        <button class='btn btn-primary btn-sm'>Save</button>
    </div>
</div>
```

## Pattern 10: Container for AJAX Content

### BEFORE (Legacy)
```html
<fieldset>
    <legend>List</legend>
    <div id=container>
        <script>loadData()</script>
    </div>
</fieldset>
```

### AFTER (Bootstrap 5)
```html
<div class='card'>
    <div class='card-header'>
        <h6 class='mb-0'>List</h6>
    </div>
    <div class='card-body'>
        <div id='container'>
            <script>loadData()</script>
        </div>
    </div>
</div>
```

## Class Conversion Quick Reference

| Legacy Class | Bootstrap 5 Class | Use Case |
|--------------|------------------|----------|
| `myinputtext` | `form-control form-control-sm` | Text input |
| `myinputtextnumber` | `form-control form-control-sm` | Number input |
| `mytextbox` | `form-control form-control-sm` | Text input |
| `mybutton` | `btn btn-primary btn-sm` | Primary button |
| `mybutton` (cancel) | `btn btn-secondary btn-sm` | Secondary button |
| `rowheader` | `table-light` | Table header |
| `rowcontent` | (remove, use default) | Table row |
| `sortable` | `table table-striped table-hover table-sm sortable` | Data table |
| `resicon` | (keep as is) | Icon sizing |
| `dellicon` | (keep as is) | Delete icon |

## Element Changes

| Legacy Element | Bootstrap 5 Element | Notes |
|----------------|---------------------|-------|
| `<td>` in thead | `<th>` | Use proper semantic HTML |
| `<fieldset>` | `<div class='card'>` | Cards for grouping |
| `<legend>` | `<div class='card-header'><h6>` | Header text |
| Table wrapper | `<div class='table-responsive'>` | Mobile scroll |
| `align=center` | `class='text-center'` | Utility class |
| `align=left` | `class='text-start'` | Utility class |
| `align=right` | `class='text-end'` | Utility class |

## Layout Grid

### Form Layout Structure
```html
<div class='card mb-3'>
    <div class='card-header'>
        <h6 class='mb-0'>Title</h6>
    </div>
    <div class='card-body'>
        <!-- Multiple rows -->
        <div class='row mb-3'>
            <label class='col-sm-3 col-form-label'>Label</label>
            <div class='col-sm-9'>
                <input type='text' class='form-control form-control-sm'>
            </div>
        </div>

        <!-- Button row -->
        <div class='row'>
            <div class='col-sm-9 offset-sm-3'>
                <button class='btn btn-primary btn-sm'>Save</button>
                <button class='btn btn-secondary btn-sm'>Cancel</button>
            </div>
        </div>
    </div>
</div>
```

## Spacing Utilities

- `mb-3` - Margin bottom (spacing between form rows)
- `mb-0` - No margin bottom (for card headers)
- `g-3` - Gap spacing for grid
- `offset-sm-3` - Offset column to align with inputs

## Responsive Breakpoints

- `col-sm-3` / `col-sm-9` - Small screens and up (≥576px)
- Use `col-12` for full width on mobile if needed
- `table-responsive` ensures horizontal scroll on small screens

## Important Notes

1. **Preserve PHP Logic:** Never change PHP variables, session calls, or database queries
2. **Keep JavaScript:** All onclick, onkeypress handlers must remain identical
3. **Maintain IDs:** Element IDs must stay the same for JavaScript compatibility
4. **Test AJAX:** After conversion, verify all AJAX save/load functions work
5. **Quote Attributes:** Use single quotes for PHP echo strings: `echo"<div class='card'>"`

## Examples from Updated Files

### pmn_5terminbayar.php - Input Group
```php
echo"<div class='row mb-3'>
    <label class='col-sm-3 col-form-label'>Termin 1</label>
    <div class='col-sm-9'>
        <div class='input-group' style='width:150px;'>
            <input type='text' id='satu' class='form-control form-control-sm'>
            <span class='input-group-text'>%</span>
        </div>
    </div>
</div>";
```

### pmn_5franco.php - Checkbox
```php
echo"<div class='row mb-3'>
    <label class='col-sm-3 col-form-label'>".$_SESSION['lang']['status']."</label>
    <div class='col-sm-9'>
        <div class='form-check'>
            <input type='checkbox' class='form-check-input' id='statFr' name='statFr' />
            <label class='form-check-label' for='statFr'>".$_SESSION['lang']['tidakaktif']."</label>
        </div>
    </div>
</div>";
```

### pmn_5klcustomer.php - Search Input Group
```php
echo"<div class='input-group' style='width:400px;'>
    <input type='hidden' id='akun_cust' />
    <input type='text' id='nama_akun' class='form-control form-control-sm' disabled='disabled'/>
    <button class='btn btn-outline-secondary btn-sm' type='button' onclick='searchAkun()'>
        <img src='images/search.png' style='width:16px;height:16px;' alt='Find'>
    </button>
</div>";
```

## Color Scheme (Auto-applied via CSS)

- Primary buttons: Navy blue (#1E3A8A)
- Hover: Orange (#EA580C)
- Table headers: Light gray background
- Card headers: Gradient blue
- Success: Green (#16A34A)
- Warning: Amber (#F59E0B)
- Error: Red (#DC2626)

## Auto-Conversion

The bootstrap-init.js file automatically converts legacy classes, but for new updates, use native Bootstrap classes directly for cleaner code.

**Legacy Auto-Conversion (Still Works):**
- `.mybutton` → `.btn .btn-primary .btn-sm`
- `.myinputtext` → `.form-control .form-control-sm`

**Recommended for New Code:**
Write Bootstrap classes directly in HTML/PHP for better maintainability.
