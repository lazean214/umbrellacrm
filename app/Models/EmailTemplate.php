<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
        'description',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function logs()
    {
        return $this->hasMany(
            DealEmailLog::class
        );
    }

    public function attachments()
    {
        return $this->hasMany(
            EmailTemplateAttachment::class
        );
    }

    
}