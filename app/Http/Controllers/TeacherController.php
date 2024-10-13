<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use App\Models\School;
use App\Models\Login;
use App\Models\Assessment\Question;
use App\Models\Assessment\Subject;
use App\Models\Assessment\TestType;
use App\Models\Assessment\Year;
use App\Models\AssessmentTakeTest;
use App\Models\AssessmentTestTaken;
use App\Models\TestScheduler;
use App\Models\UserAssessmentMarking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; 
use App\Models\Passage;

class TeacherController extends Controller
{
    public function changePassword(Request $request)
    {
        $user = Admin::where('email', 'bimbo-school@napps.com')->first();

        $user->password = "user101";
        $user->save();

        $response = [
            'status' => 'success',
            'message' => 'user fetched successfully',
            'data' => $user
        ];

        return response()->json($response);
    }

    public function fetchQuickTestPasscode($test_id, Request $request){

        $request->validate([
            'firstName'=> 'required', 
            'lastName' => 'required',
            'phone_number' => 'required',
            'school_id' => 'required'
        ]);

        $user = User::create([
            'firstname' => $request->firstName,
            'lastname' => $request->lastName,
            'middlename' => 'default',
            'gender' => 'not say',
            'state' => 'not say',
            'phone_number' => $request->phone_number,
            'password'  =>  bcrypt('password'),
            'admin_class_id' => 6,
            'school_id'=> $request->school_id,
            'assoc_cat_id' => 1
        ]);

        $user->refresh();
        $user->student_code = ($admin->chapter_code ?? 'IKJ') . '/0' . ($admin->school_id ?? '14') . '/0' . ($user->id ?? '1');
        $user->save();

        if($user){
            
            
            // Attempt login after successful registration
            
            auth()->attempt($request->only('student_code', 'password'));

            // $user_id = auth()->user()->id;
       
            $yearId = Year::where('subject_id', 49)->value('id');
            $testtype = TestType::find($test_id);
            $testTaken = AssessmentTestTaken::create([
                'user_id' => $user->id ?? 1,
                'test_type_id' => $test_id,
                'year_id' => $yearId,
                'subject_id' => 49,
                'num_question' => $testtype->num_of_questions,
            ]);

            return $this->onSuccessfulLoginTest($user, $testTaken, false);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unable to create account!'], 400);
        }     

        
    }

    //////// GET PASSAGE

