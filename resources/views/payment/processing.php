
<?php
//session_start();
include('config/init.php');

// include('../../config/configuration.php');



if(isset($_POST['email'])){
        $ref = $_POST['ref'];
        $user_id = $_POST['userId'];
        $email = $_POST['email'];
        $amount = $_POST['amount'];
     
      $result = array();
        //The parameter after verify/ is the transaction reference to be verified
        $url = 'https://api.paystack.co/transaction/verify/'.$_POST['ref'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer sk_live_89e8caf2dc5bea4def7b8c526e0a0c316bf87e12']
        );
        $request = curl_exec($ch);
        curl_close($ch);

        if ($request) {
            $result = json_decode($request, true);
        }
        $array = array('success' => 0);
        
        if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success') ) {
          $num = $result['data']['amount'] / 10000;
        //   if($num == $amount){

             $create = $getFromGeneric->create('pay_info', array('user_id' => $user_id, 'amount' => $amount, 'email'=> $email, 'ref'=> $ref));
              
             // $get  = $getFromGeneric->get_All('users', 'id', 'ASC');
            // if($create){
              $array = array('success' => 1, 'data'=> $get);
               echo json_encode($array);
            
           // }
       //  }
          
           
        }
        else {
            echo json_encode($array);
        }
       
       
       
        
    

  //  echo json_encode($outpu);

  


}

?>