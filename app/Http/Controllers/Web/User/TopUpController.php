<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\TopUp;
use Illuminate\Support\Facades\Auth;

class TopUpController extends Controller
{
    public function index()
    {
        $topUps = TopUp::where('user_id', Auth::id())->latest()->paginate(10);
        return view('user.topups.index', compact('topUps'));
    }
}
