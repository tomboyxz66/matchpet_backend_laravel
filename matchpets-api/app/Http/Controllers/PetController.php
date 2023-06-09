<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

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
        $count = $pets->count();

        if ($count == 0) {
            return response()->json([
                'status' => true,
                'data' => [
                    'message' => 'ไม่พบสัตว์เลี้ยง.'
                ],
            ]);
        }

        $petImages = [];

        foreach ($pets as $pet) {
            $imageName = $pet->pet_image;
            $imageUrl = asset('storage/' . $imageName); // สร้าง URL สำหรับแสดงรูปภาพจากโฟลเดอร์ public


            $petData[] = [

                'pet_id' => $pet->pet_id,
                'name' => $pet->name,
                'species' => $pet->species,
                'breed' => $pet->breed,
                'gender' => $pet->gender,
                'age' => $pet->age,
                'owner_id' => $pet->owner_id,
                'pet_image' => $imageUrl,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $petData,
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

        $petData = [];

        foreach ($pets as $pet) {
            $matched = DB::table('Matches')
                ->where('pet2_id', $pet->pet_id)
                ->exists();

            if (!$matched) {
                $imageName = $pet->pet_image;
                $imageUrl = asset('storage/' . $imageName);
                $petData[] = [
                    'pet_id' => $pet->pet_id,
                    'name' => $pet->name,
                    'species' => $pet->species,
                    'breed' => $pet->breed,
                    'gender' => $pet->gender,
                    'age' => $pet->age,
                    'owner_id' => $pet->owner_id,
                    'is_matched' => $matched,
                    'pet_image' => $imageUrl,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All pets',
            'data' => $petData,
        ], 200);
    }

    public function createPet(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'species' => 'required',
            'gender' => 'required',
            'breed' => 'required',
            'age' => 'required|integer',
            'owner_id' => 'required|integer',

        ]);
        $imageData = $request->input('image');
        $imageName = $request->input('filename');
        $decodedImage = base64_decode($imageData);
        Storage::disk('public')->put($imageName, $decodedImage);

        $pet = DB::table('Pets')->insertGetId([
            'name' => $validatedData['name'],
            'species' => $validatedData['species'],
            'gender' => $validatedData['gender'],
            'breed' => $validatedData['breed'],
            'age' => $validatedData['age'],
            'pet_image' => $imageName,
            'owner_id' => $validatedData['owner_id'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pet created successfully',
            'data' => $pet,
        ], 201);
    }
    public function deletePet(Request $request)
    {
        $petId = $request->input('pet_id');
        $pet = DB::table('Pets')->where('pet_id', $petId)->first();

        if (!$pet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pet not found',
            ], 404);
        }

        DB::table('Pets')->where('pet_id', $petId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pet deleted successfully',
        ], 200);
    }

}