<?php

namespace App\Http\Controllers;

use App\Models\AdminWeek;
use Illuminate\Http\Request;

class AdminWeekController extends Controller
{
    //
    public function index()
    {
        //
    }

    ////fetch weeks by subject id //////

    public function subjectWeek($id)
    {
        $data = AdminWeek::select('admin_weeks.id', 'admin_topics.topics', 'admin_weeks.week_name')->join('admin_topics', 'admin_topics.admin_week_id', '=', 'admin_weeks.id')->where('admin_subject_id', $id)->get();
        $authAdmin = auth()->user()->fullname;
        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'weeks fetched successfully',
                'data' => $data, "author" => $authAdmin
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No weeks available'
            ];
            return response()->json($response, 404);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'admin_subject_id' => 'required',
            'week_name' => 'required',
        ]);

        $createData = AdminWeek::create([
            'admin_subject_id' => $request->admin_subject_id,
            'week_name' => $request->week_name
        ]);

        if ($createData) {
            $response = [
                'status' => 'success',
                'message' => 'week created successfully',
                'data' => $createData
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create week'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {
        //

        $subject = AdminWeek::find($id);

        $data = $request->validate([
            'admin_subject_id' => 'required',
            'week_name' => 'required'
        ]);

        // Validate the request data

        $data =  $subject->update([
            'admin_subject_id' => $request->admin_subject_id,
            'week_name' => $request->week_name
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'week updated successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update week'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $book = AdminWeek::with('admin_subject')->findOrFail($id);

        if ($book) {

            $response = [
                'status' => 'success',
                'message' => 'week view fetched successfully',
                'data' => $book
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for week found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        $week = AdminWeek::findOrFail($id);
        $del =  $week->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'week deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete week'
            ];
            return response()->json($response, 404);
        }
    }
}
