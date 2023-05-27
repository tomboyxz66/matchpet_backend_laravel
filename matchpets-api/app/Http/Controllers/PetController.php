<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class PetController extends Controller
{
    //
    public function pets(Request $request)
    {
        $user_id = $request->input('user_id');

        $pets = DB::table('pets')->where('owner_id', $user_id)->get();

        if (!$pets) {
            return response()->json([
                'message' => 'ไม่พบสัตว์เลี้ยง.',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $pets,
        ]);
    }
}