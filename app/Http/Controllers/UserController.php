<?php

namespace App\Http\Controllers;

use App\Actions\Accounts\UpdateAccount;
use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
            'role' => ['nullable', Rule::in(\App\Models\Role::pluck('slug')->push('all')->all())],
        ]);
        $search = trim($data['q'] ?? '');
        $status = $data['status'] ?? 'all';
        $role = $data['role'] ?? 'all';
        $users = User::query()->with('roleDefinition')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->when($status !== 'all', fn ($q) => $q->where('is_active', $status === 'active'))
            ->when($role !== 'all', fn ($q) => $q->where('role', $role))
            ->orderBy('name')->orderBy('id')->paginate(12)->withQueryString();
        $stats = ['total' => User::count(), 'active' => User::where('is_active', true)->count(), 'admins' => User::where('is_active', true)->where('role', 'admin')->count()];

        $users->through(fn ($user) => [...$user->only('id', 'name', 'email', 'role', 'is_active', 'last_login_at'), 'role_name' => $user->roleDefinition?->name, 'can_manage' => $request->user()->canManageAccount($user)]);
        $roles = \App\Models\Role::orderBy('name')->get();

        return \Inertia\Inertia::render('Users/Index', compact('users', 'stats', 'search', 'status', 'role', 'roles'));
    }

    public function create(): Response
    {
        return \Inertia\Inertia::render('Users/Form', ['roles' => \App\Models\Role::orderBy('name')->get()->filter(fn ($role) => auth()->user()->canAssignRole($role))->values()]);
    }

    public function store(SaveUserRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password', 'role']));

        return redirect()->route('users.edit', $user)->with('success', 'Compte créé. L’utilisateur peut se connecter avec son email et le mot de passe défini.');
    }

    public function edit(User $user): Response
    {
        abort_unless(auth()->user()->canManageAccount($user), 403);

        return \Inertia\Inertia::render('Users/Form', ['user' => $user->only('id', 'name', 'email', 'role', 'is_active'), 'roles' => \App\Models\Role::orderBy('name')->get()->filter(fn ($role) => auth()->user()->canAssignRole($role))->values()]);
    }

    public function update(SaveUserRequest $request, User $user, UpdateAccount $action): RedirectResponse
    {
        $action->handle($user, $request->safe()->only(['name', 'email', 'password', 'role']), $request->user());

        return redirect()->route('users.index')->with('success', 'Compte mis à jour. Si les accès ont changé, une nouvelle connexion est nécessaire.');
    }

    public function status(Request $request, User $user, UpdateAccount $action): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $active = (bool) $data['is_active'];
        $action->handle($user, ['is_active' => $active], $request->user());

        return redirect()->route('users.index')->with('success', $active ? 'Compte réactivé.' : 'Compte désactivé. Ses sessions ne donnent plus accès à la plateforme.');
    }

    public function destroy(Request $request, User $user, UpdateAccount $action): RedirectResponse
    {
        // Compatibilité avec les anciens liens : conserver le compte et ses références.
        $action->handle($user, ['is_active' => false], $request->user());

        return redirect()->route('users.index')->with('success', 'Compte désactivé ; son historique est conservé.');
    }
}
