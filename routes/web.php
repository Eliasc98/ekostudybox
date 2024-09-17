<?php

use Illuminate\Support\Facades\Route;
use App\Models\PayInfo;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\QuestionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/testmycontol', [MobileController::class, 'testMyController'])->name('testmycontol');
Route::get('/deleteaccount', [MobileController::class, 'deleteUserView']);
Route::post('/deleteaccount', [MobileController::class, 'deleteUserAccount'])->name('destroy');

Route::get('/payment/{userId}', function ($userId){
    $user = User::find($userId);
    $email = $user->email;
        return view('payment.index', compact('userId','email'));
});

Route::get('store-record', function (Request $request) {
    
   
    $payInfoId= $request->input('payInfoId');
    $amount = $request->input('amount');
   $uid = $request->input('uid');
    
    if ($amount == 2500) {
        
                $payInfo = PayInfo::find($payInfoId);
                $payInfo->update([
                    'user_id' => $payInfo->user_id,
                    'email' => $payInfo->email,
                    'amount' => $payInfo->amount,
                    'ref' => $payInfo->ref,
                    'confirmation' => 'confirmed'
                    ]);
                
                $currentDate = Carbon::now();
                $newDate = $currentDate->addDays(30);
                
                $sub = Subscription::updateOrCreate([
                    "pay_info_id" => $payInfoId,
                    "user_id" => $uid,
                    "subscription_type" => "Monthly",
                    "expiry_date" => $newDate,
                ]);
                
                 if ($sub) {
                    return response()->json(['status' => 'success', 'data' => $sub]);
                } else {
                    return response()->json(['status' => 'failed', 'data' => "Unable to create data"]);
                }
            } else if ($amount == 6000) {
                $payInfo = PayInfo::find($payInfoId);
                $payInfo->update([
                    'user_id' => $payInfo->user_id,
                    'email' => $payInfo->email,
                    'amount' => $payInfo->amount,
                    'ref' => $payInfo->ref,
                    'confirmation' => 'confirmed'
                    ]);
                    
                $currentDate = Carbon::now();
                $newDate = $currentDate->addDays(90);
                
                $sub = Subscription::updateOrCreate([
                    "pay_info_id" => $payInfoId,
                    "user_id" => $uid,
                    "subscription_type" => "Termly",
                    "expiry_date" => $newDate,
                ]);
                
                if ($sub) {
                    return response()->json(['status' => 'success', 'data' => $sub]);
                } else {
                    return response()->json(['status' => 'failed', 'data' => "Unable to create data"]);
                }
            } else {
                
                $payInfo = PayInfo::find($payInfoId);
                $payInfo->update([
                    'user_id' => $payInfo->user_id,
                    'email' => $payInfo->email,
                    'amount' => $payInfo->amount,
                    'ref' => $payInfo->ref,
                    'confirmation' => 'confirmed'
                    ]);
                    
                $currentDate = Carbon::now();
                $newDate = $currentDate->addDays(365);
                $sub = Subscription::updateOrCreate([
                    "pay_info_id" => $payInfoId,
                    "user_id" => $uid,
                    "subscription_type" => "Yearly",
                    "expiry_date" => $newDate,
                ]);
                
                if ($sub) {
                    return response()->json(['status' => 'success', 'data' => $sub]);
                } else {
                    return response()->json(['status' => 'failed', 'data' => "Unable to create data"]);
                }
            }
   
});

Route::get('verify-payment', function (Request $request) {
    
    $email = $request->input('email');
    $amount = $request->input('amount');
    $reference = $request->input('res');

    $uid = $request->input('uid');

    $payInfo = PayInfo::updateOrCreate([
        'user_id' => $uid,
        'email' => $email,
        'amount' => $amount,
        'ref' => $reference,
    ]);

    
    
       // Verify payment with Paystack API
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer sk_live_89e8caf2dc5bea4def7b8c526e0a0c316bf87e12",
            "Cache-Control: no-cache",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    
    

    if ($err) {
        // Handle cURL error
        return response()->json(['error' => "cURL Error #: $err"]);
    } else {
        
        // Handle Paystack API response
        
        $responseData = json_decode($response);
        
        
        return ["data"=> $responseData, "db"=> $payInfo];
        
        
    }
})->name('verify-payment');

Route::get('/questions/create/{category_id}/{subject_id}/{year_id}', [QuestionController::class, 'wordCreate'])->name('questions.create');
Route::post('/questions/store', [QuestionController::class, 'wordStore'])->name('questions.store');

/////////// text upload page ////////

Route::get('/questions/create-page', [QuestionController::class, 'wordCreatePage'])->name('questions.create');
Route::post('/questions/storePage', [QuestionController::class, 'wordStorePage'])->name('questions.storePage');

Route::get('/getSubjects/{category_id}', [QuestionController::class, 'getSubs']);
Route::get('/getYears/{subject_id}', [QuestionController::class, 'getYears']);

