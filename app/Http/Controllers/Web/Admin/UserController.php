<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use RespondsWithFlash;

    public function index(Request $request)
    {
        $query = User::role('user')->with('wallet')->latest();

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($x) => $x->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"));
        }
        if ($request->filled('kyc')) {
            $query->where('kyc_status', $request->string('kyc'));
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'kyc_status' => 'not_submitted',
        ]);

        $user->assignRole('user');

        return $this->success('User created.', 'admin.users.index');
    }

    public function show(User $user)
    {
        abort_unless($user->hasRole('user'), 404);
        $user->load(['wallet', 'kycDocuments', 'topUps' => fn ($q) => $q->latest()->limit(5)]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_unless($user->hasRole('user'), 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->hasRole('user'), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'status' => ['required', 'in:active,suspended,blocked'],
        ]);

        $user->update($data);

        return $this->success('User updated.', 'admin.users.index');
    }

    public function destroy(User $user)
    {
        abort_unless($user->hasRole('user'), 404);
        $user->update(['status' => 'blocked']);
        $user->wallet?->update(['is_frozen' => true, 'freeze_reason' => 'user_blocked']);

        return $this->success('User blocked.', 'admin.users.index');

    }

    public function suspend(User $user)
    {
        $user->update(['status' => 'suspended']);

        return $this->success('User suspended.', 'admin.users.index');
    }

    public function activate(User $user)
    {
        $user->update(['status' => 'active']);

        return $this->success('User activated.', 'admin.users.index');
    }
}
