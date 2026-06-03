<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Canal de presencia para el chat de departamentos.
| Solo usuarios autenticados que pertenecen al departamento pueden unirse.
|
*/

Broadcast::channel('department.{departmentId}', function ($user, $departmentId) {
    if ((int) $user->department_id === (int) $departmentId) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});
