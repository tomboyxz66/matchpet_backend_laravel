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

    public function createPet(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'species' => 'required',
            'gender' => 'required',
            'age' => 'required|integer',
            'pet_image' => 'required|image|max:2048',
            'owner_id' => 'required|integer',

        ]);
        if ($request->hasFile('pet_image')) {
            $petImage = $request->file('pet_image');
            $petImagePath = $petImage->store('pet_images', 'public');
        }

        $pet = DB::table('Pets')->insertGetId([
            'name' => $validatedData['name'],
            'species' => $validatedData['species'],
            'gender' => $validatedData['gender'],
            'age' => $validatedData['age'],
            'pet_image' => $petImagePath,
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
    public function uploadPetImage(Request $request)
    {
        $petId = $request->input('pet_id');
        $validatedData = $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $pet = DB::table('Pets')->where('pet_id', $petId)->first();
        $validatedData = $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        if (!$pet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pet not found',
            ], 404);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('pet_images', 'public');

            $pet->image_path = $imagePath;
            $pet->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pet image uploaded successfully',
        ], 200);
    }

}