<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment\Year;
use Illuminate\Support\Facades\DB;
use App\Models\Assessment\Question;

class YearController extends Controller
{
    //
    public function index()
    {
        //
    }

    //fetch all years

    public function fetchAll()
    {
        $data = Year::toBase()->get();
        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'years fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch years'
            ];
            return response()->json($response, 404);
        }
    }

    /// get questionset by subject id

    public function yearBySubject($id)
    {
        
        $data = DB::table('years')
            ->select('years.id', 'years.yearname', 'categories.cat_name', 'subjects.subjectname')
            ->join('subjects', 'subjects.id', '=', 'years.subject_id')
            ->join('categories', 'subjects.category_id', '=', 'categories.id')
            ->leftJoin('questions', 'questions.subject_id', '=', 'subjects.id') 
            ->selectRaw('COUNT(questions.id) as noOfQuestions') 
            ->groupBy('years.id', 'years.yearname', 'categories.cat_name', 'subjects.subjectname') 
            ->where('years.subject_id', $id)
            ->get();

        // Check if any data was retrieved
        if ($data->count() > 0) {
            // If data is found, create a success response
            $response = [
                'status' => 'success',
                'message' => 'Years fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            // If no data is found, create a failure response
            $response = [
                'status' => 'failed',
                'message' => 'Unable to fetch subject years'
            ];
            return response()->json($response, 404);
        }
    }

    /// get questionset with year id

    public function yearById($id)
    {
        
        $data = DB::table('years')
            ->select('years.id', 'categories.id as category_id', 'subjects.id as subject_id', 'years.yearname', 'categories.cat_name', 'subjects.subjectname')
            ->join('subjects', 'subjects.id', '=', 'years.subject_id')
            ->join('categories', 'subjects.category_id', '=', 'categories.id')
            ->where('years.id', $id)
            ->get();

        // Check if any data was retrieved
        if ($data->count() > 0) {
            // If data is found, create a success response
            $response = [
                'status' => 'success',
                'message' => 'Years fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            // If no data is found, create a failure response
            $response = [
                'status' => 'failed',
                'message' => 'Unable to fetch question by id'
            ];
            return response()->json($response, 404);
        }
    }


    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'subject_id' => 'required',
            'yearname' => 'required'
        ]);

        $year = Year::where('subject_id', $request->subject_id)->where('yearname', $request->yearname)->first();

        if($year){
            $response = [
                'status' => 'Error',
                'message' => 'Year for subject already exists'                
            ];
            
            return response()->json($response);
        }

        $createData = Year::create([
            'subject_id' => $request->subject_id,
            'yearname' => $request->yearname
        ]);

        if ($createData) {
            $response = [
                'status' => 'success',
                'message' => 'year created successfully',
                'data' => $createData
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create year'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {
        //        
        $test = Year::find($id);

        $data = $request->validate([
            'subject_id' => 'required',
            'yearname' => 'required'
        ]);

        // Validate the request data

        $data =  $test->update([
            'subject_id' => $request->subject_id,
            'yearname' => $request->duration
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'year updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update year'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $year = Year::findOrFail($id);

        if ($year) {

            $response = [
                'status' => 'success',
                'message' => 'year view fetched successfully',
                'data' => $year
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for year found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        $year = Year::findOrFail($id);
        $del =  $year->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'year deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete year'
            ];
            return response()->json($response, 404);
        }
    }
}
