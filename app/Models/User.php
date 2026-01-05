<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Lms\Enrollment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;


    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'username',
        'email',
        'password',
        'active_role',
        'lms_status',
        // 'is_hr',
        // 'is_employee'

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
        ];
    }

    public function isActive(): bool
    {
        return $this->lms_status === self::STATUS_ACTIVE;
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
    public function switchRole(string $roleName): bool
    {
        if (! $this->hasRole($roleName)) {
            return false;
        }
        $this->forceFill(['active_role' => $roleName])->save();

        return true;
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function coursesEnrolled()
    {
        return $this->belongsToMany(\App\Models\Lms\Course::class, 'enrollments', 'user_id', 'course_id')
            ->withPivot(['status', 'enrolled_at', 'completed_at'])
            ->withTimestamps();
    }
}
