<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnoGame extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_token',
        'status',
        'result',
        'player0',
        'player0points',
        'player1',
        'player1points',
        'player2',
        'player2points',
        'player3',
        'player3points',
        'deck',
        'pile',
        'turns',
        'direction',
        'date_started'
    ];
}
