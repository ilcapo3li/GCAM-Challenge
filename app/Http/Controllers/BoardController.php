<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Board;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class BoardController extends Controller
{
    /**
     * Display the board's list.
     */
    public function index(Request $request): Response
    {
        $boards = $request->user()->boards()->with('createdBy')->get();
        return Inertia::render('Board/Index', [
            'boards' => $boards,
            'AddBoardModal' => false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|string|max:150'
        ]);
        Board::create([
            'code' => 'GC-'.now()->micro,
            'title' => $request->title,
            'created_by' => $request->user()->id,
        ]);
        return Redirect::route('board.index');
    }

    /**
     * Display boardItems's Todo.
     */
    public function details(Request $request, Board $board): Response
    {
        $board = $board->load('boardItems.createdBy', 'createdBy');
        return Inertia::render('Board/todolist', [
            'board' => $board,
            'addTaskModal' => false
        ]);
    }

    /**
     * Display BoardItems's Scrum.
     */
    public function scrum(Request $request, Board $board): Response
    {
        $board= $board->load('boardItems.createdBy', 'createdBy');
        return Inertia::render('Board/scrumboard', [
            'boards' => $board,
        ]);
    }
}
