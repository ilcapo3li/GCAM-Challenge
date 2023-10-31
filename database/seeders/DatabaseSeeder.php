<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Board;
use App\Models\BoardItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin();
        User::factory(20)
            ->has(Board::factory()->count(3)
                ->has(BoardItem::factory()->count(10)
                ->state(function (array $attributes, Board $board) {
                    return [
                        'board_id' => $board->id,
                        'created_by' => $board->created_by,
                    ];
                }), 'boardItems'),
            'boards')->create();

    }
}