    public function storeQuestionPassage(Request $request)
    {
        
        $request->validate(['passage'=> 'required', 'test_id' => 'required']);
        
        $questionPassage = Passage::updateOrCreate([
            'passage' => $request->passage,
            'test_id' => $request->test_id
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
    

    public function getPassageByTest($test_id)
    {
        $passage = Passage::where('test_id', $test_id)->get();

        if ($passage) {
            $response = [
                'status' => 'success',
                'message' => 'passages for test fetched successfully',
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

    public function questionsByTestId($test_id)
    {
        $data = Question::where('test_type_id', $test_id)->get();
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


    /////////////////////////////////////

    public function checkIfTestTaken($test_taken_id){
        $user_id = auth()->user()->id;
        $test = UserAssessmentMarking::where('user_id', $user_id)->where('assessment_test_taken_id', $test_taken_id)->first();

        if($test){
            $res = [
                'status' => 'failed',
                'message' => 'Test already Taken'
            ];
        }else {
            $res = [
                'status' => 'success',
                'message' => 'take test'
            ];
        }

        return response()->json($res);
    }

    /////// List of Endpoints for Reporting ///////

    public function school_students_result_report($test_type_id)
    {
        $school_id = auth()->user()->school_id ?? 15;
    
        $result = DB::table('assessment_test_takens') 
            ->join('users', 'users.id', '=', 'assessment_test_takens.user_id') 
            ->join('assessment_take_tests', 'assessment_test_takens.id', '=', 'assessment_take_tests.assessment_test_taken_id') 
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id') 
            ->join('test_types', 'assessment_test_takens.test_type_id', '=', 'test_types.id')
            ->join('schools', 'users.school_id', '=', 'schools.id') 
            ->select('users.firstname as student_firstname', 'users.lastname as student_lastname', 'schools.school_name', 'test_types.test_type_name', 'user_assessment_scores.score', 'user_assessment_scores.created_at')
            ->where('assessment_test_takens.test_type_id', $test_type_id)
            ->where('users.school_id', $school_id)
            ->get();
    
        if (!$result->isEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'students results fetched successfully',
                'data' => $result
            ];
    
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no test results for school'
            ];
    
            return response()->json($response);
        }
    }

    public function getScoreStats($test_type_id) 
    {
        $school_id = auth()->user()->school_id ?? 1;

        $below_50_count = DB::table('assessment_test_takens')
        ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
        ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
        ->where('assessment_test_takens.test_type_id', $test_type_id)
        ->where('users.school_id', $school_id)
        ->where('user_assessment_scores.score', '<', 50)
        ->count();

        $above_50_count = DB::table('assessment_test_takens')
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->where('assessment_test_takens.test_type_id', $test_type_id)
            ->where('users.school_id', $school_id)
            ->where('user_assessment_scores.score', '>=', 50)
            ->count();
    

            $response = [
                'status' => 'success',
                'message' => 'school students results fetched successfully',
                'data' => [
                    'below_50_count' => $below_50_count,
                    'above_50_count' => $above_50_count
                ]
            ];

            return response()->json($response);
    }

    public function school_test_pass_rates()
    {
        $auth_id = auth()->user()->id ?? 1;
        
        $tests = DB::table('assessment_test_takens')
        ->join('assessment_take_tests', 'assessment_test_takens.id', '=', 'assessment_take_tests.assessment_test_taken_id')
        ->join('test_types', 'assessment_test_takens.test_type_id', '=', 'test_types.id')
        ->leftJoin('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
        ->where('test_types.admin_id', $auth_id)
        ->select('test_types.test_type_name', DB::raw('AVG(user_assessment_scores.score >= 50) * 100 as pass_rate'))
        ->groupBy('test_types.test_type_name')
        ->get();


        if (!$tests->isEmpty()) {

            $response = [
                'status' => 'success',
                'message' => 'school test-pass-rate fetched successfully',
                'tests' => $tests
            ];
    
            return response()->json($response);

        } else {

            $response = [
                'status' => 'failed',
                'message' => 'no tests fetched'
            ];
    
            return response()->json($response);
        }

    }

    public function all_test_pass_rates()
    {
        $tests = DB::table('assessment_test_takens')
            ->join('assessment_take_tests', 'assessment_test_takens.id', '=', 'assessment_take_tests.assessment_test_taken_id')
            ->join('test_types', 'assessment_test_takens.test_type_id', '=', 'test_types.id')
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->select('test_types.test_type_name', DB::raw('AVG(user_assessment_scores.score >= 50) * 100 as pass_rate'))
            ->where('users.assoc_cat_id', 1) 
            ->groupBy('test_types.test_type_name')
            ->get();

            if (!$tests->isEmpty()) {

                $response = [
                    'status' => 'success',
                    'message' => 'NAPPS test-pass-rate fetched successfully',
                    'tests' => $tests
                ];
        
                return response()->json($response);
    
            } else {
    
                $response = [
                    'status' => 'failed',
                    'message' => 'no tests-pass-rate fetched'
                ];
        
                return response()->json($response);
            }
    }

    public function totalRegisteredUsers()
    {
        $assoc_cat_id = 1;
        $user = auth()->user();

        if ($user && $user->school_id !== null) {
            $totalUsers = DB::table('users')    
            ->where('users.school_id', $user->school_id)
            ->count();
    
        } elseif ($user->role == 3) {
            $totalUsers = DB::table('users')
            ->where('assoc_cat_id', 1)
            ->count();
        
        } else {
            $totalUsers = DB::table('users')           
            ->join('admins', 'test_types.admin_id', '=', 'admins.id')
            ->where('assoc_cat_id', 1)           
            ->where('admins.chapter_code', $user->chapter_code)
            ->count();
        }     
        

        return response()->json(['status'=> 'success','total_users' => $totalUsers]);
    }

    public function totalTestsCreated()
    {
        $assoc_cat_id = 1;
        $user = auth()->user();

        if ($user && $user->school_id !== null) {
            $totalTests = DB::table('test_types')
            ->join('admins', 'test_types.admin_id', '=', 'admins.id')
            ->where('assoc_cat_id', 1)
            ->where('admins.school_id', $user->school_id)
            ->count();
    
        } elseif ($user->role == 3) {
            $totalTests = DB::table('test_types')
            ->where('assoc_cat_id', 1)
            ->count();
        
        } else {
            $totalTests = DB::table('test_types')
            ->join('admins', 'test_types.admin_id', '=', 'admins.id')
            ->where('assoc_cat_id', 1)
            ->where('admins.chapter_code', $user->chapter_code)
            ->count();
        }
       

        return response()->json(['status'=> 'success','total_tests' => $totalTests ?? 0]);
    }

    public function totalSchools()
    {
        $totalSchools = DB::table('schools')
            ->where('assoc_category_id', 1)
            ->count();

        return response()->json(['status'=> 'success','total_schools' => $totalSchools]);
    }


    
    ////// fetch napps test questions ////////

    public function getNappsSubjects()
    {
        $subjects = Subject::where('category_id', 5)->get();

        if(!$subjects->isEmpty()){
            $response = [
                'status' => 'success',
                'message' => 'subjects fetched successfully',
                'data' => $subjects
            ];

            return response()->json($response);
        }else{
            $response = [
                'status' => 'success',
                'message' => 'no questions for category'               
            ];

            return response()->json($response);
        }
    }

    public function getTest($testtype_id, $subjectId)
    {
        $user_id = auth()->user()->id;
       
        $yearId = Year::where('subject_id', $subjectId)->value('id');
        $testtype = TestType::find($testtype_id);
        $testTaken = AssessmentTestTaken::create([
            'user_id' => $user_id ?? 1,
            'test_type_id' => $testtype_id,
            'year_id' => $yearId,
            'subject_id' => $subjectId,
            'num_question' => $testtype->num_of_questions,
        ]);
        
        if ($testTaken) {
            
            $response = [
                'status' => 'success',
                'message' => 'Test created successfully',
                'test_taken_id' => $testTaken->id
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

    public function getAssessmentQuestions($testTaken_id)
    {
        //

        $user_id = auth()->user()->id; //565
        $test = UserAssessmentMarking::where('user_id', $user_id)->where('assessment_test_taken_id', $testTaken_id)->first();

        if($test){
            $res = [
                'status' => 'failed',
                'message' => 'Test already Taken'
            ];

            return response()->json($res);
        }
    
        $testTaken = AssessmentTestTaken::find($testTaken_id);
        $columns = ['optionA', 'optionB', 'optionC', 'optionD'];

        $questions = Question::select('id', 'questionText', 'correct_option', 'image', 'passage_id', ...$columns)
            ->with('passage')
            ->where('year_id', $testTaken->year_id)
            ->where('subject_id', $testTaken->subject_id)
            ->where('test_type_id', $testTaken->test_type_id)
            ->inRandomOrder()
            ->limit($testTaken->num_question)
            ->get();

        $user = auth()->user();

        if ($questions->isNotEmpty()) {

            foreach ($questions as $question) {
                $userStudyMarking = UserAssessmentMarking::updateOrCreate(
                    [
                        'user_id' => $user->id ?? 192,
                        'year_id' => $testTaken->year_id,
                        'subject_id' => $testTaken->subject_id,
                        'assessment_test_taken_id' => $testTaken->id,
                        'question_id' => $question->id,
                        'correct_option' => $question->correct_option
                    ]
                );

                $question->user_study_marking_id = $userStudyMarking->id;
                $question->selected_option = $userStudyMarking->selected_option;
            }
            $response = [
                'status' => 'success',
                'message' => 'questions view fetched successfully',
                'duration' => $testTaken->num_question,
                'data' => $questions->makeHidden('correct_option')
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

    //////////// ASSSOCIATIVE CATEGORY CREATE TEST SCHEDULES e.g NAPPS ///////////

    public function Assoc_category_store_test(Request $request)
    {
        //
        $data = $request->validate([
            'test_type_name' => 'required',
            'duration' => 'required',
            'class_id' => 'required',
            'passcode' => 'required',
            'num_questions' => 'required',
            'subject_id'=> 'required'
        ]);

        $createData = TestType::create([
            'assoc_cat_id' => 1,
            'admin_id' => auth()->user()->id ?? 1,
            'admin_role' => auth()->user()->role ?? 2,
            'subject_id'=> $request->subject_id,
            'admin_class_id' => $request->class_id,
            'test_type_name' => $request->test_type_name,
            'duration' => $request->duration,
            'num_of_questions'=> $request->num_questions,
            'passcode' => $request->passcode
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

    public function Assoc_category_test_scheduler(Request $request)
    {
        $data = $request->validate([
            'test_type_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        $createData = TestScheduler::updateOrCreate([
            'test_type_id' => $request->test_type_id,
            'admin_id' => auth()->user()->id ?? 1,
            'school_id' => auth()->user()->school_id ?? 15,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        if ($createData) {
            $response = [
                'status' => 'success',
                'message' => 'Test schedule created successfully',
                'data' => $createData
            ];

            return response()->json($response);

        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create test schedule'
            ];
            return response()->json($response, 404);
        }
    }
    
    /////////// associative create test scheduler ////////

    public function fetch_test_scheduler(Request $request)
    {
        $createData = TestScheduler::where('school_id', $school_id);

        if ($createData) {

            $response = [
                'status' => 'success',
                'message' => 'Test schedule created successfully',
                'data' => $createData
            ];

            return response()->json($response);

        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create test schedule'
            ];
            return response()->json($response, 404);
        }
    }

    public function fetch_user_test_scheduler(Request $request)
    {
        $user = auth()->user();
    
        // Ensure that user data is set properly
        $schoolId = $user->school_id ?? 15;
        $adminClassId = $user->admin_class_id ?? null; // Don't set a default that might cause overlap
    
        if (is_null($adminClassId)) {
            // Handle the case where admin_class_id is not set, maybe return an error
            return response()->json([
                'status' => 'failed',
                'message' => 'User admin class ID is not set'
            ], 400);
        }
    
        $allTests = DB::table('test_types')
            ->join('subjects', 'test_types.subject_id', '=', 'subjects.id')
            ->join('test_schedulers', 'test_schedulers.test_type_id', '=', 'test_types.id')
            ->select('test_types.*', 'subjects.subjectname', 'test_schedulers.start_date', 'test_schedulers.end_date')
            ->where('test_types.assoc_cat_id', '=', 1)
            ->where('test_schedulers.school_id', '=', $schoolId)
            ->where('test_types.admin_class_id', '=', $adminClassId)
            ->whereNotIn('test_types.id', function ($query) use ($user) {
                $query->select('test_type_id')
                    ->from('assessment_test_takens')
                    ->where('user_id', $user->id);
            })
            ->orderBy('test_types.updated_at', 'desc')
            ->get();
    
        if ($allTests->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No tests to fetch'
            ], 404);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'All tests fetched successfully',
            'data' => $allTests
        ]);
    }
    
    

    /*///////////////////////////////////////////////////////////// */
    public function fetch_all_created_test(){
        
        $allTest = DB::table('test_types')
        ->join('subjects', 'test_types.subject_id', '=', 'subjects.id')
        ->leftJoin('test_schedulers', 'test_schedulers.test_type_id', '=', 'test_types.id')
        ->select('test_types.*', 'subjects.subjectname', 'test_schedulers.start_date', 'test_schedulers.end_date')
        ->where('test_types.assoc_cat_id', '=', 1)
        ->orderBy('updated_at', 'desc')
        ->get();
        
    
        if ($allTest->isEmpty()) {

            $response = [
                'status' => 'failed',
                'message' => 'No tests to fetch'
            ];

            return response()->json($response, 404);
        } else {
            $response = [
                'status' => 'success',
                'message' => 'All tests fetched successfully',
                'data' => $allTest
            ];
            return response()->json($response);
        }
    }

    public function fetch_recent_test(){
        $recentTest = DB::table('test_types')
            ->join('subjects', 'test_types.subject_id', '=', 'subjects.id')
            ->select('test_types.*', 'subjects.subjectname')
            ->where('test_types.assoc_cat_id', '=', 1)
            ->orderBy('test_types.created_at', 'desc')
            ->first();
    
        if (!$recentTest) {
            $response = [
                'status' => 'failed',
                'message' => 'No recent test found'
            ];
            return response()->json($response, 404);
        } else {
            $response = [
                'status' => 'success',
                'message' => 'Recent test fetched successfully',
                'data' => $recentTest
            ];
            return response()->json($response);
        }
    }
    
   //////////////////////////////////////////////////////////////////////////////// 

    public function Assoc_category_update_test($id, Request $request)
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

    public function Assoc_category_show_test($id)
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

    public function Assoc_category_destroy_test($id)
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

    public function Assoc_category_storeBulkSolution(Request $request)
    {
        $data = $request->json()->all();

        $test_type_id = is_array($data['test_type_id']) ? $data['test_type_id'] : json_decode($data['test_type_id']);

        $questions = is_array($data['questions']) ? $data['questions'] : json_decode($data['questions'], true);
        array_shift($questions);

        $createdData = [];

        $testType = TestType::where('id', $request->test_type_id)->first();
        $subject_id = $testType->subject_id;

        $subjectId = Subject::where('id', $subject_id)->value('id');
        $yearId = Year::where('subject_id', $subjectId)->value('id');

        $admin = auth()->user();

        foreach ($questions as $item) {
            $filteredItem = array_diff($item, ['QuesNo']);

            $question = Question::updateOrCreate([
                'category_id' => 5,
                'school_id' => $admin->school_id,
                'test_type_id' => $test_type_id,
                'subject_id' => $subject_id,
                'year_id' => $yearId,
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


    //
    public function registerTeacher(Request $request)
    {
        
        $data = $request->validate([
            'fullname' => 'required',
            'username' => 'required|unique:admins,username',
            'email' => 'required|email|unique:admins,email',
            'role' => 'required',
            'school_id'=> 'required',
            'password'  =>  ['required', 'string'],
            'password_confirmation'  =>  'required|string'
        ]);

        if ($request->password !== $request->password_confirmation) {
            return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
        }

        $userExists = Admin::where('email', $request->email)->first();

        if ($userExists) {
            return response()->json(['status' => 'error', 'message' => 'User already exists!'], 400);
        }

        $data['password'] = Hash::make($request->password);        
           
        $user = Admin::create($data);

        if($user){            
            
            // Attempt login after successful registration

            $response = [
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $user
            ];

            return response()->json($response);

        } else {
            return response()->json(['status' => 'error', 'message' => 'Unable to create User!'], 400);
        }     
       
    }

    /// VIEW TEACHER

    public function showTeacher($teacher_id)
    {
        //
        $user = Admin::findOrFail($teacher_id);

        if ($user) {

            $response = [
                'status' => 'success',
                'message' => 'Teacher Information fetched successfully',
                'data' => $user
            ];

            return response()->json($response);

        } else {

            $response = [
                'status' => 'failed',
                'message' => 'No view for teacher found'
            ];

            return response()->json($response, 404);
        }
    }

    //// UPDATE TEACHER

    public function updateTeacher(Request $request, $teacher_id)
    {
        
        $user = Admin::find($teacher_id);

        $data = $request->validate([
            'fullname' => 'required',
            'username' => 'required|unique:admins,username',
            'email' => 'required|email|unique:admins,email',
            'role' => 'required',
            'password'  =>  ['required', 'string'],
            'password_confirmation'  =>  'required|string'
        ]);

        if ($request->password !== $request->password_confirmation) {
            return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
        }

        // if ($request->user_img) {
        //     $userImg = auth()->user()->firstname . '.' . auth()->user()->lastname . '-'. $request->user_img->extension();
        //     $request->user_img->storeAs('public/files', $userImg);
        //     $userImgLink = URL('storage/files/' . $userImg);

        //     $user->update(
        //         ['user_img' => $userImgLink]
        //     );
        // }

        
        $data =  $user->update([
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
            'role' =>  $request->role,
            'password' => Hash::make($request->password)
        ]);



        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'teacher Profile updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update User Profile'
            ];
            return response()->json($response, 404);
        }
    }

    //// DELETE TEACHER
    public function destroyTeacher($teacher_id)
    {
        $userDel = Admin::findOrFail($$teacher_id);
        $del =  $userDel->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'teacher deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete User'
            ];
            return response()->json($response);
        }
    }

    //// Fetch all teacher

    public function fetchAllTeacher($school_id) {
        $fetchAllTeachers = Admin::where('school_id', $school_id)->get();

        if (!$fetchAllTeachers -> isEmpty()) {

            $response = [
                'status' => 'success',
                'message' => 'teachers fetched successfully',
                'data' => $fetchAllTeachers
            ];

            return response()->json($response);

        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch teachers'
            ];
            return response()->json($response, 404);
        }
    }


    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< SCHOOL CRUD >>>>>>>>>>>>>>>>>>>>>>>>>

    function storeSchool(Request $request){

        $request->validate([
            'school_name' => 'required',
            'assoc_category_id' => 'required'
        ]);      
           
        $school = School::create([
            "assoc_category_id" => $request->assoc_category_id,
            "school_name" => $request->school_name
        ]);

        if($school){

            $response = [
                'status' => 'success',
                'message' => 'School created successfully',
                'data' => $school
            ];

            return response()->json($response);

        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'Unable to create school!'], 400);
        }            
    }

    //// VIEW SCHOOL

    public function showSchool($school_id)
    {
        //
        $school = School::findOrFail($school_id);

        if ($school) {

            $response = [
                'status' => 'success',
                'message' => 'school fetched successfully',
                'data' => $school
            ];

            return response()->json($response);

        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for school found'
            ];
            return response()->json($response, 404);
        }
    }

    //// UPDATE SCHOOL

    public function updateSchool(Request $request, $school_id)
    {
        $school = School::find($school_id);

        $data =  $request->validate([
            'school_name' => 'required',
            'assoc_category_id' => 'required'
        ]); 

                
        $data =  $school->update([
            'school_name' => $request->school_name,
            'assoc_category_id' => $request->assoc_category_id
        ]);



        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'School updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update school'
            ];
            return response()->json($response, 404);
        }
    }

    //// DELETE SCHOOL

    public function destroySchool($school_id)
    {
        $schoolDel = School::findOrFail($school_id);
        $del =  $schoolDel->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'school deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete school'
            ];
            return response()->json($response);
        }
    }

    //// fetch all schools 

    public function fetchAllSchoolByCategoryID($assoc_category_id){
        $fetchAllSchool = school::where('assoc_category_id', $assoc_category_id)->get();

        if (!$fetchAllSchool -> isEmpty()) {

            $response = [
                'status' => 'success',
                'message' => 'schools fetched successfully',
                'data' => $fetchAllSchool
            ];

            return response()->json($response);

        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch schools'
            ];
            return response()->json($response, 404);
        }
    }

    //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< STUDENT ENDPOINTS >>>>>>>>>>>>>>>>>>>>>>

    public function fetchUsersBySchoolIdAndSearch(Request $request, $school_id, $paginate = 15)
    {
        $query = User::query()->where('school_id', $school_id);

        // Check if search term is provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('firstname', 'like', "%$searchTerm%")
                    ->orWhere('lastname', 'like', "%$searchTerm%");
            });
        }
        
        $users = $query->paginate($paginate);

        if ($users->isEmpty()) {
            $response = [
                'status' => 'failed',
                'message' => 'No users found for the provided criteria'
            ];
            return response()->json($response);
        }

        $response = [
            'status' => 'success',
            'message' => 'User(s) fetched successfully',
            'data' => $users
        ];

        return response()->json($response);
    }

