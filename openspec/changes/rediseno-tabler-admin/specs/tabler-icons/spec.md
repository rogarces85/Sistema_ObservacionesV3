## ADDED Requirements

### Requirement: Icons use Tabler Icons SVG set
All interface icons SHALL use Tabler Icons SVG instead of emoji characters or custom images. Icons SHALL be rendered via a PHP helper function `tablerIcon(string $name, string $size = 'md'): string`.

#### Scenario: Helper function returns SVG markup
- **WHEN** `tablerIcon('home')` is called
- **THEN** it SHALL return a string containing `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">` with the appropriate `<path>` elements for the "home" icon

#### Scenario: Icon size class is applied
- **WHEN** `tablerIcon('home', 'lg')` is called
- **THEN** the returned SVG SHALL include class `.icon.icon-lg`

### Requirement: Emoji icons replaced across all views
All emoji characters used as UI icons (📊, 📝, ✅, ⏳, ⚠️, 🔍, ⚡, 📥, 👁️, 📄, 📋, ➕, 🚨, 🗑️, 👤, etc.) SHALL be replaced with equivalent Tabler Icons.

#### Scenario: Dashboard stats use SVG icons
- **WHEN** dashboard stat cards render
- **THEN** each card icon SHALL use `tablerIcon()` output, not emoji

#### Scenario: Action buttons use SVG icons
- **WHEN** any button or link has an icon
- **THEN** it SHALL use `tablerIcon()` output instead of emoji

### Requirement: Tabler Icons CDN is loaded
The Tabler Icons webfont CSS SHALL be loaded from CDN as a fallback for any icons not yet migrated.

#### Scenario: Icon stylesheet loads
- **WHEN** any page loads
- **THEN** SHALL include `<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">`
