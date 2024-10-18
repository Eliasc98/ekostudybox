<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminWeekController;
use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\AdminTopicController;
use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\AdminQuestionController;
use App\Http\Controllers\AdminSubjectController;
use App\Http\Controllers\AdminTestController;
use App\Http\Controllers\AuthAdmin\AdminAuthController;
use App\Http\Controllers\AuthUser\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TestTypeController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\YearController;
use App\Http\Controllers\AdminManagement;
use App\Models\AdminSubject;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\GenerateCodeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\PayInfoController;
use App\Http\Controllers\InfluencerController;
use App\Http\Controllers\AdminReferralController;
use App\Http\Controllers\EkostudyAdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//// fetch all schools
// Route::post('auth/school-management/school/student/bulk-registration/{school_id}', [TeacherController::class, 'bulkRegistration']);

// routes/api.php

Route::get('/message', function (){
    $mess = "this works";

    return $mess;
}); 

Route::post('influencers/generate', [InfluencerController::class, 'generateCode']);
Route::get('admin/influencers', [InfluencerController::class, 'listCodes']);

Route::post('referrals/sale', [ReferralSaleController::class, 'trackSale']);
Route::get('admin/referrals/sales', [ReferralSaleController::class, 'listSales']);
Route::get('admin/referrals/influencersales', [ReferralSaleController::class, 'influencerSales']);

// routes/api.php
Route::get('admin/referrals', [AdminReferralController::class, 'index']);
Route::delete('admin/referrals/{id}', [AdminReferralController::class, 'deleteReferral']);

// routes/api.php
Route::post('admin/trial/activate', [TrialController::class, 'activateTrial']);
Route::post('admin/trial/auto-deduct', [TrialController::class, 'autoDeduct']);

Route::get('auth/fetch-school-by-district/{district_id}', [EkostudyAdminController::class, 'getSchoolsByDistrict']);






Route::post('auth/school-management/school/student/registration', [TeacherController::class, 'register']);
Route::post('auth/school-management/school/student/login', [TeacherController::class, 'login']);
Route::post('auth/school-management/school/student/update/{userid}', [TeacherController::class, 'update']);

Route::post('assoc-management/demo-test/{test_id}', [TeacherController::class, 'fetchQuickTestPasscode']);

//NAPPS QUESTION PASSAGE

Route::get('assess/questions/test/{test_id}', [TeacherController::class, 'questionsByTestId']); //Fetch all questions by test id

Route::post('assoc-assess/create/questionPassage', [TeacherController::class, 'storeQuestionPassage']);
Route::get('assoc-assess/assign-question-to-Passage/{question_id}/{passage_id}', [TeacherController::class, 'updatePassageId']); /// Assign passage to question
 Route::get('assoc-assess/unassign-question-to-Passage/{question_id}', [TeacherController::class, 'unassignPassageId']); /// UnAssign passage to question
Route::get('assoc-assess/get-questionPassage/{test_id}', [TeacherController::class, 'getPassageByTest']);

///////
Route::get('school-management/schools/napps-school-students/{school_id}/{class_id}', [TeacherController::class, 'fetchStudentsByClass']);
// Route::get('school-management/schools/napps-test-results-average', [TeacherController::class, 'testsAverageResults']);
Route::get('school-management/schools/napps-test-results/{test_id}', [TeacherController::class, 'testStudentResults']);


Route::get('school-management/schools/{assoc_category_id}', [TeacherController::class, 'fetchAllSchoolByCategoryID']);
Route::get('school-management/school-students/{school_id}', [TeacherController::class, 'fetchAllStudentsBySchoolId']);

// Route::get('assoc-management-cat/fetch-student-test-exempt', [TeacherController::class, 'fetch_user_test_scheduler']);
Route::get('assoc-management-cat/fetch-test-passcode/{test_id}', [TeacherController::class, 'fetchTestPasscode']);
Route::get('assoc-management-cat/napps-test', [TeacherController::class, 'fetchTestsForAssocAdmin']);
Route::get('assoc-management-cat/school-test/{school_id}', [TeacherController::class, 'fetchTestsForSchoolAdmin']);
Route::get('assoc-management-cat/chapter-admin', [TeacherController::class, 'fetchAllChaptersUnderState']); //////// yet to test



Route::get('assoc-management-cat/total-schools', [TeacherController::class, 'totalSchools']);


