<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopUp;

class TopUpController extends Controller
{
    public function index()
    {
        $topUps = TopUp::with(['user', 'processor'])->latest()->paginate(20);

        return view('admin.topups.index', compact('topUps'));
    }
}
