<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OauthProvider extends Model
{
    use HasFactory;

    protected $table = 'oauth_providers';

    protected $fillable = [
        'name'
    ];

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class, 'provider_id');
    }
}