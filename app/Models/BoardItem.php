<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BoardItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'board_id',
        'status_id',
        'type_id',
        'parent_id',
        'created_by',
    ];

    public function status(): Attribute
    {
        return Attribute::make(
            get: fn () => Status::from($this->status_id),
        );
    }

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn () => TaskType::from($this->type_id),
        );
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function parents()  // recursive retrive all leaf to ancestors EX: Sub Tasks->Task->Epic.
    {
        return $this->parent()->with('parents');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class,'parent_id');
    }

    public function issueTree() // recursive retrive all branches from ancestors EX: Epic->Task->Sub Tasks.
    {
        return $this->children()->with('issueTree');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Users::class, 'assignee_board_items');
    }
}
