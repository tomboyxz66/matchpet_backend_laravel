<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchController extends Controller
{
    //
    public function createMatch(Request $request)
    {
        $user1Id = $request->input('user1_id');
        $pet1Id = $request->input('pet1_id');
        $user2Id = $request->input('user2_id');
        $pet2Id = $request->input('pet2_id');
        $status = $request->input('match_status');

        $match = DB::table('Matches')->insertGetId([
            'user1_id' => $user1Id,
            'pet1_id' => $pet1Id,
            'user2_id' => $user2Id,
            'pet2_id' => $pet2Id,
            'match_date' => now(),
            'is_checked' => 0,
            'match_status' => $status
        ]);

        return response()->json([
            'status' => 'success',
            'match_id' => $match,
            'data' => $status,
        ], 201);
    }

    public function getMatch(Request $request)
    {
        $userId = $request->input('user_id');

        $matches = DB::table('Matches')
            ->select('Matches.*', 'u1.username as user1_username', 'u1.first_name as user1_first_name', 'u2.username as user2_username', 'u2.first_name as user2_first_name', 'p1.name as pet1_name', 'p2.name as pet2_name')
            ->join('Users as u1', 'Matches.user1_id', '=', 'u1.user_id')
            ->join('Users as u2', 'Matches.user2_id', '=', 'u2.user_id')
            ->join('Pets as p1', 'Matches.pet1_id', '=', 'p1.pet_id')
            ->join('Pets as p2', 'Matches.pet2_id', '=', 'p2.pet_id')
            ->where(function ($query) use ($userId) {
                $query->where('Matches.user2_id', $userId);
                // ->orWhere('Matches.user2_id', $userId);
            })
            ->orderBy('Matches.match_date', 'desc')
            ->get();

        if ($matches->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No matches found',
                'data' => null,
            ], 404);
        }
        $res = $matches->where('match_status', 'Pending');
        return response()->json([
            'status' => 'success',
            'message' => 'Matches',
            'data' => $res,
        ], 200);
    }
    public function acceptRequest(Request $request)
    {
        $matchId = $request->input('match_id');

        $match = DB::table('Matches')
            ->where('match_id', $matchId)
            ->update([
                'is_checked' => 1,
                'match_status' => 'Accepted',
            ]);

        if ($match) {
            return response()->json([
                'status' => 'success',
                'message' => 'Match request accepted',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to accept match request',
            ], 500);
        }
    }
    public function rejectRequest(Request $request)
    {
        $matchId = $request->input('match_id');

        $match = DB::table('Matches')
            ->where('match_id', $matchId)
            ->update([
                'is_checked' => 1,
                'match_status' => 'Rejected',
            ]);

        if ($match) {
            return response()->json([
                'status' => 'success',
                'message' => 'Match request rejected',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject match request',
            ], 500);
        }
    }

    public function getAcceptedFriends(Request $request)
    {
        $userId = $request->input('user_id');
        $petId = $request->input('pet_id');

        $friends = DB::table('Matches')
            ->where(function ($query) use ($userId) {
                $query->where('Matches.user1_id', $userId)
                    ->orWhere('Matches.user2_id', $userId);
            })
            ->where(function ($query) use ($petId) {
                $query->where('Matches.pet1_id', $petId)
                    ->orWhere('Matches.pet2_id', $petId);
            })
            ->select('Users.user_id', 'Users.username', 'Users.email', 'Users.first_name', 'Users.last_name', 'Users.location', 'Pets.pet_id', 'Pets.name as pet_name', 'Pets.species', 'Pets.gender', 'Pets.age')
            ->join('Users', function ($join) use ($userId) {
                $join->on('Matches.user1_id', '=', 'Users.user_id')
                    ->orOn('Matches.user2_id', '=', 'Users.user_id');
            })
            ->join('Pets', function ($join) {
                $join->on('Matches.pet1_id', '=', 'Pets.pet_id')
                    ->orOn('Matches.pet2_id', '=', 'Pets.pet_id');
            })
            ->where('Matches.match_status', 'Accepted')
            ->get();
        $res = $friends->where('user_id', "!=", $userId)
            ->where('pet_id', "!=", $petId);


        return response()->json([
            'status' => 'success',
            'message' => 'Accepted friends',
            'data' => $res->values(),

        ], 200);
    }
}