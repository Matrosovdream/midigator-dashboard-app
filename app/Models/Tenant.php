<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'domain',
    'midigator_api_secret',
    'midigator_sandbox_mode',
    'midigator_webhook_username',
    'midigator_webhook_password',
    'is_active',
    'settings',
])]
#[Hidden(['midigator_api_secret', 'midigator_webhook_password'])]
class Tenant extends Model
{
    protected function casts(): array
    {
        return [
            'midigator_api_secret' => 'encrypted',
            'midigator_webhook_password' => 'encrypted',
            'midigator_sandbox_mode' => 'boolean',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function chargebacks(): HasMany
    {
        return $this->hasMany(Chargeback::class);
    }

    public function preventionAlerts(): HasMany
    {
        return $this->hasMany(PreventionAlert::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function rdrCases(): HasMany
    {
        return $this->hasMany(RdrCase::class);
    }

    public function siteSettings(): HasMany
    {
        return $this->hasMany(SiteSetting::class);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }
}
