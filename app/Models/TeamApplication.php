<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $team_id
 * @property string|null $name
 * @property CarbonImmutable|null $birthdate
 * @property string|null $email
 * @property string|null $whatsapp_number
 * @property string|null $city
 * @property string|null $neighborhood
 * @property string|null $circle_url
 * @property array<string, mixed>|null $form_data
 * @property string $status
 * @property int|null $user_id
 * @property int|null $approved_by
 * @property CarbonImmutable|null $approved_at
 * @property array<string, mixed>|null $meta
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
final class TeamApplication extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'birthdate',
        'email',
        'whatsapp_number',
        'city',
        'neighborhood',
        'circle_url',
        'form_data',
        'status',
        'user_id',
        'approved_by',
        'approved_at',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date:Y-m-d',
            'approved_at' => 'datetime',
            'meta' => 'array',
            'form_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDisplayName(): ?string
    {
        if ($this->name !== null && $this->name !== '') {
            return $this->name;
        }
        $data = $this->form_data ?? [];
        return isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
    }

    public function getDisplayEmail(): ?string
    {
        if ($this->email !== null && $this->email !== '') {
            return $this->email;
        }
        $data = $this->form_data ?? [];
        return isset($data['email']) && is_string($data['email']) ? $data['email'] : null;
    }

    public function hasCustomFormData(): bool
    {
        $data = $this->form_data ?? [];
        return $data !== [];
    }
}

