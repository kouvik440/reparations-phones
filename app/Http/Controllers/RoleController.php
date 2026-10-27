<?php

namespace App\Http\Controllers;

use App\Actions\Accounts\SaveRole;
use App\Http\Requests\SaveRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

final class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()->withCount('users')->orderByDesc('is_system')->orderBy('name')->paginate(12);

        return \Inertia\Inertia::render('Roles/Index', compact('roles'));
    }

    public function create(): Response
    {
        return \Inertia\Inertia::render('Roles/Form', ['groups' => collect(config('permissions'))->groupBy('group', true)]);
    }

    public function store(SaveRoleRequest $request, SaveRole $action): RedirectResponse
    {
        $action->handle($request->validated(), $request->user());

        return redirect()->route('roles.index')->with('success', 'Rôle créé. Vous pouvez maintenant l’attribuer à un compte utilisateur.');
    }

    public function edit(Role $role): Response
    {
        abort_if($role->isProtected(), 403);

        return \Inertia\Inertia::render('Roles/Form', ['role' => $role, 'groups' => collect(config('permissions'))->groupBy('group', true)]);
    }

    public function update(SaveRoleRequest $request, Role $role, SaveRole $action): RedirectResponse
    {
        $action->handle($request->validated(), $request->user(), $role);

        return redirect()->route('roles.index')->with('success', 'Rôle mis à jour. Les utilisateurs concernés devront se reconnecter si les permissions ont changé.');
    }
}
