<?php

namespace App\Http\Controllers;

use App\Events\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Enviar una notificación privada a un usuario.
     */
    public function notifyUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string',
            'message' => 'required|string',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->input('user_id');
        $title = $request->input('title');
        $message = $request->input('message');
        $data = $request->input('data', []);

        // Emitir el evento para el canal privado del usuario
        broadcast(new UserNotification($userId, $title, $message, $data))->toOthers();

        return response()->json(['status' => 'ok', 'notified_user_id' => $userId]);
    }
}
