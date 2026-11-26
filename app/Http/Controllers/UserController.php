<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** User account management (administrator only). */
class UserController extends CrudController
{
    protected function model(): string { return User::class; }
    protected function title(): string { return 'Users'; }
    protected function route(): string { return 'users'; }
    protected function search(): array { return ['name', 'email', 'role']; }

    protected function columns(): array
    {
        return [
            'Name'   => 'name',
            'Email'  => 'email',
            'Role'   => fn ($r) => $r->role_label,
            'Status' => fn ($r) => $this->badge($r->is_active ? 'active' : 'inactive'),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'name', 'label' => 'Full name', 'rules' => 'required|string|max:150'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:255', 'unique' => true],
            ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'rules' => 'required', 'options' => User::ROLES],
            ['name' => 'password', 'label' => $record?->exists ? 'New password (leave blank to keep)' : 'Password', 'type' => 'password',
                'rules' => $record?->exists ? 'nullable|string|min:8' : 'required|string|min:8'],
            ['name' => 'is_active', 'label' => 'Account is active', 'type' => 'checkbox', 'rules' => 'boolean', 'default' => 1, 'col' => 12],
        ];
    }

    protected function saving(array $data, ?Model $record): array
    {
        if (empty($data['password'])) {
            unset($data['password']);   // keep the current password on edit
        }

        return $data;
    }

    protected function deleteGuard(Model $record): ?string
    {
        return $record->id === auth()->id() ? 'You cannot delete your own account.' : null;
    }
}
