<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $team_id
 * @property string $name
 * @property CarbonImmutable $birthdate
 * @property string $email
 * @property string $whatsapp_number
 * @property string $city
 * @property string $neighborhood
 * @property string $circle_url
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
}

