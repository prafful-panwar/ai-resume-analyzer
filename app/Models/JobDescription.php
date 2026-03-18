<?php

namespace App\Models;

use Database\Factories\JobDescriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('job_descriptions')]
#[Fillable(['user_id', 'job_role', 'experience_min', 'experience_max', 'description', 'requirements'])]
#[UseFactory(JobDescriptionFactory::class)]
class JobDescription extends Model
{
    /** @use HasFactory<JobDescriptionFactory> */
    use HasFactory;

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
