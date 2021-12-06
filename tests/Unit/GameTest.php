<?php

namespace Tests\Unit;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Illuminate\Support\Str;

class GameTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * @test
     * init game
     */
    public function should_init_game()
    {
        $this->assertDatabaseCount('games', 0);
        $this->post('/api/game/new')->assertStatus(201);
        $this->assertDatabaseCount('games', 1);
    }

    /**
     * @test
     */
    public function should_forbid_move_for_wrong_game()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $this->assertDatabaseCount('games', 1);
        $fakeUuid = Str::uuid();
        $this->put('/api/game/'. $fakeUuid . '/move', [
            'player' => 1,
            'place' => 0])
            ->assertStatus(400);
    }

    /**
     * @test
     */
    public function should_forbid_move_for_closed_game()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $this->assertDatabaseCount('games', 1);
        $fakeUuid = Str::uuid();
        $this->put('/api/game/'. $fakeUuid . '/move', [
            'player' => 1,
            'place' => 0])
            ->assertStatus(400);
    }

    /**
     * @test
     */
    public function should_forbid_same_player_move_twice()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $game = Game::first();
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 0])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 4])
            ->assertStatus(400);
    }

    /**
     * @test
     */
    public function shoud_forbid_moving_in_already_taken_position()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $game = Game::first();
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 0])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 0])
            ->assertStatus(400);
    }

    /**
     * @test
     */
    public function should_win_game_with_first_player()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $game = Game::first();
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 4])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 0])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 5])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 1])
            ->assertStatus(200);
        $response = $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 3]);
        $response->assertStatus(200)
            ->assertJson(['winner' => 1]);

        $this->assertDatabaseHas('games', ['winner' => 1, 'uuid' => $game->uuid]);
    }

    /**
     * @test
     */
    public function should_win_game_with_diagonal()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $game = Game::first();
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 4])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 1])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 8])
            ->assertStatus(200);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 2])
            ->assertStatus(200);
        $response = $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 0]);
        $response->assertStatus(200)
            ->assertJson(['winner' => 1]);

        $this->assertDatabaseHas('games', ['winner' => 1, 'uuid' => $game->uuid]);
    }

    /**
     * @test
     */
    public function test_nobody_wins()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $game = Game::first();
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 4]);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 0]);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 5]);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 3]);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 6]);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 2,
            'place' => 7]);
        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 8]);

        $this->assertDatabaseHas('games', ['winner' => 0, 'uuid' => $game->uuid]);
    }

    /**
     * @test
     */
    public function should_avoid_request_if_all_places_taken()
    {
        $this->post('/api/game/new')->assertStatus(201);
        $game = Game::first();
        $game->status = [1, 2, 1, 2, 1, 2, 2, 1, 2];
        $game->save();
        $game->refresh();

        $this->put('/api/game/'. $game->uuid . '/move', [
            'player' => 1,
            'place' => 8])->assertStatus(400);
    }
}