    // fetch users assoc_category Id

    public function fetchStudentsByAssocCateId(Request $request, $assoc_category_id, $paginate = 15)
    {
        $students = User::query()->whereHas('school', function ($query) use ($assoc_category_id) {
            $query->where('assoc_category_id', $assoc_category_id);
        });

        // Check if search term is provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $students->where(function ($q) use ($searchTerm) {
                $q->where('firstname', 'like', "%$searchTerm%")
                    ->orWhere('lastname', 'like', "%$searchTerm%");
            });
        }
        
        $users = $students->paginate($paginate);

        if ($users->isEmpty()) {
            $response = [
                'status' => 'failed',
                'message' => 'No users found for the provided criteria'
            ];
            return response()->json($response);
        }

        $response = [
            'status' => 'success',
            'message' => 'User(s) fetched successfully',
            'data' => $users
        ];

        return response()->json($response);
    }

    public function fetchStudentsByAssocCateIdOnly(Request $request, $assoc_category_id, $paginate = 15)
    {
        $students = User::query()->where('assoc_cat_id', $assoc_category_id);
        

        // Check if search term is provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $students->where(function ($q) use ($searchTerm) {
                $q->where('firstname', 'like', "%$searchTerm%")
                    ->orWhere('lastname', 'like', "%$searchTerm%");
            });
        }
        
        $users = $students->paginate($paginate);

        if ($users->isEmpty()) {
            $response = [
                'status' => 'failed',
                'message' => 'No users found for the provided criteria'
            ];
            return response()->json($response);
        }

        $response = [
            'status' => 'success',
            'message' => 'User(s) fetched successfully',
            'data' => $users
        ];

        return response()->json($response);
    }

    /// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< FETCH ALL STUDENTS >>>>>>>>>>>>>>>>>>>>>>

    public function fetchAllStudentsBySchoolId($school_id){

        // $school_id = auth()->user()->school_id ?? 1;
        $users = User::where('school_id', $school_id)->get();

        if (!$users->isEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'User(s) fetched successfully',
                'data' => $users
            ];
    
            return response()->json($response);
            
        } else{

            $response = [
                'status' => 'failed',
                'message' => 'No users found for the provided criteria'
            ];

            return response()->json($response);

        }
    }

    public function filterByDate(Request $request, $school_id)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = User::query()->where('school_id', $school_id)->orderBy('created_at', 'desc');

        // Check if both start_date and end_date are provided
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $users = $query->get();

        if (!$users->isEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'User(s) fetched successfully',
                'data' => $users
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch User(s)'
            ];

            return response()->json($response);
        }
    }

    // Endpoint that returns the registered users in the last 24hrs

    public function usersRegisteredLast24Hours($school_id)
    {
        // Calculate the date and time 24 hours ago
        $date24HoursAgo = Carbon::now()->subHours(24);

        // Query users registered in the last 24 hours
        $users = User::where('school_id', $school_id)->where('created_at', '>=', $date24HoursAgo)->get();

        if (!$users->isEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'User(s) fetched successfully',
                'data' => $users
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no User(s) to fetch'
            ];

            return response()->json($response);
        }
    }

    // Endpoint that returns registered users in the last week
    public function usersRegisteredLastWeek($school_id)
    {
        // Calculate the date and time 1 week ago
        $dateOneWeekAgo = Carbon::now()->subWeek();

        // Query users registered in the last week
        $users = User::where('school_id',$school_id)->where('created_at', '>=', $dateOneWeekAgo)->get();

        if (!$users->isEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'User(s) fetched successfully',
                'data' => $users
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch User(s)'
            ];

            return response()->json($response);
        }
    }

    // Endpoint that returns users in the last 30days

    public function usersRegisteredLast30Days($school_id)
    {
        // Calculate the date and time 30 days ago
        $date30DaysAgo = Carbon::now()->subDays(30);

        $users = User::where('school_id', $school_id)->where('created_at', '>=', $date30DaysAgo)->get();

        if (!$users->isEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'User(s) fetched successfully',
                'data' => $users
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch User(s)'
            ];

            return response()->json($response);
        }
    }

    //* Payment history

    public function paymentHistory(Request $request, $school_id)
    {  
        // Fetch payment history based on school_id
        $paymentHistory = PayInfo::whereHas('user', function ($query) use ($school_id) {
            $query->where('school_id', $school_id);
        })->get();
    
        if (!$paymentHistory->isEmpty()) {
            $response = 
            [
                'status' => 'success',
                'message' => 'payment histories fetched successfully',
                'data' => $paymentHistory
            ];
    
            return response()->json($response);
        } else {
            $response = 
            [
                'status' => 'failed',
                'message' => 'unable to fetch payment histories for the specified school'
            ];
    
            return response()->json($response, 404);
        }
    }
    
