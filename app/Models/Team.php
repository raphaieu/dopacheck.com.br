<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\TeamFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Jetstream\Team as JetstreamTeam;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Challenge;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $whatsapp_group_jid
 * @property string|null $whatsapp_group_name
 * @property bool $personal_team
 * @property string|null $landing_template
 * @property array<int, array{key: string, type: string, label: string, required?: bool, placeholder?: string}>|null $form_schema
 * @property string|null $onboarding_behavior
 * @property array<string, string>|null $theme
 * @property string|null $custom_landing
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User|null $owner
 * @property-read Collection<int, TeamInvitation> $teamInvitations
 * @property-read int|null $team_invitations_count
 * @property-read Membership|null $membership
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team wherePersonalTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUserId($value)
 *
 * @mixin \Eloquent
 */
final class Team extends JetstreamTeam
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public const LANDING_TEMPLATE_DOPAMINA = 'dopamina';

    public const LANDING_TEMPLATE_DEFAULT = 'default';

    public const ONBOARDING_BEHAVIOR_APPLICATION_ONLY = 'application_only';

    public const ONBOARDING_BEHAVIOR_CREATE_USER = 'create_user';

    protected $fillable = [
        'name',
        'slug',
        'whatsapp_join_url',
        'whatsapp_group_jid',
        'whatsapp_group_name',
        'onboarding_title',
        'onboarding_body',
        'landing_template',
        'form_schema',
        'onboarding_behavior',
        'theme',
        'custom_landing',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * {@inheritdoc}
     *
     * @return HasMany<TeamInvitation, covariant $this>
     */
    public function teamInvitations(): HasMany
    {
        return parent::teamInvitations();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'form_schema' => 'array',
            'theme' => 'array',
        ];
    }

    public function usesDopaminaLanding(): bool
    {
        return ($this->landing_template ?? self::LANDING_TEMPLATE_DOPAMINA) === self::LANDING_TEMPLATE_DOPAMINA;
    }

    /**
     * Desafios vinculados a este time.
     *
     * @return HasMany<Challenge, $this>
     */
    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }
}