Route::get('assoc-management-cat/all-test-pass-rates', [TeacherController::class, 'all_test_pass_rates']);
Route::get('assoc-management-cat/school_test_pass_rates', [TeacherController::class, 'school_test_pass_rates']);

Route::get('assoc-management-cat/get-score-stats/{test_type_id}', [TeacherController::class, 'getScoreStats']);

Route::get('assoc-management-cat/changePassword', [TeacherController::class, 'changePassword']);
///////// ASSOC ADMIN CATEGORY NAPPS  ENDPOINTS////


// Route::post('assoc-management-cat/create/test', [TeacherController::class, 'Assoc_category_store_test']); 
Route::get('assoc-management-cat/show/test/{id}', [TeacherController::class, 'Assoc_category_show_test']);
Route::post('assoc-management-cat/update/test/{id}', [TeacherController::class, 'Assoc_category_update_test']);
Route::delete('assoc-management-cat/delete/test/{id}', [TeacherController::class, 'Assoc_category_destroy_test']);
Route::get('assoc-management-cat/fetch-all/tests', [TeacherController::class, 'fetch_all_created_test']);
Route::get('assoc-management-cat/fetch-all/recent-test', [TeacherController::class, 'fetch_recent_test']);


// Route::get('assoc-management-cat/get-user-tests/{testtype_id}/{subjectId}', [TeacherController::class, 'getTest']);



Route::get('assoc-management-cat/get-all-napps-subjects', [TeacherController::class, 'getNappsSubjects']);

///////////////////////////////////////////
Route::post('user-management/create/bulk-users', [UserController::class, 'bulkRegistration']); 
Route::get('school-management/students/{school_id}/{paginate}', [TeacherController::class, 'fetchUsersBySchoolIdAndSearch']);
Route::get('school-management/assoc-category-id/students/{assoc_category_id}/{paginate}', [TeacherController::class, 'fetchStudentsByAssocCateId']);
Route::get('school-management/assoc-category-id-only/students-cate/{assoc_category_id}/{paginate}', [TeacherController::class, 'fetchStudentsByAssocCateIdOnly']);
Route::post('generate-code', [GenerateCodeController::class, 'generateAndStoreCode']);
Route::post('activate-code', [GenerateCodeController::class, 'activateCode']);

Route::get('get-total-downloads', [DownloadController::class,'getTotalClicks']);
Route::post('get-download-click', [DownloadController::class, 'store']); //route to get download clicks

