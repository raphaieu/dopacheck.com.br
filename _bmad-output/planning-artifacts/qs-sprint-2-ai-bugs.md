# Quick Tech Spec: Sprint 2 - Bug Fixes & WhatsApp Reports (No AI)

Refined focus on data consistency, challenge lifecycle, and deterministic WhatsApp group reports.

## 1. Bug Fixes: Challenge Lifecycle & Data Consistency

### Problem A: History Wiped on Re-join
Currently, `ChallengeController@join` resets `total_checkins` and **force-deletes** check-ins if an old participation is found.
- **Fix**: 
    - Modify `ChallengeController@join`: If a user has a `completed` or `expired` participation, **do not allow joining again** as if it's new. 
    - Instead, the UI in `Challenges/Show.vue` will detect the "finalized" status and show "Ver Resultados" (redirecting to `/reports/challenge/{id}`) instead of "Participar".
    - If a user *really* want to restart (edge case), we need a specific "Reset" flow, but for now, the priority is **preservation**.

### Problem B: Inconsistent Termination Logic
Participants stay active because `UserChallenge` calculates days based on individual `started_at` fallbacks.
- **Fix**: 
    - The Global `Challenge->end_date` is the absolute truth.
    - Update `UserChallenge@updateCurrentDay` to mark status as `expired` or `completed` strictly when `now() > challenge->end_date`.
    - Prevent joining if `now() > challenge->end_date`.

## 2. WhatsApp Feature: Deterministic Daily Reports
Focus on SQL-based summaries rather than AI generation.

### Logic: Daily Team Summary (`SendDailyReminderJob`)
Expand the existing job to send a "End of Day" status report to groups:
- **Metrics**: 
    - Team Completion Rate for the day.
    - "Destaques": Users who completed all tasks.
    - "Pendentes": Users who missed at least one required task.
- **Trigger**: Can be combined with the 22:00 notification or a second run at 00:00.

## 3. Implementation Plan

### Phase 1: Robustness (Data Protection)
- [ ] Block "Join" for finished challenges in `ChallengeController`.
- [ ] Protect check-in history from being deleted on re-entry attempts.
- [ ] Update `Challenges/Show.vue` to handle finalized states (View Results vs Join).

### Phase 2: Synchronization (Lifecycle)
- [ ] Standardize termination date logic in `UserChallenge` model.
- [ ] Update `CheckExpiredChallenges` command to be more aggressive on global expiration.

### Phase 3: WhatsApp Engagement (SQL-based)
- [ ] Enhance `SendDailyReminderJob` with "Team Daily Recap" message.
- [ ] Test deterministic summaries in production groups.
