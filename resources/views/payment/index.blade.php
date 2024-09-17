
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">

    <title>SuccessBox V2 Payment</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  </head>

  <body>
<!-- 
    <div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom box-shadow">
      <h5 class="my-0 mr-md-auto font-weight-normal">Company name</h5>
      <nav class="my-2 my-md-0 mr-md-3">
        <a class="p-2 text-dark" href="#">Features</a>
        <a class="p-2 text-dark" href="#">Enterprise</a>
        <a class="p-2 text-dark" href="#">Support</a>
        <a class="p-2 text-dark" href="#">Pricing</a>
      </nav>
      <a class="btn btn-outline-warning" href="#">Sign up</a>
    </div> -->

    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
      <h1 class="">Payment Options</h1>
      <p class="lead">Select a suitable payment plan of your choice</p>
    </div>

    <div class="container">
      <div class="card-deck mb-3 text-center">
        <div class="card mb-4 box-shadow">
          <div class="card-header">
            <h4 class="my-0 font-weight-normal">Monthly Package</h4>
          </div>
          <div class="card-body">
            <h1 class="card-title pricing-card-title">&#8358;2,500 <small class="text-muted">/ mo</small></h1>
            <ul class="list-unstyled mt-3 mb-4">
              <li>1 user</li>
              <li>Study module </li>
              <li>Assessment module</li>
              <li>AI chat</li>
              <li>Reporting </li>
              <li></li>
            </ul>
            <button type="button" class="btn btn-lg btn-block btn-outline-warning"  onclick="payWithPaystack(2500, '{{$email}}')">Subscribe </button>
          </div>
        </div>
        
        <div class="card mb-4 box-shadow">
          <div class="card-header">
            <h4 class="my-0 font-weight-normal">Termly Package</h4>
          </div>
          <div class="card-body">
            <h1 class="card-title pricing-card-title">&#8358;6,000 <small class="text-muted">/ term</small></h1>
            <ul class="list-unstyled mt-3 mb-4">
              <li>1 user</li>
              <li>Study module </li>
              <li>Assessment module</li>
              <li>AI chat</li>
              <li>Reporting </li>
              <li></li>
            </ul>
            <button type="button" class="btn btn-lg btn-block btn-outline-warning"  onclick="payWithPaystack(6000, '{{$email}}')">Subscribe </button>
          </div>
        </div>
        
        <div class="card mb-4 box-shadow">
          <div class="card-header">
            <h4 class="my-0 font-weight-normal">Yearly Package</h4>
          </div>
          <div class="card-body">
            <h1 class="card-title pricing-card-title">&#8358;20,000 <small class="text-muted">/ yr</small></h1>
            <ul class="list-unstyled mt-3 mb-4">
              <li>1 user</li>
              <li>Study module </li>
              <li>Assessment module</li>
              <li>AI chat</li>
              <li>Reporting </li>
              <li></li>
            </ul>
            <button type="button" class="btn btn-lg btn-block btn-outline-warning"  onclick="payWithPaystack(20000, '{{$email}}')">Subscribe </button>
          </div>
        </div>
      </div>
    
    <div>
        <button onclick="redirect()" type="button" class="btn btn-lg btn-block btn-danger">Go back to app</button>
    </div>
    </div>


   <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>
  </body>
</html>
<script>


function payWithPaystack(amount, email) {

    
    console.log('Hi confirmPayment');
 
      let handler = PaystackPop.setup({
        key: 'pk_live_b019e2ae80f47b24ac072824985285915b14eadc',
        email: email,
        amount: amount * 100,
        ref: ''+Math.floor((Math.random() * 1000000000) + 1), 
        callback: function(response){
            console.log(response);
            
             var data = {
                'amount': amount,
                'email': email,
                'res': response.reference,
                'uid': {{$userId}}
                };

        
            $.ajax({
                method: "GET",
                url: "{{URL("verify-payment")}}",
                data: data,
                error: function (xhr, status, error) {
                    console.log(error)
                    
                    if (xhr.status === 0) {
                        alert("Network error occurred. Please check your internet connection.");
                    } else if (xhr.status === 400) {
                        alert("Bad Request. Please check your input data.");
                    } else if (xhr.status === 401) {
                        alert("Unauthorized. Please check your authentication.");
                    } else if (xhr.status === 403) {
                        alert("Forbidden. You don't have permission to access this resource.");
                    } else if (xhr.status === 404) {
                        alert("Resource not found. Please check the URL.");
                    } else if (xhr.status === 500) {
                        alert("Internal Server Error. Please try again later.");
                    } else {
                        alert("An unexpected error occurred. Please try again.");
                    }
                },
                success: function (result) {
                    console.log(result.data);
                    // Handle success response here
                    var rec = {'payInfoId': result.db.id, 'amount': amount, 'uid': {{$userId}}}
                    
                     $.ajax({
                        method: "GET",
                        url: "{{URL("store-record")}}",
                        data: rec,
                        error: function (xhr, status, error){
                            console.log(error);
                        },
                        success: function (res){
                            console.log(res);
                            
                             // Redirect to the desired URL after the second AJAX call success
                            window.location.href = "https://chroniclesoft.com/tabs/home";
                        }
                     })
                }
            });

        }
  });

  handler.openIframe();
}


function redirect() {
     window.location.href = "https://chroniclesoft.com/tabs/home";
}

</script>