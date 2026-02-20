<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Tenant relationship is implicit — Employee lives in the tenant's own database.
 * Tenancy must be initialized before querying employees.
 */
class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'employee';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',

        'invited_by',
        'invited_at',
        'invitation_accepted_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'invited_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isPending(): bool
    {
        return is_null($this->invitation_accepted_at);
    }

    public function markInvitationAccepted(): void
    {
        $this->update([
            'invitation_accepted_at' => now(),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Get the employee who invited this employee.
     */
    public function inviter()
    {
        return $this->belongsTo(Employee::class, 'invited_by');
    }

    /**
     * Get all employees invited by this employee.
     */
    public function invitedEmployees()
    {
        return $this->hasMany(Employee::class, 'invited_by');
    }
}
