<?php

namespace App\Models;

use App\Enums\DealStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\LogsDealHistory;

class Deal extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsDealHistory;

    protected $fillable = [
        'name',
        'amount',
        'stage',
        'hours',
        'rate',
        'recruitment_agency',
        'consultant_name',
        'agency_deal_value',
        'margin_agreed',
        'date_sent',
        'date_signed',
        'who_signed',
        'mda_setup',
        'mda_reference_number',
        'date_set_up',
        'remittance_received',
        'date_logged',
        'user_id',

        // Compliance
        'starter_checklist_recieved_date',
        'starter_form',
        'tax_code',
        'contract_recieved_date',
        'stage_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'stage' => DealStage::class,
            'stage_updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function contacts()
    {
        return $this->belongsToMany(
            Contact::class,
            'contact_deal'
        )->withPivot('is_primary');
    }

    public function companies()
    {
        return $this->belongsToMany(
            Company::class,
            'company_deal'
        )->withPivot('is_primary');
    }

    public function primaryContact()
    {
        return $this->contacts()
            ->wherePivot('is_primary', true)
            ->first()
            ?? $this->contacts()->first();
    }

    public function primaryCompany()
    {
        return $this->companies()
            ->wherePivot('is_primary', true)
            ->first()
            ?? $this->companies()->first();
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(
            DealEmailLog::class
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | CRM Visibility Scope
    |--------------------------------------------------------------------------
    */

    public function scopeVisibleTo($query, $user)
    {
        if (! $user) {
            return $query;
        }

        if ($user->isSalesTeam()) {
            return $query->where(
                'user_id',
                $user->id
            );
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */

    public function registerMediaCollections(): void
    {
        // MULTIPLE FILES
        $this->addMediaCollection('compliance_documents');

        $this->addMediaCollection('contract_documents');
    }

    public function signableEnvelopes()
    {
        return $this->hasMany(SignableEnvelope::class);
    }
}
