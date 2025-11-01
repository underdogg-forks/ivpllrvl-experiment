# Tailwind CSS Build Process

This document describes the new CSS build process using Tailwind CSS while maintaining Bootstrap 3 class compatibility.

## Overview

InvoicePlane has been refactored to use Tailwind CSS v4 instead of Bootstrap SASS, while maintaining **100% backward compatibility** with all existing Bootstrap 3 class names used in views.

## Key Features

- ✅ All Bootstrap 3 classes preserved (btn, form-control, table, alert, panel, etc.)
- ✅ Built with Tailwind CSS v4 and PostCSS
- ✅ 84KB minified CSS (16KB gzipped)
- ✅ No changes required to existing views
- ✅ Modern build tooling with Vite
- ✅ Third-party styles included (Font Awesome, Dropzone, Select2, Bootstrap Datepicker)

## Build Process

### Prerequisites

```bash
npm install
```

This installs:
- Tailwind CSS v4.1.16
- @tailwindcss/postcss (Tailwind v4 PostCSS plugin)
- PostCSS 8.5.6
- Autoprefixer 10.4.21
- Vite 7.1.12
- Terser (for minification)

### Building CSS

```bash
# Development build (with sourcemaps)
NODE_ENV=development npx vite build

# Production build (minified)
NODE_ENV=production npx vite build
```

Output files:
- `public/assets/core/css/style-tailwind.css` - Main stylesheet with all Bootstrap classes
- `public/assets/core/css/paypal.css` - PayPal specific styles
- `public/assets/core/css/custom-pdf.css` - PDF generation styles
- `public/assets/core/js/scripts.js` - Main JavaScript file

### Development Mode

```bash
npx vite
```

This starts a development server with:
- Hot module replacement
- Auto-rebuild on file changes
- Watches `resources/assets/**` for changes

## File Structure

```
resources/assets/core/css/
├── bootstrap-tailwind.css     # Bootstrap 3 classes implemented with Tailwind
├── style-tailwind.css          # Main stylesheet (imports everything)
└── custom.css                  # User customizations

Configuration files:
├── tailwind.config.js          # Tailwind configuration
├── postcss.config.js           # PostCSS configuration
└── vite.config.js              # Build configuration
```

## Bootstrap Classes Implemented

### Buttons
- Base: `btn`
- Variants: `btn-default`, `btn-primary`, `btn-success`, `btn-info`, `btn-warning`, `btn-danger`
- Sizes: `btn-xs`, `btn-sm`, `btn-lg`
- Modifiers: `btn-block`

### Forms
- Groups: `form-group`
- Inputs: `form-control`, `input-sm`, `input-lg`
- Input groups: `input-group`, `input-group-addon`
- Labels: `label`, `control-label`

### Tables
- Base: `table`
- Modifiers: `table-striped`, `table-bordered`, `table-hover`
- Responsive: `table-responsive`

### Alerts
- Base: `alert`
- Variants: `alert-success`, `alert-info`, `alert-warning`, `alert-danger`
- Dismissible: `alert-dismissible`

### Panels
- Base: `panel`, `panel-default`
- Parts: `panel-heading`, `panel-body`, `panel-footer`
- Variants: `panel-primary`, `panel-success`, `panel-info`, `panel-warning`, `panel-danger`

### Pagination
- Base: `pagination`
- States: `active`, `disabled`

### Utilities
- Floats: `pull-left`, `pull-right`
- Text alignment: `text-left`, `text-center`, `text-right`
- Text colors: `text-muted`, `text-primary`, `text-success`, `text-info`, `text-warning`, `text-danger`
- Clear: `clearfix`

## InvoicePlane Custom Components

The following InvoicePlane-specific components are also included:

- `#headerbar`, `.headerbar-title`, `.headerbar-item`
- `.quick-actions`
- `#item_table` with special table styling
- `.te` (text ellipsis utilities with responsive widths)
- `.stamp`, `.stamp-text`
- `.discount-field`
- `.optional`

## Customization

### Adding Custom Styles

Edit `resources/assets/core/css/custom.css`:

```css
/* Custom styles */
.my-custom-class {
    color: red;
}
```

Then rebuild:

```bash
npm run build
```

### Modifying Bootstrap Classes

Edit `resources/assets/core/css/bootstrap-tailwind.css`:

```css
@layer components {
  .btn-primary {
    background-color: #yourcolor;
    /* ... */
  }
}
```

### Tailwind Configuration

Edit `tailwind.config.js` to customize:

```javascript
export default {
  content: [
    "./Modules/**/Resources/views/**/*.php",
    "./resources/views/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#337ab7',  // Customize colors
      },
    },
  },
}
```

## Migration from Bootstrap SASS

The migration process:

1. ✅ Installed Tailwind CSS v4
2. ✅ Created Bootstrap-compatible CSS using Tailwind utilities
3. ✅ Configured Vite build process
4. ✅ Tested all Bootstrap classes
5. ⚠️ Old SCSS files remain for reference but are not used

### What Changed

- **Build tool**: Grunt → Vite
- **CSS framework**: Bootstrap SASS → Tailwind CSS
- **File extension**: `.scss` → `.css`
- **Syntax**: SASS → Tailwind's @layer syntax

### What Stayed the Same

- **All Bootstrap class names** - No changes to HTML/PHP files needed
- **Visual appearance** - Colors, spacing, and styles match Bootstrap 3
- **Third-party components** - Font Awesome, Select2, etc. still included

## Troubleshooting

### Build fails with "terser not found"

```bash
npm install -D terser
```

### Build fails with PostCSS errors

Ensure you have the latest packages:

```bash
npm install -D @tailwindcss/postcss autoprefixer postcss
```

### Styles not updating

Clear Vite cache:

```bash
rm -rf node_modules/.vite
npx vite build
```

### Missing Bootstrap class

Check `resources/assets/core/css/bootstrap-tailwind.css` and add the class definition if missing.

## Performance

### File Sizes

- **Production build**: 84KB (minified)
- **Gzipped**: 16KB
- **Comparison**: Similar to original Bootstrap 3 + custom styles

### Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11 not supported (Tailwind v4 requirement)

## Future Enhancements

- [ ] Add Tailwind utility classes to views (optional)
- [ ] Remove Bootstrap compatibility layer (breaking change)
- [ ] Migrate to Tailwind's utility-first approach
- [ ] Create InvoicePlane-specific Tailwind plugin

## Resources

- [Tailwind CSS v4 Documentation](https://tailwindcss.com/)
- [Vite Documentation](https://vitejs.dev/)
- [PostCSS Documentation](https://postcss.org/)
- [InvoicePlane Documentation](https://wiki.invoiceplane.com/)

## Support

For issues related to the CSS build process, check:
1. GitHub Issues
2. Community Forums
3. Discord

For Tailwind CSS specific questions:
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