//* Filter payment history by date

public function filterHistoryByDate(Request $request, $school_id)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Parse start and end dates using Carbon
    $startDate = Carbon::parse($startDate)->startOfDay();
    $endDate = Carbon::parse($endDate)->endOfDay();

    // Query payment history within the specified date range
    $paymentHistory = PayInfo::whereHas('user', function ($query) use ($school_id) {
        $query->where('school_id', $school_id);
    })->whereBetween('created_at', [$startDate, $endDate])->get();

    if (!$paymentHistory->isEmpty()) {
        $response = [
            'status' => 'success',
            'message' => 'payment histories fetched successfully',
            'data' => $paymentHistory
        ];

        return response()->json($response);
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'unable to fetch payment histories'
        ];

        return response()->json($response);
    }
}

//* Search payment history by User names

public function searchPaymentByUser(Request $request, $school_id)
{
    $searchTerm = $request->input('search');

    // Query payment history by user's first name or last name
    $paymentHistory = PayInfo::whereHas('user', function ($query) use ($searchTerm) {
        $query->where('school_id', $school_id)
            ->where('firstname', 'like', "%$searchTerm%")
            ->orWhere('lastname', 'like', "%$searchTerm%");
    })->get();

    if (!$paymentHistory->isEmpty()) {
        $response = [
            'status' => 'success',
            'message' => 'payment histories fetched successfully',
            'data' => $paymentHistory
        ];

        return response()->json($response);
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'unable to fetch payment histories'
        ];

        return response()->json($response);
    }
}

