# API Contracts: DOPA Check

_This document outlines the primary API endpoints and their contracts, discovered during the technical scan._

## Authentication (Sanctum / Jetstream)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Standard Laravel Fortify login. |
| POST | `/logout` | Standard Laravel Fortify logout. |
| POST | `/register` | User registration. |

## Subscriptions (Stripe / Cashier)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/subscriptions` | Lists active and available subscriptions. |
| POST | `/stripe/webhook` | Handles Stripe events (handled by `StripeWebhookController`). |

## Challenges

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/challenges` | Lists available challenges. |
| POST | `/challenges` | Creates a new challenge (Restricted for Free users). |
| GET | `/challenges/{challenge}` | Shows challenge details and tasks. |

## Teams (Jetstream)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/teams/{team}` | Shows team details (Inertia). |
| POST | `/teams` | Creates a new team. |

## WhatsApp Integration

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/whatsapp/webhook` | Internal/External webhook for WhatsApp messages (Legacy/Planned). |
