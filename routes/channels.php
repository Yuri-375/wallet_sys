<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

//User Private Channel
Broadcast::channel(
    'user.{userId}',
    function (User $user, int $userId) {

        return $user->id === $userId;
    }
);

// Admin Fraud Channel
Broadcast::channel(
    'admin.fraud',
    function (User $user) {

        return $user->hasRole('admin');
    }
);
