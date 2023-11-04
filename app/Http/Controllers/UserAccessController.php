<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Login;
use Illuminate\Support\Facades\Hash;

class UserAccessController extends Controller
{
    public function register(Request $request) {
        // Validate the input data
        // $validatedData = $request->validate([
        //     'channel_name' => 'required|string',
        //     'description' => 'required|string',
        //     'business_email' => 'required|email|unique:registrations',
        //     'accept_terms' => 'required|boolean',
        //     'channel_language' => 'required|string',
        //     'competitive_channels' => 'required|string',
        //     'keywords' => 'required|string',
        //     'password' => 'required|string|min:6',
        // ]);

        $validatedData = $request->validate([
            'fullname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:registrations',
            'password' => 'required|string|min:6',
        ]);

        // Create a new registration record
        $registration = new User($validatedData);
        $registration->password = Hash::make($validatedData['password']);
        $registration->save();

        return response()->json(['message' => 'Registration successful'], 201);
    }

    public function login(Request $request) {
        // Validate the input data
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if the user exists and the password is correct
        $user = Login::where('email', $validatedData['email'])->first();
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate and return a token (you can use Laravel Passport or Sanctum for this)
        // Replace the following line with your token generation logic
        $token = $user->createToken('authToken')->accessToken;

        return response()->json(['token' => $token]);
    }
}
