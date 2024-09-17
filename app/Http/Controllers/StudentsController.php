<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentsController extends Controller
{
    //
    //fetch all questions

    public function fetchAll()
    {
        $data = User::where('usertype', 'Student')->get();
        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'students fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch students'
            ];
            return response()->json($response, 404);
        }
    }

    

    public function store(Request $request)
    {
        $data = $request->validate([
            'fullname' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',            
            'usertype'=> 'required',
            'password'  =>  ['required', 'string'],
            'password_confirmation'  =>  'required|string'
        ]);

        if ($request->password !== $request->password_confirmation) {
            return response()->json(['status' => 'error', 'message' => 'Password confirmation failed!'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            return response()->json(['status' => 'error', 'message' => 'User already exist!'], 400);
        }

        $username = User::where('username', $request->username)->first();

        if ($username) {
            return response()->json(['status' => 'error', 'message' => 'Username already taken!'], 400);
        }

        
        $data['password'] = Hash::make($request->password); 
        $createStudent = User::create($data);

        if (!$createStudent) {
            return response()->json(['status' => 'error', 'message' => 'Unable to register student!'], 400);
        } else {
            $response = [
                'status' => 'success',
                'message' => 'Student added successfully',
                'data' => $createStudent
            ];
            return response()->json($response);
        }
    }


    public function update($id, Request $request)
    {
        //

        $user = User::find($id);

        $data = $request->validate([
            'fullname' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password'  =>  ['required', 'string'],
            'password_confirmation'  =>  'required|string'
        ]);

        // Validate the request data
        $data =  $user->update([
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number
        ]);


        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'student details updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update student details'
            ];
            return response()->json($response, 404);
        }
    }
    
   
    public function show($id)
    {
        //
        $book = User::findOrFail($id);

        if ($book) {

            $response = [
                'status' => 'success',
                'message' => 'student view fetched successfully',
                'data' => $book
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for student found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        $question = User::findOrFail($id);
        $del =  $question->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'student deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete student'
            ];
            return response()->json($response, 404);
        }
    }
}
