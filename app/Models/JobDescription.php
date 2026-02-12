<?php

namespace App\Models;

use Database\Factories\JobDescriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $job_role
 * @property int $experience_min
 * @property int $experience_max
 * @property string $description
 * @property array<string>|null $requirements
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class JobDescription extends Model
{
    /** @use HasFactory<JobDescriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_role',
        'experience_min',
        'experience_max',
        'description',
        'requirements',
    ];

    /**
     * Get the user that owns the job description.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
        ];
    }
}
