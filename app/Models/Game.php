<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'status',
        'last_move_played_by',
        'winner'
    ];

    protected $casts = [
        'status' => "array"
    ];
}
