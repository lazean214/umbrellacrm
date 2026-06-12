<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'deal_id',
        'type',
        'activity_name',
        'message',
        'user_email',
        'parent_id',
        'status',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function comments()
    {
        return $this->hasMany(ActivityLog::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(ActivityLog::class, 'parent_id');
    }

    public function isComment(): bool
    {
        return $this->parent_id !== null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }
}