//* Payment Total

public function subscriptionPaymentTotal($school_id)
{
    // Query total payment amount under subscriptions
    $totalPayment = PayInfo::whereHas('user', function ($query) use ($school_id) {
        $query->where('school_id', $school_id);
    })->sum('amount');

    if ($totalPayment !== null) {
        $response = [
            'status' => 'success',
            'message' => 'Total payments fetched successfully',
            'total_payments' => $totalPayment
        ];

        return response()->json($response);
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch payment histories'
        ];

        return response()->json($response);
    }
}

//* payment total by date

public function subscriptionPaymentTotal1(Request $request)
{
    $date = $request->input('date');

    // Parse date using Carbon
    $date = Carbon::parse($date)->startOfDay();

    // Query total payment amount under subscriptions for the specified date
    $totalPayment = Subscription::join('pay_infos', 'subscriptions.pay_info_id', '=', 'pay_infos.id')
        ->whereDate('pay_infos.created_at', $date)
        ->sum('pay_infos.amount');

    if ($totalPayment !== null) {
        $response = [
            'status' => 'success',
            'message' => 'Total payments fetched successfully',
            'total_payments' => $totalPayment
        ];

        return response()->json($response);
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch total payment for specified date'
        ];

        return response()->json($response, 404);
    }
}

public function subscriptionPaymentTotal2(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Parse start and end dates using Carbon
    $startDate = Carbon::parse($startDate)->startOfDay();
    $endDate = Carbon::parse($endDate)->endOfDay();

    // Query total payment amount under subscriptions within the specified date range
    $totalPayment = Subscription::whereHas('payments', function ($query) use ($startDate, $endDate) {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    })->sum('amount');

    return response()->json(['total_payment' => $totalPayment]);
}

/////<<<<<<<<<<<<<<<<<<<<<<<< Study Module >>>>>>>>>>>>>>>>>

////////// Total number of Questions
public function totalNumberOfQuestions($school_id) {

    $data = AdminQuestion::where('school_id', $school_id)->count();

    if ($data) {
        $response = [
            'status' => 'success',
            'message' => 'Total number of questions fetched successfully',
            'total_questions' => $data ?? 0
        ];

        return response()->json($response);
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch total number of questions'
        ];

        return response()->json($response);
    }
}


/////////  Total Subjects per class

public function totalSubjectsPerClass($class_id){
    //

    $data = AdminSubject::where('admin_class_id', $class_id)->count();

    if ($data) {
        $response = [
            'status' => 'success',
            'message' => 'Total number of subjects for class fetched successfully',
            'total_questions' => $data ?? 0
        ];

        return response()->json($response);
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch total number of subject'
        ];

        return response()->json($response);
    }

}

////////  Total number of questions per subject per class

