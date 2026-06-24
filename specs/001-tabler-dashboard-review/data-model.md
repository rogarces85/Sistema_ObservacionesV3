# Data Model: Tabler Dashboard Review

This feature does not introduce database tables or persistent application data. The following entities describe the evaluation records that must be produced in documentation.

## Entity: View Evaluation

Represents the review result for a primary system view.

Fields:

- `view_name`: Human-readable name, such as Dashboard or Observaciones.
- `path`: Project-relative file path, such as `views/dashboard.php`.
- `roles_checked`: One or more of `registrador`, `supervisor`.
- `tabler_status`: One of `adopted`, `mixed`, `legacy`, `unknown`.
- `responsive_status`: One of `pass`, `conditional`, `fail`, `not_checked`.
- `accessibility_status`: One of `pass`, `conditional`, `fail`, `not_checked`.
- `business_flow_status`: One of `unchanged`, `risk_detected`, `not_checked`.
- `findings`: List of visual, responsive, accessibility, or role-specific findings.
- `recommendation`: One of `approve`, `approve_with_conditions`, `rework`, `defer`.

Validation rules:

- Every primary view listed in the spec must have one View Evaluation.
- `roles_checked` must include every role that can access the view.
- `business_flow_status` must not be `risk_detected` without a linked finding.

## Entity: Component Evaluation

Represents the review result for a reusable UI component or pattern.

Fields:

- `component_name`: Header, sidebar, table, form, modal, card, chart, badge, toast, dropdown, pagination, or loading overlay.
- `locations`: Views or includes where the component appears.
- `current_pattern`: One of `tabler`, `legacy`, `mixed`, `custom_dynamic`.
- `risk_level`: One of `low`, `medium`, `high`.
- `known_conflicts`: List of CSS or JavaScript conflicts.
- `acceptance_criteria`: Conditions that must be true before approving the component.

Validation rules:

- Components marked `mixed` or `custom_dynamic` must have explicit known conflicts or rationale.
- Components with `high` risk must have mitigation actions before final approval.

## Entity: Asset Strategy Decision

Represents the recommendation for loading Tabler assets.

Fields:

- `strategy`: One of `cdn`, `local_compiled_assets`, `package_build_pipeline`, `hybrid`.
- `availability_risk`: One of `low`, `medium`, `high`.
- `maintenance_cost`: One of `low`, `medium`, `high`.
- `customization_capacity`: One of `low`, `medium`, `high`.
- `recommended`: Boolean.
- `rationale`: Explanation of why the strategy is or is not recommended.

Validation rules:

- At least two strategies must be compared.
- Exactly one strategy should be marked recommended unless the final recommendation is to postpone.

## Entity: Adoption Decision

Represents the final outcome of the evaluation.

Fields:

- `decision`: One of `consolidate`, `consolidate_with_conditions`, `postpone`, `partial_revert`.
- `rationale`: Business and technical reason for the decision.
- `blocking_issues`: List of issues that prevent full approval.
- `next_actions`: Prioritized follow-up actions.
- `evidence_links`: Links to view evaluations, component evaluations, and quickstart results.

Validation rules:

- `decision` must align with the view/component findings.
- `consolidate` is only valid if no high-risk blocking issues remain.
- `partial_revert` requires explicit list of components/views to revert and why.

## State Transitions

View Evaluation:

```text
not_checked -> in_review -> pass
not_checked -> in_review -> conditional
not_checked -> in_review -> fail
conditional -> pass
fail -> conditional
fail -> pass
```

Adoption Decision:

```text
draft -> consolidate
draft -> consolidate_with_conditions
draft -> postpone
draft -> partial_revert
```

No application database state transitions are introduced.
