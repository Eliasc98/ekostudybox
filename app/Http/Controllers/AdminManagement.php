<?php

namespace App\Http\Controllers;
use App\Models\Passage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Login;
use App\Models\AdminClass;
use App\Models\AdminTopic;
use App\Models\AdminContent;
use App\Models\AdminSubject;
use App\Models\AdminQuestion;
use App\Models\SubjectOpening;
use App\Models\Assessment\Year;
use App\Models\UserStudyMarking;
use App\Models\Referral;
use App\Models\UserTopicProgress;
use App\Models\Assessment\Subject;
use App\Models\AssessmentTakeTest;
use App\Models\MarkingResultScore;
use App\Models\PointsHistory;
use App\Models\Chat;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use App\Models\Assessment\Category;
use App\Models\Assessment\Question;
use App\Models\Assessment\TestType;
use App\Models\AssessmentTestTaken;
use App\Models\UserAssessmentScore;
use App\Models\UserSubjectProgress;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAssessmentMarking;
use App\Models\PayInfo;

use Illuminate\Http\Request;

class AdminManagement extends Controller
{
    //////// USER MANAGEMENT ENDPOINTS (CRUD)

    public function registerUser(Request $request)
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
            'phone_number' => 'nullable',
            'state' => 'required',
            'email' => 'required|email',
            'password'  =>  ['required', 'string'],
            'password_confirmation'  =>  'required|string',
            'admin_class_id' => 'nullable'
        ]);

        // Add the referral code to the data array

        $data['referal_code'] = $randomString;

        if ($request->password !== $request->password_confirmation) {
            return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
        }

        $userExists = User::where('email', $request->email)->first();

        if ($userExists) {
            return response()->json(['status' => 'error', 'message' => 'User already exists!'], 400);
        }

        $data['password'] = Hash::make($request->password);        
           
        $user = User::create($data);

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

    //// READ USER

    public function show($userid)
    {
        //
        $user = User::findOrFail($userid);

        if ($user) {

            $response = [
                'status' => 'success',
                'message' => 'User Information fetched successfully',
                'data' => $user
            ];

            return response()->json($response);

        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for User found'
            ];
            return response()->json($response, 404);
        }
    }

    //// UPDATE USER

    public function update(Request $request, $userid)
    {
        //
       
        $user = User::find($userid);

        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'phone_number' => 'nullable',
            'user_img' => 'nullable',
            'email' => 'nullable|email',
            'password'  =>  ['required', 'string'],
            'password_confirmation'  =>  'required|string',
            'admin_class_id' => 'nullable'
        ]);

        if ($request->password !== $request->password_confirmation) {
            return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
        }

        if ($request->user_img) {
            $userImg = auth()->user()->firstname . '.' . auth()->user()->lastname . '-'. $request->user_img->extension();
            $request->user_img->storeAs('public/files', $userImg);
            $userImgLink = URL('storage/files/' . $userImg);

            $user->update(
                ['user_img' => $userImgLink]
            );
        }

        
        $data =  $user->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'admin_class_id' => $request->admin_class_id,
            'password' => Hash::make($request->password)
        ]);



        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'User Profile updated successfully',
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

    //// DELETE USER
    public function destroy($id)
    {
        $userDel = User::findOrFail($userid);
        $del =  $userDel->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'User deleted successfully',
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


    /////// REGISTERED USERSS

    // Fetch All and search by name

    public function fetchUserAndSearchByName(Request $request)
    {
        $query = User::query();

        // Check if firstname or lastname search term is provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('firstname', 'like', "%$searchTerm%")
                ->orWhere('lastname', 'like', "%$searchTerm%");
        }

        $users = $query->get();

        if ($users) {
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

    // Filter registered users by date 

    public function filterByDate(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = User::query();

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

    public function usersRegisteredLast24Hours()
    {
        // Calculate the date and time 24 hours ago
        $date24HoursAgo = Carbon::now()->subHours(24);

        // Query users registered in the last 24 hours
        $users = User::where('created_at', '>=', $date24HoursAgo)->get();

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
    public function usersRegisteredLastWeek()
    {
        // Calculate the date and time 1 week ago
        $dateOneWeekAgo = Carbon::now()->subWeek();

        // Query users registered in the last week
        $users = User::where('created_at', '>=', $dateOneWeekAgo)->get();

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

    public function usersRegisteredLast30Days()
    {
        // Calculate the date and time 30 days ago
        $date30DaysAgo = Carbon::now()->subDays(30);

        $users = User::where('created_at', '>=', $date30DaysAgo)->get();

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

///////////////>>>>>>>>>>> PAYMENT MODULE <<<<<<<<<<<<<<


//* Payment history

public function paymentHistory()
{
    $paymentHistory = PayInfo::get();

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
//* Filter payment history by date

public function filterHistoryByDate(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Parse start and end dates using Carbon
    $startDate = Carbon::parse($startDate)->startOfDay();
    $endDate = Carbon::parse($endDate)->endOfDay();

    // Query payment history within the specified date range
    $paymentHistory = PayInfo::whereBetween('created_at', [$startDate, $endDate])->get();

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

public function searchPaymentByUser(Request $request)
{
    $searchTerm = $request->input('search');

    // Query payment history by user's first name or last name
    $paymentHistory = PayInfo::whereHas('user', function ($query) use ($searchTerm) {
        $query->where('firstname', 'like', "%$searchTerm%")
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

public function subscriptionPaymentTotal()
{
    // Query total payment amount under subscriptions
    $totalPayment = PayInfo::sum('amount');

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
public function totalNumberOfQuestions() {
    $data = AdminQuestion::count();

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

}
