<?php

declare(strict_types=1);

namespace App\Models;

use App\Policies\ProjectPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(ProjectPolicy::class)]
final class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'description',
    ];

    public function organization(): BelongsTo
    {
        return $this
            ->belongsTo(Organization::class)
            ->select([
                'organizations.id',
                'organizations.name',
                'organizations.slug',
            ]);
    }

    public function user(): BelongsTo
    {
        return $this
            ->belongsTo(User::class)
            ->select([
                'users.id',
                'users.name',
            ]);
    }
}
