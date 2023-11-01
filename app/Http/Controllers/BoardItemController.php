<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class BoardItemController extends Controller
{
    public function store(Request $request, Board $board)
    {
        $request->validate([
            'title' => 'required|min:5|string|max:150'
        ]);

        $board->boardItems()->create([
            'code' => 'GC-'.now()->micro,
            'title' => $request->title,
            'created_by' => $request->user()->id,
        ]);
        return Redirect::route('board.todo', ['board' => $board]);
    }
}
