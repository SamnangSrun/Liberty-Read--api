<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can send notification to all users.
     */
    public function sendNotificationToAll(User $user)
    {
        // Check if the user is an admin (add your condition here)
        return $user->is_admin; // Assuming 'is_admin' is a boolean field in the User model
    }

    /**
     * Determine whether the user can send notifications to users who bought a specific book.
     */
    public function sendNotificationToUsersWhoBoughtBook(User $user)
    {
        // Check if the user is a seller (add your condition here)
        return $user->is_seller; // Assuming 'is_seller' is a boolean field in the User model
    }
}
