<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\Biller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillerController extends Controller
{
    use RespondsWithFlash;

    public function index(Request $request)
    {
        $billers = Biller::latest()->paginate(20);
        return view('admin.billers.index', compact('billers'));
    }

    public function create()
    {
        return view('admin.billers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:billers,code'],
            'category' => ['required', 'string', 'max:100'],
            'api_url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        Biller::create($data);

        return $this->success('Biller created.', 'admin.billers.index');
    }

    public function show(Biller $biller)
    {
        return view('admin.billers.show', compact('biller'));
    }

    public function edit(Biller $biller)
    {
        return view('admin.billers.edit', compact('biller'));
    }

    public function update(Request $request, Biller $biller)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('billers')->ignore($biller->id)],
            'category' => ['required', 'string', 'max:100'],
            'api_url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $biller->update($data);

        return $this->success('Biller updated.', 'admin.billers.index');
    }

    public function destroy(Biller $biller)
    {
        $biller->update(['is_active' => false]);

        return $this->success('Biller deactivated.', 'admin.billers.index');
    }
}
