<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function index()
    {
        return response()->json(['code' => 200]);
    }

    /**
     * init the game and return the ID
     */
    public function create()
    {
        return response()->json(['code' => HttpResponse::HTTP_CREATED, 'gameId' => Str::uuid()]);
    }
}
