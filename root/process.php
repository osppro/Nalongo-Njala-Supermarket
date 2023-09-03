<?php 
//getting the database connection... 
// require_once('config.php');
//sms-api token...
require_once('AfricasTalkingGateway.php');
$username   = "grembasi";
$apikey     = "be50b582b53d54d2634a7c9121690ee56ab42f9deaeff7c015d44c8654921f72";

$errors = array();
foreach ($errors as $error) {
    echo $errors;
}

if (isset($_POST['login_btn'])) {
    trim(extract($_POST));
    if (count($errors) == 0) {
    $password = sha1($password);
    $result = $dbh->query("SELECT * FROM users WHERE phone = '$phone' AND password = '$password' ");
        if ($result->rowCount() == 1) {
            $rows = $result->fetch(PDO::FETCH_OBJ);
            if ($rows->status == 'Approved') {  
                //`userid`, `fullname`, `email`, `role`, `password`, `phone`, `token`, `status`, `date_registered`
                $token = rand(11111, 99999);
                $dbh->query("UPDATE users SET token = '$token' WHERE userid = '".$rows->userid."' ");
                $message = "Hi ".$rows->fullname.', your Login token is  '. $token;
                $nums = array("+256".$rows->phone);
                {
                    $recipients = "".implode(',', $nums);
                    $message = "PENIEL BEACH HOTEL : ".$message;
                    $gateway    = new AfricasTalkingGateway($username, $apikey);
                    try 
                    { 
                      $results = $gateway->sendMessage($recipients, $message);
                      foreach($results as $result) {
                      echo '';
                      }
                    }
                    catch ( AfricasTalkingGatewayException $e )
                    {
                      echo "Encountered an error while sending: ".$e->getMessage();
                    }
                }
                $_SESSION['phone'] = $phone;
                $_SESSION['loader'] = '<center><div class="spinner-border text-success"></div></center>';
                $_SESSION['status'] = '<div class="card card-body alert alert-success text-center">Account mateched, New Token generated Successfully</div>';
                header("refresh:3; url=".SITE_URL.'/token');
            }else{
                $_SESSION['status'] = '<div class="card card-body alert alert-info text-center">
                Account mateched, But Under Preview!</div>';
            }
        }else{
            $_SESSION['status'] = '<div class=" card card-body alert alert-danger text-center">
            Invalid account, Try again.</div>';
        }

    }else{
        $_SESSION['status'] = '<div class=" card card-body alert alert-danger text-center">
        Wrong Token inserted</div>';
    }
}elseif (isset($_POST['verify'])) {
    trim(extract($_POST));
    if (count($errors) == 0) {
        $result = $dbh->query("SELECT * FROM users WHERE phone = '$phone' AND token = '$otp' " );
        if ($result->rowCount() == 1) {
        $row = $result->fetch(PDO::FETCH_OBJ);
         //`userid`, `fullname`, `email`, `role`, `password`, `phone`, `token`, `status`, `date_registered`
        $_SESSION['userid'] = $row->userid;
        $_SESSION['phone'] = $row->phone;
        $_SESSION['status'] = $row->status;
        $_SESSION['fullname'] = $row->fullname;
        $_SESSION['role'] = $row->role;
        $_SESSION['date_registered'] = $row->date_registered;
        if ($result->rowCount() > 0) {
            $_SESSION['loader'] = '<center><div class="spinner-border text-success"></div></center>';
            $_SESSION['status'] = '<div class="card card-body alert alert-success text-center">
            <strong>Login Successful, Redirecting...</strong></div>';
            header("refresh:2; url=".SITE_URL);
            }else{
                $_SESSION['status'] = '<div class="card card-body alert alert-warning text-center">
                Login failed, please check your login details again</div>';
            }
    }else{
        $_SESSION['status'] = '<div class="card card-body alert alert-danger text-center">
                <strong>Wrong Token inserted</strong></div>';
        echo "<script>
            alert('Wrong Token inserted');
            window.location = '".SITE_URL."/token';
            </script>";
    }
    }

}elseif (isset($_POST['recover_btn'])) {
    trim(extract($_POST));
    $res = $dbh->query("SELECT phone FROM users WHERE (phone='$phone' ) ")->fetchColumn();
    if(!$res){
        echo "<script>
            alert('This phone number is not registered in this system');
            window.location = '".SITE_URL."/auth-login';
            </script>";
     }else{
        $_SESSION['phone'] = $phone;
        header("Location: auth-new-password");

    }
}elseif (isset($_POST['newpassowrd_btn_verification'])) {
    trim(extract($_POST));
    // `userid`, `token`, `surname`, `othername`, `gender`, `phone`, `email`, `password`, `country_id`, `branch_id`, `address`, `nin_number`, `date_registered`, `account_status`, `u_type`
    $password = sha1($new_password);
    $update_password = $dbh->query("UPDATE users SET password = '$password' WHERE phone = '$phone' ");
    if ($update_password) {
        $ro = dbRow("SELECT * FROM users WHERE phone = '$phone' ");
        $message = "Hi ".$ro->surname.', your New Login details is: Phone '. $phone.' Password: '.$new_password;
            $nums = array("+256".$phone);
            {
            $recipients = "".implode(',', $nums);
            $message = "GREMBASI INVESTMENTS LTD : ".$message;
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try 
            { 
              $results = $gateway->sendMessage($recipients, $message);
              foreach($results as $result) {
              echo '';
              }
            }
            catch ( AfricasTalkingGatewayException $e )
            {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
            }
            echo "<script>
                alert('Account Login details updated Successfully');
                window.location = '".SITE_URL."/auth-login';
                </script>";
            // 0773325394 - nakayima
    }
}elseif (isset($_POST['resent_token_btn'])) {
    trim(extract($_POST));
    if (count($errors) == 0) {
        $result = $dbh->query("SELECT * FROM users WHERE phone = '$phone' " );
        if ($result->rowCount() == 1) {
            $token = rand(11111,99999);
            $dbh->query("UPDATE users SET token = '$token' WHERE phone = '$phone' ");
            $rx = dbRow("SELECT * FROM users WHERE phone = '$phone' ");
            $subj = "POST KAZI - Account Verification Token";
            $body = "Hello {$rx->fullname} you account verification token is: <br>
                <h1><b>{$token}</b></h1>";
            GoMail($email,$subj,$body);
            $_SESSION['email'] = $email;
            $_SESSION['status'] = '<div class="alert alert-success text-center">Verification token is sent to your email successfully, Please enter the OTP send to you via Email to complete registration process</div>';
            header("refresh:3; url=".SITE_URL.'/token');
        }else{
            $_SESSION['status'] = '<div class="card card-body alert alert-warning text-center">
            Account Verification Failed., please check your Token and try again.</div>';
        }
    }
}elseif(isset($_POST['add_new_user_btn_by_admin'])){
    trim(extract($_POST));
    if (count($errors) == 0) {
        //`userid`, `fullname`, `email`, `role`, `password`, `phone`, `token`, `status`, `date_registered`, `pic`
        $check = $dbh->query("SELECT phone, email FROM users WHERE (phone='$phone' OR email = '$email') ")->fetchColumn();
      if(!$check){
        $pass= sha1($password);
        $userid = rand(11111111,99999999);
        $token = rand(11111,99999);
        $fullname = addslashes($fullname);
        $sql = "INSERT INTO users VALUES('$userid','$fullname','$email','$role','$pass','$phone','$token','Approved','$today','')";
        $result = dbCreate($sql);
        if($result == 1){
            $message = "Hi ".$fullname.', your Login details is: Phone:'. $phone.' Password: '.$password;
            $nums = array("+256".$phone);
            {
            $recipients = "".implode(',', $nums);
            $message = "PENIEL BEACH HOTEL : ".$message;
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try 
            { 
              $results = $gateway->sendMessage($recipients, $message);
              foreach($results as $result) {
              echo '';
              }
            }
            catch ( AfricasTalkingGatewayException $e )
            {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
            }
            $_SESSION['status'] = '<div class="alert alert-success alert-dismissible">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Success!</strong> System User Registered Successfully.
            </div>';
            header("Location: ".SITE_URL.'/users');
        }else{
            $_SESSION['status'] = '<div class="alert alert-danger alert-dismissible">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Invalid!</strong> User Registration Failed.
            </div>';
        }
     }else{
          echo "<script>
            alert('Username already registered');
            window.location = '".SITE_URL."/users';
            </script>";
        }
    }
}elseif(isset($_POST['update_user_details_btn'])){
    trim(extract($_POST));
    //`userid`, `fullname`, `email`, `role`, `password`, `phone`, `token`, `status`, `date_registered`, `pic`
    $fullname = addslashes($fullname);
    $sql = $dbh->query("UPDATE users SET fullname = '$fullname', email = '$email', phone = '$phone' WHERE userid = '$userid' ");
    if ($sql) {
        $_SESSION['status'] = '<div class="alert alert-success alert-dismissible">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          <strong>Success!</strong> User Details Updated Successfully.
        </div>';
        header("Location: ".SITE_URL.'/users');
    }else{
        echo "<script>
            alert('User Details Update Failed');
            window.location = '".SITE_URL."/users';
            </script>";
    }

}elseif (isset($_POST['submit_banner_details_btn'])) {
    trim(extract($_POST));
    //`bid`, `bsmall_title`, `bbig_title`, `bdesc`, `bphoto`, `bdate_added`
     $filename = trim($_FILES['bphoto']['name']);
     $chk = rand(1111111111111,9999999999999);
     $ext = strrchr($filename, ".");
     $bphoto = $chk.$ext;
     $target_img = "uploads/".$bphoto;
     $url = SITE_URL.'/uploads/'.$bphoto;
     $bsmall_title = addslashes($bsmall_title);
     $bbig_title = addslashes($bbig_title);
     $bdesc = addslashes($bdesc);
    $result = dbCreate("INSERT INTO banner VALUES(NULL,'$bsmall_title','$bbig_title','$bdesc','$url','$today')");
     if (move_uploaded_file($_FILES['bphoto']['tmp_name'], $target_img)) {
          $msg ="Image uploaded Successfully";
          }else{
            $msg ="There was a problem uploading image";
          }
        if($result == 1){
            $_SESSION['upload_status'] = '<div class="alert alert-success alert-dismissible">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Success!</strong>Banner Uploaded Successfully.
            </div>';
            header("Location: ".SITE_URL."/banners");
        }else{
            $_SESSION['upload_status'] = '<div class="alert alert-success alert-dismissible">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Failed!</strong>Banner Upload Failed.
            </div>';
        }

}elseif (isset($_POST['submit_rooms_details_btn'])) {
   trim(extract($_POST));
    //`room_id`, `rtid`, `room_number`, `room_price`, `room_pic`, `room_status`
     $filename = trim($_FILES['room_pic']['name']);
     $chk = rand(1111111111111,9999999999999);
     $ext = strrchr($filename, ".");
     $room_pic = $chk.$ext;
     $target_img = "uploads/".$room_pic;
     $url = SITE_URL.'/uploads/'.$room_pic;
    $result = dbCreate("INSERT INTO rooms VALUES(NULL,'$rtid','$room_number','$room_price','$url','Available')");
     if (move_uploaded_file($_FILES['room_pic']['tmp_name'], $target_img)) {
          $msg ="Image uploaded Successfully";
          }else{
            $msg ="There was a problem uploading image";
          }
        if($result == 1){
            $_SESSION['room_status'] = '<div class="alert alert-success alert-dismissible">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Success!</strong>Room Details Updated Successfully.
            </div>';
            header("Location: ".SITE_URL."/rooms");
        }else{
            $_SESSION['room_status'] = '<div class="alert alert-success alert-dismissible">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Failed!</strong>Room Upload Failed.
            </div>';
        }
}elseif (isset($_POST['update_rooms_details_btn'])) {
    trim(extract($_POST));
    // `room_id`, `rtid`, `room_number`, `room_pic`, `room_status`
    $sql = $dbh->query("UPDATE rooms SET room_number = '$room_number', room_status = '$room_status', room_price = '$room_price' WHERE room_id = '$room_id' ");
    if ($sql) {
        echo "<script>
            alert('Room Updated Successful');
            window.location = '".SITE_URL."/rooms';
            </script>";
    }else{
        echo "<script>
            alert('Room Update Failed');
            window.location = '".SITE_URL."/rooms';
            </script>";
    }
}elseif (isset($_POST['submit_room_type_details_btn'])) {
    trim(extract($_POST));
    //`rtid`, `rt_name`, `rt_pic`, `rt_price`, `discount`
     $filename = trim($_FILES['rt_pic']['name']);
     $chk = rand(1111111111111,9999999999999);
     $ext = strrchr($filename, ".");
     $rt_pic = $chk.$ext;
     $target_img = "uploads/".$rt_pic;
     $url = SITE_URL.'/uploads/'.$rt_pic;
     $rt_name = addslashes($rt_name);
    $chk = $dbh->query("SELECT rt_name FROM room_types WHERE (rt_name='$rt_name' ) ")->fetchColumn();
    if(!$chk){
        $result = dbCreate("INSERT INTO room_types VALUES(NULL,'$rt_name','$url','$rt_price','$discount')");
        if (move_uploaded_file($_FILES['rt_pic']['tmp_name'], $target_img)) {
          $msg ="Image uploaded Successfully";
          }else{
            $msg ="There was a problem uploading image";
          }
        if($result == 1){
            $_SESSION['room_status'] = '<div class="alert alert-success alert-dismissible">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Success!</strong>Room Type Added Successfully.
            </div>';
            header("Location: ".SITE_URL."/room-types");
            }else{
                echo "<script>
                alert('Error in uploading Room Type');
                window.location = '".SITE_URL."/room-types';
                </script>";
            }
        }else{
            echo "<script>
            alert('This Room Type already exists');
            window.location = '".SITE_URL."/room-types';
            </script>";
        }
}elseif (isset($_POST['update_room_type_details_btn'])) {
    trim(extract($_POST));
    // `rtid`, `rt_price`, `discount`
    $sql = $dbh->query("UPDATE room_types SET rt_price = '$rt_price', discount = '$discount' WHERE rtid = '$rtid' ");
    if ($sql) {
        echo "<script>
            alert('Room Type Updated Successful');
            window.location = '".SITE_URL."/room-types';
            </script>";
    }else{
        echo "<script>
            alert('Room Type Update Failed');
            window.location = '".SITE_URL."/room-types';
            </script>";
    }
}elseif (isset($_POST['submit_Stock_btn'])) {
    trim(extract($_POST));
    $check = $dbh->query("SELECT product_name FROM products WHERE product_name='$product_name' ")->fetchColumn();
    if(!$check){
        // $stock_date_added = time();
        $userid = $_SESSION['userid'];
        $result = dbCreate("INSERT INTO products(`pid`, `product_name`, `cost_price`, `selling_price`, `qty`, `stock_added_by`, `stock_date_added`) 
            VALUES(NULL,'$product_name','$cost_price','$selling_price',1,'$userid','$today')" );
        if($result == 1){
            $id = $dbh->query("SELECT pid FROM products ORDER BY pid DESC LIMIT 1")->fetchColumn();
            $dbh->query("INSERT INTO store VALUES('','$id','$qty')");
            echo "<script>
            alert('Stock added successfuly');
            window.location = 'manage-stock';
            </script>";
        }else{
            echo "<script>
            alert('Stock registration failed. ');
            window.location = 'manage-stock';
            </script>";
        }
    }else{
        echo "<script>
            alert('This stock is already added... Try adding another stock');
            window.location = 'manage-stock';
            </script>";
    }

}elseif (isset($_POST['update_stock'])) {
   trim(extract($_POST));
      //`pid`, `product_name`, `cost_price`, `selling_price`, `qty`, `stock_added_by`, `stock_date_added`
      $result = dbCreate("UPDATE products SET cost_price='$cost_price', selling_price = '$selling_price' WHERE pid='$pid' ");
      if($result == 1){
         echo "<script>
        alert('Product updated successfully');
        window.location = '".SITE_URL."/manage-stock';
        </script>";
      }else{
        echo "<script>
        alert('Product updated failed');
        window.location = '".SITE_URL."/manage-stock';
        </script>";
    }
}elseif(isset($_POST['add_stock'])){
    trim(extract($_POST));
    $old_qty = $dbh->query("SELECT quantity FROM store WHERE pid='$identity' ")->fetchColumn();
    if($action == 'add'){
        $new_qty = ($old_qty + $quantity);
    }else{
        $new_qty = ($old_qty - $quantity);
    }
    if($new_qty >= 0){
        $dbh->query("UPDATE store SET quantity='$new_qty' WHERE pid='$identity' ");
        redirect_page(SITE_URL.'/manage-stock');
    }else{
        echo "<script>
        alert('Quantity can not be negative, please check the quantity or action');
        window.location = '".SITE_URL."/manage-stock';
        </script>";
    }
}elseif (isset($_POST['submit_menu_type_details_btn'])) {
    trim(extract($_POST));
      //mt_id, menu_type
    $ch = $dbh->query("SELECT menu_type FROM menu_types WHERE menu_type = '$menu_type' ")->fetchColumn(); 
    if (!$ch) {
          $result = dbCreate("INSERT INTO  menu_types VALUES(NULL, '$menu_type') ");
          if($result == 1){
             echo "<script>
            alert('Menu Type added successfully');
            window.location = '".SITE_URL."/menu';
            </script>";
          }else{
            echo "<script>
            alert('Menu Type adding failed');
            window.location = '".SITE_URL."/menu';
            </script>";
        }
    }else{
          echo "<script>
            alert('This Menu Type already exists');
            window.location = '".SITE_URL."/menu';
            </script>";
    }

}elseif (isset($_POST['submit_menu_item_details_btn'])) {
    trim(extract($_POST));
    //`mid`, `mt_id`, `mname`, `mdesc`, `mpic`, `mprice`
    $filename = trim($_FILES['mpic']['name']);
    $chk = rand(1111111111111,9999999999999);
    $ext = strrchr($filename, ".");
    $mpic = $chk.$ext;
    $target_img = "uploads/".$mpic;
    $url = SITE_URL.'/uploads/'.$mpic;
    $rt_name = addslashes($rt_name);
    $result = $dbh->query("INSERT INTO menus VALUES(NULL,'$mt_id', '$mname', '$mdesc', '$url','$mprice') ");
    if (move_uploaded_file($_FILES['mpic']['tmp_name'], $target_img)) {
      $msg ="Image uploaded Successfully";
      }else{
        $msg ="There was a problem uploading image";
    }
    if($result){
    echo "<script>
      alert('Menu Item added successfully');
      window.location = '".SITE_URL."/menu';
      </script>";  
    }else{
        echo "<script>
          alert('Menu details failed');
          window.location = '".SITE_URL."/menu';
          </script>";
    } 
}elseif (isset($_POST['submit_hotel_type_details_btn'])) {
    trim(extract($_POST));
    //`hotel_id`, `hotel_name`, `hotel_desc`, `hotel_photo`, `hotel_address`
    $filename = trim($_FILES['hotel_photo']['name']);
    $chk = rand(1111111111111,9999999999999);
    $ext = strrchr($filename, ".");
    $hotel_photo = $chk.$ext;
    $target_img = "uploads/".$hotel_photo;
    $url = SITE_URL.'/uploads/'.$hotel_photo;
    $hotel_name = addslashes($hotel_name);
    $hotel_desc = addslashes($hotel_desc);
    $hotel_address = addslashes($hotel_address);
    
    $result = $dbh->query("INSERT INTO hotels VALUES(NULL,'$hotel_name', '$hotel_desc', '$url','$hotel_address') ");
    if (move_uploaded_file($_FILES['hotel_photo']['tmp_name'], $target_img)) {
      $msg ="Image uploaded Successfully";
      }else{
        $msg ="There was a problem uploading image";
    }
    if($result){
    echo "<script>
      alert('Menu Item added successfully');
      window.location = '".SITE_URL."/hotels';
      </script>";  
    }else{
        echo "<script>
            alert('Menu details failed');
            window.location = '".SITE_URL."/hotels';
            </script>";
    } 
}elseif (isset($_POST['submit_hotel_amenity_details_btn'])) {
    trim(extract($_POST));
    //`ha_id`, `hotel_id`, `amenities`
    $amenities = addslashes($amenities);
    $result = $dbh->query("INSERT INTO hotel_amenities VALUES(NULL,'$hotel_id', '$amenities') ");
    if($result){
    echo "<script>
      alert('Hotel Amenity Item added successfully');
      window.location = '".SITE_URL."/hotels';
      </script>";  
    }else{
        echo "<script>
          alert('Hotel Amenity failed');
          window.location = '".SITE_URL."/hotels';
          </script>";
    } 
}


?>
