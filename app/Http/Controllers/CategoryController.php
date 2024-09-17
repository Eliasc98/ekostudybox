<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment\Category;

class CategoryController extends Controller
{
    //
    public function fetchAll()
    {
        $category = Category::get();

        if ($category->count() > 0) {
            $response = [
                'status' => 'success',
                'message' => 'categories fetched successfully',
                'data' => $category
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

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'cat_name' => 'required',
        ]);


        $data = Category::create([
            'cat_name' => $request->cat_name,
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'category created successfully',
                'data' =>  $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create category'
            ];
            return response()->json($response, 404);
        }
    }

    public function update($id, Request $request)
    {

        $cat = Category::find($id);

        // Validate the request data
        $this->validate($request, [
            'cat_name' => 'required'
        ]);

        $data =  $cat->update([
            'cat_name' => $request->cat_name
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'category updated successfully',
                'data' => $data
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to update category'
            ];
            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        //
        $cat = Category::findOrFail($id);

        if ($cat) {

            $response = [
                'status' => 'success',
                'message' => 'category view fetched successfully',
                'data' => $cat
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'No view for category found'
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        //
        $cat = Category::findOrFail($id);
        $del =  $cat->delete();

        if ($del) {
            $response = [
                'status' => 'success',
                'message' => 'category deleted successfully',
            ];
            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete category'
            ];
            return response()->json($response, 404);
        }
    }
}
