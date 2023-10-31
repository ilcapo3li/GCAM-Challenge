<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InviteUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'created_by',
        'data'
    ];

    protected $casts = [
        'data' => 'object'
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function getInvitationUrl(): string
    {
        return URL::temporarySignedRoute(
            'invitation',
            now()->addWeeks(2),
            $this->token
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->token= Str::uuid();
        });
    }
}