public function totalNumberOfQuestionsPerSubjectPerClass($class_id, $subject_id){    
    
    $totalQuestions = DB::table('admin_classes')
        ->join('admin_subjects', 'admin_classes.id', '=', 'admin_subjects.admin_class_id')
        ->join('admin_topics', 'admin_subjects.id', '=', 'admin_topics.admin_subject_id')
        ->join('admin_questions', 'admin_topics.id', '=', 'admin_questions.admin_topic_id')
        ->where('admin_classes.id', $class_id)
        ->where('admin_subjects.id', $subject_id)
        ->count();

    if ($totalQuestions !== null) {

        $response = [
            'status' => 'success',
            'message' => 'Total number of questions for subject fetched successfully',                
            'total_questions' => $totalQuestions
        ];

        return response()->json($response);

    } else {
        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch total number of questions for subject'
        ];

        return response()->json($response);
    }
    
}

//// <<<<<<<<<<<<<<<<<<<<<< Practice Module >>>>>>>>>>>>>>>>

///////// Total Subjects by Category 

public function totalSubjectsByCategory($category_id){
    
    $data = Subject::where('category_id', $category_id)->count();

    if ($data) {
        $response = [
            'status' => 'success',
            'message' => 'Total number of subjects for class fetched successfully',
            'data' => $data ?? 0
        ];

        return response()->json($response);

    } else {
        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch total number of subject'
        ];

        return response()->json($response);
    }
}

/////////Total Questions per subject by category

public function totalQuestionsPerSubjectByCategory($category_id, $subject_id){

    $data = Question::where('category_id', $category_id)->where('subject_id', $subject_id)->count();

    if ($data) {

        $response = [
            'status' => 'success',
            'message' => 'Total number of questions for category and subjects fetched successfully',
            'total_questions' => $data ?? 0
        ];

        return response()->json($response);

    } else {

        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch total number of subject'
        ];

        return response()->json($response);
    }
}

////<<<<<<<<<<<<<<<<<<<< Dashboard Analytics >>>>>>>>>>>>>>>>>>

/////// Total test taken in categories(One endpoint to return total test taken in each categories[SSCE, BECE, UTME])

public function totalTestTakenInCategories($category_id){
    
    $totalTestsByCategory = DB::table('assessment_take_tests')
    ->join('subjects', 'assessment_take_tests.subject_id', '=', 'subjects.id')
    ->where('subjects.category_id', $category_id)
    ->count();

    if ($totalTestsByCategory !== null) {

        $response = [
            'status' => 'success',
            'message' => 'Total tests by category fetched successfully',
            'data' => $totalTestsByCategory ?? 0
        ];

        return response()->json($response);

    } else {

        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch total tests by category'
        ];

        return response()->json($response);
    }

}

//////  List of Test taken with Student info Order by newest to the old

public function testTakenListWithStudentInfo(){

    $tests = DB::table('assessment_take_tests')
            ->join('users', 'assessment_take_tests.user_id', '=', 'users.id')
            ->select('assessment_take_tests.*', 'users.*')
            ->orderBy('assessment_take_tests.created_at', 'desc')
            ->get();

            if ($tests !== null) {

                $response = [
                    'status' => 'success',
                    'message' => 'test taken with list of student info fetched successfully',
                    'data' => $tests ?? 0
                ];
        
                return response()->json($response);
        
            } else {
        
                $response = [
                    'status' => 'failed',
                    'message' => 'Unable to fetch data'
                ];
        
                return response()->json($response);
            }

}

/////   List of Test taken with Student info In Order of Top Scorer

public function testTakenWithStudentInfoInOrderOfTopScorer(){

    $tests = DB::table('assessment_take_tests')
    ->join('user_assessment_scores', 'assessment_take_tests.id', '=', 'user_assessment_scores.assessment_test_taken_id')
    ->join('users', 'assessment_take_tests.user_id', '=', 'users.id')
    ->select('assessment_take_tests.*', 'users.*', 'user_assessment_scores.score')
    ->orderByDesc('user_assessment_scores.score')
    ->get();

    if ($tests !== null) {

        $response = [
            'status' => 'success',
            'message' => 'test taken with list of student info fetched successfully',
            'data' => $tests
        ];

        return response()->json($response);

    } else {

        $response = [
            'status' => 'failed',
            'message' => 'Unable to fetch data'
        ];

        return response()->json($response);
    }

}

////////////////////// USER REGISTRATION
public function register(Request $request)
{
    // Generate Referral Code 
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    $length = 6; 

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
        
    $data = $request->validate([
        'firstname' => 'required',
        'lastname' => 'required',
        'middlename' => 'nullable',
        'gender' => 'nullable',
        'state' => 'nullable',
        'admin_class_id' => 'nullable',
        'school_id'=> 'nullable'
    ]);

    // Add the referral code to the data array

    $data['referal_code'] = $randomString;

    if ($request->password !== $request->password_confirmation) {
        return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
    }

    $user = User::where('firstname', $request->firstname)->first();
    $user1 = User::where('lastname', $request->lastname)->first();

    // if ($user && $user1) {
    //     return response()->json(['status' => 'error', 'message' => 'User already exists!'], 400);
    // }

    $data['password'] = Hash::make('password');
    $admin = auth()->user();
    

    if($request->referal_code){
        $refferalcode = User::where('referal_code', $request->referal_code)->first();

        if ($refferalcode) {
            $referrer = User::where('referal_code', $request->referal_code)->first();

            // Create the user
            $user = User::create($data);


            if ($referrer) {
                // Create the referral record
                Referral::create([
                    'user_id' => $referrer->id,
                    'referee_id' => $user->id, 
                    'referal_code' => $request->referal_code
                ]);      
                
                $referrer->points += 50;
                $referrer->save();

                $pointsHistory = new PointsHistory();
                $pointsHistory->user_id = $referrer->id;
                $pointsHistory->points += 50;
                $pointsHistory->descriptions = 'Referred User Points';
                $pointsHistory->save();
            }

            

            if($user){
            
            
                // Attempt login after successful registration
                
                auth()->attempt($request->only('email', 'password'));

                return $this->onSuccessfulLogin($user, false);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Unable to register!'], 400);
            }     
        }else{
            return response()->json(['status' => 'error', 'message' => 'Invalid Referral Code'], 400);
        }               
    }else{
        // Create the user
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'middlename' => $request->middlename,
            'gender' => $request->gender,
            'state' => $request->state,
            'password'  =>  'password',
            'admin_class_id' => $request->admin_class_id,
            'school_id'=> $request->school_id,
            'assoc_cat_id' => 1
        ]);

        $user->refresh();
        $user->student_code = ($admin->chapter_code ?? 'IKJ') . '/0' . ($admin->school_id ?? '14') . '/0' . ($user->id ?? '1');
        $user->save();

        if($user){
            
            
            // Attempt login after successful registration
            
            auth()->attempt($request->only('student_code', 'password'));

            return $this->onSuccessfulLogin($user, false);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unable to register!'], 400);
        }     
    }

        // Check if the referral code provided exists in the database
        
    
}

