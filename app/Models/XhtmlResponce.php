<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XhtmlResponce extends Model
{

    protected $fillable = [
        'accountId',
        'html',
    ];

    use HasFactory;
}
