# Project Overview: DOPA Check

DOPA Check is a habit-tracking platform designed to reduce friction by integrating WhatsApp with a mobile-first web interface. Users can join challenges and track progress via hashtags in WhatsApp groups.

## Executive Summary
- **Goal**: Transform discipline into a low-friction social experience.
- **Key Feature**: Automatic check-ins via WhatsApp messages and images.
- **Monetization**: Freemium model with a "PRO" plan (powered by Stripe).

## Technology Stack
- **Backend**: Laravel 12 (PHP 8.3)
- **Frontend**: Vue 3 (Inertia.js 2, Tailwind 4)
- **Database**: MySQL 8+
- **Cache/Queues**: Redis (Laravel Horizon)
- **Infrastructure**: Docker, Laravel Octane (Experimental)
- **Payments**: Stripe (Laravel Cashier)

## Architecture at a Glance
- **Monolith**: A unified Laravel application serving both the API and the Inertia-driven frontend.
- **VILT Stack**: Highly reactive UI with robust backend validation.
- **Admin Panel**: Powered by Filament 3 for managing users, challenges, and teams.