public function update(Request $request, $userid)
{
    $user = User::find($userid);

    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'User not found!'], 404);
    }

    $data = $request->validate([
        'firstname' => 'sometimes|required|string|max:255',
        'lastname' => 'sometimes|required|string|max:255',
        'middlename' => 'nullable|string|max:255',
        'gender' => 'nullable|string', 
        'state' => 'nullable|string|max:255',
        'admin_class_id' => 'nullable|integer|exists:admin_classes,id',
        'school_id' => 'nullable|integer|exists:schools,id',
        'password' => 'nullable|string|min:8|confirmed',
        'user_img' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    if ($request->hasFile('user_img')) {
        $userImg = $request->file('user_img')->store('public/files');
        $userImgLink = URL::to(Storage::url($userImg));
        $user->user_img = $userImgLink;
    }

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    if ($request->filled('firstname')) {
        $user->firstname = $request->firstname;
    }

    if ($request->filled('lastname')) {
        $user->lastname = $request->lastname;
    }

    if ($request->has('middlename')) {
        $user->middlename = $request->middlename;
    }

    if ($request->has('gender')) {
        $user->gender = $request->gender;
    }

    if ($request->has('state')) {
        $user->state = $request->state;
    }

    if ($request->filled('admin_class_id')) {
        $user->admin_class_id = $request->admin_class_id;
    }

    if ($request->filled('school_id')) {
        $user->school_id = $request->school_id;
    }

    // Assuming assoc_cat_id is meant to be static and not changing
    $user->assoc_cat_id = 1;

    $user->save();

    $response = [
        'status' => 'success',
        'message' => 'User Profile updated successfully',
        'data' => $user
    ];
    return response()->json($response);
}


public function bulkRegistration(Request $request, $school_id)
{
    $data = $request->json()->all();

    // $school_id = is_array($data['school_id']) ? $data['school_id'] : json_decode($data['school_id']);
    $admin_class_id = is_array($data['admin_class_id']) ? $data['admin_class_id'] : json_decode($data['admin_class_id']);

    $students = is_array($data['students']) ? $data['students'] : json_decode($data['students'], true);
    array_shift($students);
    
    $createdData = [];
    $admin = auth()->user();

    // 'firstname' => $request->firstname,
    // 'lastname' => $request->lastname,
    // 'middlename' => $request->middlename,
    // 'gender' => $request->gender,
    // 'state' => $request->state,
    // 'password'  =>  $request->password,
    // 'admin_class_id' => $request->admin_class_id,
    // 'school_id'=> $request->school_id,
    // 'assoc_cat_id' => 1

    foreach ($students as $item) {
        $randomString = ''; // Generate new referral code for each user
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = 6;
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }


        $filteredItem = array_diff($item, ['StudentNo']);

        $student = User::updateOrCreate([
            'assoc_cat_id'=> 1,
            'school_id' => $school_id ?? 1,
            'admin_class_id' => $admin_class_id,
            'firstname' => $item[0],
            'lastname' => $item[1],
            'middlename' => $item[2],
            'state' => $item[3],
            'gender' => $item[4],
        ], [
            'referal_code' => $randomString,
            'password' => Hash::make('password')
        ]);

        $student->refresh();
        $student->student_code = ($admin->chapter_code ?? 'IKJ') . '/0' . ($school_id ?? '14') . '/0' . ($student->id ?? '1');
        $student->save();

        $createdData[] = $student;
    }

    $response = [
        'status' => 'success',
        'message' => 'Users created successfully',
        'data' => $createdData
    ];

    return response()->json($response);
}

public function login(Request $request)
{
    $request->validate([
        'student_code' => 'required'
    ]);

    // Find the user by student code
    $user = User::where('student_code', $request->student_code)->first();

    // Check if user exists
    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'User does not exist!'], 400);
    }

    // Manually log in the user without password
   $log = Auth::login($user);

    // Record login information
    $login = new Login;
    $login->user_id = $user->id;
    $login->email = $request->student_code ?? 'no-data';
    $login->save();

    return $this->onSuccessfulLogin($user);

    if ($log) {
        $student = User::where('student_code', $request->student_code)->first();
        $login = new Login;
        $login->user_id = $student->id;
        $login->email = $request->student_code ?? 'no-date';
        $login->save();
        
         return $this->onSuccessfulLogin($user);
    } else {
      return response()->json(['status' => 'error', 'message' => 'Bad credentials'], 400);
    }
}


private function onSuccessfulLoginTest($user, $testTaken, $isLogin = true)
{
    $token = $user->createToken('Bearer')->plainTextToken;

    $response = [
        'status'    =>  'success',
        'message'   =>  $isLogin ? 'Login successful!' : "Registration successful, Welcome!",
        'data'      =>  [
            'user'              =>  $user,
            'token'             =>  $token,
            'uid'               =>  $user->id,
            'tt_id'             =>  $testTaken->id
        ]
    ];

    $user->school_name = DB::table('schools')->where('id', $user->school_id)->first('school_name');

    return response()->json($response);
}

private function onSuccessfulLogin($user, $isLogin = true) 
{
    $token = $user->createToken('Bearer')->plainTextToken;

    $response = [
        'status'    =>  'success',
        'message'   =>  $isLogin ? 'Login successful!' : "Registration successful, Welcome!",
        'data'      =>  [
            'user'              =>  $user,
            'token'             =>  $token,
            'uid'               =>  $user->id
        ]
    ];

    $user->school_name = DB::table('schools')->where('id', $user->school_id)->first('school_name');

    return response()->json($response);
}

public function getUser(Request $request)
{
    $response = [
        'status'    =>  'success',
        'message'   =>  'Fetch successful!',
        'data'      =>  [
            'user'              =>  $request->user(),
            'uid'               =>  auth()->id()
        ]
    ];

    return response()->json($response);
}

