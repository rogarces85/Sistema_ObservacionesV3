# Research: Tabler Dashboard Review

## Decision: Treat Tabler as a consolidation effort, not a new installation

**Rationale**: The application already loads Tabler 1.4.0 by CDN in the shared header and login, includes `assets/css/tabler-override.css`, and has an existing OpenSpec change (`openspec/changes/migrar-tabler-dashboard`) with most migration tasks marked complete. Planning from a greenfield assumption would duplicate work and miss the actual risk: partial adoption plus unresolved visual/responsive verification.

**Alternatives considered**:

- Start a new Tabler migration from scratch: rejected because current code already has Tabler shell and view migration evidence.
- Ignore prior OpenSpec artifacts: rejected because the constitution requires source-of-truth traceability.

## Decision: Compare CDN current state against controlled local assets

**Rationale**: Tabler documentation and repository support both CDN distribution and package-managed installation. The current system uses CDN, which is simple and avoids adding a build toolchain. However, a health/public-sector internal system may need predictable availability even when internet/CDN access is restricted. The evaluation must compare simplicity versus resilience.

**Alternatives considered**:

- Keep CDN without evaluation: rejected because it leaves availability and integrity risk undocumented.
- Force npm/Sass adoption immediately: rejected because the PHP monolith currently has no Node build pipeline and the constitution prefers simple, justified changes.
- Vendor only compiled CSS/JS locally: viable future option if CDN risk is unacceptable.

## Decision: Preserve legacy CSS until audited safe to remove

**Rationale**: `assets/css/styles.css` is still loaded before `tabler-override.css`. The prior OpenSpec tasks explicitly note that legacy classes remain as fallback for dynamic content. Removing it without a component inventory can break modals, report tabs, badges, loading overlays, or JS-generated content.

**Alternatives considered**:

- Remove all legacy CSS now: rejected due to high regression risk.
- Keep legacy CSS indefinitely: rejected because it hides conflicts and undermines visual standardization.
- Mark selectors by view/component and remove only after evidence: selected as safest path.

## Decision: Use role-based visual walkthroughs as the primary verification method

**Rationale**: The feature is a design architecture evaluation, not a backend change. The highest-value validation is whether registradores and supervisores can complete their real UI tasks across dashboard, observations, supervision, reports, and administration without lost actions or confusing layouts.

**Alternatives considered**:

- Only inspect code: rejected because responsive and usability issues require visual review.
- Require automated browser tests immediately: rejected for this planning phase because no browser test infrastructure exists; can be recommended as follow-up.

## Decision: Scope excludes backend, database, and REM business logic changes

**Rationale**: The user request is about Tabler design architecture and pros/cons. The spec explicitly requires no changes to roles, data visibility, imports, exports, reports, or business rules. Any functional issue discovered during UI review must become a separate specification.

**Alternatives considered**:

- Bundle UI consolidation with backend cleanup: rejected because it violates feature focus and increases risk.
- Modify report/data behavior to fit UI components: rejected because visual architecture must adapt to REM workflows, not the reverse.

## Decision: Evaluation output must include a go/conditional-go/postpone/revert recommendation

**Rationale**: The purpose is to decide whether Tabler should become the dashboard standard. A plain research document is insufficient unless it produces a decision, rationale, risks, and next actions.

**Alternatives considered**:

- Produce only pros/cons: rejected because it does not drive implementation planning.
- Proceed directly to implementation: rejected because the system already has partial implementation and unresolved verification tasks.

## Pros of Tabler in this system

- Fits administrative dashboards with sidebar, cards, tables, forms, badges, modals, tabs, and status components.
- Works with server-rendered PHP views without requiring a SPA rewrite.
- Builds on Bootstrap-compatible conventions familiar to many maintainers.
- Offers responsive layout primitives that match the system's dashboard/reporting needs.
- Reduces the burden of maintaining large custom CSS for common UI patterns.
- MIT/open-source ecosystem reduces licensing risk for internal government/health operations.
- Current integration already proves feasibility in shared layout and several views.

## Cons and risks of Tabler in this system

- CDN dependency can affect availability in restricted or offline environments.
- Mixing Tabler, Bootstrap conventions, CSS overrides, and legacy CSS can create regressions.
- Without a Sass/build pipeline, theme customization is limited to compiled CSS overrides.
- Large operational tables may still need custom responsive behavior beyond default components.
- Visual migration can hide or expose role-specific controls if not checked per role.
- JavaScript behavior for modals/dropdowns/toasts can conflict with existing vanilla scripts.
- A partial migration can leave inconsistent user experience and long-lived CSS debt.

## Resolved unknowns

- **Is Tabler already present?** Yes; current code loads Tabler 1.4.0 and uses override CSS.
- **Is this implementation or evaluation?** Evaluation/consolidation plan first; implementation tasks may follow only after recommendation.
- **Does the feature require database changes?** No.
- **Does the feature require permission changes?** No.
- **Does the feature require external API changes?** No.
