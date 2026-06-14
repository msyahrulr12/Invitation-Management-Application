<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Event') || $authUser->hasRole('Receptionist');
    }

    public function view(AuthUser $authUser, Event $event): bool
    {
        return $authUser->can('View:Event') || $authUser->hasRole('Receptionist');
    }

    public function create(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Create:Event');
    }

    public function update(AuthUser $authUser, Event $event): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Update:Event');
    }

    public function delete(AuthUser $authUser, Event $event): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Delete:Event');
    }

    public function restore(AuthUser $authUser, Event $event): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Restore:Event');
    }

    public function forceDelete(AuthUser $authUser, Event $event): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('ForceDelete:Event');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('ForceDeleteAny:Event');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('RestoreAny:Event');
    }

    public function replicate(AuthUser $authUser, Event $event): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Replicate:Event');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('Receptionist')) {
            return false;
        }

        return $authUser->can('Reorder:Event');
    }
}
