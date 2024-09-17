<?php

namespace App\Http\Controllers;

use App\Models\AdminTopic;
use Illuminate\Http\Request;
use App\Models\AdminQuestion;
use Illuminate\Support\Facades\DB;

class AdminQuestionController extends Controller
{
    //
    public function index()
    {
        //
    }

    ///Fetch all Questions
    public function fetchAll()
    {
        $data = AdminQuestion::toBase()->get();

        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'questions fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no questions to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'admin_topic_id' => 'required',
            'questionText' => 'required',
            'image' => 'nullable|mimes:jpeg,jpg,png', // Change 'required' to 'nullable'
        ]);

        $createData = AdminQuestion::create([
            'admin_topic_id' => $request->admin_topic_id,
            'questionText' => $request->questionText,
            'image' => null, // Initialize 'image' to null if no image is provided
            'optionA' => $request->optionA,
            'optionB' => $request->optionB,
            'optionC' => $request->optionC,
            'optionD' => $request->optionD,
            'correct_option' => $request->correct_option,
            'explanation' => $request->explanation
        ]);

        if ($request->hasFile('image')) {
            $imageName = time() . '-' . $request->image->getClientOriginalName();
            $request->image->storeAs('public/files', $imageName);
            $createData->update(['image' => $imageName]); 
        }

        if ($createData) {
            $num_of_questions = AdminQuestion::where('admin_topic_id', $request->admin_topic_id,)->count();

            AdminTopic::where('id', $request->admin_topic_id,)->update([
                'status' => 'quiz available',
                'num_of_test_questions' => $num_of_questions,
            ]);            

            $response = [
                'status' => 'success',
                'message' => 'question created successfully',
                'data' => $createData
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create question'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {
        //

        $question = AdminQuestion::find($id);

        $data = $request->validate([
            'questionText' => 'required',
            'image' => 'mimes: jpeg, jpg, png',
            'correct_option' => 'required',
        ]);

        // Validate the request data

        $data =  $question->update([
            'questionText' => $request->questionText,
            'optionA' => $request->optionA,
            'optionB' => $request->optionB,
            'optionC' => $request->optionC,
            'optionD' => $request->optionD,
            'correct_option' => $request->correct_option,
        ]);


        if ($request->image) {
            $image = time() . '-' . '.' . $data->id . $request->image->extension();
            $request->file('image')->storeAs('public/files', $image);
            $question->update([
                'image' => $image
            ]);
        }

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'question updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update question'
            ];
            return response()->json($response, 404);
        }
    }
    ////question count ///////
    public function count($id)
    {
        $questions = AdminQuestion::where('admin_topic_id', $id)->count();

        if ($questions) {
            $response = [
                'status' => 'success',
                'message' => 'number of questions fetched successfully',
                'data' => $questions
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No question for the specified topic found'
            ];

            return response()->json($response, 404);
        }
    }

    /// fetch  class_name, Subject_name, and Topic_name based on the  topic ID

    public function fetchCST($id)
    {
        $fetch = DB::table('admin_topics')
        ->select('admin_classes.class_name', 'admin_topics.topics', 'admin_subjects.subject_name')
        ->join('admin_subjects', 'admin_subjects.id', '=', 'admin_topics.admin_subject_id')
        ->join('admin_classes', 'admin_classes.id', '=', 'admin_subjects.admin_class_id')
        ->where('admin_topics.id', $id)->get();

        if ($fetch->isNotEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'data fetched successfully',
                'data' => $fetch
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No data for the specified topic found'
            ];

            return response()->json($response, 404);
        }
    }

    public function questionTopic($id)
    {
        $questions = AdminQuestion::where('admin_topic_id', $id)->orderBy('id', 'desc')->get();

        if ($questions->isNotEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'question content view fetched successfully',
                'data' => $questions
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No question for the specified topic found'
            ];

            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $book = AdminQuestion::with('admin_topic')->findOrFail($id);

        if ($book) {

            $response = [
                'status' => 'success',
                'message' => 'question view fetched successfully',
                'data' => $book
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for question found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        $question = AdminQuestion::findOrFail($id);
        $del =  $question->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'question deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete question'
            ];
            return response()->json($response, 404);
        }
    }
}
