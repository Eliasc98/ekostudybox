<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Assessment\Question;
use App\Models\Passage;
use App\Models\Assessment\Category;
use App\Models\Assessment\Year;
use App\Models\AdminSubject;
use App\Models\Assessment\Subject;

class QuestionController extends Controller
{
    //fetch all questions

    public function fetchAll()
    {
        $data = Question::toBase()->get();
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
                'message' => 'unable to fetch questions'
            ];
            return response()->json($response, 404);
        }
    }

    ///All QuestionsSet by subject_id

    public function questionsBySub($id)
    {
        $data = Question::where('subject_id', $id)->get();
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
                'message' => 'unable to fetch questions'
            ];
            return response()->json($response, 404);
        }
    }

    //// all questions by year_id

    public function questionsByYear($id)
    {
        $data = Question::where('year_id', $id)->get();
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
                'message' => 'unable to fetch questions'
            ];
            return response()->json($response, 404);
        }
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'subject_id' => 'required',
            'year_id' => 'required',
            'questionText' => 'required',
        ]);

        $createData = Question::create([
            'category_id' => $request->category_id,
            'subject_id' => $request->subject_id,
            'year_id' => $request->year_id,
            'questionText' => $request->questionText,
            'image' => null, // Initialize 'image' to null if no image is provided
            'optionA' => $request->optionA,
            'optionB' => $request->optionB,
            'optionC' => $request->optionC,
            'optionD' => $request->optionD,
            'optionE' => $request->optionE,
            'correct_option' => $request->correct_option
        ]);

        if ($request->hasFile('image')) {
            $imageName = time() . '-' . $request->image->getClientOriginalName();
            $request->image->storeAs('public/files', $imageName);
            $createData->update(['image' => $imageName]); // Update 'image' with the stored filename
        }

        if ($createData) {
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

    // public function storeBulk(Request $request)
    // {
    //     $data = $request->json()->all();

    //     $category_id = is_array($data['category_id']) ? $data['category_id'] : json_decode($data['category_id']);
    //     $subject_id = is_array($data['subject_id']) ? $data['subject_id'] : json_decode($data['subject_id']);
    //     $year_id = is_array($data['year_id']) ? $data['year_id'] : json_decode($data['year_id']);

    //     $questions = is_array($data['questions']) ? $data['questions'] : json_decode($data['questions'], true);
    //     array_shift($questions);

    //     $createdData = [];

    //     foreach ($questions as $item) {

    //         $filteredItem = array_diff($item, ['QuesNo']);

    //         $question = Question::create([
    //             'category_id' => $category_id,
    //             'subject_id' => $subject_id,
    //             'year_id' => $year_id,
    //             'questionText' => $filteredItem[1],
    //             'image' => $filteredItem[7],
    //             'optionA' => $filteredItem[2],
    //             'optionB' => $filteredItem[3],
    //             'optionC' => $filteredItem[4],
    //             'optionD' => $filteredItem[5],
    //             'optionE' => null,
    //             'correct_option' => $filteredItem[6]
    //         ]);

    //         $createdData[] = $question;
    //     }

    //     $response = [
    //         'status' => 'success',
    //         'message' => 'Questions created successfully',
    //         'data' => $createdData
    //     ];

    //     return response()->json($response);
    // }
    
    public function storeBulkSolution(Request $request)
    {
        $data = $request->json()->all();

        $category_id = is_array($data['category_id']) ? $data['category_id'] : json_decode($data['category_id']);
        $subject_id = is_array($data['subject_id']) ? $data['subject_id'] : json_decode($data['subject_id']);
        $year_id = is_array($data['year_id']) ? $data['year_id'] : json_decode($data['year_id']);

        $questions = is_array($data['questions']) ? $data['questions'] : json_decode($data['questions'], true);
        array_shift($questions);

        $createdData = [];

        foreach ($questions as $item) {

            $filteredItem = array_diff($item, ['QuesNo']);

            $question = Question::updateOrCreate([
                'category_id' => $category_id,
                'subject_id' => $subject_id,
                'year_id' => $year_id,
                'questionText' => $filteredItem[1],
                'image' => $filteredItem[7],
                'optionA' => $filteredItem[2],
                'optionB' => $filteredItem[3],
                'optionC' => $filteredItem[4],
                'optionD' => $filteredItem[5],
                'optionE' => null,
                'correct_option' => $filteredItem[6],
                'explanation' => $filteredItem[8]
            ]);

            $createdData[] = $question;
        }

        $response = [
            'status' => 'success',
            'message' => 'Questions created successfully',
            'data' => $createdData
        ];

        return response()->json($response);
    }

    public function storeQuestionPassage(Request $request)
    {
        
        $request->validate(['passage'=> 'required', 'year_id' => 'required']);
        
        $questionPassage = Passage::updateOrCreate([
            'passage' => $request->passage,
            'year_id' => $request->year_id
        ]);

        if ($questionPassage) {
            $response = [
                'status' => 'success',
                'message' => 'passage created successfully',
                'data' => $questionPassage
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create question passage'
            ];
            return response()->json($response, 404);
        }
    }
    
///////// CODE FOR CONTENT UPLOAD PORTAL IN TEXT WORD FORMAT/////

        public function wordCreate($category_id, $subject_id, $year_id)
        {
            return view('word', compact('category_id', 'subject_id', 'year_id'));
        }

        public function wordStore(Request $request)
        {
            // Validate the uploaded file
            $request->validate([
                'file' => 'required|max:2048', // assuming max file size is 2MB
            ]);

            // Retrieve category_id, subject_id, and year_id from the request
            $category_id = $request->category_id;
            $subject_id = $request->subject_id;
            $year_id = $request->year_id;

            // Read the uploaded file content
            $content = file_get_contents($request->file('file')->getRealPath());

            // Parse the content and insert into database
            $lines = explode("\n", $content);

            foreach ($lines as $line) {

                $data = explode(',', trim($line));
                
                // Check if line ends with a specific delimiter (e.g., "||") indicating a multi-line question
                if (substr($line, -2) === "||") {
                    $questionText = $line; 

                    
                    $i = array_search($line, $lines) + 1; 
                    while (isset($lines[$i]) && substr($lines[$i], -2) !== "||") {
                        $questionText .= $lines[$i];
                        $i++;
                    }
                    
                    $questionText = substr($questionText, 0, -2);
                } 
                // dd($data);
                // Assuming the first element of the content corresponds to category_id
                $question = Question::updateOrCreate(
                    ['questionText' => $data[0]],  // Search criteria
                    [
                        'category_id' => $category_id,
                        'subject_id' => $subject_id,
                        'year_id' => $year_id,
                        'image' => $data[1],
                        'optionA' => $data[2],
                        'optionB' => $data[3],
                        'optionC' => $data[4],
                        'optionD' => $data[5],
                        'correct_option' => $data[6],
                        'explanation' => $data[7],
                    ]
                );
            }

            return redirect()->route('questions.create')->with('success', 'Questions have been uploaded successfully.');
        }        
///////////// CODE TO ADD SEPERATE PAGE FOR TEXT FILE UPLOAD

public function wordCreatePage()
{
    $categories = Category::get();
    return view('wordpage', compact('categories'));
}

public function getSubs($category_id){
    $subject = Subject::where('category_id', $category_id)->get();

    return $subject;
}

public function getYears($subject_id){
    $year = Year::where('subject_id', $subject_id)->get();

    return $year;
}

public function wordStorePage(Request $request)
{
    // Validate the uploaded file
    $request->validate([
        'file' => 'required|max:2048', // assuming max file size is 2MB
    ]);

    // Retrieve category_id, subject_id, and year_id from the request
    $category_id = $request->category_id;
    $subject_id = $request->subject_id;
    $year_id = $request->year_id;

    // Read the uploaded file content
    $content = file_get_contents($request->file('file')->getRealPath());

    // Parse the content and insert into database
    $lines = explode("\n", $content);

    foreach ($lines as $line) {
        // Skip empty lines
        if (empty(trim($line))) {
            continue;
        }

        // Split the line into data elements
        $data = explode(',', trim($line));

        // Check if the data array has enough elements
        if (count($data) < 8) {
            // Log an error or handle it appropriately
            continue; // Skip this line and move to the next
        }

        // Assuming the first element of the content corresponds to question text
        $questionText = $data[0];

        // Assuming the last element of the content corresponds to explanation
        $explanation = end($data);

        // Assuming the correct_option is the second to last element
        $correct_option = prev($data);

        // Assuming the image is the third element
        $image = $data[1];

        // Assuming options A, B, C, D are in order
        $options = array_slice($data, 2, 4);

        // Insert into database
        $question = Question::updateOrCreate(
            ['questionText' => $questionText],  // Search criteria
            [
                'category_id' => $category_id,
                'subject_id' => $subject_id,
                'year_id' => $year_id,
                'image' => $image,
                'optionA' => $options[0],
                'optionB' => $options[1],
                'optionC' => $options[2],
                'optionD' => $options[3],
                'correct_option' => $correct_option,
                'explanation' => $explanation,
            ]
        );
    }

    return redirect()->route('questions.create')->with('success', 'Questions have been uploaded successfully.');
}

public function wordStorePageReborn(Request $request)
{
    // Validate the uploaded file
    $request->validate([
        'file' => 'required|max:2048|mimes:doc,docx,txt'
    ]);

    // Retrieve category_id, subject_id, and year_id from the request
    $category_id = $request->category_id;
    $subject_id = $request->subject_id;
    $year_id = $request->year_id;

    // Read the uploaded file content
    $content = "";

    $file = $request->file('file');

    if ($file->getClientOriginalExtension() == 'docx') {
        $content = $this->extractTextFromDocx($file); 
        
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            // Skip empty lines
            if (empty(trim($line))) {
                continue;
            }

            // Split the line into data elements
            $data = explode(',', trim($line));

            // Check if the data array has enough elements
            if (count($data) < 8) {
                // Log an error or handle it appropriately
                continue; // Skip this line and move to the next
            }

            // Assuming the first element of the content corresponds to question text
            $questionText = $data[0];

            // Assuming the last element of the content corresponds to explanation
            $explanation = end($data);

            // Assuming the correct_option is the second to last element
            $correct_option = prev($data);

            // Assuming the image is the third element
            $image = $data[1];

            // Assuming options A, B, C, D are in order
            $options = array_slice($data, 2, 4);

            // Insert into database
            $question = Question::updateOrCreate(
                ['questionText' => $questionText],  // Search criteria
                [
                    'category_id' => $category_id,
                    'subject_id' => $subject_id,
                    'year_id' => $year_id,
                    'image' => $image,
                    'optionA' => $options[0],
                    'optionB' => $options[1],
                    'optionC' => $options[2],
                    'optionD' => $options[3],
                    'correct_option' => $correct_option,
                    'explanation' => $explanation,
                ]
            );
        }

        return redirect()->route('questions.create')->with('success', 'Questions have been uploaded successfully.');
    
    } else if ($file->getClientOriginalExtension() == 'doc') {
        // Handle .doc extraction (more complex, consider external libraries)
        // You can return an error message or handle it appropriately
        return redirect()->route('questions.create')->with('error', 'DOC format is not currently supported.'); 
    }
    $content = file_get_contents($request->file('file')->getRealPath());

    // Parse the content and insert into database
    $lines = explode("\n", $content);

    foreach ($lines as $line) {
        // Skip empty lines
        if (empty(trim($line))) {
            continue;
        }

        // Split the line into data elements
        $data = explode(',', trim($line));

        // Check if the data array has enough elements
        if (count($data) < 8) {
            // Log an error or handle it appropriately
            continue; // Skip this line and move to the next
        }

        // Assuming the first element of the content corresponds to question text
        $questionText = $data[0];

        // Assuming the last element of the content corresponds to explanation
        $explanation = end($data);

        // Assuming the correct_option is the second to last element
        $correct_option = prev($data);

        // Assuming the image is the third element
        $image = $data[1];

        // Assuming options A, B, C, D are in order
        $options = array_slice($data, 2, 4);

        // Insert into database
        $question = Question::updateOrCreate(
            ['questionText' => $questionText],  // Search criteria
            [
                'category_id' => $category_id,
                'subject_id' => $subject_id,
                'year_id' => $year_id,
                'image' => $image,
                'optionA' => $options[0],
                'optionB' => $options[1],
                'optionC' => $options[2],
                'optionD' => $options[3],
                'correct_option' => $correct_option,
                'explanation' => $explanation,
            ]
        );
    }

    return redirect()->route('questions.create')->with('success', 'Questions have been uploaded successfully.');
}

