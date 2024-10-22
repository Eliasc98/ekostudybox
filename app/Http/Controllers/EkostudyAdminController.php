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
    ///

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
    public function getStudentReportByClass($school_id, $class_id)
{
    try {

        $query = DB::table('users')
            ->leftJoin('user_topic_progress', 'users.id', '=', 'user_topic_progress.user_id')
            ->leftJoin('marking_result_scores', 'users.id', '=', 'marking_result_scores.user_study_marking_id')
            ->select(
                'users.id as student_id',
                'users.firstname',
                'users.lastname',
                DB::raw('COUNT(DISTINCT user_topic_progress.id) as total_topics_completed'),
                DB::raw('COUNT(DISTINCT marking_result_scores.id) as total_tests_marked'),
                DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score')
            )
            ->where('users.school_id', $school_id);

       
        if ($class_id) {
            $query->where('users.admin_class_id', $class_id);
        }

        $report = $query->groupBy('users.id', 'users.firstname', 'users.lastname')->get();

        if ($report->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No data found for the selected class'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Student reports fetched successfully',
            'data' => $report
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unable to retrieve student reports',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function getAssessmentTopStudents()///top 10 assessment scorers
{
    try {
        // Query to calculate the top 10 students
        $topStudents = DB::table('users')
            ->leftJoin('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
            ->leftJoin('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
            ->select(
                'users.id as user_id',
                DB::raw("CONCAT(users.firstname, ' ', users.lastname) as student_name"),
                DB::raw('COUNT(assessment_test_takens.id) as total_tests_taken'),
                DB::raw('IFNULL(AVG(user_assessment_scores.score), 0) as average_score'),
                DB::raw('((COUNT(assessment_test_takens.id) + IFNULL(AVG(user_assessment_scores.score), 0)) / 2) as performance_score')
            )
            ->groupBy('users.id', 'users.firstname', 'users.lastname')
            ->orderBy('performance_score', 'DESC')
            ->limit(10)  // Get the top 10 students
            ->get();

        if ($topStudents->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Assessment top scorer fetched successfully',
                'data' => $topStudents
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unable to retrieve top students.',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function getTopStudyStudents()
{
    try {
        // Query to calculate the top 10 students in the study module
        $topStudents = DB::table('users')
            ->leftJoin('user_topic_progress', 'users.id', '=', 'user_topic_progress.user_id')
            ->leftJoin('marking_result_scores', 'users.id', '=', 'marking_result_scores.user_study_marking_id')
            ->select(
                'users.id as user_id',
                DB::raw("CONCAT(users.firstname, ' ', users.lastname) as student_name"),
                DB::raw('COUNT(user_topic_progress.id) as total_topics_completed'),
                DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score'),
                DB::raw('((COUNT(user_topic_progress.id) + IFNULL(AVG(marking_result_scores.score), 0)) / 2) as performance_score')
            )
            ->groupBy('users.id', 'users.firstname', 'users.lastname')
            ->orderBy('performance_score', 'DESC')
            ->limit(10)  // Get the top 10 students
            ->get();

        if ($topStudents->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Study top scorer fetched successfully',
                'data' => $topStudents
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unable to retrieve top study students.',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    
    public function fetchSchoolAdmin(){
       $data = Admin::where('role', '1') 
        ->join('schools', 'admins.school_id', '=', 'schools.id') 
        ->select(
            'admins.*', 
            'schools.school_name as school_name', 
            'schools.phone as phone_number'
        )
        ->get();

        // Check if the data exists
        if ($data->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'School Admins fetched successfully',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found'
            ]);
        }
    }

    public function fetchDistrictAdmin(){
        $data = Admin::where('role', '2')
        ->join('districts', 'admins.district_id', '=', 'districts.id') 
        ->select(
            'admins.*', 
            'districts.name as district_name', 
            'districts.state as district_state'
        )
        ->get();

        // Check if the data exists
        if ($data->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'District Admins fetched successfully',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found'
            ]);
        }
    }

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
    
    
    ///// Study module
    
    public function getOverallPerformance() //get overall performance
    {
    try {
        // Query to get total students and average score
        $query = DB::table('users')
            ->leftJoin('user_subject_progress', 'users.id', '=', 'user_subject_progress.user_id')
            ->leftJoin('marking_result_scores', 'user_subject_progress.id', '=', 'marking_result_scores.user_study_marking_id')
            ->select(
                DB::raw('COUNT(DISTINCT users.id) as total_students'),
                DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score')
            )
            ->first();  // Get the first row since we are dealing with an aggregate result
            
            

        return response()->json([
            'total_students' => $query->total_students,
            'average_score' => $query->average_score
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unable to retrieve overall performance data.',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function getStudyDistrictSummary()
    {
        try {
           $query = DB::table('districts')
            // Join to calculate total topics read for each district
            ->leftJoin('schools', 'districts.id', '=', 'schools.district_id')
            ->leftJoin('users', 'schools.id', '=', 'users.school_id')
            ->leftJoin('user_topic_progress', 'users.id', '=', 'user_topic_progress.user_id')
            ->leftJoin('marking_result_scores', 'users.id', '=', 'marking_result_scores.user_study_marking_id')
            ->select(
                'districts.name as district_name',
                DB::raw('COUNT(DISTINCT user_topic_progress.id) as total_topics_read'),
                DB::raw('COUNT(DISTINCT marking_result_scores.id) as total_marked_tests'),
                DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score')
            )
            ->groupBy('districts.name')
            ->get();
    
            // Check if the data exists
            if ($query) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'District admin study summary fetched successfully',
                    'data' => $query
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No data found'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to retrieve district summary.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getStudySchoolSummary($schoolId)
    {
        try {
            $query = DB::table('admin_classes')
                ->leftJoin('users', 'admin_classes.id', '=', 'users.admin_class_id')
                ->leftJoin('user_topic_progress', 'users.id', '=', 'user_topic_progress.user_id')
                ->leftJoin('marking_result_scores', 'users.id', '=', 'marking_result_scores.user_study_marking_id')
                ->select(
                    'admin_classes.class_name as class_name',
                    DB::raw('IFNULL(COUNT(DISTINCT user_topic_progress.id), 0) as total_topics_read'),
                    DB::raw('IFNULL(COUNT(DISTINCT marking_result_scores.id), 0) as total_marked_tests'),
                    DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score')
                )
                ->where('users.school_id', $schoolId) // Filter by school
                ->groupBy('admin_classes.class_name')
                ->get();
          
            $responseData = $query->map(function ($item) {
                return [
                    'class_name' => $item->class_name,
                    'total_topics_read' => (int) $item->total_topics_read ?? 0,
                    'total_marked_tests' => (int) $item->total_marked_tests ?? 0,
                    'average_score' => (float) $item->average_score ?? 0
                ];
            });
    
            return response()->json([
                'status' => 'success',
                'message' => 'School admin study summary fetched successfully',
                'data' => $responseData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to retrieve school summary.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getStudyDistrictTable($districtId)
    {
        try {
            // Query to get the district summary with schools, total topics read, total tests taken, and average score
            $query = DB::table('schools')
                ->leftJoin('users', 'schools.id', '=', 'users.school_id')
                ->leftJoin('user_topic_progress', 'users.id', '=', 'user_topic_progress.user_id')
                ->leftJoin('marking_result_scores', 'users.id', '=', 'marking_result_scores.user_study_marking_id')
                ->select(
                    'schools.school_name as school_name',
                    DB::raw('COUNT(DISTINCT user_topic_progress.id) as total_topics_read'),
                    DB::raw('COUNT(DISTINCT marking_result_scores.id) as total_marked_tests'),
                    DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score')
                )
                ->where('schools.district_id', $districtId)  // Filter by district
                ->groupBy('schools.school_name')
                ->get();
    
            if ($query) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'District admin study summary fetched successfully',
                        'data' => $query
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'No data found'
                    ]);
                }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to retrieve district summary.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    //// school study
    
    public function getStudentsSummaryBySchool($schoolId)
    {
        try {
            $query = DB::table('users')
                ->leftJoin('marking_result_scores', 'users.id', '=', 'marking_result_scores.user_study_marking_id')
                ->select(
                    'users.id as student_id',
                    'users.firstname',
                    'users.lastname',
                    DB::raw('IFNULL(COUNT(marking_result_scores.id), 0) as total_tests_taken'),
                    DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score')
                )
                ->where('users.school_id', $schoolId)
                ->groupBy('users.id', 'users.firstname', 'users.lastname')
                ->get();
                
                // Check if any data was found
                if ($query->isEmpty()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'No student found for the given school'
                    ], 200);
                }
    
            // Prepare the response data
            $responseData = $query->map(function ($item) {
                return [
                    'student_id' => $item->student_id,
                    'fullname' => $item->firstname . ' ' . $item->lastname,
                    'total_tests_taken' => (int) $item->total_tests_taken,
                    'average_score' => (float) $item->average_score
                ];
            });
    
            return response()->json([
                'status' => 'success',
                'message' => 'Student summary fetched successfully',
                'data' => $responseData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to retrieve student summary.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAssessmentSummaryBySchool($schoolId)
    {
        try {
            $query = DB::table('users')
                ->leftJoin('assessment_test_takens', 'users.id', '=', 'assessment_test_takens.user_id')
                ->leftJoin('user_assessment_scores', 'assessment_test_takens.id', '=', 'user_assessment_scores.assessment_test_taken_id')
                ->select(
                    'users.id as student_id',
                    'users.firstname',
                    'users.lastname',
                    DB::raw('IFNULL(COUNT(assessment_test_takens.id), 0) as total_tests_taken'),
                    DB::raw('IFNULL(AVG(user_assessment_scores.score), 0) as average_score')
                )
                ->where('users.school_id', $schoolId)
                ->groupBy('users.id', 'users.firstname', 'users.lastname')
                ->get();
                
                // Check if any data was found
                if ($query->isEmpty()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'No student found for the given school'
                    ], 200);
                }
    
            // Prepare the response data
            $responseData = $query->map(function ($item) {
                return [
                    'student_id' => $item->student_id,
                    'fullname' => $item->firstname . ' ' . $item->lastname,
                    'total_tests_taken' => (int) $item->total_tests_taken,
                    'average_score' => (float) $item->average_score
                ];
            });
    
            return response()->json([
                'status' => 'success',
                'message' => 'Assessment summary fetched successfully',
                'data' => $responseData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to retrieve assessment summary.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    //
    public function getTopSchoolsInDistrict($districtId)
    {
    try {
        $query = DB::table('schools')
            ->leftJoin('users', 'schools.id', '=', 'users.school_id')
            ->leftJoin('user_topic_progress', 'users.id', '=', 'user_topic_progress.user_id')
            ->leftJoin('marking_result_scores', 'users.id', '=', 'marking_result_scores.user_study_marking_id')
            ->select(
                'schools.id as school_id',
                'schools.school_name as school_name',
                DB::raw('COUNT(DISTINCT user_topic_progress.id) as total_topics_read'),
                DB::raw('COUNT(DISTINCT marking_result_scores.id) as total_marked_tests'),
                DB::raw('IFNULL(AVG(marking_result_scores.score), 0) as average_score')
            )
            ->where('schools.district_id', $districtId) // Filter by district
            ->groupBy('schools.id', 'schools.school_name')
            ->orderBy(DB::raw('AVG(marking_result_scores.score)'), 'desc') // Order by average score
            ->orderBy(DB::raw('COUNT(user_topic_progress.id)'), 'desc') // Secondary order by total topics read
            ->limit(10) // Limit to top 10 schools
            ->get();

        // Check if any data was found
        if ($query->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No schools found for the given district'
            ], 200);
        }

        // Prepare the response data
        $responseData = $query->map(function ($item) {
            return [
                'school_id' => $item->school_id,
                'school_name' => $item->school_name,
                'total_topics_read' => (int) $item->total_topics_read,
                'total_marked_tests' => (int) $item->total_marked_tests,
                'average_score' => (float) $item->average_score
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Top 10 schools fetched successfully',
            'data' => $responseData
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unable to retrieve top schools.',
            'error' => $e->getMessage(),
        ], 500);
    }
}







    // Controller Method in AdminReportController.php
    
    public function getSchoolPerformance($school_id)
    {
                $report = DB::table('marking_result_scores')
                    ->join('user_study_marking', 'marking_result_scores.user_study_marking_id', '=', 'user_study_marking.id')
                    ->join('users', 'user_study_marking.user_id', '=', 'users.id')
                    ->join('schools', 'users.school_id', '=', 'schools.id')
                    ->select(
                        'schools.name as school_name',
                        DB::raw('COUNT(marking_result_scores.id) as total_tests_taken'),
                        DB::raw('AVG(marking_result_scores.score) as average_score'),
                        DB::raw('AVG(marking_result_scores.score / 100) * 100 as score_percentage')
                    )
                    ->where('schools.id', $schoolId)
                    ->groupBy('schools.school_name')
                    ->get();
    
                if ($report) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'school performance fetched successfully',
                        'data' => $report
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'No data found'
                    ]);
                }
    }
    
    public function getProgressByDistrictWithSubjects($district_id)
    {
    try {

        $query = DB::table('user_topic_progress')
            ->join('users', 'user_topic_progress.user_id', '=', 'users.id')
            ->join('schools', 'users.school_id', '=', 'schools.id')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->join('admin_classes', 'users.class_id', '=', 'admin_classes.id')
            ->join('admin_subjects', 'user_topic_progress.subject_id', '=', 'admin_subjects.id')
            ->select(
                'districts.name as district_name',
                'admin_subjects.subject_name as subject_name',
                DB::raw('COUNT(user_topic_progress.id) as total_topics_completed'),
                DB::raw('AVG(user_topic_progress.progress) as average_progress')
            )
            ->groupBy('districts.name', 'admin_subjects.subject_name');

        // Apply district filter
        if ($districtId) {
            $query->where('districts.id', $districtId);
        }

        $report = $query->get();

        if ($report) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'progress by district per subject fetched successfully',
                        'data' => $report
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'No data found'
                    ]);
                }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unable to generate progress overview by district with subjects.',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function getProgressBySchoolWithSubjects($school_id)
    {
    try {
        // $schoolId = $request->input('school_id');

        $query = DB::table('user_topic_progress')
            ->join('users', 'user_topic_progress.user_id', '=', 'users.id')
            ->join('schools', 'users.school_id', '=', 'schools.id')
            ->join('admin_classes', 'users.class_id', '=', 'admin_classes.id')
            ->join('admin_subjects', 'user_topic_progress.subject_id', '=', 'admin_subjects.id')
            ->select(
                'schools.school_name as school_name',
                'admin_subjects.subject_name as subject_name',
                DB::raw('COUNT(user_topic_progress.id) as total_topics_completed'),
                DB::raw('AVG(user_topic_progress.progress) as average_progress')
            )
            ->groupBy('schools.school_name', 'admin_subjects.subject_name');

        // Apply school filter
        if ($schoolId) {
            $query->where('schools.id', $schoolId);
        }

        $report = $query->get();

        if ($report) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'school performance fetched successfully',
                        'data' => $report
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'No data found'
                    ]);
                }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unable to generate progress overview by school with subjects.',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function getProgressByClassWithSubjects($class_id)
    {
    try {
        // $classId = $request->input('class_id');

        $query = DB::table('user_topic_progress')
            ->join('users', 'user_topic_progress.user_id', '=', 'users.id')
            ->join('admin_classes', 'users.admin_class_id', '=', 'admin_classes.id')
            ->join('admin_subjects', 'user_topic_progress.subject_id', '=', 'admin_subjects.id')
            ->select(
                'admin_classes.class_name as class_name',
                'admin_subjects.subject_name as subject_name',
                DB::raw('COUNT(user_topic_progress.id) as total_topics_completed'),
                DB::raw('AVG(user_topic_progress.progress) as average_progress')
            )
            ->groupBy('admin_classes.class_name', 'admin_subjects.subject_name');

        // Apply class filter
        if ($classId) {
            $query->where('admin_classes.id', $classId);
        }

        $report = $query->get();

        if ($report) {
            return response()->json([
                'status' => 'success',
                'message' => 'progress by class fetched successfully',
                'data' => $report
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No data found'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unable to generate progress overview by class with subjects.',
            'message' => $e->getMessage(),
        ], 500);
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
