<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Canal de presencia para chat de departamentos
Broadcast::channel('department.{departmentId}', function ($user, $departmentId) {
    if ((int) $user->department_id === (int) $departmentId) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});

// Canal privado para notificaciones del usuario
Broadcast::channel('user.{userId}.notifications', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
