<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userFollow extends Model
{
    use HasFactory;

    public $timestamps = false; //gets rid of 'updated_at' and 'created_at'

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'follower_id',
        'following_id',
        'status',
        'follow_date',
    ];
}
