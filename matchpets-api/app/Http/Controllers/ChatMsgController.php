<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatMsgController extends Controller
{
    //
    public function show()
    {
        $message = "true";
        return response()->json($message);
    }
    public function getMsg(Request $request)
    {
        $validationRules = [
            'sender_id' => 'required|int',
            'receiver_id' => 'required|int',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        // Check for validation errors
        // if ($validator->fails()) {
        //     // return response()->json([
        //     //     'errors' => $validator->errors(),
        //     // ], 400);
        //     return response()->json([
        //         'message' => 'ไม่สำเร็จ.',
        //     ]);
        // }
        $senderId = $request->input('sender_id');
        $receiverId = $request->input('receiver_id');
        $messages = $messages = DB::table('chatmessages')
            ->where(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $senderId)
                    ->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $receiverId)
                    ->where('receiver_id', $senderId);
            })
            ->orderBy('timestamp', 'ASC')
            ->get();


        return response()->json([
            'message' => $messages,
        ]);

    }
    public function sendMsg(Request $request)
    {
        $message = DB::table('chatmessages')->insertGetId([
            'sender_id' => $request->input('sender_id'),
            'receiver_id' => $request->input('receiver_id'),
            'message' => $request->input('message'),
            'timestamp' => now(),
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $message
            ]
        ], 201);
    }
}