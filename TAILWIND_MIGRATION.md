# Tailwind Migration Prompt

You are converting admin templates from Tabler CSS to Tailwind CSS. Follow these rules strictly.

## Critical Rules

1. **No inline styles, no CSS files.** Use only Tailwind utility classes. The sole exception is EditorJS or third-party libraries where Tailwind alone cannot work — but always ask before writing any CSS.
2. **Dark/light mode must always work.** Every color choice needs a dark mode equivalent. Use the design token classes (e.g. `bg-surface`, `text-content`, `border-border`) which auto-switch. For non-token colors (red, green, amber, etc.), always add `dark:` variants.
3. **Use existing Twig components** instead of raw HTML where possible (Button, Alert, Badge, Tag, DataTable, Sidebar, Modal, etc.).

## Design System

### Color Tokens (use these instead of raw Tailwind colors)

Backgrounds: `bg-surface`, `bg-surface-raised`, `bg-surface-overlay`
Borders: `border-border`, `border-border-strong`
Text: `text-content`, `text-content-secondary`, `text-content-tertiary`
Accent: `bg-accent`, `bg-accent-hover`, `bg-accent-subtle`, `text-accent`

These are defined as CSS custom properties in `tailwind.css` with automatic dark mode overrides.

### Typography

- Font: Inter (`font-sans`)
- Body text: `text-[13px]`
- Labels/captions: `text-[12px]` or `text-[11px]`
- Section headers: `text-[11px] font-medium uppercase tracking-wider text-content-tertiary`
- Page titles: `text-lg font-semibold`

### Icons

Phosphor Icons via `<twig:ux:icon name="ph:icon-name" class="h-4 w-4" />`. Common sizes: `h-3.5 w-3.5`, `h-4 w-4`, `h-5 w-5`. Always add `shrink-0` when inside flex containers.

### Spacing & Layout

- Max content width: `mx-auto max-w-7xl px-4 lg:px-6`
- Card containers: `rounded-xl border border-border`
- Section spacing: `space-y-0.5` for nav items, `gap-2` or `gap-3` for flex groups
- Padding: `px-4 py-3` for card headers, `px-2.5 py-1.5` for interactive elements

### Status/Semantic Colors Pattern

For colored UI elements, always include dark mode:
```
border-green-500/30 bg-green-50 text-green-800 dark:bg-green-500/10 dark:text-green-300
border-red-500/30 bg-red-50 text-red-800 dark:bg-red-500/10 dark:text-red-300
border-amber-500/30 bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300
```

## Available Twig Components

| Component | Usage | Key variants |
|-----------|-------|-------------|
| `<twig:Xutim:Admin:Button>` | Buttons/links | primary, secondary, ghost, danger, warning, success, nav, dropdown, card-link |
| `<twig:Xutim:Admin:Alert>` | Flash/status messages | success, error, warning, info |
| `<twig:Xutim:Admin:Badge>` | Status badges | — |
| `<twig:Xutim:Admin:Tag>` | Tag labels | — |
| `<twig:Xutim:Admin:DataTable>` | Paginated tables with filters | — |
| `<twig:Xutim:Admin:Modal>` | Modal dialogs | — |
| `<twig:Xutim:Admin:ModalDialog>` | Standalone dialog | — |
| `<twig:Xutim:Admin:ModalForm>` | Form inside modal | — |
| `<twig:Xutim:Admin:Sidebar>` | Right-side panel | — |
| `<twig:Xutim:Admin:SidebarSection>` | Section within sidebar | — |
| `<twig:Xutim:Admin:SidebarItem>` | Item in sidebar section | — |
| `<twig:Xutim:Admin:SidebarTabs>` | Tabbed sidebar | — |
| `<twig:Xutim:Admin:ListGroup>` | Grouped list | — |
| `<twig:Xutim:Admin:ListGroupItem>` | Item in grouped list | — |
| `<twig:Xutim:Admin:Breadcrumbs>` | Breadcrumb navigation | — |
| `<twig:Xutim:Admin:Placeholder>` | Loading skeleton | — |

## Common Patterns

