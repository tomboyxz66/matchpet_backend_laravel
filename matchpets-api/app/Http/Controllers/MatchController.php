<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchController extends Controller
{
    //
    public function matchPets(Request $request)
    {
        $validationRules = [
            'pet1_id' => 'required|exists:Pets,pet_id',
            'pet2_id' => 'required|exists:Pets,pet_id',
            'user1_id' => 'required',
            'user2_id' => 'required',
            'is_checked' => 'required',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }
        // Validate the request data
        // $request->validate([
        //     'pet1_id' => 'required|exists:Pets,pet_id',
        //     'pet2_id' => 'required|exists:Pets,pet_id',
        //     'user1_id' => 'required',
        //     'user2_id' => 'required',
        //     'is_checked' => 'required',
        // ]);

        // Retrieve the pet IDs from the request
        $petId1 = $request->input('pet1_id');
        $petId2 = $request->input('pet2_id');
        $userId1 = $request->input('user1_id');
        $userId2 = $request->input('user2_id');
        $isChecked = $request->input('is_checked');

        // Check if the match already exists in the Matches table
        $existingMatch = DB::table('matches')
            ->where(function ($query) use ($petId1, $petId2) {
                $query->where('pet1_id', $petId1)->where('pet2_id', $petId2);
            })
            ->orWhere(function ($query) use ($petId1, $petId2) {
                $query->where('pet1_id', $petId2)->where('pet2_id', $petId1);
            })
            ->first();
        if ($existingMatch) {
            return response()->json([
                'message' => "จับคู่ไปแล้ว",
            ]);
        }
        $currentDate = Carbon::now();
        DB::table('matches')->insert([
            'pet1_id' => $petId1,
            'pet2_id' => $petId2,
            'user1_id' => $userId1,
            'user2_id' => $userId2,
            'is_checked' => $isChecked,
            'match_date' => $currentDate,
        ]);
        if ($isChecked) {
            # code...

            return response()->json([
                'message' => 'Match accept successfully.',
            ]);
        } else {
            # code...
            return response()->json([
                'message' => 'Match reject successfully.',
            ]);
        }


        // Insert the new match into the Matches table
        // DB::table('Matches')->insert([
        //     'pet_id_1' => $petId1,
        //     'pet_id_2' => $petId2,
        //     'is_checked' => $isChecked,
        // ]);

        // return response()->json([
        //     'message' => 'Match created successfully.',
        // ]);
    }
}