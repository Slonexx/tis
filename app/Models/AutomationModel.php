<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',

        'entity',
        'status',
        'payment',
        'saleschannel',
        'project',
    ];

}
