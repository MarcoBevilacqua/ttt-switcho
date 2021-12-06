<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;
use Exception;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GameController extends Controller
{
    /**
     * init the game and return the ID
     */
    public function store()
    {
        try {
            $game = new Game();
        } catch (\Exception $exception) {
            Log::error("Cannot create game: {$exception->getMessage()}");
            return response()->json(['code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR, 'message' => $exception->getMessage()]);
        }
        return response()->json(['code' => HttpResponse::HTTP_CREATED, 'gameId' => $game->uuid]);
    }

    public function update(UpdateGameRequest $updateGameRequest)
    {
        /**
         * 1.retrieve game by ID
         **/


        /**
         * 2.check if errors
         * 3.update schema
         * 4.check if game should end
         * 5.return response
         */
    }
}
