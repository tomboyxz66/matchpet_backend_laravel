<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatMsgController extends Controller
{
    //
    public function getMessages(Request $request)
    {
        $receiverId = $request->input('receiver_id');
        $senderId = $request->input('sender_id');
        // ดึงข้อมูล message ทั้งหมดจากตาราง ChatMessages
        $messages = DB::table('ChatMessages')
            ->where(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $senderId)
                    ->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $receiverId)
                    ->where('receiver_id', $senderId);
            })
            ->orderBy('timestamp', 'asc')
            ->get();

        // ส่งข้อมูลกลับเป็น JSON response
        return response()->json(
            [
                "status" => "success",
                "data" => $messages
            ]
        );
    }

    public function sendMessage(Request $request)
    {
        // รับค่า message, sender_id, receiver_id จาก request
        $message = $request->input('message');
        $senderId = $request->input('sender_id');
        $receiverId = $request->input('receiver_id');

        // บันทึกข้อมูลลงในตาราง ChatMessages
        DB::table('ChatMessages')->insert([
            'message' => $message,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'timestamp' => now()
        ]);

        // ส่งข้อความยืนยันการส่งกลับเป็น JSON response
        return response()->json(['message' => 'Message sent successfully']);
    }
}