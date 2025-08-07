<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\AzureGroupService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'azure_id',
        'azure_token',
        'token_expires_at',
        'refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'azure_token',
        'refresh_token',
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
            'azure_token' => 'json',
            'token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the AzureGroupService instance
     */
    protected function getAzureGroupService(): AzureGroupService
    {
        return App::make(AzureGroupService::class);
    }

    /**
     * Check if user is a member of the target Azure AD group
     * Uses cached results where possible
     *
     * @return bool True if user is a member, false otherwise
     */
    public function isInTargetGroup(): bool
    {
        if (empty($this->azure_token)) {
            return false;
        }

        return $this->getAzureGroupService()->isUserInGroup($this->azure_token);
    }

    /**
     * Check if user is a member of a specific Azure AD group
     *
     * @param  string  $groupId  The Azure AD group ID to check
     * @return bool True if user is a member, false otherwise
     */
    public function isInAzureGroup(string $groupId): bool
    {
        if (empty($this->azure_token)) {
            return false;
        }

        return $this->getAzureGroupService()->isUserInGroup($this->azure_token, $groupId);
    }
}
