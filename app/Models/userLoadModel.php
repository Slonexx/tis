<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userLoadModel extends Model
{

    protected $fillable = [
        'accountId',
        'email',
        'name',
        'status',
    ];

    use HasFactory;
}
