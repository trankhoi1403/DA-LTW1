<?php
ob_start(); // xóa kí tự lạ đầu file

include "PHPMailer-master/src/PHPMailer.php";
include "PHPMailer-master/src/Exception.php";
include "PHPMailer-master/src/OAuth.php";
include "PHPMailer-master/src/POP3.php";
include "PHPMailer-master/src/SMTP.php";
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

    //goi thu vien
    include('class.smtp.php');
    include "class.phpmailer.php"; 
    $nFrom = "Freetuts.net";    //mail duoc gui tu dau, thuong de ten cong ty ban
    $mFrom = 'xxxx@gmail.com';  //dia chi email cua ban 
    $mPass = 'passlamatkhau';       //mat khau email cua ban
    $nTo = 'Huudepzai'; //Ten nguoi nhan
    $mTo = 'dinhhuugt@gmail.com';   //dia chi nhan mail
    $mail             = new PHPMailer();
    $body             = 'Bạn đang tìm hiểu về cách gửi email bằng php mailler trên freetuts.net chúc các bạn có         những phút giây vui vẻ.';   // Noi dung email
    $title = 'Hướng dẫn gửi mail bằng PHPMailer';   //Tieu de gui mail
    $mail->IsSMTP();             
    $mail->CharSet  = "utf-8";
    $mail->SMTPDebug  = 0;   // enables SMTP debug information (for testing)
    $mail->SMTPAuth   = true;    // enable SMTP authentication
    $mail->SMTPSecure = "ssl";   // sets the prefix to the servier
    $mail->Host       = "smtp.gmail.com";    // sever gui mail.
    $mail->Port       = 465;         // cong gui mail de nguyen
    // xong phan cau hinh bat dau phan gui mail
    $mail->Username   = $mFrom;  // khai bao dia chi email
    $mail->Password   = $mPass;              // khai bao mat khau
    $mail->SetFrom($mFrom, $nFrom);
    $mail->AddReplyTo('info@freetuts.net', 'Freetuts.net'); //khi nguoi dung phan hoi se duoc gui den email nay
    $mail->Subject    = $title;// tieu de email 
    $mail->MsgHTML($body);// noi dung chinh cua mail se nam o day.
    $mail->AddAddress($mTo, $nTo);
    // thuc thi lenh gui mail 
    if(!$mail->Send()) {
        echo 'Co loi!';
         
    } else {
         
        echo 'mail của bạn đã được gửi đi hãy kiếm tra hộp thư đến để xem kết quả. ';
    }
?>