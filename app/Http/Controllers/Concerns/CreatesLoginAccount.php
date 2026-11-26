<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Lets student / trainee forms optionally create a portal login. */
trait CreatesLoginAccount
{
    protected function checkAccount(array $data, ?Model $record): void
    {
        if (empty($data['create_account']) || $record?->user_id) {
            return;
        }
        if (empty($data['email'])) {
            throw ValidationException::withMessages(['email' => 'Email is required to create a login account.']);
        }
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages(['email' => 'This email already has an account.']);
        }
    }

    protected function createAccount(Model $record, array $data, string $role): ?string
    {
        if (empty($data['create_account']) || $record->user_id) {
            return null;
        }

        $password = Str::random(8);
        $user = User::create([
            'name'      => trim($record->first_name.' '.$record->last_name),
            'email'     => $record->email,
            'password'  => $password,
            'role'      => $role,
            'is_active' => true,
        ]);
        $record->update(['user_id' => $user->id]);

        return "Login created for {$record->email} - temporary password: {$password}";
    }
}
