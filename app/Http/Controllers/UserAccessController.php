<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\AppUser;
use App\Models\Registration;
use App\Models\Login;
use Illuminate\Support\Facades\Hash;

class UserAccessController extends Controller
{
    public function register(Request $request) {
        // Validate the input data
        $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'accountType' => 'nullable',
        ]);
    
        // Check if the user with the given email exists
        $existingUser = Registration::where('email', $request->email)->first();
    
        if ($existingUser) {
            return response()->json(['success' => false, 'message' => 'This email is already in use.'], 400);
        }
    
        // Create a new registration record
        $registration = new Registration();
        $registration->firstname = $request->firstName;
        $registration->lastname = $request->lastName;
        $registration->fullName = "$request->firstName $request->lastName";
        $registration->email = $request->email;
        $registration->accountType = $request->accountType;
        $registration->password = Hash::make($request->password);
        $registration->save();

        $user = Registration::where('email', $request->email)->first();
    
        return response()->json(['success' => true, 'message' => 'Sign Up Successful', 'userRecordId' => $user->id], 201);
    }

    public function addUserId(Request $request) {
        // Validate the input data
        $request->validate([
            'email' => 'required|email',
            'user_id' => 'required|string',
        ]);
    
        // Find the existing user by email
        $existingUser = Registration::where('email', $request->email)->first();
    
        // Check if the user exists
        if ($existingUser) {
            // Update user_id using parameter binding to prevent SQL injection
            Registration::where('email', $request->email)->update(['user_id' => $request->user_id]);
    
            return response()->json(['success' => true, 'message' => 'User Id added successfully'], 200);
        } else {
            // If the user is not found, return an appropriate response
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
    }
    
    public function login(Request $request) {
        // Validate the input data
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if the user exists and the password is correct
        $user = Registration::where('email', $validatedData['email'])->first();
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        $accessLevel = $user->user_id !== NULL ? "L2" : "L1";

        // Generate and return a token (you can use Laravel Passport o  r Sanctum for this)
        // Replace the following line with your token generation logic
        // $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Successful',
            'accessLevel' => $accessLevel,
            'userRecordId' => $user->id,
            'user_id' => $user->user_id,
            'firstName' => $user->firstName
        ]);
    }
}
