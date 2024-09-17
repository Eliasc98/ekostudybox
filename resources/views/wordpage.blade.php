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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                @if(session('success'))
                    <div class="alert alert-success" id="successMessage">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('questions.storePage') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <!-- Category Select -->
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select class="form-control" id="category" name="category_id">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subject Select -->
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select class="form-control" id="subject" name="subject_id" disabled>
                            <option value="">Select Subject</option>
                        </select>
                    </div>

                    <!-- Year Select -->
                    <div class="form-group">
                        <label for="year">Year</label>
                        <select class="form-control" id="year" name="year_id" disabled>
                            <option value="">Select Year</option>
                        </select>
                    </div>

                    <!-- File Input -->
                    <div class="form-group mt-4 mb-3">
                        <label for="file">Choose File</label>
                        <input type="file" class="form-control-file" id="file" name="file">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            // When category is selected, fetch subjects based on category id
            $('#category').change(function() {
                var category_id = $(this).val();
                if (category_id) {
                    $.ajax({
                        url: '/getSubjects/' + category_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#subject').empty();
                            $('#subject').append('<option value="">Select Subject</option>');
                            $.each(data, function(key, value) {
                                $('#subject').append('<option value="' + value.id + '">' + value.subjectname + '</option>');
                            });
                            $('#subject').prop('disabled', false);
                        }
                    });
                } else {
                    $('#subject').empty();
                    $('#subject').prop('disabled', true);
                }
            });

            // When subject is selected, fetch years based on subject id
            $('#subject').change(function() {
                var subject_id = $(this).val();
                if (subject_id) {
                    $.ajax({
                        url: '/getYears/' + subject_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#year').empty();
                            $('#year').append('<option value="">Select Year</option>');
                            $.each(data, function(key, value) {
                                $('#year').append('<option value="' + value.id + '">' + value.yearname + '</option>');
                            });
                            $('#year').prop('disabled', false);
                        }
                    });
                } else {
                    $('#year').empty();
                    $('#year').prop('disabled', true);
                }
            });
        });
    </script>

<script>
    // Wait for the document to be ready
    document.addEventListener("DOMContentLoaded", function() {
        // If success message exists, hide it after 5 seconds
        var successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 5000);
        }

        // Reset form fields after 5 seconds if upload was successful
        var successFlag = {!! json_encode(session('success')) !!};
        if (successFlag) {
            setTimeout(function() {
                document.getElementById("uploadForm").reset();
            }, 5000);
        }
    });
</script>

    <div class="container rounded p-2" style="background: rgb(30,129,176);">
        <p style="color: #eeeee4;"><span style="color: red;">*</span> <b>The contents of the Doc file should adhere to the following sequence:</b><i> questionText, Image name, optionA, optionB, optionC, optionD, correct option, and explanation, in that order.</i></p>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
