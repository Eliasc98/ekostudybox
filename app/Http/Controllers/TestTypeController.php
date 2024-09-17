<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment\TestType;

class TestTypeController extends Controller
{
    //
    public function index()
    {
        //
    }

    //fetch all test-types

    public function fetchAll(){
        $data = TestType::toBase()->get();
        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'testtypes fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch testtypes'
            ];
            return response()->json($response, 404);
        }
    }

    public function store(Request $request)
    {
        
        $data = $request->validate([
            'test_type_name' => 'required',
            'duration' => 'required',
            'num_questions' => 'required'
        ]);

        $createData = TestType::create([
            'test_type_name' => $request->test_type_name,
            'duration' => $request->duration,
            'num_of_questions'=>$request->num_questions
        ]);

        if ($createData) {
            $response = [
                'status' => 'success',
                'message' => 'Test_type created successfully',
                'data' => $createData
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create test_type'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {
        //        
        $test = TestType::find($id);

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
                'message' => 'test type updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update test type'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $test = TestType::findOrFail($id);

        if ($test) {

            $response = [
                'status' => 'success',
                'message' => 'test type view fetched successfully',
                'data' => $test
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for test-type found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        $test = TestType::findOrFail($id);
        $del =  $test->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'test type deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete test type'
            ];
            return response()->json($response, 404);
        }
    }
}
