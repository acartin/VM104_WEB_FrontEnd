<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles;

    protected $table = 'lead_users';
    public $timestamps = false;
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'job_title',
        'available_status',
        'can_receive_leads',
        'password',
        'theme',
        'theme_color',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_receive_leads' => 'boolean',
        ];
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'lead_client_user');
    }

    public function contactMethods()
    {
        return $this->hasMany(ContactMethod::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->clients;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->clients()->whereKey($tenant)->exists();
    }
}
