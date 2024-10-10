<?php

namespace App\Http\Controllers\AuthUser;

use App\Models\User;
use App\Models\Login;
use App\Models\School;
use App\Models\Referral;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PointsHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    
    // public function register(Request $request)
    // {
    //     // Generate Referral Code 
    //     $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //     $randomString = '';
    
    //     $length = 6; 

    //     for ($i = 0; $i < $length; $i++) {
    //         $randomString .= $characters[rand(0, strlen($characters) - 1)];
    //     }
            
    //     $data = $request->validate([
    //         'firstname' => 'required',
    //         'lastname' => 'required',
    //         'phone_number' => 'nullable',
    //         'state' => 'nullable',
    //         'email' => 'required|email',
    //         'password'  =>  ['required', 'string'],
    //         'password_confirmation'  =>  'required|string',
    //         'admin_class_id' => 'nullable',
    //         'school_id'=> 'nullable'
    //     ]);

    //     // Add the referral code to the data array

    //     $data['referal_code'] = $randomString;

    //     if ($request->password !== $request->password_confirmation) {
    //         return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
    //     }

    //     $user = User::where('email', $request->email)->first();

    //     if ($user) {
    //         return response()->json(['status' => 'error', 'message' => 'User already exists!'], 400);
    //     }

    //     $data['password'] = Hash::make($request->password);

    //     if($request->referal_code){
    //         $refferalcode = User::where('referal_code', $request->referal_code)->first();

    //         if ($refferalcode) {
    //             $referrer = User::where('referal_code', $request->referal_code)->first();

    //             // Create the user
    //             $user = User::create($data);


    //             if ($referrer) {
    //                 // Create the referral record
    //                 Referral::create([
    //                     'user_id' => $referrer->id,
    //                     'referee_id' => $user->id, 
    //                     'referal_code' => $request->referal_code
    //                 ]);      
                    
    //                 $referrer->points += 50;
    //                 $referrer->save();

    //                 $pointsHistory = new PointsHistory();
    //                 $pointsHistory->user_id = $referrer->id;
    //                 $pointsHistory->points += 50;
    //                 $pointsHistory->descriptions = 'Referred User Points';
    //                 $pointsHistory->save();
    //             }

                

    //             if($user){
                
                
    //                 // Attempt login after successful registration
                    
    //                 auth()->attempt($request->only('email', 'password'));

    //                 return $this->onSuccessfulLogin($user, false);
    //             } else {
    //                 return response()->json(['status' => 'error', 'message' => 'Unable to register!'], 400);
    //             }     
    //         }else{
    //             return response()->json(['status' => 'error', 'message' => 'Invalid Referral Code'], 400);
    //         }               
    //     }else{
    //         // Create the user
    //         $user = User::create($data);

    //         if($user){
                
                
    //             // Attempt login after successful registration
                
    //             auth()->attempt($request->only('email', 'password'));

    //             return $this->onSuccessfulLogin($user, false);
    //         } else {
    //             return response()->json(['status' => 'error', 'message' => 'Unable to register!'], 400);
    //         }     
    //     }

    //      // Check if the referral code provided exists in the database
         
       
    // }

    // public function bulkRegistration(Request $request)
    // {
    //     $data = $request->json()->all();

    //     // $school_id = is_array($data['school_id']) ? $data['school_id'] : json_decode($data['school_id']);
    //     $admin_class_id = is_array($data['admin_class_id']) ? $data['admin_class_id'] : json_decode($data['admin_class_id']);

    //     $students = is_array($data['students']) ? $data['students'] : json_decode($data['students'], true);
    //     array_shift($students);
        
    //     $createdData = [];

    //     foreach ($students as $item) {
    //         $randomString = ''; // Generate new referral code for each user
    //         $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //         $length = 6;
    //         for ($i = 0; $i < $length; $i++) {
    //             $randomString .= $characters[rand(0, strlen($characters) - 1)];
    //         }

    //         $filteredItem = array_diff($item, ['StudentNo']);

    //         $student = User::updateOrCreate([
    //             'assoc_cat_id'=> 1,
    //             'school_id' => auth()->user()->school_id ?? 1,
    //             'admin_class_id' => $admin_class_id,
    //             'firstname' => $item[0],
    //             'lastname' => $item[1],
    //             'phone_number' => $item[2],
    //             'state' => $item[3],
    //             'email' => $item[4],
    //         ], [
    //             'referal_code' => $randomString,
    //             'password' => Hash::make($item[5])
    //         ]);

    //         $createdData[] = $student;
    //     }

    //     $response = [
    //         'status' => 'success',
    //         'message' => 'Users created successfully',
    //         'data' => $createdData
    //     ];

    //     return response()->json($response);
    // }

    // public function bulkRegistrationWithFile(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xls,xlsx', // Validate file type
    //     ]);

    //     // Handle file upload
    //     $file = $request->file('file');

    //     // Import data from the Excel file
    //     $students = Excel::toCollection(null, $file)->first();

    //     // Process imported data
    //     $createdData = [];
    //     foreach ($students as $student) {
    //         $randomString = ''; // Generate new referral code for each user
    //         $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //         $length = 6;
    //         for ($i = 0; $i < $length; $i++) {
    //             $randomString .= $characters[rand(0, strlen($characters) - 1)];
    //         }

    //         $newStudent = User::updateOrCreate([
    //             'school_id' => $request->school_id,
    //             'admin_class_id' => $request->admin_class_id,
    //             'firstname' => $student['firstname'],
    //             'lastname' => $student['lastname'],
    //             'phone_number' => $student['phone_number'],
    //             'state' => $student['state'],
    //             'email' => $student['email'],
    //         ], [
    //             'referal_code' => $randomString,
    //             'password' => Hash::make($student['password'])
    //         ]);

    //         $createdData[] = $newStudent;
    //     }

    //     $response = [
    //         'status' => 'success',
    //         'message' => 'Users created successfully',
    //         'data' => $createdData
    //     ];

    //     return response()->json($response);
    // }



    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email'     =>  'required|email',
    //         'password'  =>  'required|string'
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) return response()->json(['status' => 'error', 'message' => 'User does not exist!'], 400);

    //     if (!Hash::check($request->password, $user->password)) {
    //         return response()->json(['status' => 'error', 'message' => 'Bad credentials'], 400);
    //     } else {
    //         if (auth()->attempt($request->only('email', 'password'))) {
    //             $email = User::where('email', $request->email)->first();
    //             $login = new Login;
    //             $login->user_id = $email->id;
    //             $login->email = $request->email ?? 'no-email';
    //             $login->save();

    //             return $this->onSuccessfulLogin($user);
    //         } else {
    //             return response()->json(['status' => 'error', 'message' => 'Bad credentials'], 400);
    //         }
    //     }
    // }

    // private function onSuccessfulLogin($user, $isLogin = true)
    // {
    //     $token = $user->createToken('Bearer')->plainTextToken;

    //     $response = [
    //         'status'    =>  'success',
    //         'message'   =>  $isLogin ? 'Login successful!' : "Registration successful, Welcome!",
    //         'data'      =>  [
    //             'user'              =>  $user,
    //             'token'             =>  $token,
    //             'uid'               =>  $user->id
    //         ]
    //     ];

    //     return response()->json($response);
    // }



    // public function getUser(Request $request)
    // {
    //     $response = [
    //         'status'    =>  'success',
    //         'message'   =>  'Fetch successful!',
    //         'data'      =>  [
    //             'user'              =>  $request->user(),
    //             'uid'               =>  auth()->id()
    //         ]
    //     ];

    //     return response()->json($response);
    // }

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
        'state' => 'required',
        'district' => 'required',
        'admin_class_id' => 'nullable',
        'school_id'=> 'nullable'
    ]);

    // Add the referral code to the data array

    $data['referal_code'] = $randomString;

    // if ($request->password !== $request->password_confirmation) {
    //     return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
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
            'school_id'=> $request->school_id
        ]);

        $school = School::find($user->school_id);
        $local_govt = $school->local_govt ?? 'IKJ';  

        // Generate student code
        $user->student_code = substr($local_govt, 0, 3) . '/0' . ($user->school_id ?? '14') . '/0' . ($user->id ?? '1');

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

    // return $this->onSuccessfulLogin($user);

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

    public function logOut(Request $request)
    {
        $user = $request->user();
        if ($user) {
            // $user->tokens()->delete();
            $user->currentAccessToken()->delete();

            return response()->json([
                'status'    =>  'success',
                'message'   =>  'Logged Out'
            ]);
        }
        return response()->json([
            'status'    =>  'error',
            'message'   =>  'User not logged in'
        ], 400);
    }

    // public function update(Request $request)
    // {
    //     //
    //     $userid = auth()->user()->id;
    //     $user = User::find($userid);

    //     $data = $request->validate([
    //         'firstname' => 'required',
    //         'lastname' => 'required',
    //         'phone_number' => 'nullable',
    //         'user_img' => 'nullable',
    //         'email' => 'nullable|email',
    //         'password'  =>  ['nullable', 'string'],
    //         'password_confirmation'  =>  'nullable|string',
    //         'admin_class_id' => 'nullable'
    //     ]);

    //     if ($request->password !== $request->password_confirmation) {
    //         return response()->json(['status' => 'error', 'message' => 'Password does not match!'], 400);
    //     }

    //     if ($request->user_img) {
    //         $userImg = auth()->user()->firstname . '.' . auth()->user()->lastname . '-'. $request->user_img->extension();
    //         $request->user_img->storeAs('public/files', $userImg);
    //         $userImgLink = URL('storage/files/' . $userImg);

    //         $user->update(
    //             ['user_img' => $userImgLink]
    //         );
    //     }

        
    //     $data =  $user->update([
    //         'firstname' => $request->firstname,
    //         'lastname' => $request->lastname,
    //         'phone_number' => $request->phone_number,
    //         'email' => $request->email,
    //         'admin_class_id' => $request->admin_class_id,
    //         'password' => Hash::make($request->password)
    //     ]);



    //     if ($data) {
    //         $response = [
    //             'status' => 'success',
    //             'message' => 'User Profile updated successfully',
    //             'data' => $data
    //         ];
    //         return response()->json($response);
    //     } else {
    //         $response = [
    //             'status' => 'failed',
    //             'message' => 'unable to update User Profile'
    //         ];
    //         return response()->json($response, 404);
    //     }
    // }

    public function show()
    {
        //
        $userid = auth()->user()->id;
        $user = User::findOrFail($userid);
        $user->no_of_referals = Referral::where('user_id', $userid)->count();

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

    public function destroy($id)
    {
        $userid = auth()->user()->id;
        $subject = User::findOrFail($userid);
        $del =  $subject->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'Account deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete Account'
            ];
            return response()->json($response, 404);
        }
    }
}
