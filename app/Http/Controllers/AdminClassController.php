<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AdminClass;
use Illuminate\Http\Request;

class AdminClassController extends Controller
{
    public function index()
    {
        $classes = AdminClass::select('class_name','total_num_of_students','class_level', 'status','author','id')->orderBy('class_name', 'asc')->get();

        if ($classes->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'class fetched successfully',
                'data' => $classes
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no class to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'className' => 'required',
            'classLevel' => 'required',
            'status' => 'required'
        ]);

        $userCount = User::join('admin_classes', 'users.admin_class_id', '=', 'admin_classes.id')->count();

        $authAdmin = auth()->user()->fullname;
        $data = AdminClass::create([
            'class_name' => $request->className,
            'class_level' => $request->classLevel,
            'total_num_of_students' => $userCount,
            'author' => $authAdmin,
            'status' => $request->status
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'class created successfully',
                'data' =>  $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create class'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {

        $book = AdminClass::find($id);

        // Validate the request data
        $this->validate($request, [
            'className' => 'required',
            'classLevel' => 'required',
            'status' => 'required'
        ]);

        $data =  $book->update([
            'class_name' => $request->className,
            'class_level' => $request->classLevel,
            'status' => $request->status
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'class updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update class'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $class = AdminClass::findOrFail($id);

        if ($class) {
            
            $response = [
                'status' => 'success',
                'message' => 'class view fetched successfully',
                'data' => $class
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for class found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        //
        $class = AdminClass::findOrFail($id);
        $del =  $class->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'class deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete class'
            ];
            return response()->json($response, 404);
        }
    }
}
