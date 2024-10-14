<?php

namespace App\Http\Controllers\AuthAdmin;

use App\Models\Admin;
use App\Models\AdminLogin;
use App\Models\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function register(Request $request)
    {
         $data = $request->validate([
            'fullname' => 'required',
            'school_id' => 'nullable',
            'state' => 'required',
            'district_id' => 'required',
            'role' => 'nullable',
            'username' => 'nullable',
            'email' => 'required|email|unique:admins,email',
            'password'  =>  ['required', 'string'],
            'password_confirmation'  =>  'required|string'
        ]);

        if ($request->password !== $request->password_confirmation) {
            return response()->json(['status' => 'error', 'message' => 'Password confirmation failed!'], 400);
        }

        $user = Admin::where('email', $request->email)->first();

        if ($user) {
            return response()->json(['status' => 'error', 'message' => 'User already exist!'], 400);
        }

        $username = Admin::where('username', $request->username)->first();

        if ($username) {
            return response()->json(['status' => 'error', 'message' => 'Username already taken!'], 400);
        }

        $data['username'] = $data['username'] ?? 'null';
        $data['password'] = Hash::make($request->password); 

        if (!$user = Admin::create($data)) {
            return response()->json(['status' => 'error', 'message' => 'Unable to register!'], 400);
        } else {
            // Admin::create(['role' => 1]);
            auth()->attempt($request->only('email', 'password'));
            return $this->onSuccessfulLogin($user, false);
        }
    }

    public function login(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string'
        ]);

        // Retrieve the admin using the provided email
        $admin = Admin::where('email', $request->email)->first();

        // Check if the admin exists
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'User does not exist!'], 400);
        }

        // Verify the provided password with the stored hash
        if (!Hash::check($request->password, $admin->password)) {
            return response()->json(['status' => 'error', 'message' => 'Bad credentials'], 400);
        }

        // Log the successful login attempt
        $login = new AdminLogin;
        $login->admin_id = $admin->id;
        $login->email = $request->email;
        $login->save();

        // Authenticate the user
        auth()->attempt($request->only('email', 'password'));

        // Retrieve the school name associated with the admin
        $admin->school_name = School::where('id', $admin->school_id)->value('school_name');

        // Handle successful login response
        return $this->onSuccessfulLogin($admin);
    }

    private function onSuccessfulLogin($admin, $isLogin = true)
    {
        $token = $admin->createToken('Bearer')->plainTextToken;

        $response = [
            'status'    =>  'success',
            'message'   =>  $isLogin ? 'Login successful!' : "Registration successful, Welcome Admin!",
            'data'      =>  [
                'user'              =>  $admin,
                'token'             =>  $token,
                'uid'               =>  $admin->id
            ]
        ];



        return response()->json($response);
    }

    public function getUser(Request $request)
    {
        $response = [
            'status'    =>  'success',
            'message'   =>  'Fetch successful!',
            'data'      =>  [
                'user'              =>  $request->user(),
                'uid'               =>  auth()->id()
            ]
        ];

        return response()->json($response);
    }

    public function logOut(Request $request)
    {
        $user = $request->user();
        if ($user) {
            // $user->tokens()->delete();
            $user->currentAccessToken()->delete();

            return response()->json([
                'status'    =>  'success',
                'message'   =>  'Logged Out'
            ]);
        }
        return response()->json([
            'status'    =>  'error',
            'message'   =>  'Admin not logged in'
        ], 400);
    }
}
