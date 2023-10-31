<?php

namespace App\Models;

use App\Enums\UserType;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type_id'
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn () => UserType::from($this->type_id),
        );
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class, 'created_by');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(BoardItems::class, 'created_by');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(BoardItems::class, 'assignee_board_items');
    }

    public function attachedBoards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'board_users');
    }

    public function invitedUsers(): BelongsToMany
    {
        return $this->belongsToMany(InviteUser::class, 'created_by');
    }
}
