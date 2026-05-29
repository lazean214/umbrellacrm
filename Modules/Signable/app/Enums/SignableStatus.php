<?php

namespace Modules\Signable\App\Enums;

enum SignableStatus: string
{
    case SENT = 'sent'; // BG COLOR BLUE
    case SIGNED = 'signed'; // BG COLOR GREEN
    case DRAFT = 'draft'; // BG COLOR GRAY
    case CANCELLED = 'cancelled'; // BG COLOR BLACK
}