### Sidebar nav link (active/inactive)
```twig
<a href="..." class="group flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-[13px] font-medium {{ isActive ? 'bg-accent-subtle text-accent' : 'text-content-secondary hover:bg-surface-raised hover:text-content' }} transition-colors">
    <twig:ux:icon name="ph:icon" class="h-4 w-4 shrink-0" />
    Label
</a>
```

### Form inputs
```twig
<input class="w-full rounded-md border border-border bg-surface px-2.5 py-1.5 text-[12px] focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent" />
<select class="rounded-md border border-border bg-surface px-2 py-1 text-[12px] focus:border-accent focus:outline-none" />
```

### Table header row
```twig
<tr class="border-b border-border bg-surface-raised text-left">
    <th class="px-4 py-2.5 font-medium text-content-tertiary whitespace-nowrap">...</th>
</tr>
```

### Table body
```twig
<tbody class="divide-y divide-border">
    <tr class="hover:bg-surface-raised transition-colors">
        <td class="px-4 py-2.5">...</td>
    </tr>
</tbody>
```

### Action icon button (header/toolbar)
```twig
<button class="rounded-md p-1.5 text-content-tertiary hover:text-content hover:bg-surface-raised transition-colors">
    <twig:ux:icon name="ph:icon" class="h-4 w-4" />
</button>
```

### Notification badge
```twig
<span class="ml-auto flex h-4 min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">5</span>
```

## Tabler to Tailwind Quick Reference

| Tabler | Tailwind equivalent |
|--------|-------------------|
| `card` | `rounded-xl border border-border` |
| `card-header` | `flex items-center justify-between px-4 py-3 border-b border-border` |
| `card-body` | `p-4` |
| `btn btn-primary` | `<twig:Xutim:Admin:Button variant="primary">` |
| `btn btn-secondary` | `<twig:Xutim:Admin:Button variant="secondary">` |
| `btn btn-ghost-*` | `<twig:Xutim:Admin:Button variant="ghost">` |
| `btn btn-danger` | `<twig:Xutim:Admin:Button variant="danger">` |
| `table table-vcenter` | `w-full text-[13px]` with divide-y tbody |
| `badge` | `<twig:Xutim:Admin:Badge>` or inline class |
| `alert alert-success` | `<twig:Xutim:Admin:Alert type="success">` |
| `modal` | `<twig:Xutim:Admin:Modal>` |
| `page-header` | Already handled by base.html.twig `pageTitle` block |
| `icon ti-*` | `<twig:ux:icon name="ph:*" class="h-4 w-4" />` |
| `row` / `col-*` | `grid grid-cols-*` or `flex` |
| `mb-3` | `mb-3` (same) |
| `d-flex` | `flex` |
| `d-none` | `hidden` |
| `text-muted` | `text-content-secondary` or `text-content-tertiary` |
| `fw-bold` | `font-semibold` or `font-bold` |
| `form-control` | Manual: `w-full rounded-md border border-border bg-surface ...` |
| `form-label` | `block text-[12px] font-medium text-content-secondary mb-1.5` |

## Testing App (Pulse)

The testing application at `/project/Taize/pulse` has:
- **Override templates** in `templates/bundles/XutimCoreBundle/admin/` and `templates/bundles/XutimMediaBundle/admin/`
- **Custom Stimulus controllers** in `assets/controllers/` (translation-modal, fullscreen-panel, ai-locale-row, clickable-row, etc.)
- **Custom admin templates** in `templates/taize/admin/` (country management, local prayer, color pickers, etc.)
- **Additional CSS** in `assets/styles/app.css` (Taize brand colors only)
- **Its own Tailwind config** in `assets/tailwind/styles/app.css` extending the core with brand colors

Pulse templates that override core templates also need to be migrated. The Pulse-specific templates in `templates/taize/admin/` will also need migration.

## Migration Process

When converting a template:
1. Read the existing template fully
2. Identify all Tabler classes and Bootstrap grid usage
3. Replace with Tailwind equivalents using this guide
4. Ensure dark mode works for all color choices
5. Replace Tabler icons (`ti-*`) with Phosphor equivalents (`ph:*`)
6. Use Twig components where applicable
7. Keep all Stimulus controller data attributes and Turbo frames intact
8. Preserve all existing functionality (routes, forms, modals, etc.)
