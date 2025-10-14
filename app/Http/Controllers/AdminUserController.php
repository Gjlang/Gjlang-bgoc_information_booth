<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function store(Request $request)
    {
        // 1) Validasi tegas biar ketahuan kalau gagal
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role'     => ['required','in:admin,user'],
        ]);

        try {
            return DB::transaction(function () use ($data) {
                // 2) Buat user (hash password!)
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);

                // 3) Pastikan role ada, lalu assign
                $role = Role::where('name', $data['role'])->firstOrFail();
                $user->syncRoles([$role->name]);

                return redirect()
                    ->back()
                    ->with('success', 'User created: '.$user->email);
            });
        } catch (\Throwable $e) {
            Log::error('Admin create user failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->except(['password']),
            ]);

            return redirect()
                ->back()
                ->withInput($request->except('password'))
                ->withErrors(['general' => 'Create user failed: '.$e->getMessage()]);
        }
    }
}
