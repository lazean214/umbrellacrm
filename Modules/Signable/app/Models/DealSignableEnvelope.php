<?php

namespace Modules\Signable\App\Models;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealSignableEnvelope extends Model
{
    protected $table = 'deal_signable_envelopes';

    protected $fillable = [
        'deal_id',
        'user_id',
        'envelope_title',
        'envelope_fingerprint',
        'date_created',
        'date_signed',
        'envelope_status',
        'download_link',
    ];

    protected function casts(): array
    {
        return [
            'deal_id' => 'integer',
            'user_id' => 'integer',
            'date_created' => 'datetime',
            'date_signed' => 'datetime',
        ];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
