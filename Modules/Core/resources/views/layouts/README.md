# Layout System Documentation

## Overview

The InvoicePlane application now uses a modern Blade layout system compatible with Laravel Filament design patterns. The layout provides a consistent header with navigation menu and a collapsible sidebar.

## Layout Structure

```
Modules/Core/resources/views/layouts/
└── app.blade.php                    # Main application layout
```

## Blade Components

### Component Classes
```
Modules/Core/src/View/Components/Layouts/
├── Header.php                       # Header component class
└── Sidebar.php                      # Sidebar component class
```

### Component Views
```
Modules/Core/resources/views/components/layouts/
├── header.blade.php                 # Header view with menu
└── sidebar.blade.php                # Sidebar view with navigation
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

## Customization

### Changing Logo/Title
Update the `custom_title` setting in the database.

### Adding Menu Items

**Header Menu**: Edit `Modules/Core/resources/views/components/layouts/header.blade.php`
**Sidebar Menu**: Edit `Modules/Core/resources/views/components/layouts/sidebar.blade.php`

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

## Components as Blade Components

The Header and Sidebar are registered as Blade components using:

```blade
<x-core::layouts.header />
<x-core::layouts.sidebar />
```

This follows Laravel's Blade component pattern and is compatible with Filament's component system.
