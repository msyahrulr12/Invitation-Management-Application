<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Receptionist;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceptionistPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Receptionist') || $authUser->hasRole('Receptionist');
    }

    public function view(AuthUser $authUser, Receptionist $receptionist): bool
    {
        return $authUser->can('View:Receptionist') || $authUser->hasRole('Receptionist');
    }

    public function create(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Create:Receptionist');
    }

    public function update(AuthUser $authUser, Receptionist $receptionist): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Update:Receptionist');
    }

    public function delete(AuthUser $authUser, Receptionist $receptionist): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Delete:Receptionist');
    }

    public function restore(AuthUser $authUser, Receptionist $receptionist): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Restore:Receptionist');
    }

    public function forceDelete(AuthUser $authUser, Receptionist $receptionist): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('ForceDelete:Receptionist');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('ForceDeleteAny:Receptionist');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('RestoreAny:Receptionist');
    }

    public function replicate(AuthUser $authUser, Receptionist $receptionist): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Replicate:Receptionist');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Reorder:Receptionist');
    }
}
