<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Registration;


class UserAdministration extends Controller
{
    public function getAllUsers(Request $request){
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $users = Registration::all();

        return response()->json(['success' => true, 'users' => $users]);
    }

    public function getUser(Request $request){
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        
        $user = Registration::where("user_id", $user_id)->first();
        return $user;

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            return response()->json(['success' => true, 'user' => $user]);
    }

    public function update(Request $request){
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        // Find the user by ID
        $user = Registration::find($user_id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Validation rules for user update
        $validatedData = $request->validate([
            'email' => 'email|unique:users,email,' . $user_id,
            'password' => 'string',
            'firstName' => 'string',
            // Add other fields as needed
        ]);

        // Update the user
        $user->update($validatedData);

        return response()->json(['success' => true, 'message' => 'User updated successfully', 'user' => $user]);
    }

    public function delete(Request $request){

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        // Find the user by ID
        $user = Registration::find($user_id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Delete the user
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    private function grabUserFromToken($request){
        $key = env('JWT_SECRET');
        $token = explode(" ", $request->header("authorization"))[1];
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $decodedArr = json_decode(json_encode($decoded), true);

        $user_id = $decodedArr['user_id'];
        
        return $user_id;
    }
}
