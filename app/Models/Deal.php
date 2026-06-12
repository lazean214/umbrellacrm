<?php

namespace App\Models;

use App\Enums\DealStage;
<<<<<<< HEAD
use App\Traits\LogsDealHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
<<<<<<< HEAD
=======
use App\Traits\LogsDealHistory;
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c

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

<<<<<<< HEAD
    public function contacts(): BelongsToMany
=======
    public function contacts()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        return $this->belongsToMany(
            Contact::class,
            'contact_deal'
        )->withPivot('is_primary');
    }

<<<<<<< HEAD
    public function companies(): BelongsToMany
=======
    public function companies()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        return $this->belongsToMany(
            Company::class,
            'company_deal'
        )->withPivot('is_primary');
    }

<<<<<<< HEAD
    public function primaryContact(): ?Contact
=======
    public function primaryContact()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        return $this->contacts()
            ->wherePivot('is_primary', true)
            ->first()
            ?? $this->contacts()->first();
    }

<<<<<<< HEAD
    public function primaryCompany(): ?Company
=======
    public function primaryCompany()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
    public function user(): BelongsTo
=======
    public function user()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | CRM Visibility Scope
    |--------------------------------------------------------------------------
    */

<<<<<<< HEAD
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
=======
    public function scopeVisibleTo($query, $user)
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
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

<<<<<<< HEAD
    public function signableEnvelopes(): HasMany
=======
    public function signableEnvelopes()
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
    {
        return $this->hasMany(SignableEnvelope::class);
    }
}
