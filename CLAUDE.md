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

## Stimulus Controllers

New Stimulus controllers must be registered in `CoreBundle/assets/admin/bootstrap.js`:
1. Import the controller at the top of the file
2. Call `app.register('controller-name', ControllerClass)` at the bottom

## Plan Mode

- Make the plan extremely concise. Sacrifice grammar for the sake of concision.
- At the end of each plan, give me a list of unresolved questions to answer, if any.
