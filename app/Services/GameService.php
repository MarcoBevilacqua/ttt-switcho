<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\Log;

class GameService
{
    private $game;
    private $checks = [
        'horizontal' => [
            [1, 2], //0
            [0, 2],
            [0, 1],
            [4, 5],
            [3, 5], //4
            [3, 4],
            [7, 8],
            [6, 8],
            [6, 7] //8
        ],
        'vertical' => [
            [3, 6], //0
            [4, 7],
            [5, 8],
            [0, 6],
            [1, 7], //4
            [2, 8],
            [0, 3],
            [1, 4],
            [2, 5] //8
        ],
        'diagonal' => [
            [4, 8], //0
            [],
            [4, 6],
            [],
            [2, 6], //4
            [],
            [2, 4],
            [],
            [0, 4] //8
        ],
    ];

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * check if a player wins the game
     * @param int $playerId
     * @param int $place
     *
     * @return boolean
     */
    private function playerHasWon(int $playerId, int $place)
    {
        foreach (array_keys($this->checks) as $checkKey) {
            if (count($this->checks[$checkKey][$place]) > 0 &&
            $this->game->status[$this->checks[$checkKey][$place][0]] == $playerId &&
            $this->game->status[$this->checks[$checkKey][$place][1]] == $playerId) {
                Log::info("Player {$playerId} won {$checkKey}!!!");
                return true;
            }
        }

        return false;
    }

    /**
     * check if move is valid
     * @param int $playerId
     * @param int $place
     *
     * @return boolean
     */
    public function moveIsValid($playerId, $place)
    {
        Log::info("Player {$playerId} playing in position {$place}");
        return
            $this->game->status[$place] !== $playerId &&
            $this->game->last_move_played_by !== $playerId &&
            $this->game->status[$place] === 0 &&
            $this->game->winner === 0;
    }

    /**
     * public accessor for private method
     * @param int $playerId
     * @param int $place
     *
     * @return boolean
     */
    public function gameShouldEnd($playerId, $place)
    {
        Log::info("Check for {$playerId} playing in position {$place}");

        return $this->playerHasWon($playerId, $place);
    }
}
