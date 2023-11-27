<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class html_integration extends Model
{
    use HasFactory;


    protected $fillable = [
        'accountId',
        'kkm_id',
        'html',
    ];

}
