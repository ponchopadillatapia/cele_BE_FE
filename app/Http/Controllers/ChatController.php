<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Department;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Obtener los departamentos disponibles.
     */
    public function departments(): JsonResponse
    {
        return response()->json(Department::all());
    }

    /**
     * Obtener mensajes de un departamento.
     */
    public function messages(Department $department): JsonResponse
    {
        $messages = $department->messages()
            ->with('user:id,name')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * Enviar un mensaje al canal del departamento.
     */
    public function send(Request $request, Department $department): JsonResponse
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'user_id' => $request->user()->id,
            'department_id' => $department->id,
            'body' => $request->body,
        ]);

        $message->load('user:id,name');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }
}
