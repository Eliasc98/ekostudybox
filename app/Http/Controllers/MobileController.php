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
use Illuminate\Http\Request;
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
use Illuminate\Support\Facades\Mail;

class MobileController extends Controller
{
    
    public function deleteUserView(){
        return view('deleteuser');
    }    
    
    public function deleteUserAccount(Request $request){
        
        $this->validate($request,['email'=> 'required']);
        
        $userMail = User::where('email', $request->email)->first();
        
        if($userMail){
            $userMail->delete();
            
            return back()->with('status', 'User Deleted Successfully');;
        }else{
            return back()->with('status', 'invalid email');
        }
        
        
    }
    
    public function fetchSubjectTopicbyTopic($id)
    {
        $subject = AdminTopic::select('admin_subjects.id', 'admin_subjects.subject_name', 'admin_topics.topics')
            ->join('admin_subjects', 'admin_topics.admin_subject_id', '=', 'admin_subjects.id')
            ->where('admin_topics.id', $id)
            ->get();

        if ($subject->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Data fetched successfully',
                'data' => $subject
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No data available'
            ];
            return response()->json($response, 404);
        }
    }

    public function fetchSubjectByClass($id)
    {
        //
        $subject = AdminSubject::select('id', 'subject_name', 'subject_img')->where('admin_class_id', $id)->orderBy('subject_name', 'asc')->get();

        if ($subject->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'subject fetched successfully',
                'data' => $subject
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

    public function getAllUsers()
    {
        $users = User::toBase()->get();

        if ($users) {
            $response = [
                'status' => 'success',
                'message' => 'Users list fetched successfully',
                'data' => $users
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch users list'
            ];

            return response()->json($response, 404);
        }
    }

    public function fetchTopicBySubject($id, $termId)
    {
        $topics = AdminTopic::select('id', 'admin_subject_id', 'topics', 'week')->where('admin_subject_id', $id)->where('term_id', $termId)->orderBy('week', 'asc')->get();

        if ($topics->count() > 0) {
            $uid = auth()->user()->id;
            $check = SubjectOpening::where('user_id', $uid)->where('admin_subject_id', $id)->first();

            if (!$check) {
                SubjectOpening::create([
                    'user_id' => $uid,
                    'admin_subject_id' => $id
                ]);
            }

            foreach ($topics as $topic) {
                $topicProgress =  UserTopicProgress::where('user_id', $uid)
                    ->where('admin_subject_id', $id)
                    ->where('admin_topic_id', $topic->id)
                    ->first();


                if ($topicProgress) {
                    $topic->topic_progress = $topicProgress->completed_percentage;
                } else {
                    $topic->topic_progress = null;
                }
            }



            $response = [
                'status' => 'success',
                'message' => 'subject fetched successfully',
                'data' => $topics
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

    public function fetchContentByTopic($id)
    {
        //
        $books = AdminContent::where('admin_topic_id', $id)->latest()->first();

        // $parsedContent = $this->parseContent($book->content);
        $topic = AdminTopic::find($id);
        if ($books) {
            $response = [
                'status' => 'success',
                'message' => 'content fetched successfully',
                'content' => $books,
                'no_of_questions' => $topic->num_of_test_questions
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

    public function fetchTestQuestionByTopic($id)
    {
        $columns = ['optionA', 'optionB', 'optionC', 'optionD'];

        $questions = AdminQuestion::select('id', 'questionText', 'correct_option', ...$columns)
            ->where('admin_topic_id', $id)
            ->inRandomOrder()
            ->get()
            ->map(function ($question) use ($columns) {
                $shuffledOptions = $question->only($columns);
                shuffle($shuffledOptions);
                $question->only($columns, $shuffledOptions);
                return $question;
            });

        if ($questions->isNotEmpty()) {
            $user_id = auth()->user()->id;

            foreach ($questions as $question) {
                $userStudyMarking = UserStudyMarking::updateOrCreate(
                    [
                        'user_id' => $user_id,
                        'admin_topic_id' => $id,
                        'admin_question_id' => $question->id,
                        'correct_option' => $question->correct_option
                    ]
                );

                $question->user_study_marking_id = $userStudyMarking->id;
                $question->selected_option = $userStudyMarking->selected_option;
            }

            $response = [
                'status' => 'success',
                'message' => 'questions view fetched successfully',
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

    public function recentlyOpenedSubjects()
    {
        $uid = auth()->user()->id;
        $recentOpenings = SubjectOpening::select('admin_subjects.id', 'admin_subjects.subject_name', 'admin_subjects.subject_img')
            ->join('admin_subjects', 'subject_openings.admin_subject_id', '=', 'admin_subjects.id')
            ->where('subject_openings.user_id', $uid)
            ->orderBy('subject_openings.opened_at', 'desc')
            ->take(3)
            ->get();

        if ($recentOpenings->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Recently opened subjects fetched successfully',
                'data' => $recentOpenings,
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No recently opened subjects available',
            ];
            return response()->json($response, 404);
        }
    }

    public function createUserStudyMarkingTest($id, Request $request)
    {

        try {
            $marking = UserStudyMarking::findOrFail($id);

            $marking->update(
                [
                    'selected_option' => $request->selected_option
                ]
            );


            $response = [
                'status' => 'success',
                'message' => 'Marking Updated Successfully',
            ];
            return response()->json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Database Error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function fetchClassByLevelName($classLevel)
    {
        $class = AdminClass::where('class_level', $classLevel)->orderBy('class_level', 'asc')->get();

        if ($class->count() > 0) {

            $response = [
                'status' => 'success',
                'message' => 'Class-Levels fetched successfully',
                'data' => $class
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No class-level available'
            ];
            return response()->json($response, 404);
        }
    }

    public function getSubjectDetailsById($id)
    {
        $subject = AdminSubject::where('id', $id)->get();

        if ($subject->count() > 0) {

            $response = [
                'status' => 'success',
                'message' => 'subject details fetched successfully',
                'data' => $subject
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No subject available'
            ];
            return response()->json($response, 404);
        }
    }

    public function fetchMarkings()
    {
        $data = UserStudyMarking::get();

        if ($data->count() > 0) {

            $response = [
                'status' => 'success',
                'message' => 'markings details fetched successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No markings available'
            ];
            return response()->json($response, 404);
        }
    }

    public function resultScore($topicId)
    {
        $user_id = auth()->user()->id;

        $userStudyMarkings = UserStudyMarking::where('user_id', $user_id)
            ->where('admin_topic_id', $topicId)
            ->get();

        if ($userStudyMarkings->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No records found for the specified topic and user.',
            ], 404);
        }

        $totalQuestions = $userStudyMarkings->count();
        $correctlyAnswered = $userStudyMarkings->filter(function ($marking) {
            return $marking->selected_option === $marking->correct_option;
        })->count();

        $scorePercentage = ($correctlyAnswered / $totalQuestions) * 100;

        // Loop through each userStudyMarking and update or create a MarkingResultScore
        foreach ($userStudyMarkings as $marking) {
            MarkingResultScore::updateOrCreate(
                [
                    'user_study_marking_id' => $marking->id,
                ],
                [
                    'score' => $scorePercentage,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Score calculated successfully',
            'data' => [
                'total_questions' => $totalQuestions,
                'correctly_answered' => $correctlyAnswered,
                'score_percentage' => $scorePercentage,
            ],
        ]);
    }

    /// progress bar


    public function getSubjectProgress($subjectId)
    {
        $user = auth()->user();

        $progress = UserSubjectProgress::where(['user_id' => $user->id, 'admin_subject_id' => $subjectId])->first();

        if ($progress) {
            return response()->json(['status' => 'success', 'message' => 'Progress fetched successfully', 'data' => $progress]);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Progress not found'], 404);
        }
    }

    public function updateTopicProgress($topicId, $subjectId, $completedPercentage)
    {
        $user = auth()->user();

        UserTopicProgress::updateOrCreate(
            ['user_id' => $user->id, 'admin_topic_id' => $topicId, 'admin_subject_id' => $subjectId],
            ['completed_percentage' => $completedPercentage]
        );

        // Recalculate the average topic progress for the subject
        $averageTopicProgress = UserTopicProgress::where('user_id', $user->id)
            ->where('admin_subject_id', $subjectId)
            ->sum('completed_percentage') ?? 0;
            
        $NoOftopics = AdminTopic::where('admin_subject_id', $subjectId)->count();
        
        $subjectPercentage = ($averageTopicProgress/ $NoOftopics) * 100;

        $progress = UserSubjectProgress::updateOrCreate(
            ['user_id' => $user->id, 'admin_subject_id' => $subjectId],
            ['completed_percentage' => $subjectPercentage]
        );

        return response()->json(['status' => 'success', 'message' => 'Topic progress updated successfully']);
    }

    public function getTopicProgress($topicId)
    {
        $user = auth()->user();

        $progress = UserTopicProgress::where(['user_id' => $user->id, 'admin_topic_id' => $topicId])->first();

        if ($progress) {
            return response()->json(['status' => 'success', 'message' => 'Topic progress fetched successfully', 'data' => $progress]);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Topic progress not found'], 404);
        }
    }

    public function averageTopicProgress($subjectId)
    {
        $user = auth()->user();

        // Get the average topic progress for the user
        $averageTopicProgress = UserTopicProgress::where('user_id', $user->id)
            ->where('admin_subject_id', $subjectId)
            ->avg('completed_percentage') ?? 0;

        return $averageTopicProgress;
    }

    ///////////////// ASSESSMENT MODULE /////////////////

    public function getCategory()
    {
        //
        $cat =  Category::get();

        if ($cat->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'categories fetched successfully',
                'data' => $cat
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no category to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function getTestType()
    {
        //
        $testType = TestType::whereNull('assoc_cat_id')
                    ->orWhere('assoc_cat_id', '')
                    ->get();

        if ($testType->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'test-type fetched successfully',
                'data' => $testType
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no test-type to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function getSubjectsByCategoryId($catId)
    {
        //
        $subjects = Subject::where('category_id', $catId)->get();

        if ($subjects->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'subjects for Category fetched successfully',
                'data' => $subjects
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'no subject to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function getYearsBySubjectId($subjectId, $noQues = null)
    {
            $query = Year::where('subject_id', $subjectId)->orderBy('yearname', 'desc');
        
            if ($noQues !== null) {
                $query = $query->limit($noQues);
            }
        
            $years = $query->get();
        
            if ($years->count() > 0) {
                $response = [
                    'status' => 'success',
                    'message' => 'Question-set for subject fetched successfully',
                    'data' => $years
                ];
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'failed',
                    'message' => 'No Question-set to fetch'
                ];
                return response()->json($response, 404);
            }
    }


    public function getTest($testtype_id, $yearId, $subjectId)
    {
        $user_id = auth()->user()->id;
        $testtype = TestType::find($testtype_id);
        $testTaken = AssessmentTestTaken::create([
            'user_id' => $user_id,
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
        $user_id = auth()->user()->id;
        $testTaken = AssessmentTestTaken::find($testTaken_id);
        $columns = ['optionA', 'optionB', 'optionC', 'optionD'];

        $questions = Question::select('id', 'questionText', 'correct_option', 'image', 'passage_id', ...$columns)
            ->with('passage')
            ->where('year_id', $testTaken->year_id)
            ->where('subject_id', $testTaken->subject_id)
            ->inRandomOrder()
            ->limit($testTaken->num_question)
            ->get()
            ->map(function ($question) use ($columns) {
                $shuffledOptions = $question->only($columns);
                shuffle($shuffledOptions);
                $question->only($columns, $shuffledOptions);
                return $question;
            });

        if ($questions->isNotEmpty()) {

            foreach ($questions as $question) {
                $userStudyMarking = UserAssessmentMarking::updateOrCreate(
                    [
                        'user_id' => $user_id,
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
    
    public function getSubjectAndYear($testTaken_id)
{
        $testTaken = AssessmentTestTaken::find($testTaken_id);
    
        if ($testTaken) {
            $year = Year::find($testTaken->year_id);
            $subject = Subject::find($testTaken->subject_id);
    
            if ($year && $subject) {
                $response = [
                    'status' => 'success',
                    'message' => 'Subject and year fetched successfully',
                    'year_name' => $year->yearname,
                    'subject_name' => $subject->subjectname,
                ];
    
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'failed',
                    'message' => 'Year or subject not found',
                ];
    
                return response()->json($response, 404);
            }
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'TestTaken not found',
            ];
    
            return response()->json($response, 404);
        }
}


    public function getAllQuestions()
    {

        $columns = ['optionA', 'optionB', 'optionC', 'optionD'];

        $questions = Question::inRandomOrder()
            ->get();

        if ($questions->isNotEmpty()) {

            $response = [
                'status' => 'success',
                'message' => 'questions view fetched successfully',
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

    public function UpdateUserSelection($id, Request $request)
    {
        //
        try {
            $marking = UserAssessmentMarking::findOrFail($id);

            $marking->update(
                [
                    'selected_option' => $request->selected_option
                ]
            );


            $response = [
                'status' => 'success',
                'message' => 'Marking Updated Successfully',
            ];
            return response()->json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Database Error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function getAssessmentMarking()
    {
        //
    }

    public function getAssessmentScore($tt_id)
    {
        //
        $user_id = auth()->user()->id;
        $testTaken = AssessmentTestTaken::find($tt_id);
        $userStudyMarkings = UserAssessmentMarking::where('assessment_test_taken_id', $tt_id)
            ->get();

        if ($userStudyMarkings->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No marking records found for the specified assessment',
            ], 404);
        }

        $totalQuestions = $userStudyMarkings->count();
        $correctlyAnswered = $userStudyMarkings->filter(function ($marking) {
            return $marking->selected_option === $marking->correct_option;
        })->count();

        $scorePercentage = ($correctlyAnswered / $totalQuestions) * 100;



        UserAssessmentScore::updateOrCreate(
            [
                'assessment_test_taken_id' => $tt_id,
                'score' => $scorePercentage
            ]
        );


        $num_question = UserAssessmentMarking::where('assessment_test_taken_id', $tt_id)
            ->count();

        $correctlyAnswered = UserAssessmentMarking::where('assessment_test_taken_id', $tt_id)
            ->whereColumn('correct_option', '=', 'selected_option')
            ->count();


        $wronglyAnswered = UserAssessmentMarking::where('assessment_test_taken_id', $tt_id)
            ->whereColumn('correct_option', '!=', 'selected_option')
            ->count();

        AssessmentTakeTest::updateOrCreate([
            'user_id' => $user_id,
            'year_id' => $testTaken->year_id,
            'subject_id' => $testTaken->subject_id,
            'assessment_test_taken_id' => $tt_id,
            'num_question' => $num_question,
            'correctly_answ' => $correctlyAnswered,
            'wrongly_answ' => $wronglyAnswered
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Score calculated successfully',
            'data' => [
                'total_questions' => $totalQuestions,
                'correctly_answered' => $correctlyAnswered,
                'score_percentage' => $scorePercentage,
            ],
        ]);
    }

    public function createTestType()
    {
        $speedtest =  TestType::updateOrCreate([
            'test_type_name' => 'Speed Test',
            'duration' => 10,
            'num_of_questions' => 10
        ]);

        $standardtest = TestType::updateOrCreate([
            'test_type_name' => 'Standard Test',
            'duration' => 45,
            'num_of_questions' => 40
        ]);

        if ($speedtest && $standardtest) {
            $response = [
                'status' => 'success',
                'message' => 'Speedtest and Standard test create or  Updated Successfully',
                'data' => [$standardtest, $speedtest]
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'Speedtest and Standard test not created',
            ];
            return response()->json($response, 404);
        }
    }

    ///////////// REVIEW AND REPORT FOR ASSESSMENT/////

    public function assessmentReview($tt_id)
    {
        $user_id = auth()->user()->id;
        

        $userAssessmentMarkings = DB::table('user_assessment_markings')
            ->select(
                'questions.questionText',
                'questions.explanation',
                'questions.image',
                'questions.year_id',
                'user_assessment_markings.selected_option',
                'user_assessment_markings.correct_option'
            )
            ->where('user_assessment_markings.assessment_test_taken_id', $tt_id)
            ->join('questions', 'user_assessment_markings.question_id', '=', 'questions.id')
            ->get();


        if ($userAssessmentMarkings->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Assessment review fetched successfully',
                'data' => $userAssessmentMarkings
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No review to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function assessmentReport($tt_id)
    {

        $user_id = auth()->user()->id;

        $userAssessmentTT = AssessmentTakeTest::where('user_id', $user_id)
            ->where('assessment_test_taken_id', $tt_id)
            ->get()
            ->groupBy('assessment_test_taken_id');

        if ($userAssessmentTT->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Assessment report fetched successfully',
                'data' => $userAssessmentTT->toArray(),
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No report to fetch',
            ];
            return response()->json($response, 404);
        }
    }
    
     public function assessmentReportByCat($cat_Id)
    {
       $uid = auth()->user()->id;
        $cat = AssessmentTakeTest::where('user_id', $uid)->whereHas('subject', function ($query) use ($cat_Id) {
           $query->where('category_id', $cat_Id);
       })->get()->groupBy('assessment_test_taken_id');

        if ($cat->isNotEmpty()) {
             $response = [
                'status' => 'success',
                 'message' => 'User test records fetched for category successfully',
                 'data' => $cat
           ];
             return response()->json($response);
         } else {
             $response = [
                'status' => 'failed',
                 'message' => 'No record the category specified'
             ];
            return response()->json($response, 404);
         }
    }
    
      


    //////// STUDY WEEKLY REVIEW

    public function studyWeeklyReport()
    {
        $user_id = auth()->user()->id;

        // Calculate the start and end date for the past 7 days
        $startDate = Carbon::now()->subDays(7)->toDateString();
        $endDate = Carbon::now()->toDateString();

        $userStudyMarkings = UserStudyMarking::where('user_id', $user_id)
            ->join('admin_questions', function ($join) {
                $join->on('user_study_markings.admin_topic_id', '=', 'admin_questions.admin_topic_id');
            })
            ->whereBetween('user_study_markings.created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('admin_topic_id');

        if ($userStudyMarkings->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Study review fetched successfully for the past 7 days',
                'data' => $userStudyMarkings
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No review to fetch for the past 7 days'
            ];
            return response()->json($response, 404);
        }
    }

    public function studyWeeklyReview()
    {
        $user_id = auth()->user()->id;

        // Calculate the start and end date for the past 7 days
        $startDate = Carbon::now()->subDays(7)->toDateString();
        $endDate = Carbon::now()->toDateString();

        $userStudyMarkings = DB::table('user_study_markings')->select('admin_questions.questionText', 'admin_questions.correct_option', 'admin_questions.explanation', 'user_study_markings.selected_option')
            ->where('user_id', $user_id)
            ->join('admin_questions', function ($join) {
                $join->on('user_study_markings.admin_topic_id', '=', 'admin_questions.admin_topic_id');
            })
            ->whereBetween('user_study_markings.created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('admin_topic_id');

        if ($userStudyMarkings->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Study review fetched successfully for the past 7 days',
                'data' => $userStudyMarkings
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No review to fetch for the past 7 days'
            ];
            return response()->json($response, 404);
        }
    }

    ///// getting the number of tests taken by user
    
    public function fetchTestTakenCategory($cat_Id)
    {
        $uid = auth()->user()->id;
        $cats = AssessmentTakeTest::where('user_id', $uid)->whereHas('subject', function ($query) use ($cat_Id) {
            $query->where('category_id', $cat_Id);
        })->get();

        if ($cats->isNotEmpty()) {
            
           $cats->map(function ($test) {
            
                $test->subject_name = $test->subject->subjectname;
                $test->year_name =$test->year->yearname;
        });
            $response = [
                'status' => 'success',
                'message' => 'User test records fetched for category successfully',
                'data' => $cats->makeHidden(['year_id', 'subject_id'])
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No record the category specified'
            ];
            return response()->json($response, 404);
        }
    }

    public function getTestsRecord()
    {
        //
        $uid = auth()->user()->id;
        $userTakeTestRecord = AssessmentTakeTest::where('user_id', $uid)->get()->groupBy('assessment_test_taken_id');
        $userTakeTestcount = AssessmentTakeTest::where('user_id', $uid)->count();
        $noOftestsFailed = AssessmentTakeTest::where('user_id', $uid)->whereColumn('wrongly_answ', '>', 'correctly_answ')->count();
        $noOfTestPassed = AssessmentTakeTest::where('user_id', $uid)->whereColumn('correctly_answ', '>', 'wrongly_answ')->count();

        if ($userTakeTestRecord->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'User test records fetched successfully',
                'data' => ['No_of_tests_taken' => $userTakeTestcount, 'No_of_tests_failed' => $noOftestsFailed, 'No_of_tests_passed' => $noOfTestPassed]
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No record to fetch'
            ];
            return response()->json($response, 404);
        }
    }
    
    public function getTestsRecordByCatId($cat_Id)
    {
        //
        $uid = auth()->user()->id;
        $userTakeTestRecord = AssessmentTakeTest::where('user_id', $uid)->get()->groupBy('assessment_test_taken_id');
        $userTakeTestcount = AssessmentTakeTest::where('user_id', $uid)->whereHas('subject', function ($query) use ($cat_Id) {
            $query->where('category_id', $cat_Id);
        })->count();
        $noOftestsFailed = AssessmentTakeTest::where('user_id', $uid)->whereHas('subject', function ($query) use ($cat_Id) {
            $query->where('category_id', $cat_Id);
        })->whereColumn('wrongly_answ', '>', 'correctly_answ')->count();
        $noOfTestPassed = AssessmentTakeTest::where('user_id', $uid)->whereHas('subject', function ($query) use ($cat_Id) {
            $query->where('category_id', $cat_Id);
        })->whereColumn('correctly_answ', '>', 'wrongly_answ')->count();

        if ($userTakeTestRecord->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'User test records fetched successfully',
                'data' => ['No_of_tests_taken' => $userTakeTestcount, 'No_of_tests_failed' => $noOftestsFailed, 'No_of_tests_passed' => $noOfTestPassed]
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No record to fetch'
            ];
            return response()->json($response, 404);
        }
    }
    
   public function getTop5SubjectsByScore()
    {
        $userId = auth()->user()->id;
        $topSubjects = DB::table('user_assessment_scores')
            ->join('assessment_test_takens', 'user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id')
            ->join('subjects', 'assessment_test_takens.subject_id', '=', 'subjects.id')
            ->join('years', 'assessment_test_takens.year_id', '=', 'years.id')
            ->where('assessment_test_takens.user_id', $userId)
            ->orderByDesc('user_assessment_scores.score')
            ->groupBy('assessment_test_takens.subject_id', 'subjects.subjectname', 'years.yearname','user_assessment_scores.score')
            ->take(5)
            ->select('subjects.subjectname', 'years.yearname', 'user_assessment_scores.score')
            ->get();
    
        if ($topSubjects->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Best Subjects fetched successfully',
                'data' => $topSubjects  
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No assessment to fetch best subjects'
            ];
            return response()->json($response, 404);
        }
    }


    ///// testing code to check if $tt_id exist in userassessmentmarking table
    public function userAssessmentMarking($tt_id)
    {
        $getUserAssessment = UserAssessmentMarking::where('assessment_test_taken_id', $tt_id)->get();

        if ($getUserAssessment->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'user assessment fetched successfully for the past 7 days',
                'data' => $getUserAssessment
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No assessemnt to fetch for the past 7 days'
            ];
            return response()->json($response, 404);
        }
    }
    
    public function checkPaymentStatus(){
        $uid = auth()->user()->id;
        
        $fetchPaymentStatus = PayInfo::where('user_id', $uid)->first(['confirmation']);

        if ($fetchPaymentStatus) {
            $response = [
                'status' => 'success',
                'message' => 'User Payment status fetched successfully',
                'data' => $fetchPaymentStatus
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'User has not attempted any payment'
            ];
            return response()->json($response, 404);
        }
    }
    
    //// Leader dashboard
    
    public function getUsersWithHighestScoresByCategory($categoryId)
    {
        $highestScores = DB::table('user_assessment_scores')
            ->join('assessment_test_takens', 'user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id')
            ->join('subjects', 'assessment_test_takens.subject_id', '=', 'subjects.id')
            ->join('categories', 'subjects.category_id', '=', 'categories.id')
            ->where('categories.id', $categoryId)
            ->select(
                'assessment_test_takens.subject_id',
                DB::raw('MAX(user_assessment_scores.score) as highest_score')
            )
            ->groupBy('assessment_test_takens.subject_id');
    
        $usersWithHighestScores = DB::table('user_assessment_scores')
            ->join('assessment_test_takens', 'user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id')
            ->join('subjects', 'assessment_test_takens.subject_id', '=', 'subjects.id')
            ->join('categories', 'subjects.category_id', '=', 'categories.id')
            ->joinSub($highestScores, 'highest_scores', function ($join) {
                $join->on('user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id');
                $join->on('user_assessment_scores.score', '=', 'highest_scores.highest_score');
            })
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->select(
                'users.firstname as firstname',
                'users.lastname as lastname',
                'users.state as state',
                'assessment_test_takens.user_id',
                'assessment_test_takens.num_question',
                'user_assessment_scores.score',
                'assessment_test_takens.subject_id',
                'subjects.subjectname',
                'categories.cat_name'
            )
            ->orderByDesc('user_assessment_scores.score')
            ->get();
    
        if ( $usersWithHighestScores->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Data fetched successfully',
                'data' =>  $usersWithHighestScores
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No data to fetch'
            ];
            return response()->json($response, 404);
        }
    }
    
    
    public function getUsersWithScoresByCategoryAndSubjectId($categoryId, $subjectId)
    {
        $usersWithScores = DB::table('user_assessment_scores')
            ->join('assessment_test_takens', 'user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id')
            ->join('assessment_take_tests', 'assessment_take_tests.assessment_test_taken_id', '=', 'assessment_test_takens.id')
            ->join('subjects', 'assessment_test_takens.subject_id', '=', 'subjects.id')
            ->join('categories', 'subjects.category_id', '=', 'categories.id')
            ->where('categories.id', $categoryId)
            ->where('subjects.id', $subjectId)
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->select(
                'users.firstname as firstname',
                'users.lastname as lastname',
                'users.state as state',
                'user_assessment_scores.score as percentage_score',
                'assessment_take_tests.correctly_answ',
                'assessment_test_takens.num_question as num_of_test_questions',
                'subjects.subjectname as subject_name',
                'categories.cat_name'
            )
            ->distinct('assessment_test_takens.user_id') 
            ->orderByDesc('user_assessment_scores.score')
            ->take(10)
            ->get();
    
        if ($usersWithScores->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'Data fetched successfully',
                'data' =>  $usersWithScores
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No data to fetch'
            ];
            return response()->json($response, 404);
        }
    }

    public function getActiveUserRank($categoryId)
    {
        $highestScores = DB::table('user_assessment_scores')
            ->join('assessment_test_takens', 'user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id')
            ->join('subjects', 'assessment_test_takens.subject_id', '=', 'subjects.id')
            ->join('categories', 'subjects.category_id', '=', 'categories.id')
            ->where('categories.id', $categoryId)
            ->select(
                'assessment_test_takens.subject_id',
                DB::raw('MAX(user_assessment_scores.score) as highest_score')
            )
            ->groupBy('assessment_test_takens.subject_id');

        $usersWithHighestScores = DB::table('user_assessment_scores')
            ->join('assessment_test_takens', 'user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id')
            ->join('subjects', 'assessment_test_takens.subject_id', '=', 'subjects.id')
            ->join('categories', 'subjects.category_id', '=', 'categories.id')
            ->joinSub($highestScores, 'highest_scores', function ($join) {
                $join->on('user_assessment_scores.assessment_test_taken_id', '=', 'assessment_test_takens.id');
                $join->on('user_assessment_scores.score', '=', 'highest_scores.highest_score');
            })
            ->join('users', 'assessment_test_takens.user_id', '=', 'users.id')
            ->select(
                'users.firstname as firstname',
                'users.lastname as lastname',
                'users.state as state',
                'assessment_test_takens.user_id',
                'assessment_test_takens.num_question',
                'user_assessment_scores.score',
                'assessment_test_takens.subject_id',
                'subjects.subjectname',
                'categories.cat_name'
            )
            ->orderByDesc('user_assessment_scores.score')
            ->get();

        // Find the rank of the active user
        $activeUserRank = null;
        $userId = auth()->user()->id;
        foreach ($usersWithHighestScores as $key => $user) {
            if ($user->user_id == $userId) {
                $activeUserRank = $key + 1; // Adding 1 to make it 1-based rank
                break;
            }
        }

        if ($activeUserRank !== null) {
            $response = [
                'status' => 'success',
                'message' => 'Active user rank fetched successfully',
                'data' => [
                    'active_user_id' => $userId,
                    'active_user_rank' => $activeUserRank
                ]
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'Active user rank not found'
            ];
            return response()->json($response, 404);
        }
    }


    
//   public function getUsersWithHighestScoresInClass($classId)
//     {
//         // Subquery to get highest scores for each subject in the class
//         $highestScoresSubquery = DB::table('user_study_markings')
//             ->join('admin_topics', 'user_study_markings.admin_topic_id', '=', 'admin_topics.id')
//             ->join('marking_result_scores', 'user_study_markings.id', '=', 'marking_result_scores.user_study_marking_id')
//              ->join('users', 'user_study_markings.user_id', '=', 'users.id')
//             ->where('users.admin_class_id', $classId)
//             ->select(
//                 'admin_topics.admin_subject_id',
//                 DB::raw('MAX(marking_result_scores.score) as highest_score')
//             )
//             ->groupBy('admin_topics.admin_subject_id');
    
//         // Main query to get user details with the highest scores for each subject
//         $usersWithHighestScores = DB::table('marking_result_scores')
//             ->join('admin_topics', 'user_study_markings.admin_topic_id', '=', 'admin_topics.id')
//             ->join('users', 'user_study_markings.user_id', '=', 'users.id')
//             ->join('admin_subjects', 'admin_topics.admin_subject_id', '=', 'admin_subjects.id')
//             ->joinSub($highestScoresSubquery, 'highest_scores', function ($join) {
//                 $join->on('marking_result_scores.score', '=', 'highest_scores.highest_score');
//             })
//             ->join('admin_classes', 'users.admin_class_id', '=', 'admin_classes.id')
//             ->select(
//                 'users.firstname as first_name',
//                 'users.lastname as last_name',
//                 'users.state as user_state',
//                 'admin_classes.class_name',
//                 'admin_subjects.subject_name',
//                 'marking_result_scores.score'
//             )
//             ->orderByDesc('marking_result_scores.score')
//             ->get();
    
//         // Response handling
//         if ($usersWithHighestScores->count() > 0) {
//             $response = [
//                 'status' => 'success',
//                 'message' => 'Data fetched successfully',
//                 'data' => $usersWithHighestScores
//             ];
//             return response()->json($response);
//         } else {
//             $response = [
//                 'status' => 'failed',
//                 'message' => 'No data to fetch'
//             ];
//             return response()->json($response, 404);
//         }
//     }

    public function getUsersWithHighestScoresInClass($classId)
    {
        // $user_id = auth()->user()->id;

        

        // if ($userStudyMarkings->isEmpty()) {
        //     return response()->json([
        //         'status' => 'failed',
        //         'message' => 'No records found for the specified topic and user.',
        //     ], 404);
        // }

        // $totalQuestions = $userStudyMarkings->count();
        // $correctlyAnswered = $userStudyMarkings->filter(function ($marking) {
        //     return $marking->selected_option === $marking->correct_option;
        // })->count();

        // $scorePercentage = ($correctlyAnswered / $totalQuestions) * 100;

        $topScorers = User::join('user_study_markings', 'users.id', '=', 'user_study_markings.user_id')
        ->join('marking_result_scores', 'user_study_markings.id', '=', 'marking_result_scores.user_study_marking_id')
        ->join('admin_topics', 'user_study_markings.admin_topic_id', '=', 'admin_topics.id')
        ->join('admin_subjects', 'admin_topics.admin_subject_id', '=', 'admin_subjects.id')
        ->where('users.admin_class_id', $classId)
        ->select(
            'users.firstname',
            'users.lastname',
            'users.state',
            'marking_result_scores.score',
            'admin_subjects.subject_name',
            'admin_topics.num_of_test_questions',
            \DB::raw('SUM(CASE WHEN user_study_markings.selected_option = user_study_markings.correct_option THEN 1 ELSE 0 END) as correctlyAnswered')
        )
        ->orderByDesc('marking_result_scores.score')
        ->limit(10)
        ->groupBy('users.id', 'users.firstname', 'users.lastname', 'users.state', 'marking_result_scores.score', 'admin_subjects.subject_name', 'admin_topics.num_of_test_questions')
        ->get();


            
            
            if ($topScorers->count() > 0) {
                $response = [
                    'status' => 'success',
                    'message' => 'Data fetched successfully',
                    'data' => $topScorers
                ];
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'failed',
                    'message' => 'No data to fetch'
                ];
                return response()->json($response, 404);
            }
            
    }
    
    public function getUsersWithHighestScoresInClassWithSubject($classId, $subjectId)
    {
        $topScorers = User::join('user_study_markings', 'users.id', '=', 'user_study_markings.user_id')
            ->join('marking_result_scores', 'user_study_markings.id', '=', 'marking_result_scores.user_study_marking_id')
            ->join('admin_topics', 'user_study_markings.admin_topic_id', '=', 'admin_topics.id')
            ->join('admin_subjects', 'admin_topics.admin_subject_id', '=', 'admin_subjects.id')
            ->where('users.admin_class_id', $classId)
            ->where('admin_subjects.id', $subjectId)
            ->select('users.firstname', 'users.lastname', 'users.state', 'marking_result_scores.score', 'admin_subjects.subject_name', 'admin_topics.num_of_test_questions')
            ->orderByDesc('marking_result_scores.score')
            ->limit(10)
            ->get();
            
            if ($topScorers->count() > 0) {
                $response = [
                    'status' => 'success',
                    'message' => 'Data fetched successfully',
                    'data' => $topScorers
                ];
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'failed',
                    'message' => 'No data to fetch'
                ];
                return response()->json($response, 404);
            }
            
    }

    public function subjectRank(){
        //
    }

    ///////// User Login History///

    public function getNoOfUsers(){
        $response = [
            'status' => 'success',
            'message' => 'no of users fetched successfully',
            'data' => User::all()->count() ?? 0 // Return 0 if no users
        ];
        
        return response()->json($response);
    }

    public function userLoginHistory($uid){
        $userLogin = Login::where('user_id', $uid)->get();
        $userLogin->number_of_logins = Login::where('user_id', $uid)->count();
        
        if ($userLogin) {
            $response = [
                'status' => 'success',
                'message' => 'User Login History fetched successfully',
                'data' => $userLogin
                
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

    /////// Endpoint for referral code

    public function getReferralCount($user_id){
        
        $referralCount = Referral::where('user_id', $user_id)->count();
    
        if ($referralCount > 0) { 
            $response = [
                'status' => 'success',
                'message' => 'Number of referrals fetched successfully',
                'data' => $referralCount ?? 0 
            ];
    
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No referrals for user'
            ];
    
            return response()->json($response, 404);
        }
    }
    
    /// Number of Downloads

    /// Success Points

    public function addPoint($points, $description) {
        $user = auth()->user();
    
        if (!$user) {
            $response = [
                'status' => 'failed',
                'message' => 'User not found'
            ];
    
            return response()->json($response, 404);
        }
        
        $user->points += $points;
        $user->save();

        // Assuming you have a 'points_history' table to store points transactions
            $pointsHistory = new PointsHistory();
            $pointsHistory->user_id = $user->id;
            $pointsHistory->points = $points;
            $pointsHistory->descriptions = $description;
            $pointsHistory->save();
    
        $response = [
            'status' => 'success',
            'message' => 'Points added successfully',
            'data' => [
                'user_id' => $user->id,
                'points' => $user->points
            ]
        ];
    
        return response()->json($response);
    }
    

    public function fetchUserPoints($userId) {
        
        $user = User::find($userId);

        $userPoints = $user->points;

        if ($userPoints) { 
            $response = [
                'status' => 'success',
                'message' => 'user points fetched successfully',
                'data' => $userPoints
            ];
    
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No points for user'
            ];
    
            return response()->json($response, 404);
        }
    }

    public function fetchUserPointHistory($userId) {
        //
        $pointsHistory = PointsHistory::where('user_id', $userId)->get();


        if (!$pointsHistory->isEmpty()) { 
            $response = [
                'status' => 'success',
                'message' => 'user points-history fetched successfully',
                'data' => $pointsHistory
            ];
    
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No points for user'
            ];
    
            return response()->json($response, 404);
        }
    }


    ///// CHATS 

    public function fetchUserChats() {
        //
        $userId = auth()->user()->id;
        $chats = Chat::where('user_id', $userId)->get();


        if (!$chats->isEmpty()) { 
            $response = [
                'status' => 'success',
                'message' => 'user chats fetched successfully',
                'data' => $chats
            ];
    
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No chats for user'
            ];
    
            return response()->json($response, 404);
        }
    }

    public function addChat($message, $response) {
        $user = auth()->user();
    
        if (!$user) {
            $response = [
                'status' => 'failed',
                'message' => 'User not found'
            ];
    
            return response()->json($response, 404);
        }
        

        // Assuming you have a 'points_history' table to store points transactions
            $chat = new Chat();
            $chat->user_id = $user->id;
            $chat->response = $response;
            $chat->message = $message;
            $chat->save();

            $userChat = Chat::where('user_id', $user->id)->get();

            if (!$userChat->isEmpty()) { 
                $response = [
                    'status' => 'success',
                    'message' => 'user chats fetched successfully',
                    'data' => $userChat
                ];
        
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'failed',
                    'message' => 'No chats for user'
                ];
        
                return response()->json($response, 404);
            }

    }

    public function addChatPost(Request $request) {
        $user = auth()->user();
    
        if (!$user) {
            $response = [
                'status' => 'failed',
                'message' => 'User not found'
            ];
    
            return response()->json($response, 404);
        }

        $this->validate($request, [
            'message' => 'nullable',
            'response' => 'nullable'
        ]);
        

        // Assuming you have a 'points_history' table to store points transactions
            $chat = new Chat();
            $chat->user_id = $user->id;
            $chat->response = $request->response;
            $chat->message = $request->message;
            $chat->save();

            $userChat = Chat::where('user_id', $user->id)->get();

            if (!$userChat->isEmpty()) { 
                $response = [
                    'status' => 'success',
                    'message' => 'user chats fetched successfully',
                    'data' => $userChat
                ];
        
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'failed',
                    'message' => 'No chats for user'
                ];
        
                return response()->json($response, 404);
            }

    }

    public function addReferalCode(Request $request){

        if($request->referal_code){

            $user = auth()->user();
            $userReferalCode = $user->referal_code;

            $checkIfReferred = Referral::where('referee_id', $user->id)->first();
            
    
            if($userReferalCode == $request->referal_code){
                return response()->json(['status' => 'error', 'message' => 'Referal code cannot be yours!'], 400);
            }

            if($checkIfReferred){
                return response()->json(['status' => 'error', 'message' => 'you have been referred!'], 400);
            }
           
            $referrer = User::where('referal_code', $request->referal_code)->first();

                
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

                $response = [
                    'status' => 'success',
                    'message' => 'Referal code added successfully'                    
                ];
        
                return response()->json($response);

            }else{
                return response()->json(['status' => 'error', 'message' => 'Invalid Referral Code']);
            }               
        
        }
     
    }
    
}
