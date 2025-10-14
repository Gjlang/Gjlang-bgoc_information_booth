<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Item;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // everyone logged-in can view list
    }

    public function view(User $user, Item $item): bool
    {
        return true; // everyone logged-in can view details
    }

    public function create(User $user): bool
    {
        // Both admin and user can create
        return $user->hasRole('admin') || $user->hasRole('user');
    }

    public function update(User $user, Item $item): bool
    {
        // Admin can update anything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Regular users can only update what they created
        // String-safe comparison handles '5' vs 5
        return (string)$item->created_by === (string)$user->id;
    }

    public function delete(User $user, Item $item): bool
    {
        // Delete limited to admin only
        return $user->hasRole('admin');
    }

    // Ability for export (used by @can('export', Item::class))
    public function export(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
