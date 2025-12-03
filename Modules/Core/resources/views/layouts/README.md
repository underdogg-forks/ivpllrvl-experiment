# Layout System Documentation

## Overview

The InvoicePlane application now uses a modern Blade layout system compatible with Laravel Filament design patterns. The layout provides a consistent header with navigation menu and a collapsible sidebar.

## Layout Structure

```
Modules/Core/resources/views/layouts/
├── app.blade.php                    # Main application layout
└── partials/
    ├── header.blade.php             # Header partial with menu
    └── sidebar.blade.php            # Sidebar partial with navigation
```

## How to Use

### Extending the Layout

In your view files, use:

```blade
@extends('core::layouts.app')

@section('content')
    <!-- Your page content here -->
@endsection
```

### Layout Features

#### Header
- **Logo/Title**: Displays application title (configurable via settings)
- **Menu Navigation**: Main menu items for Dashboard, Clients, Quotes, Invoices, Payments, Products
- **Toggle Button**: Collapses/expands sidebar
- **User Profile Dropdown**: 
  - User name and company
  - Link to profile
  - Link to settings
  - Logout button

#### Sidebar
- **Collapsible**: Can be toggled open/closed
- **Responsive**: 
  - Desktop: Shows full width (64px collapsed, 256px expanded)
  - Mobile: Hidden when collapsed, full width when expanded
- **Navigation Items**:
  - Dashboard
  - Clients
  - Quotes
  - Invoices
  - Payments
  - Products
  - Projects (if enabled)
  - Settings
  - Reports
- **Active State**: Highlights current page

## Filament Compatibility

The layout is designed to be compatible with Laravel Filament's design system:

- Uses Filament-inspired CSS classes (`fi-body`, `fi-panel-admin`)
- Tailwind CSS for styling
- Alpine.js for interactivity
- Dark mode support
- Responsive design patterns
- Simple Blade partials (no PHP component classes required)

## Customization

### Changing Logo/Title
Update the `custom_title` setting in the database.

### Adding Menu Items

**Header Menu**: Edit `Modules/Core/resources/views/layouts/partials/header.blade.php`
**Sidebar Menu**: Edit `Modules/Core/resources/views/layouts/partials/sidebar.blade.php`

### Styling
Main styles are loaded via Vite:
- `resources/assets/core/css/style-tailwind.css`
- `resources/assets/overrides/filament-fixes.css`

## Migration from Old Layout

Views previously extending `core::components.layouts.app` should now extend `core::layouts.app`.

### Example Migration

**Before:**
```blade
@extends('core::components.layouts.app')

@section('content')
    <!-- content -->
@endsection
```

**After:**
```blade
@extends('core::layouts.app')

@section('content')
    <!-- content -->
@endsection
```

## Layout Partials

The header and sidebar are included as simple Blade partials using:

```blade
@include('core::layouts.partials.header')
@include('core::layouts.partials.sidebar')
```

This approach keeps the layout system simple with just Blade templates, without requiring PHP component classes.
