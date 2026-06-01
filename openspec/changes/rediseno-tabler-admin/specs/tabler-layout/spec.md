## ADDED Requirements

### Requirement: Admin layout shell
The system SHALL use Tabler's page layout structure: `.page` → `.page-sidebar` → `.page-wrapper` for all authenticated views.

#### Scenario: Authenticated page renders Tabler layout
- **WHEN** any authenticated page loads
- **THEN** the HTML SHALL contain `<div class="page">` as root container
- **THEN** the sidebar SHALL use `<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">`
- **THEN** the top navbar SHALL use `<header class="navbar navbar-expand-md d-print-none">`
- **THEN** main content SHALL be inside `.page-body > .container-xl`

### Requirement: Login page with Tabler
The login page SHALL use Tabler's `.page-center` + `.container-tight` layout with `.card-md`.

#### Scenario: Login renders centered card
- **WHEN** user visits the login page
- **THEN** the page SHALL use `<body class="page page-center">`
- **THEN** the form container SHALL use `.container-tight`
- **THEN** the login card SHALL use `.card.card-md`

### Requirement: Page headers use Tabler component
All page titles SHALL use `.page-header` with `.page-pretitle` and `.page-title`.

#### Scenario: Dashboard shows page header
- **WHEN** user navigates to dashboard
- **THEN** the heading section SHALL contain `.page-header` with `.page-title`

### Requirement: No redundant layout wrappers
View files SHALL NOT contain duplicate `.page-wrapper`, `.page-body`, or `.container-xl` wrappers since these are provided by `includes/header.php`.

#### Scenario: View files omit layout wrappers
- **WHEN** inspecting any view file in `views/`
- **THEN** it SHALL NOT contain `<div class="page-wrapper">` or `<div class="page-body">` or `<div class="container-xl">`

### Requirement: Sidebar with role-based navigation
The sidebar SHALL use Tabler's `.navbar-vertical` with role-filtered nav groups, active state via `.nav-link.active`, and group subtitles via `.nav-subtitle`.

#### Scenario: Supervisor sees all menu items
- **WHEN** a supervisor logs in
- **THEN** sidebar SHALL show all navigation groups

#### Scenario: Registrador sees limited menu
- **WHEN** a registrador logs in
- **THEN** sidebar SHALL hide supervision, usuarios, asignaciones, establecimientos, eliminadas items
