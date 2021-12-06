<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGameRequest;
use App\Services\GameService;
use App\Models\Game;
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
            $game = new Game([
                'uuid' => Str::uuid(),
                'status' => array_fill(0, 9, 0),
                'last_move_played_by' => 0
            ]);
            
            $game->save();
        } catch (\Exception $exception) {
            Log::error("Cannot create game: {$exception->getMessage()}");
            return response()->json(['message' => $exception->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['gameId' => $game->uuid], HttpResponse::HTTP_CREATED);
    }

    public function update(UpdateGameRequest $updateGameRequest, $gameId)
    {
        //retrieve game by ID
        try {
            $game = Game::where('uuid', $gameId)->firstOrFail();
        } catch (\Exception $exception) {
            Log::error("An error occurred retrieving the game: {$exception->getMessage()}");
            return response()->json(['message' => 'An error occurred retrieving the game: ' . $exception->getMessage()], HttpResponse::HTTP_BAD_REQUEST);
        }
        
        //check if errors: move is valid and player is entitled
        $gameService = new GameService($game);

        if (!$gameService->moveIsValid($updateGameRequest->player, $updateGameRequest->place)) {
            return response()->json(
                ['message' => 'Invalid move, player is not entitled or game is closed'],
                HttpResponse::HTTP_BAD_REQUEST
            );
        }
        
        //update schema
        $status = $game->status;
        $status[$updateGameRequest->place] = $updateGameRequest->player;
        $game->last_move_played_by = $updateGameRequest->player;

        try {
            $game->status = $status;
            $game->save();
        } catch (\Exception $exception) {
            Log::error("An error occurred updating the game: {$exception->getMessage()}");
            return response()->json(['message' => 'An error occurred updating the game: ' . $exception->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        //check if player has won and return response
        if ($gameService->gameShouldEnd($updateGameRequest->player, $updateGameRequest->place)) {
            $game->update(['winner' => $updateGameRequest->player]);
            return response()->json(['winner' => $updateGameRequest->player, 'status' => $game->status]);
        }

        //game goes on, FE can display status
        return response()->json(['status' => $game->status]);
    }
}
