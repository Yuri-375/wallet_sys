<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    use RespondsWithFlash;

    public function index(Request $request)
    {
        $query = User::role('agent', 'api');

        if ($request->filled('q')) {
            $q = $request->string('q');

            $query->where(function ($x) use ($q) {
                $x->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $agents = $query->latest()->paginate(20)->withQueryString();

        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        return view('admin.agents.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $agent = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'kyc_status' => 'approved',
        ]);

        $agent->assignRole('agent');

        return $this->success('Agent created.', 'admin.agents.index');
    }

    public function show(User $agent)
    {
        abort_unless($agent->hasRole('agent'), 404);

        return view('admin.agents.show', compact('agent'));
    }

    public function edit(User $agent)
    {
        abort_unless($agent->hasRole('agent'), 404);

        return view('admin.agents.edit', compact('agent'));
    }

    public function update(Request $request, User $agent)
    {
        abort_unless($agent->hasRole('agent'), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', Rule::unique('users')->ignore($agent->id)],
            'status' => ['required', 'in:active,suspended,blocked'],
        ]);

        $agent->update($data);

        return $this->success('Agent updated.', 'admin.agents.index');
    }

    public function destroy(User $agent)
    {
        abort_unless($agent->hasRole('agent'), 404);
        $agent->update(['status' => 'blocked']);

        return $this->success('Agent blocked.', 'admin.agents.index');
    }
}
