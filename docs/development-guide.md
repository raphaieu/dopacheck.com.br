# Development Guide: DOPA Check

_Instructions for setting up the local development environment._

## Prerequisites
- **PHP**: 8.3+
- **Node.js**: Latest LTS
- **Package Manager**: `npm` (manifest) or `bun` (scripts)
- **Database**: MySQL 8.0+
- **Cache**: Redis

## Setup Instructions
1. **Clone & Install**:
   ```bash
   composer install
   npm install
   ```
2. **Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Database**:
   ```bash
   php artisan migrate --seed
   ```
4. **Run Services**:
   - Web Server: `php artisan serve`
   - Assets: `npm run dev`
   - Queues: `php artisan horizon`

## Common Commands
- **Testing**: `php artisan test` or `./vendor/bin/pest`
- **Formatting**: `php artisan pint`
- **Static Analysis**: `npm run lint` or `php artisan analyse` (PHPStan)

## Code Standards
- **PHP**: Use strict types and type hints for all parameters and returns.
- **Vue**: Use `<script setup>` syntax.
- **Naming**: CamelCase for methods/variables, PascalCase for classes.
