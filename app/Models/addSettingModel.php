<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class addSettingModel extends Model
{

    protected $fillable = [
        'accountId',
        'paymentDocument',
    ];

    use HasFactory;
}
