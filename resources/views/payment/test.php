

<script>





// This calls the paystack form
function buy_band(amount) {
  var email = "<?=$email?>";
    var amount_part = $('#amount_part').val();
    var course_id = $('#course_id').val();
    
var amount =  (amounts)/2;
 
    
    var amountInKobo = amount * 100;

    var handler = PaystackPop.setup({
        key: 'pk_live_fc4ea9275855c558136c924bcd3813830404b851',
        email: email,
        amount: amountInKobo,
        currency: 'USD',
        ref: ''+Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response){
            confirmPayment(response.reference,course_id,amount, email);
        },
        onClose: function(){
            window.location('<?=BASE_URL?>student/subscribe/'+course_id);
        }
    });
    handler.openIframe();
}





// This calls the paystack form
function buy_full() {
     var email = "<?=$email?>";
    //alert(email);
    var amount = $('#amount').val();
    var course_id = $('#course_id').val();
    //alert(amount);
    
    var amountInKobo = amount * 100;

    var handler = PaystackPop.setup({
        key: 'pk_live_fc4ea9275855c558136c924bcd3813830404b851',
        email: email,
        amount: amountInKobo,
        currency: 'USD',
        ref: ''+Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response){
            confirmPayment(response.reference,course_id,amount, email);
        },
        onClose: function(){
            window.location('<?=BASE_URL?>student/subscribe/'+course_id);
        }
    });
    handler.openIframe();
}

//Do ajax post to the php url that will confirm the payment
function confirmPayment(ref, course_id,amount) {
    var student_id = "<?=$std_id?>";
   
    var data = {
        'ref': ref,
        'url': '<?=BASE_URL?>student/includes/payment.php',
        'course_id': course_id,
        'amount': amount,
         'student_id': student_id,
       
       
    };
    dataS = JSON.stringify(data);
    $.ajax({
        method: "POST",
        url: url,
        data: dataS,
        error: function(xhr,status,error){
            alert("An Error ocurred! Check Internet Connection.");
        },
        success: function(result, status, xhr) {
            jsonResult = JSON.parse(result);
            if (jsonResult.result.success) {
               alert('Your payment was made Successfully!');
               window.location('<?=BASE_URL?>student/billing/');   
            } else {
               alert('Operation Failed! An Error was encounterd while making your payment!');
            }
        }
    });
}











    $(document).ready(function(){

    $('a[name="modal_learning_name"]').click(function(){
       event.preventDefault();

       var link =  $(this).attr('my_value');
       var c_id =  $(this).attr('my_id');
       var course_id =  $(this).attr('my_course_id');
       var student_id = "<?=$std_id?>";
   
   
       //alert(c_id);

      
              $.ajax({

          url:"<?=BASE_URL?>student/includes/process.php",
          method:"POST",
          data:{ c_id : c_id, course_id : course_id},
          dataType:"json",



          })
          .done(function(data) {
            //  alert( "second success" );
            setVideoSource(link);


            var audio = document.getElementById("video_id");
            audio.onprogress = function(){

              setInterval(save_video_progress(c_id, course_id, student_id), 60000);

              
              
           };
    
                  
          })
          .fail(function(errorData) {
           setVideoSource(link);
              alert( 'Video Failed ' );
          })

          



  
      }); 
      
      });






      
      function save_video_progress(c_id, course_id, student_id){

        
      
        $.ajax({

          url:"<?=BASE_URL?>student/includes/processing.php",
          method:"POST",
          data:{ c_id : c_id, course_id : course_id, student_id : student_id},
          dataType:"json",



          })
          .done(function(data) {
            //  alert( "second success" );
            alert('hi down');
           // setAudioSource(link);
                  
          })
          .fail(function(errorData) {
              alert( 'Failed Contact Administrator' );
          })

        
      } 
      function save_audio_progress(c_id, course_id, student_id){
        
        
                  $.ajax({

          url:"<?=BASE_URL?>student/includes/processing.php",
          method:"POST",
          data:{ c_id : c_id, course_id : course_id, student_id : student_id},
          dataType:"json",



          })
          .done(function(data) {
            //  alert( "second success" );
            alert('hi down');
          // setAudioSource(link);
                  
          })
          .fail(function(errorData) {
              alert( 'Failed Contact Administrator' );
          })
      }








      $(document).ready(function(){

        $('a[name="modal_audio_name"]').click(function(){
          event.preventDefault();

          var student_id = "<?=$std_id?>";
   
   
          var link =  $(this).attr('my_value');
          var c_id =  $(this).attr('my_id');
          var course_id =  $(this).attr('my_course_id');
      
      // alert(link);

      
              $.ajax({

          url:"<?=BASE_URL?>student/includes/process.php",
          method:"POST",
          data:{ c_id : c_id, course_id : course_id},
         dataType:"json",



          })
          .done(function(data) {
            //  alert( "second success" );
            setAudioSource(link);


            var audio = document.getElementById("audio_id");
            audio.onprogress = function(){
            
              setInterval(save_audio_progress(c_id, course_id, student_id), 60000);
            
              //save_audio_progress(c_id, course_id, student_id);
            };
    
                  
          })
          .fail(function(errorData) {
              alert( 'Failed Contact Administrator' );
          })


      
          // alert(link);
         
         



          }); 
          
          });

      
      function setVideoSource(srcs){
            $('#video_src').attr('src', srcs);
            var video = document.getElementById('video_id');
           video.load();
      } 
      function setAudioSource(srcs){
            $('#audio_src').attr('src', srcs);
            var audio = document.getElementById('audio_id');
           audio.load();
      }



   // $('#video_src').addSrc('')
  //alert(learning_content);
  
    /* $.ajax({
      method : 'post',
      url : 'ajaxfile.php',
      data: { learning_content : learning_content },
      dataType:"json",
          success:function(data){
           //alert(learning_content);
               // $('#modal-learning').html(data.varia);
                //console.log(data)
                       
                                   
             }
    })*/





    

   $(document).ready(function(){
    $('#submit_rating').submit(function(event){
      event.preventDefault();
     
     var star1 =  $('#star1').val();
     var star2 =  $('#star2').val();
     var star3 =  $('#star3').val();
     var star4 =  $('#star4').val();
     var star5 =  $('#star5').val();
  //alert(star5);

     var comment =  $('#comments').val();
     var course_id =  $('#course_id').val();

     if(star1 != 0) {
     var rating = star1;

     }else if(star2 != 0){
     var rating = star2;

     }else if(star3 != 0){

     var rating = star3;

     }else if(star4 != 0){

     var rating = star4;

     }else if(star5 != 0){

     var rating = star5;
      }
      var id = "<?=@$_SESSION['login_id'];?>";
    // alert(course_id);

     
     $.ajax({

          url:"<?=BASE_URL?>student_portal/includes/process.php",
          method:"POST",
          data:{std_id:id, course_id:course_id, rating:rating, comment:comment},
          dataType:"json",



          })
          .done(function(data) {
            //  alert( "second success" );
              if(data){

                  alert('Rating Submitted Successfully');

              }else{
                  alert('Failed to submit your Rating! Try Again');
              }
                  
          })
          .fail(function(errorData) {
              alert( 'Rating Failed ' );
          })
        


    });

   });







    

   $(document).ready(function(){
    $('#submit_part_payment').submit(function(event){
      event.preventDefault();
     
     var amount =  $('#amount_part').val();
    
     var id = "<?=@$_SESSION['login_id'];?>";
     alert(amount);
    buy_band(amount);
   
     
    
  }); 
   });

