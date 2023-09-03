<?php
include 'config.php';
include '../silicon-apis/siliconpay-functions.php';
$errors = array();
foreach ($errors as $error) {
	echo $errors;
}

if (isset($_POST['login_btn'])) {
    trim(extract($_POST));
    if (count($errors) == 0) {
    $password = sha1($password);
    $result = $dbh->query("SELECT * FROM users WHERE email = '$email' AND password = '$password' AND account_status = 'Active' ");
    $result1 = $dbh->query("SELECT * FROM users WHERE email = '$email' AND password = '$password' AND account_status = 'Pending' ");
        if ($result->rowCount() == 1) {
            $row = $result->fetch(PDO::FETCH_OBJ);
            //`userid`, `fullname`, `pic`, `email`, `country`, `gender`, `phone`, `password`, `role`, `date_registered`, `token`, `account_status`
            $_SESSION['userid'] = $row->userid;
            $_SESSION['fullname'] = $row->fullname;
            $_SESSION['email'] = $row->email;
            $_SESSION['role'] = $row->role;
            $_SESSION['date_registered'] = $row->date_registered;  
            $_SESSION['status'] = '<div class=" card card-body alert alert-success text-center">
            Login Successful...Redirecting to Job Search</div>';
            $_SESSION['loader'] = '<center><div class="spinner-border text-center text-success"></div></center>';
            header("Location: ".SITE_URL.'/login-success');

        }elseif ($result1->rowCount() == 1) {
            $token = rand(11111,99999);
            $dbh->query("UPDATE users SET token = '$token' WHERE email = '$email' ");
            $rx = dbRow("SELECT * FROM users WHERE email = '$email' ");
            $subj = "POST KAZI - Account Verification Token";
            $body = "Hello {$rx->fullname} you account verification token is: <br>
                <h1><b>{$token}</b></h1>";
            GoMail($email,$subj,$body);
            $_SESSION['email'] = $email;
            $_SESSION['status'] = '<div class="alert alert-success text-center">Verification token is sent to your email successfully, Please enter the OTP send to you via Email to complete registration process</div>';
            header("refresh:3; url=".SITE_URL.'/token');
        }else{
        	echo "<script>
			alert('Wrong User Login Details');
			window.location = '".SITE_URL."/login';
			</script>";
        }
    }else{
        $_SESSION['status'] = '<div class="card card-body alert alert-danger text-center">
        Wrong Token inserted</div>';
    }
}elseif (isset($_POST['upload_cv_btn'])) {
    trim(extract($_POST));
    // `cv_id`, `userid`, `cv_url`, `cv_views`, `cv_last_updated`
     $filename = trim($_FILES['cv_url']['name']);
     $chk = rand(1111111111116,9999999999996);
     $ext = strrchr($filename, ".");
     $cv_url = $chk.$ext;
     $target_img = "../uploads/".$cv_url;
     $url = SITE_URL.'/uploads/'.$cv_url;
     if (count($errors) == 0) {
        $result = dbCreate("INSERT INTO cvs VALUES(NULL,'$userid','$url',0,'$today')");
     if (move_uploaded_file($_FILES['cv_url']['tmp_name'], $target_img)) {
          $msg ="Image uploaded Successfully";
          }else{
            $msg ="There was a problem uploading Video Details";
          }
        if($result == 1){
            $_SESSION['status'] = '<div class="alert alert-success text-center single-item__time single-item__time--live">CV uploaded Successfully!</div>';
            $_SESSION['loader'] = '<center><img src='.SITE_URL.'/uploads/preload.gif></center>';
            header("Location: ".SITE_URL."/cv-upload-success");
        }else{
            $_SESSION['status'] = '<div class="alert alert-success text-center single-item__time single-item__time--live">Video Details upload Failed!</div>';
        }
     }
}elseif (isset($_POST['submit_job_application_btn'])) {
    // `job_app_id`, `job_id`, `userid`, `app_amount`, `app_currency`, `app_phone`, `app_email`, `app_status`, `app_txRef`, `app_trans_status`, `app_date_paid`, `app_letter`
    trim(extract($_POST));
    $filename = trim($_FILES['app_letter']['name']);
    $chk = rand(11111111111163,99999999999963);
    $ext = strrchr($filename, ".");
    $app_letter = $chk.$ext;
    $target_img = "../uploads/".$app_letter;
    $app_url = SITE_URL.'/uploads/'.$app_letter;

   //check if empty fields..
   if (empty($app_amount) || empty($method)) {
     echo "<script>window.alert('Fill all the fields.'); </script>";
   }
   if (count($errors) == 0) {
      sleep(3);
       $method = trim($_POST['method']);
        if($method == 'mtn'){
           $trx = trim($_POST['mtn_number']);
        }else{
           $trx = trim($_POST['airtel_number']);
        }
     // `job_app_id`, `job_id`, `userid`, `app_amount`, `app_currency`, `app_phone`, `app_email`, `app_status`, `app_txRef`, `app_trans_status`, `app_date_paid`, `app_letter`
     $Phone = "+256".ltrim($trx,$trx[0]);
     //$Phone = $trx;
     $app_amount = $_POST['app_amount'];
     $_SESSION['msisdn'] = $Phone;
     $_SESSION['method'] = $method;
     $currency = "UGX";
     $en_phone = base64_encode($Phone);
     $method = base64_encode($method);
     $result = json_decode(job_app_deposit($job_id,$userid,$app_amount,$Phone,$app_email, $app_url), true);

    if (move_uploaded_file($_FILES['app_letter']['tmp_name'], $target_img)) {
    $msg ="Image uploaded Successfully";
    }else{
    $msg ="There was a problem uploading Application Letter";
    }
    $_SESSION['status'] = '<div class="alert alert-success text-center single-item__time single-item__time--live">Your Job application is submitted to our system and its processing. Application Fee Payment is Initiated successfully on your Phone.<br> Redirecting to Jobs... </div>';
    $_SESSION['loader'] = '<center><img src='.SITE_URL.'/uploads/preload.gif></center>';
    header("Location: ".SITE_URL."/app-success");
     
    // if ($result['status'] == 'success') {
    // // header("Location: ".HOME_URL);
    // echo "<script>
    // alert('Your Job application is submitted to our system and its processing. Payment Initiated successfully on your Phone');
    // window.location = ".SITE_URL."; 
    // </script>";
    // } else{
    // echo "<script>
    // alert('Your Job Application Failed, try again.');
    // window.location = ".SITE_URL."; 
    // </script>";
    // } 
 }
}elseif (isset($_POST['submit_gig_application_btn'])) {
    trim(extract($_POST));
    // `gig_app_id`, `gig_id`, `userid`, `gig_app_desc`, `gig_app_amount`, `gig_app_currency`, `gig_complition_date`, `gig_app_pay_status`, `gig_status`, `app_app_date`
    $gig_app_desc = addslashes($gig_app_desc);
    $sql = $dbh->query("INSERT INTO gig_applications VALUES (NULL, '$gig_id', '$userid', '$gig_app_desc','$gig_app_amount', '$gig_app_currency', '$gig_complition_date', 'Pending', 'Pending', '$now' ) ");
    if ($sql) {
        $_SESSION['status'] = '<div class="alert alert-success text-center single-item__time single-item__time--live">Your Gig application is submitted and gig owner is mailed. You will recieve update soon from the gig owner... </div>';
        $_SESSION['loader'] = '<center><img src='.SITE_URL.'/uploads/preload.gif></center>';
        header("Location: ".SITE_URL."/gig-success");
    }else{
        echo "<script>
        alert('Problem occured, try submiting your Gig again.');
        window.location = ".SITE_URL."; 
        </script>";  
    }
}

?>