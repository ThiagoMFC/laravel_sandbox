<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'post_id',
        'author_id',
        'content',
        'date_posted',
        'status',
    ];
}
