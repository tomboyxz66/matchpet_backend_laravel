<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function show(Request $request)
    {
        $username = $request->input('username');

        $user = DB::table('users')->where('username', $username)->select('username')->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user->username,
        ]);
    }
    public function insertPets(Request $request)
    {
        $validationRules = [
            'username' => 'required',
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'age' => 'required|integer',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        // Retrieve pet data from the request
        $petData = $request->only('name', 'species', 'gender', 'age');
        $petData['owner_id'] = $request->input('user_id');
        $user_id = $request->input('user_id');
        $count = DB::table('pets')->where('owner_id', $user_id)->count();
        if ($count >= 2) {
            return response()->json([
                'message' => "เพิ่มสัตว์เลี้ยงไม่สำเร็จ. เนื่องจากตอนนี้คนมีสัตว์เลี้ยงจำนวน $count",
            ]);
        }

        DB::table('pets')->insert($petData);

        return response()->json([
            'message' => 'เพิ่มสัตว์เลี้ยงสำเร็จ.',
        ]);
    }

}