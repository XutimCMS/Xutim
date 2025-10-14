# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Xutim ("Shoo-teem") is a modular CMS system built with Symfony 7.3, Doctrine ORM, Turbo/Stimulus, and Editor.js. This repository is a **monorepo for bundle development** managed by `symplify/monorepo-builder`.

## Architecture

### Monorepo Structure

This is a bundle development monorepo, not a standalone application:

- **Monorepo root**: The root-level `composer.json` is used to merge all bundle dependencies using `make merge`. Individual bundle `composer.json` files are merged into this root file for unified dependency management.
- **Bundles**: Located in `src/Xutim/Bundle/` - Independent Symfony bundles providing CMS features
- **Domain**: A minimal `src/Xutim/Domain/src/DomainEvent.php` exists for shared domain contracts across bundles

### Bundles

Each bundle is independently defined with its own `composer.json` and follows a consistent structure:

- **CoreBundle**: Core CMS functionality, content management, articles, pages, tags, menus, revisions
- **MediaBundle**: Media library and file management
- **ResourceBundle**: Resource management with DataTable and Grid systems
- **SecurityBundle**: User authentication, roles, and permissions
- **EventBundle**: Event management functionality
- **AnalyticsBundle**: Analytics and tracking
- **RedirectBundle**: URL redirection management
- **SnippetBundle**: Reusable content snippets

### Code Organization Pattern

Bundles follow a consistent layered architecture:

```
src/Xutim/Bundle/{BundleName}/
├── assets/              # Frontend assets (JS, CSS, Stimulus controllers)
├── config/              # Bundle configuration (PHP-based config files)
├── src/
│   ├── Action/          # Controllers (organized by Admin/Public)
│   ├── Domain/          # Domain layer
│   │   ├── Model/       # Domain interfaces
│   │   ├── Event/       # Domain events
│   │   └── Data/        # DTOs
│   ├── Entity/          # Doctrine entities (implements Domain\Model interfaces)
│   ├── Repository/      # Doctrine repositories
│   ├── Form/            # Symfony form types
│   ├── Service/         # Application services
│   ├── Infra/           # Infrastructure (Doctrine, Layout, Image processing)
│   ├── DependencyInjection/
│   └── XutimBundleNameBundle.php
├── templates/           # Twig templates
└── tests/               # Unit and integration tests
```

**Key architectural principles:**

1. **Domain-driven design**: Domain interfaces in `Domain/Model/`, concrete implementations in `Entity/`
2. **Action-based controllers**: Controllers are called "Actions" and organized by Admin/Public scope
3. **Interface segregation**: Entities implement focused domain interfaces (e.g., `ArticleInterface`, `ContentTranslationInterface`)
4. **Traits for cross-cutting concerns**: `TimestampableTrait`, `FileTrait`, `ArchiveStatusTrait`, `BasicTranslatableTrait`, `PublishableTranslatableTrait`

### Frontend Architecture

- **Asset Mapper**: Uses Symfony AssetMapper (no Node.js build required for development)
- **Stimulus**: Controllers in bundle `assets/` directories (e.g., `CoreBundle/assets/admin/controllers/`)
- **Turbo**: Hotwire Turbo for SPA-like navigation
- **Tabler CSS**: Admin interface uses Tabler CSS framework for styling
- **UX Components**: Symfony UX packages (Dropzone, Icons, Live Components)
- **Editor.js**: Rich content editor for articles and pages

### Content Management

- **Translatable content**: Multi-locale support via `BasicTranslatableTrait` and alternative locales
- **Content revisions**: Full revision tracking with diff rendering (see `EditorJsDiffRenderer`)
- **Editor.js integration**: Content stored as JSON with type-safe PHPStan definitions in `phpstan.neon`
- **Publication workflow**: Publishable entities with status management
- **Hierarchical structures**: Tags, pages, and menu items support parent-child relationships

## Common Development Commands

### Testing

```bash
# Run all tests
make test
# Or directly:
vendor/bin/simple-phpunit

# Initialize test database
make init-test-db
```

The test suite is configured in `phpunit.xml` and runs tests from CoreBundle and EventBundle.

### Code Quality

```bash
# Run PHPStan static analysis
make phpstan

# Generate PHPStan baseline (when adding new code with known issues)
make phpstan-baseline

# Run coding standards check
make ecs

# Fix coding standards automatically
make ecs-fix
```

**Code standards:**

