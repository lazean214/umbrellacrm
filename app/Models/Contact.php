<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'street_address',
        'city',
        'state',
        'postal_code',
        'country',
        'ni_number',
        'bank',
        'account_number',
        'sort_code',
        'date_of_birth',
        'marital_status',
        'gender',
    ];

    public function companies()
    {
        return $this->belongsToMany(
            Company::class,
            'company_contact'
        );
    }

    public function deals()
    {
        return $this->belongsToMany(
            Deal::class,
            'contact_deal'
        )->withPivot('is_primary');
    }

    public function emailLogs()
    {
        return $this->hasMany(
            DealEmailLog::class
        );
    }
}