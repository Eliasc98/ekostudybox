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

    // public function totalTestTaken()
    // {

    //     $totalTestsByCategory = DB::table('assessment_take_tests')
    //     ->join('subjects', 'assessment_take_tests.subject_id', '=', 'subjects.id')
    //     ->count();

    //     if ($totalTestsByCategory !== null) {

    //         $response = [
    //             'status' => 'success',
    //             'message' => 'Total tests by category fetched successfully',
    //             'data' => $totalTestsByCategory ?? 0
    //         ];

    //         return response()->json($response);

    //     } else {

    //         $response = [
    //             'status' => 'failed',
    //             'message' => 'Unable to fetch total tests by category'
    //         ];

    //         return response()->json($response);
    //     }
    // }

    // public function totalStudents(){

    //     $totalStudents = User::count();       

    //     $response = [
    //         'status' => 'success',
    //         'message' => 'Total tests by category fetched successfully',
    //         'data' => $totalTestsByCategory ?? 0
    //     ];

    //     return response()->json($response);
        
    // }

    public function getGeneralAdminSummary()
    {
        try {
            // Fetch total districts, total students, and total tests taken
            $result = DB::table('districts')
                ->leftJoin('schools', 'districts.id', '=', 'schools.district_id')
                ->leftJoin('users', 'schools.id', '=', 'users.school_id')
                ->leftJoin('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
                ->select(
                    DB::raw('COUNT(DISTINCT districts.id) as total_districts'),
                    DB::raw('COUNT(DISTINCT users.id) as total_students'),
                    DB::raw('COUNT(assessment_test_takens.id) as total_tests_taken')
                )
                ->first();

            // Check if the data exists
            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'General admin summary fetched successfully',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No data found'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
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

public function getDistrictAverageScoreByCategory($district_id)
{
    try {
        // Check if the district exists
        $district = DB::table('districts')->where('id', $district_id)->first();
        if (!$district) {
            return response()->json([
                'status' => 'failed',
                'message' => 'District not found'
            ]);
        }

        // List of subjects
        $subjects = ['Mathematics', 'English', 'Biology', 'Chemistry', 'Physics', 'Commerce', 'Accounting', 'Government', 'Lit-in-Eng', 'Agricultural Science'];

        // Fetch the average score for each subject in the district
        $results = DB::table('test_types')
            ->join('assessment_test_takens', 'test_types.id', '=', 'assessment_test_takens.test_type_id')
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->join('schools', 'users.school_id', '=', 'schools.id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->join('subjects', 'test_types.subject_id', '=', 'subjects.id') // Join with subjects table
            ->select(
                'subjects.name AS subject_name', // Fetch the subject name
                DB::raw('AVG(user_assessment_scores.score) AS average_score'),
                DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
            )
            ->whereIn('subjects.name', $subjects) // Filter for specific subjects
            ->where('districts.id', $district_id)
            ->groupBy('subjects.name') // Group by subject name
            ->get();

        // Check if data exists
        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found for the district'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'District average score by subject fetched successfully',
            'data' => $results
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'failed',
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}

public function getSchoolAverageScoreByCategory($school_id)
{
    try {
        // Check if the school exists
        $school = DB::table('schools')->where('id', $school_id)->first();
        if (!$school) {
            return response()->json([
                'status' => 'failed',
                'message' => 'School not found'
            ]);
        }

        // List of subjects
        $subjects = ['Mathematics', 'English', 'Biology', 'Chemistry', 'Physics', 'Commerce', 'Accounting', 'Government', 'Lit-in-Eng', 'Agricultural Science'];

        // Fetch the average score for each subject in the school
        $results = DB::table('test_types')
            ->join('assessment_test_takens', 'test_types.id', '=', 'assessment_test_takens.test_type_id')
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->join('subjects', 'test_types.subject_id', '=', 'subjects.id') // Join with subjects table
            ->select(
                'subjects.name AS subject_name', // Fetch the subject name
                DB::raw('AVG(user_assessment_scores.score) AS average_score'),
                DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
            )
            ->whereIn('subjects.name', $subjects) // Filter for specific subjects
            ->where('users.school_id', $school_id)
            ->groupBy('subjects.name') // Group by subject name
            ->get();

        // Check if data exists
        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found for the school'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'School average score by subject fetched successfully',
            'data' => $results
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'failed',
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
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

 public function districtTestResults()
{
    try {
        // Get the authenticated user
        $user = auth()->user();
        // $user = 3;
        

        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not authenticated'
            ]);
        }

        // Check the role of the user to determine the data to fetch
        if ($user->role == 3) { // Admin can view results for all districts
           $results = DB::table('test_types')
            ->join('assessment_test_takens', 'test_types.id', '=', 'assessment_test_takens.test_type_id')
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->join('users', 'users.id', '=', 'assessment_test_takens.user_id')  // Join users for user data
            ->join('schools', 'users.school_id', '=', 'schools.id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select(
                'districts.name as district_name',
                DB::raw('IFNULL(COUNT(assessment_test_takens.id), 0) as total_tests_taken'),
                DB::raw('IFNULL(AVG(user_assessment_scores.score), 0) as average_score'),
                DB::raw('IFNULL((AVG(user_assessment_scores.score) / 100) * 100, 0) as average_score_percentage')
            )
            ->groupBy('districts.name')
            ->get();


        } elseif ($user->role == 2) { // District Admin can view results only for their district
            $results = DB::table('districts')
                ->join('schools', 'districts.id', '=', 'schools.district_id')
                ->join('admins', 'schools.id', '=', 'admins.school_id')
                ->join('users', 'users.school_id','=', 'schools.id')
                ->join('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
                ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
                ->select(
                    'districts.name as district_name',
                    DB::raw('COUNT(assessment_test_takens.id) as total_tests_taken'),
                    DB::raw('AVG(user_assessment_scores.score) as average_score'),
                    DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
                )
                ->where('districts.id', $user->district_id)
                ->groupBy('districts.name')
                ->get();

        } elseif ($user->role == 1) { // School Admin can view results for their school’s district
            $results = DB::table('districts')
                ->join('schools', 'districts.id', '=', 'schools.district_id')
                ->join('admins', 'schools.id', '=', 'admins.school_id')
                ->join('users', 'users.school_id','=', 'schools.id')
                ->join('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
                ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
                ->select(
                    'districts.name as district_name',
                    DB::raw('COUNT(assessment_test_takens.id) as total_tests_taken'),
                    DB::raw('AVG(user_assessment_scores.score) as average_score'),
                    DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
                )
                ->where('schools.id', $user->school_id)
                ->groupBy('districts.name')
                ->get();

        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized access'
            ]);
        }

        // Respond with results or failure message
        if (!$results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Test results by district fetched successfully',
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

public function getDistrictSummary($district_id)
{
    try {
        // Check if the district exists
        $district = DB::table('districts')->where('id', $district_id)->first();
        if (!$district) {
            return response()->json([
                'status' => 'failed',
                'message' => 'District not found'
            ]);
        }

        // Fetch total students, total schools, and total tests taken for the district
        $result = DB::table('districts')
            ->join('schools', 'districts.id', '=', 'schools.district_id')
            ->join('users', 'schools.id', '=', 'users.school_id')
            ->leftJoin('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
            ->select(
                'districts.id',   // Group by district ID to avoid issues
                'districts.name as district_name',
                DB::raw('COUNT(DISTINCT users.id) as total_students'),
                DB::raw('COUNT(DISTINCT schools.id) as total_schools'),
                DB::raw('COUNT(assessment_test_takens.id) as total_tests_taken')
            )
            ->where('districts.id', $district_id)
            ->groupBy('districts.id', 'districts.name')  // Add necessary GROUP BY clause
            ->first();

        if ($result) {
            return response()->json([
                'status' => 'success',
                'message' => 'District summary fetched successfully',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found for the district'
            ]);
        }
    } catch (Exception $e) {
        return response()->json([
            'status' => 'failed',
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}



public function getSchoolSummary($school_id)
{
    try {
        // Check if the school exists
        $school = DB::table('schools')->where('id', $school_id)->first();
        if (!$school) {
            return response()->json([
                'status' => 'failed',
                'message' => 'School not found'
            ]);
        }

        // Fetch total students and total tests taken for the school
        $result = DB::table('schools')
            ->join('users', 'schools.id', '=', 'users.school_id')
            ->leftJoin('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
            ->select(
                'schools.name as school_name',
                DB::raw('COUNT(DISTINCT users.id) as total_students'),
                DB::raw('COUNT(assessment_test_takens.id) as total_tests_taken')
            )
            ->where('schools.id', $school_id)
            ->first();

        if ($result) {
            return response()->json([
                'status' => 'success',
                'message' => 'School summary fetched successfully',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found for the school'
            ]);
        }
    } catch (Exception $e) {
        return response()->json([
            'status' => 'failed',
                'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}

public function getSchoolsByDistrict($district_id)
{
    try {
        // Check if the district exists
        $district = DB::table('districts')->where('id', $district_id)->first();
        if (!$district) {
            return response()->json([
                'status' => 'failed',
                'message' => 'District not found'
            ]);
        }

        // Fetch schools for the district

        $schools = DB::table('schools')
            ->where('district_id', $district_id)
            ->get();

        if ($schools->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No schools found for this district'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Schools fetched successfully',
            'data' => $schools
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 'failed',
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}

public function getSchoolsInDistrictWithTestInfo($district_id)
{
    try {
        // Check if the district exists
        $district = DB::table('districts')->where('id', $district_id)->first();
        if (!$district) {
            return response()->json([
                'status' => 'failed',
                'message' => 'District not found'
            ]);
        }

        // Fetch schools, total test taken, and average score for each school
        $results = DB::table('schools')
            ->join('users', 'schools.id', '=', 'users.school_id')
            ->join('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->select(
                'schools.school_name AS school_name',
                DB::raw('COUNT(DISTINCT assessment_test_takens.id) AS total_tests_taken'),
                DB::raw('AVG(user_assessment_scores.score) AS average_score'),
                DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
            )
            ->where('schools.district_id', $district_id)
            ->groupBy('schools.id')
            ->get();

        // Check if data exists
        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No schools or test data found for the district'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Schools with test data fetched successfully',
            'data' => $results
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'failed',
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}

public function getSubjectsTestInfoBySchool($school_id)
{
    try {
        // Check if the school exists
        $school = DB::table('schools')->where('id', $school_id)->first();
        if (!$school) {
            return response()->json([
                'status' => 'failed',
                'message' => 'School not found'
            ]);
        }

        // List of subjects
        $subjects = ['Mathematics', 'English', 'Biology', 'Chemistry', 'Physics', 'Commerce', 'Accounting', 'Government', 'Lit-in-Eng', 'Agricultural Science'];

        // Fetch subjects, total test taken, and average score for each subject in the school
        $results = DB::table('test_types')
            ->join('assessment_test_takens', 'test_types.id', '=', 'assessment_test_takens.test_type_id')
            ->join('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->join('subjects', 'test_types.subject_id', '=', 'subjects.id') // Join with subjects table
            ->select(
                'subjects.name AS subject_name',
                DB::raw('COUNT(DISTINCT assessment_test_takens.id) AS total_tests_taken'),
                DB::raw('AVG(user_assessment_scores.score) AS average_score'),
                DB::raw('(AVG(user_assessment_scores.score) / 100) * 100 as average_score_percentage')
            )
            ->whereIn('subjects.name', $subjects) // Filter for specified subjects
            ->where('users.school_id', $school_id) // Filter by school
            ->groupBy('subjects.id')
            ->get();

        // Check if data exists
        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No test data found for the school'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Subjects with test data fetched successfully',
            'data' => $results
        ]);
    } catch (Exception $e) {
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
