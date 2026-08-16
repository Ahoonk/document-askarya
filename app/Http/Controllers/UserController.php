<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $currentUser = auth()->user();

        abort_unless($currentUser?->isSuperAdmin(), 403);

        $users = User::query()
            ->with('company')
            ->orderByRaw("CASE WHEN role = 'superadmin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company' => [
                    'id' => $user->company?->id,
                    'name' => $user->company?->name,
                ],
                'created_at' => optional($user->created_at)->toISOString(),
                'updated_at' => optional($user->updated_at)->toISOString(),
                'is_current' => $currentUser?->id === $user->id,
            ])
            ->values();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'stats' => [
                'total' => $users->count(),
                'superadmin' => $users->where('role', 'superadmin')->count(),
                'admin' => $users->where('role', 'admin')->count(),
            ],
            'current_user_id' => $currentUser?->id,
        ]);
    }

    public function create(): Response
    {
        $currentUser = auth()->user();

        abort_unless($currentUser?->isSuperAdmin(), 403);

        return Inertia::render('Users/Create', [
            'companies' => Company::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Company $company) => [
                    'id' => $company->id,
                    'name' => $company->name,
                ])
                ->values(),
            'options' => [
                'roles' => ['admin', 'superadmin'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = auth()->user();

        abort_unless($currentUser?->isSuperAdmin(), 403);

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'superadmin'])],
        ]);

        User::create([
            'company_id' => $validated['company_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        $currentUser = auth()->user();

        abort_unless($currentUser?->isSuperAdmin(), 403);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'company_id' => $user->company_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'companies' => Company::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Company $company) => [
                    'id' => $company->id,
                    'name' => $company->name,
                ])
                ->values(),
            'options' => [
                'roles' => ['admin', 'superadmin'],
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        abort_unless($currentUser?->isSuperAdmin(), 403);

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'superadmin'])],
        ]);

        $payload = [
            'company_id' => $validated['company_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        abort_unless($currentUser?->isSuperAdmin(), 403);
        abort_if($currentUser?->id === $user->id, 422, 'Akun aktif tidak bisa dihapus.');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
