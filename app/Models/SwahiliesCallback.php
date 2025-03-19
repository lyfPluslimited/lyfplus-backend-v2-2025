<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwahiliesCallback extends Model
{
    protected $table = 'swahilies_callback';

    protected $fillable = [
        'code', 'order_id', 'reference_id', 'amount'
    ];
}