//////// GET TSTS ENDPOINTS
 //>>>>>.i need endpoint that use test id to fetch all the student results in a particuular test>>>>>>>

 public function testStudentResults($test_id)
 {
     $assoc_cat_id = 1;
     $user = auth()->user();
 
     if ($user && $user->school_id !== null) {
        $school_id = $user->school_id;
         $results = DB::table('assessment_test_takens')
             ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
             ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
             ->select('users.firstname', 'users.lastname', 'user_assessment_scores.score')
             ->where('assessment_test_takens.test_type_id', $test_id)
             ->where('users.assoc_cat_id', $assoc_cat_id)
             ->where('users.school_id', $school_id)
             ->get();
 
         if (!$results->isEmpty()) {
             $response = [
                 'status' => 'success',
                 'message' => 'test result for students fetched successfully',
                 'data' => $results
             ];
 
             return response()->json($response);
         } else {
             $response = [
                 'status' => 'failed',
                 'message' => 'no records to fetch'
             ];
 
             return response()->json($response);
         }
     } else {
         $results = DB::table('assessment_test_takens')
             ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
             ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
             ->select('users.firstname', 'users.lastname', 'user_assessment_scores.score')
             ->where('assessment_test_takens.test_type_id', $test_id)
             ->where('users.assoc_cat_id', $assoc_cat_id)
             ->get();
 
         if (!$results->isEmpty()) {
             $response = [
                 'status' => 'success',
                 'message' => 'test result for students fetched successfully',
                 'data' => $results
             ];
 
             return response()->json($response);
         } else {
             $response = [
                 'status' => 'failed',
                 'message' => 'no records to fetch'
             ];
 
             return response()->json($response);
         }
     }
 }
 
 //>>>>>i need endpoint that fetch all the test with the average result in percentage >>>>>>

 public function testsAverageResults()
{
    try {
        $assoc_cat_id = 1;
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not authenticated'
            ]);
        }

        // Check if the user role is 1 (School Admin)
        if ($user && $user->school_id !== null) {
            $results = DB::table('test_types')
                ->join('assessment_test_takens', 'test_types.id', '=', 'assessment_test_takens.test_type_id')
                ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
                ->join('admins', 'test_types.admin_id', '=', 'admins.id')
                ->select(
                    'test_types.test_type_name',
                    DB::raw('AVG(user_assessment_scores.score) AS average_score'),
                    DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
                )
                ->where('test_types.assoc_cat_id', $assoc_cat_id)
                ->where('admins.school_id', $user->school_id)
                ->groupBy('test_types.test_type_name')
                ->get();

        // Check if the user role is 3 (Admin)
        } elseif ($user->role == 3) {
            $results = DB::table('test_types')
                ->join('assessment_test_takens', 'test_types.id', '=', 'assessment_test_takens.test_type_id')
                ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
                ->join('admins', 'test_types.admin_id', '=', 'admins.id')
                ->select(
                    'test_types.test_type_name',
                    DB::raw('AVG(user_assessment_scores.score) AS average_score'),
                    DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
                )
                ->where('test_types.assoc_cat_id', $assoc_cat_id)
                ->groupBy('test_types.test_type_name')
                ->get();

        // For other roles (assuming role 2 and chapter_code checking)
        } else {
            $results = DB::table('test_types')
                ->join('assessment_test_takens', 'test_types.id', '=', 'assessment_test_takens.test_type_id')
                ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
                ->join('admins', 'test_types.admin_id', '=', 'admins.id')
                ->select(
                    'test_types.test_type_name',
                    DB::raw('AVG(user_assessment_scores.score) AS average_score'),
                    DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
                )
                ->where('test_types.assoc_cat_id', $assoc_cat_id)
                ->where('admins.role', 2)
                ->where('admins.chapter_code', $user->chapter_code)
                ->groupBy('test_types.test_type_name')
                ->get();
        }

        // Respond with results or failure message
        if (!$results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Test average results for students fetched successfully',
                'data' => $results
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No records to fetch'
            ]);
        }

    } catch (Exception $e) {
        // Catch any exception and return an error response
        return response()->json([
            'status' => 'failed',
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}



public function fetchTestsForAssocAdmin(){
    $adminTest = DB::table('test_types')
                    ->join('admins', 'admins.id', '=', 'test_types.admin_id')
                    ->join('subjects', 'test_types.subject_id', '=', 'subjects.id')
                    ->leftJoin('test_schedulers', 'test_schedulers.test_type_id', '=', 'test_types.id')
                    ->select('test_types.*', 'subjects.subjectname', 'test_schedulers.start_date', 'test_schedulers.end_date')
                    // ->where('admins.role', 3)
                    ->where('test_types.admin_id', 18)
                    ->where('test_types.assoc_cat_id', 1)
                    ->orderBy('updated_at', 'desc')
                    ->get();

                    if (!$adminTest->isEmpty()) {
                        $response = [
                            'status' => 'success',
                            'message' => 'tests fetched successfully',
                            'data' => $adminTest
                        ];
            
                        return response()->json($response);
                    } else {
                        $response = [
                            'status' => 'failed',
                            'message' => 'no records to fetch'
                        ];
            
                        return response()->json($response);
                    }
}

public function fetchTestsForSchoolAdmin($school_id){
    $adminTest = DB::table('test_types')
                    ->join('admins', 'admins.id', '=', 'test_types.admin_id')
                    ->join('subjects', 'test_types.subject_id', '=', 'subjects.id')
                    ->leftJoin('test_schedulers', 'test_schedulers.test_type_id', '=', 'test_types.id')
                    ->select('test_types.*', 'subjects.subjectname', 'test_schedulers.start_date', 'test_schedulers.end_date')
                    // ->where('admins.role', 2)
                    ->where('test_types.assoc_cat_id', 1)
                    ->where('admins.school_id', $school_id)
                    ->orderBy('updated_at', 'desc')
                    ->get();

                    if (!$adminTest->isEmpty()) {
                        $response = [
                            'status' => 'success',
                            'message' => 'tests fetched successfully',
                            'data' => $adminTest
                        ];
            
                        return response()->json($response);
                    } else {
                        $response = [
                            'status' => 'failed',
                            'message' => 'no records to fetch'
                        ];
            
                        return response()->json($response);
                    }
}

///>>>>>>> i also need an endpoint to check the test passcode on the student dashboard

public function fetchTestPasscode($test_id)
{
    // Fetch the passcode directly
    $testPasscode = TestType::where('id', $test_id)->value('passcode');

    // Check if the passcode is not null
    if ($testPasscode !== null) {
        $response = [
            'status' => 'success',
            'message' => 'Test passcode fetched successfully',
            'data' => ['passcode' => $testPasscode]
        ];
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'No passcode for test'
        ];
    }

    return response()->json($response);
}

////>>>>>> fetch students by class

public function fetchStudentsByClass($school_id, $class_id){
    $fetchStudents = User::where('school_id', $school_id)->where('admin_class_id', $class_id)->get();

    if (!$fetchStudents->isEmpty()) {
        $response = [
            'status' => 'success',
            'message' => 'students fetched successfully',
            'data' => $fetchStudents
        ];
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'no students for class'
        ];
    }
    return response()->json($response);
}


/////// fetch all chapters under the chapter admin

public function fetchAllChaptersUnderState(){
    $data = Admin::where('state', 'lagos')->where('role',  2)->get();

    if (!$data->isEmpty()) {
        $response = [
            'status' => 'success',
            'message' => 'Chapter Admins fetched successfully',
            'data' => $data
        ];
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'no admin available'
        ];
    }
    return response()->json($response);
}

}
