# Professional Navy & Orange Color Scheme

Color scheme yang telah diterapkan pada aplikasi ERP Mill.

## Color Palette

### Primary Colors
- **Primary (Blue 900)**: `#1E3A8A`
  - Digunakan untuk: Navbar, header utama, elemen penting
- **Secondary (Blue 700)**: `#1E40AF`
  - Digunakan untuk: Border, secondary elements, dropdown backgrounds
- **Primary Lighter (Blue 500)**: `#3B82F6`
  - Digunakan untuk: Hover states pada primary elements

### Accent & Status Colors
- **Accent (Orange 600)**: `#EA580C`
  - Digunakan untuk: Hover states, highlights, call-to-action buttons
- **Success (Green 600)**: `#16A34A`
  - Digunakan untuk: Success messages, positive indicators, number inputs
- **Warning (Amber 500)**: `#F59E0B`
  - Digunakan untuk: Warning messages, dropdown headers
- **Error (Red 600)**: `#DC2626`
  - Digunakan untuk: Error messages, delete buttons, critical alerts

### Background Colors
- **Background (Gray 50)**: `#F9FAFB`
  - Digunakan untuk: Main background, body background
- **Surface (White)**: `#FFFFFF`
  - Digunakan untuk: Cards, table rows, content areas

### Text Colors
- **Text Primary (Gray 900)**: `#111827`
  - Digunakan untuk: Primary text, headings
- **Text Secondary (Gray 500)**: `#6B7280`
  - Digunakan untuk: Secondary text, labels, disabled text

### Interactive Colors
- **Hover Background**: `#FEF3C7` (Amber 100)
  - Digunakan untuk: Table cell hover, interactive element hover

## Files Updated

### CSS Files
1. **style/bootstrap-custom.css**
   - Updated CSS variables
   - Navbar colors
   - Dropdown menu colors
   - Button colors
   - Table styling

2. **style/generic.css**
   - Table headers and data cells
   - Form input borders
   - Menu elements
   - Box styling
   - Row content backgrounds

### PHP Files
1. **main_menuSettings.php**
   - Card headers
   - Button styles
   - Text colors

2. **master_mainMenu.php**
   - Navbar background color

## Usage Guidelines

### Buttons
- Primary actions: `#1E3A8A` (Primary)
- Secondary actions: `#1E40AF` (Secondary)
- Warnings/Alerts: `#F59E0B` (Warning)
- Dangerous actions: `#DC2626` (Error)
- Success/Confirm: `#16A34A` (Success)

### Text Hierarchy
- Headings: `#111827` (Text Primary)
- Body text: `#111827` (Text Primary)
- Labels/Secondary: `#6B7280` (Text Secondary)
- Disabled: `#6B7280` (Text Secondary)

### Interactive States
- Default hover: `#EA580C` (Accent)
- Table cell hover: `#FEF3C7` (Hover Background)
- Row hover: `#F9FAFB` (Background)

## CSS Variables

Semua warna disimpan sebagai CSS variables di `style/bootstrap-custom.css`:

```css
:root {
    --primary-color: #1E3A8A;
    --primary-light: #1E40AF;
    --primary-lighter: #3B82F6;
    --accent-color: #EA580C;
    --success-color: #16A34A;
    --warning-color: #F59E0B;
    --error-color: #DC2626;
    --bg-main: #F9FAFB;
    --bg-content: #FFFFFF;
    --bg-surface: #FFFFFF;
    --border-color: #1E40AF;
    --text-dark: #111827;
    --text-light: #6B7280;
    --text-secondary: #6B7280;
    --hover-color: #FEF3C7;
}
```

## Implementation Date
2025-10-07
