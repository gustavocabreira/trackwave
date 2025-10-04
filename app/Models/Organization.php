<?php

declare(strict_types=1);

namespace App\Models;

use App\Policies\OrganizationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UsePolicy(OrganizationPolicy::class)]
final class Organization extends Model
{
    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id')->select('id', 'name');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
