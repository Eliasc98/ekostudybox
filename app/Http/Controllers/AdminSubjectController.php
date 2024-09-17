<?php

namespace App\Http\Controllers;

use App\Models\AdminClass;
use App\Models\AdminSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AdminSubjectController extends Controller
{
    public function index()
    {
        //
    }

    ///Fetch all subjects
    public function fetchAll()
    {
        $data = AdminSubject::toBase()->get();

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
                'message' => 'no subjects to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    ///Fetch Subject with class Id/////
    public function subjectClass($id)
    {
        $subject = AdminSubject::select('id', 'subject_name', 'subject_img')->where('admin_class_id', $id)->orderBy('subject_name', 'asc')->get();
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
            'admin_class_id' => 'required',
            'subject_name' => 'required',
            'subject_img' => 'mimes:jpeg,png,jpg,gif|max:2048'
        ]);



        $createData = AdminSubject::create([
            'admin_class_id' => $request->admin_class_id,
            'subject_name' => $request->subject_name
        ]);

        if ($request->subject_img) {
            $subjectImg = $request->subject_name . '.' . $request->subject_img->extension();
            $request->subject_img->storeAs('public/files', $subjectImg);
            $subjectLink = URL('storage/files/' . $subjectImg);

            $createData->update(
                ['subject_img' => $subjectLink]
            );
        }


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
        $subject = AdminSubject::find($id);

        $data = $request->validate([
            'subject_img' => 'mimes:png,jpeg,jpg'
        ]);

        $subject->update([
            'admin_class_id' => $subject->admin_class_id,
            'subject_name' => $request->input('subject_name', $subject->subject_name)
            // Add other columns here with their existing values
        ]);

        if ($request->hasFile('subject_img')) {
            $subjectImg = time() . '-' . $request->subject_name . '.' . $request->subject_img->extension();
            
            // Move the uploaded file to the storage path
            $request->file('subject_img')->move(public_path('storage/files'), $subjectImg);
            
            // Update the subject_img column
            $subjectLink = URL('storage/files/' . $subjectImg);
            $subject->update(['subject_img' => $subjectLink]);
        }

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'subject updated successfully',
                'data' => $subject
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
        $book = AdminSubject::with('admin_class')->findOrFail($id);

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

        $subject = AdminSubject::findOrFail($id);
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
