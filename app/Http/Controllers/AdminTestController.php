<?php

namespace App\Http\Controllers;

use App\Models\AdminTest;
use Illuminate\Http\Request;

class AdminTestController extends Controller
{
    public function index()
    {
        //
    }

    ///Fetch all tests
    public function fetchAll()
    {
        $data = AdminTest::toBase()->get();

        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'tests fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no tests to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'test_type' => 'required',
            'duration' => 'required',
            'num_questions' => 'required'
        ]);

        $createData = AdminTest::create([
            'test_type' => $request->test_type,
            'duration' => $request->duration,
            'num_questions' => $request->num_questions
        ]);

        if ($createData) {
            $response = [
                'status' => 'success',
                'message' => 'Test created successfully',
                'data' => $createData
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create test'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {
        //        
        $test = AdminTest::find($id);

        $data = $request->validate([
            'test_type' => 'required',
            'duration' => 'required',
            'num_questions' => 'required'
        ]);

        // Validate the request data

        $data =  $test->update([
            'test_type' => $request->test_type,
            'duration' => $request->duration,
            'num_questions' => $request->num_questions
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'test updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update test'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $test = AdminTest::findOrFail($id);

        if ($test) {

            $response = [
                'status' => 'success',
                'message' => 'test view fetched successfully',
                'data' => $test
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for test found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        $test = AdminTest::findOrFail($id);
        $del =  $test->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'test deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete test'
            ];
            return response()->json($response, 404);
        }
    }
}
