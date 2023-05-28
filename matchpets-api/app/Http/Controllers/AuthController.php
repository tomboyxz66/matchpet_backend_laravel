<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $validationRules = [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'age' => 'required|integer',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        // Check for validation errors
        if ($validator->fails()) {
            // return response()->json([
            //     'errors' => $validator->errors(),
            // ], 400);
            return response()->json([
                'message' => 'สมัครสมาชิกไม่สำเร็จ.',
            ]);
        }

        // Retrieve user data from the request
        $userData = $request->only('username', 'email', 'password');

        // Retrieve pet data from the request
        $petData = $request->only('name', 'species', 'gender', 'age');
        //bcrypt password
        $userData['password'] = Hash::make($userData['password']);

        // Insert user data into the Users table
        $userId = DB::table('users')->insertGetId($userData);

        // Insert pet data into the Pets table
        $petData['owner_id'] = $userId;
        DB::table('pets')->insert($petData);

        return response()->json([
            'message' => 'สมัครสมาชิกสำเร็จ.',
        ]);
    }
    public function login(Request $request)
    {
        // Validate the request data
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Retrieve the email and password from the request
        $username = $request->input('username');
        $password = $request->input('password');

        // Retrieve the user from the Users table
        $user = DB::table('users')->where('username', $username)->first();
        $pet = DB::table('pets')->where('owner_id', $user->user_id)->get();

        // Check if the user exists and the password is correct
        if ($user && Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'data' => [
                    'users' => [
                        'username' => $user->username,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                    ],
                    'pet' => $pet
                ],
            ]);
        } else {
            return response()->json([
                'message' => 'เข้าสู่ระบบล้มเหลว',
                'data' => [
                    'users' => null,
                    'pet' => null
                ],
            ]);
        }

        // Invalid login credentials
        // throw ValidationException::withMessages([
        //     'username' => ['The provided credentials are incorrect.'],
        // ]);
    }
}
