<?php

namespace App\Http\Controllers;

use App\Models\AdminTest;
use App\Models\AdminClass;
use App\Models\AdminTopic;
use App\Models\AdminContent;
use Illuminate\Http\Request;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminContentController extends Controller
{
    public function index()
    {
        //Get book content with topics

        $bookContent = AdminContent::with('admin_topic')->get();

        if ($bookContent->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Contents fetched successfully',
                'data' => $bookContent
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No content found'
            ];
            return response()->json($response, 404);
        }
    }

    ///Fetch all Contents
    public function fetchAll()
    {
        $data = AdminContent::toBase()->get();

        if ($data->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'contents fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no contents to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    ///////////////// FOR MARKDOWN /////////////////

    public function store(Request $request)
    {
        // Validate the request data
        $this->validate($request, [
            'admin_topic_id' => 'required',
            'content' => 'required'
        ]);

       

        $content = AdminContent::updateOrCreate(
            ['admin_topic_id' => $request->admin_topic_id],
            ['content' => $request->content]
        );

        //return a response

        if ($content) {

          $data =  AdminTopic::where('id', $content->admin_topic_id)->update([
                'content_status' => 'active',
            ]);

            $response = [
                'status' => 'success',
                'message' => 'content created successfully',
                'data' => $content
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'Unable to create content'
            ];
            return response()->json($response, 404);
        }
    }

    public function storeEpub(Request $request)
    {
        // Validate the request data
        $request->validate([
            'admin_subject_id' => 'required',
            'topic' => 'required',
            'week' => 'required'
        ]);

        $week = AdminTopic::where('week', $request->week)->where('admin_subject_id', $request->admin_subject_id)->first();

        if ($week) {
            return response()->json(['status' => 'error', 'message' => 'week already exist for subject!'], 400);
        }

        $authAdmin = auth()->user()->fullname;

        $createData = AdminTopic::create([
            'admin_subject_id' => $request->admin_subject_id,
            'topics' => $request->topic,
            'week' => $request->week,
            'author' => $authAdmin,
            'num_of_test_questions' => 0
        ]);

        $content = AdminContent::updateOrCreate(
            ['admin_topic_id' => $createData->id],
            ['content' => $request->content]
        );

        //return a response

        if ($content) {

            $response = [
                'status' => 'success',
                'message' => 'epub Uploaded successfully',
                'data' => $content
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'Unable to upload epub'
            ];
            return response()->json($response, 404);
        }
    }
    
    protected function parseContent($rawContent)
    {
        return Markdown::convertToHtml($rawContent);
    }

    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'content' => 'required',
        ]);

        $book = AdminContent::findOrFail($id);


        $parsedContent = $this->parseContent($request->content);

        $update =   $book->update(
            ['content' => $request->content]
        );

        if ($update) {
            $response = [
                'status' => 'success',
                'message' => 'content updated successfully',
                'data' => $update
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'content failed to update'
            ];
            return response()->json($response, 404);
        }
    }

    ///////////// FOR JSON PARSE CONTENT /////////////////////

    // public function parseStore(Request $request)
    // {
    //     // Validate the request data (you can use validation rules)

    //     $parsedContent = $this->parseContent($request->input('content'));

    //     AdminBook::create([
    //         'title' => $request->input('title'),
    //         'parsed_content' => $parsedContent,
    //     ]);

    //     return response()->json(['message' => 'Content stored successfully'], 201);
    // }

    // protected function parseContentJson($contentData)
    // {
    //     // Custom parsing logic for the JSON content format

    //     $parsedContent = '';

    //     foreach ($contentData as $item) {
    //         switch ($item['type']) {
    //             case 'heading':
    //                 $parsedContent .= '<h' . $item['level'] . '>' . $item['text'] . '</h' . $item['level'] . '>';
    //                 break;

    //             case 'paragraph':
    //                 $parsedContent .= '<p>' . $item['text'] . '</p>';
    //                 break;

    //             case 'bulletList':
    //                 $parsedContent .= '<ul>';
    //                 foreach ($item['items'] as $bullet) {
    //                     $parsedContent .= '<li>' . $bullet . '</li>';
    //                 }
    //                 $parsedContent .= '</ul>';
    //                 break;

    //             case 'image':
    //                 $parsedContent .= '<img src="' . $item['url'] . '" alt="' . $item['alt'] . '">';
    //                 break;

    //             case 'video':
    //                 $parsedContent .= '<iframe width="560" height="315" src="' . $item['url'] . '" frameborder="0" allowfullscreen></iframe>';
    //                 break;
    //         }
    //     }

    //     return $parsedContent;
    // }

    public function contentTopic($id)
    {
        $books = AdminContent::where('admin_topic_id', $id)->latest()->first();

        // $parsedContent = $this->parseContent($book->content);

        if ($books) {
            $response = [
                'status' => 'success',
                'message' => 'Topic content view fetched successfully',
                'data' => $books
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No content for the specified topic found'
            ];

            return response()->json($response, 404);
        }
    }


    public function show($id)
    {
        try {
            $book = AdminContent::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $response = [
                'status' => 'failed',
                'message' => 'Book content not found'
            ];
            return response()->json($response, 404);
        }

        $parsedContent = $this->parseContent($book->content);

        if ($book) {
            $response = [
                'status' => 'success',
                'message' => 'Book content view fetched successfully',
                'data' => $book
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for book found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        $book = AdminContent::findOrFail($id);
        $del =  $book->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'content deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete content'
            ];
            return response()->json($response, 404);
        }
    }
}
