<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Statement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StatementController extends Controller
{
    public function index()
    {
        $statements = Statement::where('user_id', Auth::id())
            ->latest('period_start')->paginate(10);
        return view('user.statements.index', compact('statements'));
    }

    public function download(Statement $statement)
    {
        abort_unless($statement->user_id === Auth::id(), 403);
        abort_unless($statement->pdf_path && Storage::exists($statement->pdf_path), 404);
        return Storage::download($statement->pdf_path);
    }
}