private function extractTextFromDocx($file)
{
    $phpWord = new PhpWord();
    $phpWord->load($file);

    $text = '';

    // Loop through sections and elements to extract text (as before)

    return $text;
}


//////// UPDATE PASSAGE /////////

    public function updatePassageId($id, $passage_id){
        $question = Question::find($id);
        $question->passage_id = $passage_id;
        $question->save();

        if ($question) {
            $response = [
                'status' => 'success',
                'message' => 'passage assigned to question successfully',
                'data' => $question->passage_id
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to assign passage'
            ];
            return response()->json($response, 404);
        }
    }
    
    public function unassignPassageId($id, $passage_id = null){
        $question = Question::find($id);
        $question->passage_id = $passage_id;
        $question->save();

        if ($question) {
            $response = [
                'status' => 'success',
                'message' => 'passage unassigned to question successfully',
                'data' => $question->passage_id
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to unassign passage'
            ];
            return response()->json($response, 404);
        }
    }

    public function getPassageByYear($year_id)
    {
        $passage = Passage::where('year_id', $year_id)->get();

        if ($passage) {
            $response = [
                'status' => 'success',
                'message' => 'passages for year fetched successfully',
                'data' => $passage
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch passages'
            ];
            return response()->json($response, 404);
        }
    }



/////// UPDATE QUESTIONS ///////

    public function update($id, Request $request)
    {
        //

        $question = Question::find($id);

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
            'optionE' => $request->optionE,
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
    
    
    ////question count by subject id ///////
    public function count($id)
    {
        $questions = Question::where('subject_id', $id)->count();

        if ($questions->isNotEmpty()) {
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


    public function questionTopic($id)
    {
        $questions = Question::where('admin_topic_id', $id)->orderBy('id', 'desc')->get();

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
        $book = Question::with('passage')->findOrFail($id);

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
        $question = Question::findOrFail($id);
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
