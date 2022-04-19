<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChessGame extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_token',
        'status',
        'result',
        'white_pieces',
        'black_pieces',
        'turns',
        'date_started'
    ];
}