</script>





<!--

    * 
    * Admin New Creation Modal 
    *


    -->

    
    <!-- Part Payment  Modal-->
   <div class="modal fade" id="modal-part_payment">
      <div class="modal-dialog modal-xs">
          <div class="modal-content" id="modals_content">
             
          
              <div class="modal-body">

              <form method="POST" id="submit_part_payment"   enctype="multipart/form-data">
          
       
         
            <div class="form-group" >

              <label class="col-md-12 control-label"> Amount </label>

                <div class="col-md-12 " >
                <input type="hidden" name="course_id" id="course_id" value="<?=$course_id?>">
                  <input type="number" class="form-control" required id="amount_part" name="amount" placeholder="Enter Amount..">
                </div>

            </div>
          

            <div class="form-group" >

              <div class="col-md-12 " >
                <input type="submit" class="btn btn-outline-secondary" name="submit" id="submit_rating" value="Submit Payment">
              </div>

            </div>
           

         
       </form> 
            
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
          </div>       
      </div>  
    </div>
    

    <!-- Learning Video  Modal-->
   <div class="modal fade" id="modal-learning">
      <div class="modal-dialog modal-lg">
          <div class="modal-content" id="modals_content">
             
          
              <div class="modal-body">

                <video id="video_id" width="100%" height="100%" controls>
                  <source id="video_src" src="" type="video/mp4">
                </video>       
            
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
          </div>       
      </div>  
    </div>
    



      <!-- Learning Audio  Modal-->
   <div class="modal fade" id="modal-audio">
      <div class="modal-dialog">
          <div class="modal-content" id="modals_content">
              
          
              <div class="modal-body">

              <audio controls id="audio_id"  width="100%" height="100%" >

               
                <source id="audio_src" src="" type="audio/mpeg">
             
              </audio>      
            
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>       
      </div>  
    </div>
    










    <!-- Rating  Modal -->
    <div class="modal fade" id="modal-rating">
    <div class="modal-dialog">
        <div class="modal-content" id="modals_content">
        <div class="modal-header">
        
           <h6 class="modal-title" id="header_title">Rate This Course </h6>
           
         </div>
        
         <div class="modal-body">
         <form method="POST" id="submit_rating"   enctype="multipart/form-data">
          
            <div class="form-group offset-2" ><!-- form-group Starts -->
          
              <span>
                  <a id="star1" class=""> <i class="large material-icons" style="font-size: 60px;">star</i></a>
                  <a id="star2" class=""> <i class="material-icons"  style="font-size: 60px;">star</i></a>
                  <a id="star3" class=""> <i class="material-icons "  style="font-size: 60px;">star</i></a>
                  <a id="star4" class=""> <i class="material-icons "  style="font-size: 60px;">star</i></a>
                  <a id="star5" class=""> <i class="material-icons "  style="font-size: 60px;">star</i></a>
              </span>
            </div>

           
              <div class="form-group" ><!-- form-group Starts -->

                <label class="col-md-12 control-label"> Comment </label>

                  <div class="col-md-12 " >
                  <input type="hidden" name="course_id" id="course_id" value="<?=$course_id?>">
                    <textarea class="form-control" required id="comments" name="comment" placeholder="Enter Your Comment Here.."></textarea>
                  </div>

              </div>
            

              <div class="form-group" ><!-- form-group Starts -->

                <div class="col-md-12 " >
                  <input type="submit" class="btn btn-outline-secondary" name="submit" id="submit_rating" value="Submit Rating">
                </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

           
         </form>
         
         </div>
       
    

         

      
        </div>
        <!-- /.modal-content -->
      </div>
      

    </div>
      <!-- /.modal -->