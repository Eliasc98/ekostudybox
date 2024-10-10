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

class EkostudyAdminController extends Controller
{
    //

    ////<<<<<<<<<<<<<<<<<<<< General Dashboard Analytics >>>>>>>>>>>>>>>>>>

    public function totalTestTaken()
    {

        $totalTestsByCategory = DB::table('assessment_take_tests')
        ->join('subjects', 'assessment_take_tests.subject_id', '=', 'subjects.id')
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

    public function totalStudents(){

        $totalStudents = User::count();       

        $response = [
            'status' => 'success',
            'message' => 'Total tests by category fetched successfully',
            'data' => $totalTestsByCategory ?? 0
        ];

        return response()->json($response);
        
    }

    public function generalTable(){
        
    }

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

}
