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
    public function getPets(Request $request)
    {
        $validationRules = [
            'owner_id' => 'required|integer',
            'species' => 'required|string',
            'name' => 'required|string',
            'gender' => 'required|string',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => null,
            ]);
        }
        // Retrieve the current user's pet ID
        $userId = $request->input('owner_id');
        $species = $request->input('species');
        $gender = $request->input('gender');
        $name = $request->input('name');
        $userPet = DB::table('pets')->where('owner_id', $userId)->where('name', $name)->first();
        // Retrieve all pets except the current user's pet
        $query = DB::table('pets')
            ->where('owner_id', '!=', $userId)
            ->where('species', $species)
            ->where('gender', '!=', $gender)
            ->whereNotIn('pet_id', function ($subQuery) use ($userPet) {
                $subQuery->select('pet2_id')
                    ->from('matches')
                    ->where('pet1_id', $userPet->pet_id)
                    ->where('is_checked', true);
            })
            ->whereNotIn('pet_id', function ($subQuery) use ($userPet) {
                $subQuery->select('pet1_id')
                    ->from('matches')
                    ->where('pet2_id', $userPet->pet_id)
                    ->where('is_checked', true);
            });

        // if ($species) {
        //     $query->where('species', $species)->where('gender', $gender);
        // }
        // Retrieve the status of each match for the user's pet
        $petsForMatching = $query->get();
        $matchStatus = [];

        foreach ($petsForMatching as $pet) {
            $match = DB::table('matches')
                ->where(function ($query) use ($userPet, $pet) {
                    $query->where('pet1_id', $userPet->pet_id)->where('pet2_id', $pet->pet_id);
                })
                ->orWhere(function ($query) use ($userPet, $pet) {
                    $query->where('pet1_id', $pet->pet_id)->where('pet2_id', $userPet->pet_id);
                })
                ->get();

            $status = $match ? true : false;
            $matchStatus[$pet->pet_id] = $status;
        }


        return response()->json([
            'status' => true,
            'data' => [
                'pets_for_matching' => $petsForMatching,
                'match_status' => $matchStatus,
            ],

        ]);

    }
}