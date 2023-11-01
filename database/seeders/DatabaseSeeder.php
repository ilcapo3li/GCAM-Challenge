<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\Status;
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
            ->has(Board::factory()->count(10)
                ->has(BoardItem::factory()->todo()->count(15)
                ->state(function (array $attributes, Board $board) {
                    return [
                        'created_by' => $board->created_by,
                    ];
                }), 'boardItems')
                ->has(BoardItem::factory()->completed()->count(20)
                ->state(function (array $attributes, Board $board) {
                    return [
                        'created_by' => $board->created_by,
                    ];
                }), 'boardItems'),
            'boards')->create();

    }
}