- **PHP CS**: Uses `symplify/easy-coding-standard` with PSR-12, Clean Code, and Strict rulesets
- **PHPStan**: Level max with strict rules, Symfony, Doctrine, PHPUnit extensions
- **Twig CS**: Uses `vincentlanglet/twig-cs-fixer` (cache in `.twig-cs-fixer.cache`)

### Monorepo Management

```bash
# Merge individual bundle composer.json files into root composer.json
make merge

# Before merging: backup bundle composer.json files
make backup-composer

# Restore bundle composer.json files
make restore-composer
```

The `replace` section in root `composer.json` ensures monorepo packages are not downloaded from Packagist.

## Development Workflow

### Running Tests for a Single Bundle

```bash
# Run tests for a specific directory
vendor/bin/simple-phpunit src/Xutim/Bundle/CoreBundle/tests
vendor/bin/simple-phpunit src/Xutim/Bundle/EventBundle/tests
```

### Working with Entities

When creating or modifying entities:

1. Define domain interface in `Bundle/src/Domain/Model/`
2. Implement interface in `Bundle/src/Entity/`
3. Use traits for common functionality (timestamps, files, archiving, translations)
4. Define repository in `Bundle/src/Repository/`
5. Register repository in bundle's `config/repositories.php`

### Working with Frontend Assets

Frontend assets are managed per-bundle:

1. Stimulus controllers: `Bundle/assets/admin/controllers/` or `Bundle/assets/public/controllers/`
2. Styles: `Bundle/assets/admin/styles/` (admin uses Tabler CSS framework)
3. Controllers registered in `Bundle/assets/controllers.json`
4. Bundle assets exposed via Symfony AssetMapper

### Adding Custom User Roles

See `doc/how-to-extend-user-roles.md` for detailed instructions on:

- Defining custom role constants
- Extending role hierarchy via compiler pass
- Registering roles in `UserRolesProviderInterface`
- Adding translatable role descriptions via `UserRoleDescriptorProviderInterface`

## Git Workflow

This project uses **Conventional Commits** with scopes when applicable.

### Commit Message Format

```
<type>(<scope>): <subject>

<body>
```

**Common types:**

- `feat`: New feature
- `fix`: Bug fix
- `refactor`: Code refactoring
- `docs`: Documentation changes
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Common scopes:**

- Bundle names: `core-bundle`, `media-bundle`, `security-bundle`, `analytics-bundle`, etc.
- Feature areas: `auth`, `routing`, `assets`, `domain`, etc.

**Examples:**

```bash
feat(analytics-bundle): add session tracking
fix(media-bundle): resolve image upload validation
refactor(core-bundle): extract menu builder logic
docs: update CLAUDE.md with commit conventions
```

**Important:**

- Never include AI attribution, co-authorship, or "Generated with Claude Code" in commit messages
- Never use emojis in commit messages
- Keep commits professional and focused on the technical changes
- Hard limit of 72 characters per line for commit messages (subject and body)
- Only reference issues from this repository (XutimCMS/Xutim) using simple issue numbers like "#45"
- Do not reference external repositories (e.g., avoid "php/src#45")

## Important Configuration Files

- `composer.json`: Root package definition with monorepo `replace` section
- `monorepo-builder.php`: Defines monorepo package directories
- `phpstan.neon`: PHPStan configuration with Editor.js type aliases
- `ecs.php`: Easy Coding Standard configuration
- `tests/Kernel.php`: Test kernel for PHPUnit tests
- `tests/phpunit.xml.dist`: PHPUnit configuration for full test suite
- `phpunit.xml`: Root PHPUnit configuration (CoreBundle and EventBundle only)

## Type Safety and PHPStan

The project uses **strict PHPStan level max** with custom type aliases for Editor.js block types defined in `phpstan.neon`:

- `EditorBlock`: Full Editor.js document structure
- `ListBlock`, `ParagraphBlock`, `HeaderBlock`, etc.: Individual block types
- `XutimAnchorTune`, `AlignmentTune`, `CombinedTunes`: Block tune types

When working with Editor.js content, reference these type aliases in PHPDoc annotations.

## Testing Notes

- **Database**: PostgreSQL required (uses ext-pgsql)
- **Test database**: Initialized with `make init-test-db` (drops, creates, migrates, loads fixtures)
- **Test bundle**: Uses `dama/doctrine-test-bundle` for transaction-based test isolation
- **PHPStan bootstrap**: Loads PHPUnit vendor autoload from `vendor/bin/.phpunit/phpunit/vendor/autoload.php`

## PHP Requirements

- **PHP 8.4+**: Project uses PHP 8.4 features
- **Required extensions**: ctype, gd, iconv, mbstring, pdo, pgsql
