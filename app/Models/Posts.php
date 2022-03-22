<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'author_id',
        'content',
        'post_date',
        'status',
    ];
}
