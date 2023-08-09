<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
public function index()
    {
        return Inertia::render('Users/Index', [
            'filters' => request()->all('search', 'role', 'trashed'),
            'users' => User::orderBy('name')
//                ->filter(request()->only('search', 'role', 'trashed'))
                ->paginate()
                ->withQueryString()
                ->through(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'deleted_at' => $user->deleted_at,
                    ];
                }),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store()
    {
        $data = Request::validate([
                'first_name' => ['required', 'max:255'],
                'last_name' => ['required', 'max:255'],
                'email' => ['required', 'max:255', 'email', 'unique:users'],
//                'role' => ['required', Rule::in(User::roles())],
            ]);

        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        $data['password'] = 'password';

        User::create($data);

        return Redirect::route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'deleted_at' => $user->deleted_at,
            ],
        ]);
    }

    public function update(User $user)
    {
        $user->update(
            Request::validate([
                'name' => ['required', 'max:255'],
                'email' => [
                    'required',
                    'max:255',
                    'email',
                    Rule::unique('users')->ignore($user->id),
                ],
                'role' => ['required', Rule::in(User::roles())],
            ])
        );

        return Redirect::back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return Redirect::back()->with('success', 'User deleted.');
    }

    public function restore(User $user)
    {
        $user->restore();

        return Redirect::back()->with('success', 'User restored.');
    }
}
