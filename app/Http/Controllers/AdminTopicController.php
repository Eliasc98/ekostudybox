<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\AdminTopic;
use App\Models\AdminQuestion;
use App\Models\AdminContent;
use Illuminate\Http\Request;

class AdminTopicController extends Controller
{
    public function index()
    {
        //
    }

    ///Fetch all topics
    public function fetchAll()
    {
        $data = AdminTopic::toBase()->get();

        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'topics fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no topic to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    ///Fetch topic by subject Id

    public function subjectTopic($id, $termId)
    {
        //
        $topic = AdminTopic::where('admin_subject_id', $id)->where('term_id', $termId)->orderBy('week', 'asc')->get();

        if ($topic->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'subject fetched successfully',
                'data' => $topic
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

    public function subjectWeek($id, $termId)
    {
        $data = AdminTopic::select('id','week','topics', 'author', 'content_status', 'num_of_test_questions', 'status')->where('admin_subject_id', $id)->where('term_id', $termId)->orderBy('week', 'asc')->get();
        $authAdmin = auth()->user()->fullname;
        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'weeks fetched successfully',
                'data' => $data
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
        $request->validate([
            'admin_subject_id' => 'required',
            'term_id' => 'required',
            'topic' => 'required',
            'week' => 'required'
        ]);

        $week = AdminTopic::where('week', $request->week)->where('admin_subject_id', $request->admin_subject_id)->where('term_id', $request->term_id)->first();
        

        if ($week) {
            return response()->json(['status' => 'error', 'message' => 'week already exist for subject term!'], 400);
        }

        $authAdmin = auth()->user()->fullname;


        // Create AdminTopic first
        $createData = AdminTopic::create([
            'admin_subject_id' => $request->admin_subject_id,
            'term_id' => $request->term_id,
            'topics' => $request->topic,
            'week' => $request->week,
            'author' => $authAdmin,
            'num_of_test_questions' => 0
        ]);

        
        // Check if related records exist in admin_contents
        $relatedContentsExist = AdminContent::where('admin_topic_id', $createData->id)->exists();
        $contentStatus = $relatedContentsExist ? 'active' : 'inactive';

        // Check if related records exist in admin_contents
        $questionExists = AdminQuestion::where('admin_topic_id', $createData->id)->exists();
        $status = $questionExists ? 'quiz available' : 'no quiz';

        $createData->update([            
            'content_status' => $contentStatus,
            'status' => $status
        ]);
               
        
        if ($createData) {
            $response = [
                'status' => 'success',
                'message' => 'topic created successfully',
                'data' => $createData
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create topic'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {
        //

        $subject = AdminTopic::find($id);

        $data = $request->validate([
            'admin_subject_id' => 'required',
            'topic' => 'required',
            'week' => 'required|unique:admin_topics'
        ]);

        // Validate the request data

        $data =  $subject->update([
            'admin_subject_id' => $request->admin_subject_id,
            'topics' => $request->topic,
            'week' => $request->week
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'subject updated successfully',
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
        $book = AdminTopic::with('admin_subject')->findOrFail($id);

        if ($book) {

            $response = [
                'status' => 'success',
                'message' => 'topic view fetched successfully',
                'data' => $book
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for topic found'
            ];
            return response()->json($response, 404);
        }
    }

    public function delete($id)
    {
        $topic = AdminTopic::findOrFail($id);
        $del =  $topic->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'topic deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete topic'
            ];
            return response()->json($response, 404);
        }
    }
}
