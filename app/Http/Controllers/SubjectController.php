<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment\Subject;

class SubjectController extends Controller
{
    //

    //fetch all subjects

    public function fetchAll()
    {
        $data = Subject::toBase()->get();
        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'subjects fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch subjects'
            ];
            return response()->json($response, 404);
        }
    }

    /// all subjects by cat_id

    public function subjectsByCat($id)
    {
        $data = Subject::where('category_id', $id)->get();
        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'subjects fetched by category successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch subjects by category'
            ];
            return response()->json($response, 404);
        }
    }

    ///Fetch Subject with class Id/////
    public function subjectClass($id)
    {
        $subject = Subject::select('id', 'subject_name')->where('admin_class_id', $id)->get();
        $authAdmin = auth()->user()->fullname;
        if ($subject->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'subject fetched successfully',
                'data' => $subject, "author" => $authAdmin
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No subjects available'
            ];
            return response()->json($response, 404);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'subjectname' => 'required'
        ]);

        $createData = Subject::create([
            'category_id' => $request->category_id,
            'subjectname' => $request->subjectname
        ]);

        if ($createData) {
            $response = [
                'status' => 'success',
                'message' => 'subject created successfully',
                'data' => $createData
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create subject'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {
        //

        $subject = Subject::find($id);

        // $subjectImg = time() . '-' . $request->subject_name . '.' . $request->subject_img->extension();
        // $request->file('subject_img')->storeAs('public/files', $subjectImg);
        // $subjectLink = URL('storage/files' . $subjectImg);

        // Validate the request data
        $data =  $subject->update([
            'category_id' => $request->category_id,
            'subjectname' => $request->subjectname,
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'subject updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update subject'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $book = Subject::findOrFail($id);

        if ($book) {

            $response = [
                'status' => 'success',
                'message' => 'subject view fetched successfully',
                'data' => $book
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for subject found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {

        $subject = Subject::findOrFail($id);
        $del =  $subject->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'subject deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete subject'
            ];
            return response()->json($response, 404);
        }
    }
}
