# Data Models: DOPA Check

_This document outlines the core models and their relationships, discovered during the technical scan._

## Core Models

### User
- **Fields**: `name`, `email`, `username`, `password`, `plan` (free/pro), `whatsapp_number`, `subscription_ends_at`, `preferences` (json).
- **Relationships**:
  - `ownedTeams`: Teams owned by the user.
  - `teams`: Teams the user belongs to.
  - `userChallenges`: Participations in challenges.
  - `whatsappSession`: Active WhatsApp connection details.

### Challenge
- **Fields**: `title`, `description`, `duration_days`, `visibility` (private/team/global), `category`, `image_url`.
- **Relationships**:
  - `creator`: User who created the challenge.
  - `tasks`: Tasks associated with the challenge (`ChallengeTask`).
  - `userChallenges`: Users participating in the challenge.

### UserChallenge (Pivot)
- **Fields**: `user_id`, `challenge_id`, `status` (active/completed/expired), `current_streak`, `best_streak`.

### Checkin
- **Fields**: `user_challenge_id`, `task_id`, `checked_at`, `media_path` (proof), `message`.

## Database Schema Highlights
- **Stripe Integration**: Uses `stripe_id`, `pm_type`, `pm_last_four`, and `trial_ends_at` (provided by Laravel Cashier).
- **Teams**: Standard Jetstream team structure (`teams`, `team_user`, `team_invitations`).
