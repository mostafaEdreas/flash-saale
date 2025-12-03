<?php

namespace App\Enums;

enum EarlyWebhookStatus :string
{
    case RECEIVED = 'reseved' ;
    case PROCESSED = 'processed';
    case FAILED = 'failed' ;

}
