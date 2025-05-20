<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // user register
    public function userRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'user'; // default role

        $user = User::create($data);

        return response()->json(['message' => 'User registered successfully', 'data' => $user, "status" => "success"], 201);
    }

    // login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials', "status" => "failed"], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login Successfully', 'data' => ['user' => $user, 'token' => $token], "status" => "success"], 200);
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout Successfully', "status" => "success"], 200);
    }

    // register restaurant
    public function registerRestaurant(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|string',
            'restaurant_name' => 'required|string',
            'restaurant_address' => 'required|string',
            'latlong' => 'required|string',
            'photo' => 'required|image',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'restaurant'; // default role

        $user = User::create($data);

        // check photo
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile'), $filename);
            $user->photo =  $filename;
            $user->save();
        }

        return response()->json(['message' => 'Restaurant registered successfully', 'data' => $user, "status" => "success"], 201);
    }

    // register driver
    public function registerDriver(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'address' => 'nullable|string',
            'phone' => 'required|string',
            'license_plate' => 'required|string',
            'photo' => 'required|image',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'driver'; // default role

        $user = User::create($data);
         // check photo
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile'), $filename);
            $user->photo =  $filename;
            $user->save();
        }

        return response()->json(['message' => 'Driver registered successfully', 'data' => $user, "status" => "success"], 201);
    }

    // update latlong user
    public function updateLatLong(Request $request)
    {
        $request->validate([
            'latlong' => 'required|string',
        ]);

        $user = $request->user();
        $user->latlong = $request->latlong;
        $user->save();

        return response()->json(['message' => 'Latlong updated successfully', 'data' => $user, "status" => "success"], 200);
    }

    // get all restaurants
    public function getAllRestaurants(Request $request)
    {
        $restaurants = User::where('roles', 'restaurant')->get();

        return response()->json(['message' => 'Restaurants retrieved successfully', 'data' => $restaurants, "status" => "success"], 200);
    }
}
