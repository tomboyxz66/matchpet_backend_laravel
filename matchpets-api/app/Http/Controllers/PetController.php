<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    //
    public function pets(Request $request)
    {
        $validationRules = [
            'user_id' => 'required|integer',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => true,
                'data' => [
                    'message' => 'กรุณาลองอีกครั้ง.'
                ],
            ]);
        }
        $user_id = $request->input('user_id');

        $pets = DB::table('pets')->where('owner_id', $user_id)->get();
        $count = DB::table('pets')->where('owner_id', $user_id)->count();
if($count==0){
    return response()->json([
        'status' => true,
        'data' => [
            'message' => 'ไม่พบสัตว์เลี้ยง.'
        ],
    ]);
}
        return response()->json([
            'status' => true,
            'data' => $pets,
        ]);

    }
}
