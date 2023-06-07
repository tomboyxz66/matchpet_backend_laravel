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
        if ($count == 0) {
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
    public function getAllPets(Request $request)
    {
        $ownerId = $request->input('owner_id');
        $petId = $request->input('pet_id');
        $gender = $request->input('gender');
        $species = $request->input('species');

        $petsQuery = DB::table('Pets');

        if ($ownerId) {
            $petsQuery->where('owner_id', "!=", $ownerId);
        }

        if ($petId) {
            $petsQuery->where('pet_id', "!=", $petId);
        }

        if ($gender) {
            $petsQuery->where('gender', "!=", $gender);
        }
        if ($species) {
            $petsQuery->where('species', $species);
        }

        $pets = $petsQuery->get();

        foreach ($pets as $pet) {
            $matched = DB::table('Matches')
                ->where('pet2_id', $pet->pet_id)
                // ->orWhere('pet2_id', $pet->pet_id)
                // ->where('is_checked',false)
                // ->where('match_status','Accepted')
                // ->where('pet2_id', "!=", $petId)
                ->exists();

            $pet->is_matched = $matched;
        }
        $data = $pets->where('is_matched', '!=', true);

        return response()->json([
            'status' => 'success',
            'message' => 'All pets',
            'data' => $data->values(),

        ], 200);
    }
}
