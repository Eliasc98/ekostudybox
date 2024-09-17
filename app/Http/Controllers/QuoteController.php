<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    //
    public function index()
{
    $quotes = Quote::orderBy('created_at', 'asc')->take(5)->inRandomOrder()->get();

    if ($quotes->count() > 0) {
        $response = [
            'status' => 'success',
            'message' => 'Quotes fetched successfully',
            'data' => $quotes
        ];
        return response()->json($response);
    } else {
        $response = [
            'status' => 'failed',
            'message' => 'No quote to fetch'
        ];
        return response()->json($response, 404);
    }
}

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'quote' => 'required'
        ]);       

        $authAdmin = auth()->user()->fullname;
        $data = Quote::create([
            'quote' => $request->quote,
            'admin_name' => $authAdmin
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'quote created successfully',
                'data' =>  $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create quote'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {

        $book = Quote::find($id);

        // Validate the request data
        $this->validate($request, [
            'className' => 'required',
            'classLevel' => 'required',
            'status' => 'required'
        ]);

        $authAdmin = auth()->user()->fullname;

        $book->quote = $request->quote;
        $book->admin_name = $authAdmin;


        if ($book) {
            $response = [
                'status' => 'success',
                'message' => 'Quote updated successfully',
                'data' => $book
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update quote'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $quote = Quote::findOrFail($id);

        if ($quote) {
            
            $response = [
                'status' => 'success',
                'message' => 'quote view fetched successfully',
                'data' => $quote
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for quote found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        //
        $quote = Quote::findOrFail($id);
        $del =  $quote->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'quote deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete quote'
            ];
            return response()->json($response, 404);
        }
    }
}
