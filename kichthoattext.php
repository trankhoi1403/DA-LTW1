<?php
// if the sign up form was submitted
if($_POST){
 
    $email = isset($_POST['email']) ? $_POST['email'] : "";
 
    // posted email must not be empty
    if(empty($email)){
        echo "<div>Email cannot be empty.</div>";
    }
 
    // must be a valid email address
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<div>Your email address is not valid.</div>";
    }
 
    else{
       //include database connection
        ...
 
        // check first if record exists
        $query = "SELECT id FROM users WHERE email = ? and verified = '1'";
        $stmt = $con->prepare( $query );
        $stmt->bindParam(1, $email);
        $stmt->execute();
        $num = $stmt->rowCount();
 
        if($num>0){
            echo "<div>Your email is already activated.</div>";
        }
 
        else{
 
            // check first if there's unverified email related
            $query = "SELECT id FROM users WHERE email = ? and verified = '0'";
            $stmt = $con->prepare( $query );
            $stmt->bindParam(1, $email);
            $stmt->execute();
            $num = $stmt->rowCount();
 
            if($num>0){
 
                // you have to create a resend verification script
                echo "<div>Your email is already in the system but not yet verified. <a href='resend.php'>Resend verification?</a>.</div>";
            }
 
            else{
 
                // now, compose the content of the verification email, it will be sent to the email provided during sign up
                // generate verification code, acts as the "key"
                $verificationCode = md5(uniqid("yourrandomstringyouwanttoaddhere", true));
 
                // send the email verification
                $verificationLink = "http://example.com/activate.php?code=" . $verificationCode;
 
                $htmlStr = "";
                $htmlStr .= "Hi " . $email . ",<br /><br />";
 
                $htmlStr .= "Please click the button below to verify your subscription and have access to the download center.<br /><br /><br />";
                $htmlStr .= "<a href='{$verificationLink}' target='_blank' style='padding:1em; font-weight:bold; background-color:blue; color:#fff;'>VERIFY EMAIL</a><br /><br /><br />";
 
                $htmlStr .= "Kind regards,<br />";
                $htmlStr .= "<a href='https://www.hoangweb.com/' target='_blank'>Dịch vụ thiết kế web giá rẻ</a><br />";
 
 
                $name = "Hoangweb.com";
                $email_sender = "no-reply@hoangweb.com";
                $subject = "Verification Link | Thiết kế web giá rẻ | Subscription";
                $recipient_email = $email;
 
                $headers  = "MIME-Version: 1.0rn";
                $headers .= "Content-type: text/html; charset=iso-8859-1rn";
                $headers .= "From: {$name} <{$email_sender}> n";
 
                $body = $htmlStr;
 
                // send email using the mail function, you can also use php mailer library if you want
                if( mail($recipient_email, $subject, $body, $headers) ){
 
                    // tell the user a verification email were sent
                    echo "<div id='successMessage'>A verification email were sent to <b>" . $email . "</b>, please open your email inbox and click the given link so you can login.</div>";
 
 
                    // save the email in the database
                    $created = date('Y-m-d H:i:s');
 
                    //write query, verified = '0' means it is unverified, on activation, it becomes '1'
                    $query = "INSERT INTO 
                                users 
                            SET 
                                email = ?, 
                                verification_code = ?, 
                                created = ?,
                                verified = '0'";
 
                    $stmt = $con->prepare($query);
 
                    $stmt->bindParam(1, $email);
                    $stmt->bindParam(2, $verificationCode);
                    $stmt->bindParam(3, $created);
 
                    // Execute the query
                    if($stmt->execute()){
                        // echo "<div>Unverified email was saved to the database.</div>";
                    }else{
                        echo "<div>Unable to save your email to the database. <a href='https://www.hoangweb.com/lien-he'>Contact Us.</a></div>";
                        //print_r($stmt->errorInfo());
                    }
 
                }else{
                    die("Sending failed.");
                }
            }
 
 
        }
 
    }
 
}
 
// show your sign up or registration form
echo "<form action='" . $_SERVER[PHP_SELF] . "' method='post'>";
    echo "<input type='email' name='email' placeholder='Enter your email address to subscribe' required />";
    echo "<input type='submit' value='Subscribe' />";
echo "</form>";
?>