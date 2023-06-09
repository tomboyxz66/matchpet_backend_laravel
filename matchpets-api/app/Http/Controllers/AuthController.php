<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {

        $validationRules = [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'location' => 'required',
            'password' => 'required|string',
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'age' => 'required|integer',
            // 'filename' => 'required|string',
            // 'image' => 'required|string'
        ];
        $imageData = $request->input('image');
        $imageName = $request->input('filename');

        $decodedImage = base64_decode($imageData);
        Storage::disk('public')->put($imageName, $decodedImage);

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        // Check for validation errors
        if ($validator->fails()) {
            $errors = $validator->errors();

            // Check if username is duplicated
            if ($errors->has('username')) {
                return response()->json([
                    'message' => 'ชื่อผู้ใช้ถูกใช้งานแล้ว',
                ], 400);
            }

            // Check if email is duplicated
            if ($errors->has('email')) {
                return response()->json([
                    'message' => 'อีเมล์ถูกใช้งานแล้ว',
                ], 400);
            }

        }



        // Retrieve user data from the request
        $userData = $request->only('username', 'email', 'password', 'first_name', 'last_name', 'location');

        // Retrieve pet data from the request
        $petData = $request->only('name', 'species', 'gender', 'age', "breed");

        // Hash the password
        $userData['password'] = Hash::make($userData['password']);

        // Insert user data into the Users table
        $userId = DB::table('users')->insertGetId($userData);

        // Insert pet data into the Pets table
        $petData['pet_image'] = $imageName;
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
                        'user_id' => $user->user_id,
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
    }
}