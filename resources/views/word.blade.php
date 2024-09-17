<!-- resources/views/questions/create.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Upload text Questions</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        
        body {
            background-color: white;
        }

        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <div class="text-center p-4" style="background: orange;"><h1>Upload Text File</h1></div>

    <div class="container rounded shadow-lg p-4 mb-5 bg-white">
        @if (session('success'))
            <div class="alert alert-success">
                <strong>{{session('success') }}</strong>
            </div>
        @endif

        <form action="{{ route('questions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category_id" value="{{ $category_id }}">
            <input type="hidden" name="subject_id" value="{{ $subject_id }}">
            <input type="hidden" name="year_id" value="{{ $year_id }}">
            
            <div class="form-group">
                <label for="file" class="form-label">Choose a Text File:</label>
                <input type="file" class="form-control" id="file" name="file">
            </div>
            <button type="submit" class="btn btn-primary mt-2">Upload</button>
        </form>
    </div>

    <div class="container rounded p-2" style="background: rgb(30,129,176);">
        <p style="color: #eeeee4;"><span style="color: red;">*</span> <b>The contents of the Doc file should adhere to the following sequence:</b><i> questionText, Image name, optionA, optionB, optionC, optionD, correct option, and explanation, in that order.</i></p>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
