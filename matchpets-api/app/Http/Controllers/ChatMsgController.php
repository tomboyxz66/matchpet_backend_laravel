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
        // ดึงข้อมูล message ทั้งหมดจากตาราง ChatMessages
        $messages = DB::table('ChatMessages')->where('receiver_id', $receiverId)->get();

        // ส่งข้อมูลกลับเป็น JSON response
        return response()->json($messages);
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