///////// ADMIN ROUTE /////////////
Route::post('auth/admin/register', [AdminAuthController::class, 'register']);
Route::post('auth/admin/login', [AdminAuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {

    ///eko-study
    
    ///study module
    
    Route::get('admin/reports/get-top-schools-in-district/{district_id}', [EkostudyAdminController::class, 'getTopSchoolsInDistrict']);
    Route::get('admin/reports/get-students-summary-study-by-school/{school_id}', [EkostudyAdminController::class, 'getStudentsSummaryBySchool']);
    
    Route::get('admin/reports/get-students-summary-assessment-by-school/{school_id}', [EkostudyAdminController::class, 'getAssessmentSummaryBySchool']);
    
    Route::get('admin/reports/get-top-students-study', [EkostudyAdminController::class, 'getTopStudyStudents']);
    
    Route::get('admin/reports/get-top-students-assessments', [EkostudyAdminController::class, 'getAssessmentTopStudents']);
    
    Route::get('admin/reports/get-overall-performance', [EkostudyAdminController::class, 'getOverallPerformance']);
    
    Route::get('admin/reports/get-study-districts-summary', [EkostudyAdminController::class, 'getStudyDistrictSummary']);
    
    Route::get('admin/reports/get-study-school-summary/{school_id}', [EkostudyAdminController::class, 'getStudySchoolSummary']);
    
    Route::get('admin/reports/get-study-district-summary/{district_id}', [EkostudyAdminController::class, 'getStudyDistrictTable']);
    
    Route::get('admin/reports/school-performance/{school_id}', [EkostudyAdminController::class, 'getSchoolPerformance']);
    
    Route::get('admin/reports/progress-performance-by-district/{district_id}', [EkostudyAdminController::class, 'getProgressByDistrictWithSubjects']);
    
    Route::get('admin/reports/progress-performance-by-school/{school_id}', [EkostudyAdminController::class, 'getProgressBySchoolWithSubjects']);
    
    Route::get('admin/reports/progress-performance-by-class/{class_id}', [EkostudyAdminController::class, 'getProgressByClassWithSubjects']);
    
    //end of study
     
     Route::get('eko-study/get-district-admin', [EkostudyAdminController::class, 'fetchDistrictAdmin']);

    Route::get('eko-study/get-school-admin', [EkostudyAdminController::class, 'fetchSchoolAdmin']);

    Route::get('eko-study/get-general-summary', [EkostudyAdminController::class, 'getGeneralAdminSummary']); //state dashboard table
    
    Route::get('eko-study/get-admin-table', [EkostudyAdminController::class, 'districtTestResults']); //general admin table
    
    Route::get('eko-study/get-district-admin-board/{district_id}', [EkostudyAdminController::class, 'getDistrictSummary']); //district summary table
    
    Route::get('eko-study/get-school-admin-board/{school_id}', [EkostudyAdminController::class, 'getSchoolSummary']); //school summary table
    
    Route::get('eko-study/district-chart/{district_id}', [EkostudyAdminController::class, 'getDistrictAverageScoreByCategory']); //district chart
    
    Route::get('eko-study/school-chart/{school_id}', [EkostudyAdminController::class, 'getSchoolAverageScoreByCategory']); //district chart
    
    
    Route::get('eko-study/get-school-table-data/{school_id}', [EkostudyAdminController::class, 'getSubjectsTestInfoBySchool']); //district chart
    
    Route::get('eko-study/get-district-table-data/{district_id}', [EkostudyAdminController::class, 'getSchoolsInDistrictWithTestInfo']); //district chart
    
    
    
    


    Route::get('assoc-management-cat/test-checker/{test_taken_id}', [TeacherController::class, 'checkIfTestTaken']);
    /////////// NAPPS ENDPOINTS///////
    Route::post('assoc-management-cat/upload/bulkquestions', [TeacherController::class, 'Assoc_category_storeBulkSolution']);
    Route::get('assoc-management-cat/get-user-tests-questions/{testTaken_id}', [TeacherController::class, 'getAssessmentQuestions']);
    Route::get('assoc-management-cat/fetch-student-test-exempt', [TeacherController::class, 'fetch_user_test_scheduler']);
    Route::post('assoc-management-cat/create/test-scheduler', [TeacherController::class, 'Assoc_category_test_scheduler']);
    Route::get('school-management/assocs/napps-test-results-average', [TeacherController::class, 'testsAverageResults']);
    Route::get('assoc-management-cat/total-tests-created', [TeacherController::class, 'totalTestsCreated']);
    Route::get('assoc-management-cat/total-registered-users', [TeacherController::class, 'totalRegisteredUsers']);
    Route::get('assoc-management-cat/get-user-tests/{testtype_id}/{subjectId}', [TeacherController::class, 'getTest']);
    Route::get('assoc-management-cat/school-students-result/{test_type_id}', [TeacherController::class, 'school_students_result_report']);

   
    Route::post('assoc-management-cat/create/test', [TeacherController::class, 'Assoc_category_store_test']);
    Route::post('auth/school-management/school/student/bulk-registration/{school_id}', [TeacherController::class, 'bulkRegistration']);
    
    //Class endpoints/////
    Route::get('admin/classes', [AdminClassController::class, 'index']); ///fetchall
    Route::post('admin/create/class', [AdminClassController::class, 'store']);
    Route::get('admin/show/class/{id}', [AdminClassController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/update/class/{id}', [AdminClassController::class, 'update']);
    Route::delete('delete/class/{id}', [AdminClassController::class, 'destroy']);

    //Content Endpoints////
    Route::post('admin/create/content', [AdminContentController::class, 'store']);

    //fetch count of registered users
    Route::get('admin/no-of-users', [MobileController::class, 'getNoOfUsers']);
    
    Route::post('admin/upload/content', [AdminContentController::class, 'storeEpub']);
    Route::get('admin/show/content/{id}', [AdminContentController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/update/content/{id}', [AdminContentController::class, 'update']);
    Route::delete('delete/content/{id}', [AdminContentController::class, 'destroy']);

    Route::get('content/topic/{id}', [AdminContentController::class, 'contentTopic']); ///////Fetch Content by topic id
    Route::get('admin/contents', [AdminContentController::class, 'fetchAll']); //Fetch all contents



    /// Subject Crud endpoints////
    Route::post('admin/create/subject', [AdminSubjectController::class, 'store']);
    Route::get('admin/show/subject/{id}', [AdminSubjectController::class, 'show']);
    Route::post('admin/update/subject/{id}', [AdminSubjectController::class, 'update']);
    Route::delete('delete/subject/{id}', [AdminSubjectController::class, 'destroy']);
    Route::get('admin/class/subject/{id}', [AdminSubjectController::class, 'subjectClass']); ///Fetch subject by class Id
    Route::get('admin/subjects', [AdminSubjectController::class, 'fetchAll']); //Fetch all subjects



    //// Topic crud endpoints///////
    Route::post('admin/create/topic', [AdminTopicController::class, 'store']);
    Route::get('admin/show/topic/{id}', [AdminTopicController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/topic/{id}', [AdminTopicController::class, 'update']);
    Route::delete('delete/topic/{id}', [AdminTopicController::class, 'destroy']);
    Route::get('admin/subject/weeks/{subjectId}/{termId}', [AdminTopicController::class, 'subjectWeek']); ///Fetch week by subject Id
    Route::get('admin/topics', [AdminTopicController::class, 'fetchAll']); //Fetch all topics


    //// Test Crud endpoints//////
    Route::post('admin/create/test', [AdminTestController::class, 'store']);
    Route::get('admin/show/test/{id}', [AdminTestController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/test/{id}', [AdminTestController::class, 'update']);
    Route::delete('delete/test/{id}', [AdminTestController::class, 'destroy']);

    Route::get('admin/tests', [AdminTestController::class, 'fetchAll']); //Fetch all tests

    //content Questions crud endpoints///////
    Route::post('admin/create/questions', [AdminQuestionController::class, 'store']);
    Route::get('admin/show/question/{id}', [AdminQuestionController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/question/{id}', [AdminQuestionController::class, 'update']);
    Route::delete('delete/question/{id}', [AdminQuestionController::class, 'destroy']);

    Route::get('admin/topic/question/{id}', [AdminQuestionController::class, 'questionTopic']);
    Route::get('admin/topic/question/count/{id}', [AdminQuestionController::class, 'count']);
    Route::get('admin/fetch/class/subject/topic/question/{id}', [AdminQuestionController::class, 'fetchCST']); /// fetch  class_name, Subject_name, and Topic_name based on the  topic ID
    Route::get('admin/questions', [AdminQuestionController::class, 'fetchAll']); //Fetch all questions

    //// Students endpoints///////
    Route::post('admin/create/student', [StudentsController::class, 'store']);
    Route::get('admin/show/student/{id}', [StudentsController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/student/update/{id}', [StudentsController::class, 'update']);
    Route::delete('delete/student/{id}', [StudentsController::class, 'destroy']);
    Route::get('admin/students', [StudentsController::class, 'fetchAll']); //Fetch all students

    ///////////ASSESSMENTS///////////////

    //Category crud endpoints///////
    Route::post('assess/create/category', [CategoryController::class, 'store']);
    Route::get('assess/show/category/{id}', [CategoryController::class, 'show']);
    Route::match(['put', 'patch'], 'assess/category/update/{id}', [CategoryController::class, 'update']);
    Route::delete('delete/category/{id}', [CategoryController::class, 'destroy']);
    Route::get('assess/categories', [CategoryController::class, 'fetchAll']); //Fetch all Categories

    //Test type crud endpoints///////
    Route::post('assess/create/testtype', [TestTypeController::class, 'store']);
    Route::get('assess/show/testtype/{id}', [TestTypeController::class, 'show']);
    Route::match(['put', 'patch'], 'assess/update/testtype/{id}', [TestTypeController::class, 'update']);
    Route::delete('delete/testtype/{id}', [TestTypeController::class, 'destroy']);
    Route::get('assess/testtypes', [TestTypeController::class, 'fetchAll']); //Fetch all Test types

    //Subject crud endpoints///////
    Route::post('assess/create/subject', [SubjectController::class, 'store']);
    Route::get('assess/show/subject/{id}', [SubjectController::class, 'show']);
    Route::match(['put', 'patch'], 'assess/update/subject/{id}', [SubjectController::class, 'update']);
    Route::delete('delete/assess/subject/{id}', [SubjectController::class, 'destroy']);
    Route::get('assess/subjects', [SubjectController::class, 'fetchAll']); //Fetch all subjects
    Route::get('assess/subjects/category/{id}', [SubjectController::class, 'subjectsByCat']); //Fetch all subjects by category

    //Year crud endpoints///////
    Route::post('assess/create/years', [YearController::class, 'store']);
    Route::get('assess/show/year/{id}', [YearController::class, 'show']);
    Route::match(['put', 'patch'], 'assess/update/year/{id}', [YearController::class, 'update']);
    Route::delete('delete/assess/year/{id}', [YearController::class, 'destroy']);
    Route::get('assess/years', [YearController::class, 'fetchAll']); //Fetch all years
    Route::get('assess/year/subject/{id}', [YearController::class, 'yearBySubject']); //Fetch years by subject id
    Route::get('assess/year/{id}', [YearController::class, 'yearById']); //Fetch years by year id

    //Assessment Questions crud endpoints///////
    Route::post('assess/create/bulk/questions', [QuestionController::class, 'storeBulk']); ///create bulk questions
    Route::post('assess/create/questions', [QuestionController::class, 'store']);
    Route::post('assess/create/bulk-solution/questions', [QuestionController::class, 'storeBulkSolution']); ///create bulk Solutions questions
    
    Route::post('assess/create/questionPassage', [QuestionController::class, 'storeQuestionPassage']);
    Route::get('assess/assign-question-to-Passage/{question_id}/{passage_id}', [QuestionController::class, 'updatePassageId']); /// Assign passage to question
     Route::get('assess/unassign-question-to-Passage/{question_id}', [QuestionController::class, 'unassignPassageId']); /// UnAssign passage to question
    Route::get('assess/get-questionPassage/{year_id}', [QuestionController::class, 'getPassageByYear']);
    Route::get('assess/show/question/{id}', [QuestionController::class, 'show']);
    Route::match(['put', 'patch'], 'assess/update/question/{id}', [QuestionController::class, 'update']);
    Route::delete('delete/assess/question/{id}', [QuestionController::class, 'destroy']);
    Route::get('assess/questions', [QuestionController::class, 'fetchAll']); //Fetch all questions
    Route::get('assess/questions/subject/{id}', [QuestionController::class, 'questionsBySub']); //Fetch all questions by subject id
    Route::get('assess/questions/year/{id}', [QuestionController::class, 'questionsByYear']); //Fetch all questions by year id
    
    //Quote CRUD operations

    Route::post('assess/create/quote', [QuoteController::class, 'store']); //create quotes
    Route::get('assess/show/quote/{id}', [QuoteController::class, 'show']);
    Route::post('assess/update/quote/{id}', [QuoteController::class, 'update']);
    Route::delete('delete/assess/quote/{id}', [QuoteController::class, 'destroy']);


    //////////////// ADMIN DASHBOARD MANAGEMENT ENDPOINTS /////////////////////

    //// user management endpoint
    Route::post('user-management/create/user', [AdminManagement::class, 'registerUser']); 
    //
    Route::get('user-management/show/user/{id}', [AdminManagement::class, 'show']);
    Route::post('user-management/update/user/{id}', [AdminManagement::class, 'update']);
    Route::delete('delete/user-management/user/{id}', [AdminManagement::class, 'destroy']);
    
    //// Registered users
    Route::get('user-management/users', [AdminManagement::class, 'fetchUserAndSearchByName']);

    //// filter registered users by date
    Route::get('user-management/users/filter-by-date',[AdminManagement::class,'filterByDate']);
    
    // Endpoint that returns the registered users in the last 24hrs
    Route::get('user-management/users/last-24-hours',[AdminManagement::class,'usersRegisteredLast24Hours']);
    
    // Endpoint that returns registered users in the last week
    Route::get('user-management/users/last-week',[AdminManagement::class,'usersRegisteredLastWeek']);
    
    Route::get('user-management/users/last-30-days',[AdminManagement::class,'usersRegisteredLast30Days']);
    
    
    ////<<<<<<<<<<<<<<<<<< PAYMENT MODULE >>>>>>>>>>>>>>>>>>>>>>>>>>>>

    Route::get('user-management/payment/payment-history',[AdminManagement::class,'paymentHistory']);
    Route::get('user-management/payment/filter-payment-date',[AdminManagement::class,'filterHistoryByDate']);
    Route::get('user-management/payment/search-payment-user',[AdminManagement::class,'searchPaymentByUser']);
    
    //// total payment
    Route::get('user-management/payment/payment-total',[AdminManagement::class,'subscriptionPaymentTotal']);
    Route::get('user-management/payment/payment-total-date',[AdminManagement::class,'subscriptionPaymentTotal1']);


    ////<<<<<<<<<<<<<<<< STUDY MODULE >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

    Route::get('user-management/study-module/no-of-questions',[AdminManagement::class,'totalNumberOfQuestions']);
    Route::get('user-management/study-module/total-subject-perclass/{class_id}',[AdminManagement::class,'totalSubjectsPerClass']);
    Route::get('user-management/study-module/total-questions-persubjectclass/{class_id}/{subject_id}',[AdminManagement::class,'totalNumberOfQuestionsPerSubjectPerClass']);
    Route::get('user-management/study-module/total-subjects-percategory/{category_id}',[AdminManagement::class,'totalSubjectsByCategory']);
    Route::get('user-management/study-module/total-questions-persubjectcategory/{category_id}/{subject_id}',[AdminManagement::class,'totalQuestionsPerSubjectByCategory']);


    /////<<<<<<<<<<<<<<< DASHBOARD ANALYTICS >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

    Route::get('user-management/study-module/total-test-taken-in-categories/{category_id}',[AdminManagement::class,'totalTestTakenInCategories']);
    Route::get('user-management/study-module/test-taken-list-with-student-info',[AdminManagement::class,'testTakenListWithStudentInfo']);
    Route::get('user-management/study-module/test-taken-with-student-info-in-order-of-topScorer',[AdminManagement::class,'testTakenWithStudentInfoInOrderOfTopScorer']);

    ///// GET TOTAL DOWNLOADS
    // Route::get('get-total-downloads', [DownloadController::class,'getTotalClicks']);


    //////////// SCHOOL ADMIN ENDPOINTS /////////

    //<<<<<<<<<<<<<< Teacher Endpoints >>>>>>>>>

    Route::post('school-management/create/teacher', [TeacherController::class, 'registerTeacher']); 
    Route::get('school-management/show/teacher/{teacher_id}', [TeacherController::class, 'showTeacher']);
    Route::post('school-management/update/teacher/{teacher_id}', [TeacherController::class, 'updateTeacher']);
    Route::delete('delete/school-management/teacher/{teacher_id}', [TeacherController::class, 'destroyTeacher']);
    
    //// fetch all teachers by school Id
    Route::get('school-management/teachers/{school_id}', [TeacherController::class, 'fetchAllTeacher']);

    //<<<<<<<<<<<<<<<< School Endpoints >>>>>>>>

   
    Route::post('school-management/create/school', [TeacherController::class, 'storeSchool']); 
    Route::get('school-management/show/school/{school_id}', [TeacherController::class, 'showSchool']);
    Route::post('school-management/update/school/{school_id}', [TeacherController::class, 'updateSchool']);
    Route::delete('delete/school-management/school/{school_id}', [TeacherController::class, 'destroySchool']);
    
   

    ///fetch students 
    
    //// filter registered students by date
    Route::get('school-management/students/filter-by-date/{school_id}',[TeacherController::class,'filterByDate']);
    
    // Endpoint that returns the registered users in the last 24hrs
    Route::get('school-management/students/last-24-hours/{school_id}',[TeacherController::class,'usersRegisteredLast24Hours']);
    
    // Endpoint that returns registered users in the last week
    Route::get('school-management/students/last-week/{school_id}',[TeacherController::class,'usersRegisteredLastWeek']);
    
    Route::get('school-management/students/last-30-days/{school_id}',[TeacherController::class,'usersRegisteredLast30Days']);


    Route::get('school-management/payment/payment-history/{school_id}',[TeacherController::class,'paymentHistory']);
    Route::get('school-management/payment/filter-payment-date/{school_id}',[TeacherController::class,'filterHistoryByDate']);
    Route::get('school-management/payment/search-payment-user/{school_id}',[TeacherController::class,'searchPaymentByUser']);
    
    //// total payment
    Route::get('school-management/payment/payment-total',[TeacherController::class,'subscriptionPaymentTotal']);
    Route::get('school-management/payment/payment-total-date',[TeacherController::class,'subscriptionPaymentTotal1']);


    ////<<<<<<<<<<<<<<<< STUDY MODULE >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

    Route::get('school-management/study-module/no-of-questions',[TeacherController::class,'totalNumberOfQuestions']);
    Route::get('school-management/study-module/total-subject-perclass/{class_id}',[TeacherController::class,'totalSubjectsPerClass']);
    Route::get('school-management/study-module/total-questions-persubjectclass/{class_id}/{subject_id}',[TeacherController::class,'totalNumberOfQuestionsPerSubjectPerClass']);
    Route::get('school-management/study-module/total-subjects-percategory/{category_id}',[TeacherController::class,'totalSubjectsByCategory']);
    Route::get('school-management/study-module/total-questions-persubjectcategory/{category_id}/{subject_id}',[TeacherController::class,'totalQuestionsPerSubjectByCategory']);


    /////<<<<<<<<<<<<<<< DASHBOARD ANALYTICS >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

    Route::get('school-management/study-module/total-test-taken-in-categories/{category_id}',[TeacherController::class,'totalTestTakenInCategories']);
    Route::get('school-management/study-module/test-taken-list-with-student-info',[TeacherController::class,'testTakenListWithStudentInfo']);
    Route::get('school-management/study-module/test-taken-with-student-info-in-order-of-topScorer',[TeacherController::class,'testTakenWithStudentInfoInOrderOfTopScorer']);

    //////////<<<<<<<<<Napps test questions>>>>

   

});


Route::post('auth/user/register', [UserController::class, 'register']);
Route::post('auth/user/login', [UserController::class, 'login']);

// Route::get('auth/users', [UserController::class, 'fetchalluser']);

Route::get('user/class-details/level/{classLevel}', [MobileController::class, 'fetchClassByLevelName']);
Route::post('user/verify-payment', [PayInfoController::class, 'verifyPayment']);




Route::middleware('auth:sanctum')->group(function () {
    
    ////fetch REQUESTS ///////////
    Route::get('user/recent/subjects', [MobileController::class, 'recentlyOpenedSubjects']);
    Route::get('user/subject/class/{id}', [MobileController::class, 'fetchSubjectByClass']);
    Route::get('user/topic/subject/{id}/{termId}', [MobileController::class, 'fetchTopicBySubject']);
    Route::get('user/content/topic/{id}', [MobileController::class, 'fetchContentByTopic']);
    Route::get('user/testquestion/topic/{id}', [MobileController::class, 'fetchTestQuestionByTopic']);
    Route::get('user/subject-details/topic/{id}', [MobileController::class, 'fetchSubjectTopicbyTopic']);
    Route::get('user/subject-details/subject/{id}', [MobileController::class, 'getSubjectDetailsById']);

    Route::post('user/update-select-option/{id}', [MobileController::class, 'createUserStudyMarkingTest']);
    Route::get('user/markings', [MobileController::class, 'fetchMarkings']);
    Route::get('user/result-score/{topicId}', [MobileController::class, 'resultScore']);

    /////get all userss 

    Route::get('user/allusers', [MobileController::class, 'getAllUsers']);

    ///// Progress Percentage

    Route::get('user/update-topic-progress/{topicId}/{subjectId}/{completedPercentage}', [MobileController::class, 'updateTopicProgress']);
    Route::get('user/get-topic-progress/{topicId}', [MobileController::class, 'getTopicProgress']); //// topic progress

    Route::get('user/get-subject-progress/{subjectId}', [MobileController::class, 'getSubjectProgress']); //// Subject progress


    ////// User CRUD///////
    Route::match(['put', 'patch'], 'auth/update/user', [UserController::class, 'update']);
    Route::delete('delete/user', [UserController::class, 'destroy']);
    Route::get('auth/user/profile', [UserController::class, 'show']);

    ////// Assessments //////

    Route::get('user/assessment-categories', [MobileController::class, 'getCategory']);
    Route::get('user/assessment-testTypes', [MobileController::class, 'getTestType']);
    Route::get('user/assessment-subjects/{categoryId}', [MobileController::class, 'getSubjectsByCategoryId']);
    Route::get('user/assessment-questionSet/{subjectId}/{noQues?}', [MobileController::class, 'getYearsBySubjectId']);
    Route::get('user/create-assessment-test/{testtype_id}/{yearId}/{subjectId}', [MobileController::class, 'getTest']);
    Route::get('user/user-assessment-questions/{testTaken_id}', [MobileController::class, 'getAssessmentQuestions']);
    Route::get('user/user-subject-year/{testTaken_id}', [MobileController::class, 'getSubjectAndYear']);
    Route::post('user/assessment-update-selection/{markingId}', [MobileController::class, 'UpdateUserSelection']);
    Route::get('user/assessment-score/{testtaken_id}', [MobileController::class, 'getAssessmentScore']);
    Route::get('user/allquestions', [MobileController::class, 'getAllQuestions']);

    ////// assessment review and report

    Route::get('user/assessment-review/{testtaken_id}', [MobileController::class, 'assessmentReview']);
    Route::get('user/assessment-report/{testtaken_id}', [MobileController::class, 'assessmentReport']);
    Route::get('user/userassessment/{testtaken_id}', [MobileController::class, 'userAssessmentMarking']);

    Route::get('user/user-assess-record-category/{cat_Id}', [MobileController::class, 'fetchTestTakenCategory']);
    Route::get('user/user-test-record-report', [MobileController::class, 'getTestsRecord']);
    // Route::get('user/user-test-record-report/{cat_id}', [MobileController::class, 'getTestsRecordByCatId']);
    Route::get('user/user-test-record-report/{cat_id}', [MobileController::class, 'getTestsRecordByCatId']);
    
    Route::get('user/user-test-report-category/{cat_Id}', [MobileController::class, 'assessmentReportByCat']);
    Route::get('user/user-best-subjects', [MobileController::class, 'getTop5SubjectsByScore']);

    //// study review
    Route::get('user/study-review', [MobileController::class, 'studyWeeklyReview']);
    Route::get('user/study-report', [MobileController::class, 'studyWeeklyReport']);    
    Route::get('user/study-review/{topic_id}', [MobileController::class, 'studyReview']);
    
    //// Quotes
    
    Route::get('user/quotes', [QuoteController::class, 'index']);
    
    /// payment
    
    Route::get('payment-status', [MobileController::class, 'checkPaymentStatus']);
    
    /// leaderboard
    
    Route::get('user/practice-test/{cat_Id}', [MobileController::class, 'getUsersWithHighestScoresByCategory']);
    
    Route::get('user/practice-test-sub/{cat_Id}/{subjectId}', [MobileController::class, 'getUsersWithScoresByCategoryAndSubjectId']);
    
    Route::get('user/study-module/{class_Id}', [MobileController::class, 'getUsersWithHighestScoresInClass']);
    
    Route::get('user/study-module-subject/{class_Id}/{subject_id}', [MobileController::class, 'getUsersWithHighestScoresInClassWithSubject']);
    
    Route::get('user/get-user-rank/{cat_id}', [MobileController::class, 'getActiveUserRank']);

    
    //// User feedback
    
    Route::post('user/feedback-store', [FeedbackController::class, 'store']);
    Route::get('user/get-feedback', [FeedbackController::class, 'index']);
    
    /// USER LOGIN HISTORY //
    
    Route::get('user/user-login-history', [MobileController::class, 'userLoginHistory']);

    //// REFERRAL COUNTS

    Route::get('user/user-referrals/{user_id}', [MobileController::class, 'getReferralCount']);
    Route::post('user/add-referral', [MobileController::class, 'addReferalCode']);

    /// USER POINTS

    Route::get('user/addPoints/{points}/{description}', [MobileController::class, 'addPoint']);
    Route::get('user/fetch-success-points/{userId}', [MobileController::class, 'fetchUserPoints']);
    Route::get('user/fetch-points-history/{userId}', [MobileController::class, 'fetchUserPointHistory']);

    /// USER CHATS

    Route::get('user/fetch-user-chats', [MobileController::class, 'fetchUserChats']);
    Route::get('user/save-chats/{message}/{response}', [MobileController::class, 'addChat']);
    Route::post('user/save-chats-user', [MobileController::class, 'addChatPost']);



});

Route::get('user/inserttestype-record', [MobileController::class, 'createTestType']);
////////// USER ROUTE ////////////////