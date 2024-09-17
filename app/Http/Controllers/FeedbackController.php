<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $feedback = Feedback::join('users', 'feedback.user_id', '=', 'users.id')
        ->select('feedback.*', 'users.firstname', 'users.phone_number')
        ->orderBy('feedback.created_at', 'desc')
        ->get();

        if($feedback->isNotEmpty()) {            
            
            $response = [
                "status" => "success",
                "message" => "feedback fetched successfully",
                "data" => $feedback
                ];
                
            return response()->json($response);

        } else {
            
            $response = [
                "status" => "failed",
                "message" => "unable to save feedback"
                ];
                
            return response()->json($response);

        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        
        $this->validate($request,[
            'subject' => 'required',
            'body' => 'required'
            ]);
            
        $uid = auth()->user()->id;
        $email = auth()->user()->email;
            
        $feedback = Feedback::create([
            'user_id' => $uid,
            'email' => $email,
            'subject' => $request->subject,
            'body' => $request->body
            ]);
            
        if($feedback) {
            
            // \Illuminate\Support\Facades\Mail::raw($request->body, function ($message) use ($request) {
            //     $message->to('egodwin@chronicles.com')
            //             ->cc('oluwadamilare.c99@gmail.com')
            //             ->subject($request->subject);
            // });
            
            $to = "oluwadamilare.c99@gmail.com";
            $subject = $request->subject;
            $message = $request->body;
            $headers = "From: $email\r\n";
            $headers .= "Cc: $email";
            
            // Use the mail() function to send the email

            $mailSuccess = mail($to, $subject, $message, $headers);

            
            $response = [
                "status" => "success",
                "message" => "feedback saved successfully",
                "data" => $feedback
                ];
                
            return response()->json($response);
        } else {
            
            $response = [
                "status" => "failed",
                "message" => "unable to save feedback"
                ];
                
            return response()->json($response);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feedback $feedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feedback $feedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        //
    }
}
