<?php 
ob_start(); // xóa kí tự lạ đầu file

include "PHPMailer-master/src/PHPMailer.php";
include "PHPMailer-master/src/Exception.php";
include "PHPMailer-master/src/OAuth.php";
include "PHPMailer-master/src/POP3.php";
include "PHPMailer-master/src/SMTP.php";
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function insertCmtForPost($postID, $userID, $content){
	global $db;
	$stmt = $db->prepare("INSERT INTO comments(postID, userID, content, timecreate) values (?, ?, ?, now())");
	$stmt->execute(array($postID, $userID, $content));
}

function loadCmtForPost($postID){
	global $db;
	$stmt = $db->prepare("SELECT *
							FROM comments c, myuser u
							WHERE 	c.userID = u.userID and
									postID=?
							Order by c.timecreate");
	$stmt->execute(array($postID));
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// xuất danh sách các comment dưới dạng html
function inDSCmtHTML($postID){
	$rows = loadCmtForPost($postID);
	$result = "";
	foreach ($rows as $row) {
		$time = date_format(date_create($row['timecreate']),"d/m/Y H:i:s");
		$uid = $row['userID'];
		$fname = $row['fullname'];
		$content = $row['content'];
		$result = $result . "<p style='width: 800px;'>" . $time . " <strong><a href='trang-ca-nhan.php?userID=" . $uid . "'>" . $fname . "</a></strong> : " . $content . "</p>";
	}
	return $result;
}

// kiểm tra xem ai đó có like bài viết đó hay không
function checkLike($postID, $userID){
	global $db;
	$stmt = $db->prepare("SELECT *
						 	FROM likes
						 	WHERE postID=? and userID=?");
	$stmt->execute(array($postID, $userID));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row == null) {
    	return false;
    }
    else {
    	return true;
    }
}

// tính tổng số like của một bài viết
function totalLike($postID){
	global $db;
	$stmt = $db->prepare("SELECT count(*) as 'total'
						 	FROM likes
						 	WHERE postID=?");
	$stmt->execute(array($postID));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'];
}

function setLikeForPost($postID, $userID, $likeValue){
	global $db;
	if ($likeValue == 'true') {
		$stmt = $db->prepare("INSERT INTO likes(postID, userID, timecreate) values (?, ?, now())");
		$stmt->execute(array($postID, $userID));
	}
	else if ($likeValue == 'false') {
		$stmt = $db->prepare("DELETE FROM likes WHERE postID=? and userID=?");
		$stmt->execute(array($postID, $userID));
	}
}

// trả về số lượng các lời mời kết bạn được nhận
function totalFriendRequest($userID){
	global $db;
	$stmt = $db->prepare("SELECT * FROM friends WHERE userIDRecive=? and status=?");
	$stmt->execute(array($userID, false));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return sizeof($row);
}

// trả về những người đã gửi lời mời kết bạn cho mình
function totalUserFriendRequest($userID){
	global $db;
	$stmt = $db->prepare("	SELECT userIDSend
							FROM friends 
							WHERE 	userIDRecive=?
									and status=?");
	$stmt->execute(array($userID, false));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $row;
}

// kiểm tra xem có đang chờ kết bạn từ một ai đó
function isFriendRequest($userIDSend, $userIDRecive){
	global $db;
	$stmt = $db->prepare("SELECT * FROM friends WHERE userIDSend=? and userIDRecive=? and status=?");
	$stmt->execute(array($userIDSend, $userIDRecive, false));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row == null) {
    	return false;
    }
    else{
    	return true;
    }
}

// current gửi lời mời kết bạn
function sendFriendRequest($currentUserID, $userID){
	$stmt = $GLOBALS["db"]->prepare("insert into friends(userIDSend, userIDRecive, status, timecreate) values (?,?,?,now())");
	$sucess = $stmt->execute(array($currentUserID, $userID, false));
	return $sucess;
}

// current hủy lời mời kết bạn của mình tới ai đó
function cancelFriendRequest($currentUserID, $userID){
	global $db;
	$stmt = $db->prepare("DELETE FROM friends WHERE userIDSend=? and userIDRecive=? and status=false");
	$stmt->execute(array($currentUserID, $userID));
}

// current chấp nhận lời mời từ một ai đó
function acceptFriendRequest($currentUserID, $userID){
	global $db;
	$stmt = $db->prepare("UPDATE friends SET status=true , timecreate=now() WHERE userIDSend=? and userIDRecive=?");
	$stmt->execute(array($userID, $currentUserID));
}

// hủy kết bạn
function cancelFriend($currentUserID, $userID){
	global $db;
	$stmt = $db->prepare("DELETE FROM friends WHERE userIDSend=? and userIDRecive=?");
	$stmt->execute(array($currentUserID, $userID));
	$stmt->execute(array($userID, $currentUserID));
}

// kiểm tra quan hệ giữa hai người
function checkRelationship($currentUserID, $userID){

	// kiểm tra xem người 1 có gửi lời mời cho người 2 hay hong
	global $db;
	$stmt = $db->prepare("SELECT * FROM friends WHERE userIDSend=? and userIDRecive=?");
	$stmt->execute(array($currentUserID, $userID));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);	// chỉ lấy ra dòng đầu tiên
    if ($row != null) {
    	if ($row['status'] == true) {
    		return "Friend";
    	}
    	else{
    		return "currentWaitingForAccept";
    	}
    }
    else{

    	// kiểm tra ngược lại coi người kia có gửi lời mời cho current ko  
		$stmt = $db->prepare("SELECT * FROM friends WHERE userIDSend=? and userIDRecive=?");
		$stmt->execute(array($userID, $currentUserID));
	    $row = $stmt->fetch(PDO::FETCH_ASSOC);	// chỉ lấy ra dòng đầu tiên
		if ($row != null) {
			if ($row['status'] == true) {
				return "Friend";
			}
			else{
				return "currentReciveFriendRequest";
			}
		}
		else{
	    	return "NotFriend";
		}   
    }
}

function sendMail($email, $subject ,$htmlContent){
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {
	    //Server settings
	    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
	    $mail->isSMTP();                                      // Set mailer to use SMTP
	    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
	    $mail->SMTPAuth = true;                               // Enable SMTP authentication
	    $mail->Username = 'tk1660281@gmail.com';                 // SMTP username
	    $mail->Password = 'google1660281';                           // SMTP password
	    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	    $mail->Port = 587;                                    // TCP port to connect to
	 
	    //Recipients
	    $mail->CharSet = 'UTF-8';
	    $mail->setFrom('tk1660281@gmail.com', 'Đổi mật khẩu');
	    $mail->addAddress($email, 'User');     // Add a recipient
	    //$mail->addAddress('ellen@example.com');               // Name is optional
	    //$mail->addReplyTo('info@example.com', 'Information');
	    //$mail->addCC('cc@example.com');
	    //$mail->addBCC('bcc@example.com');
	 
	    //Attachments
	    //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	 
	    //Content
	    $mail->isHTML(true);                                  // Set email format to HTML
	    $mail->Subject = $subject;
	    $mail->Body    = $htmlContent;
	    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	 
	    $mail->send();
	    echo 'Message has been sent';
	} catch (Exception $e) {
	    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	}	
}

function detectPage(){
	$parts = explode('/', $_SERVER['REQUEST_URI']);
	$fileName = $parts[count($parts)-1];
	$page = explode('.', $fileName)[0];
	return $page;
}

function getRandomStr(){
	$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	// Output: 54esmdr0qf
	return substr(str_shuffle($permitted_chars), 0, 10);
}

// kiểm tra pass so với mật khẩu của người dùng đang đăng nhập
function checkPass($pass){
	if(isset($_SESSION['userID'])){
		if (password_verify($pass, getUserPasswordHash($_SESSION['userID']))) {
			return true;
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}

function getUserPasswordHash($userID){
    $stmt = $GLOBALS['db']->prepare("SELECT password FROM myuser WHERE userID = ?");
    $stmt->execute(array($userID));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['password'];
}

function setPassword($userID, $pass){
    $stmt = $GLOBALS['db']->prepare("UPDATE myuser SET password = ? WHERE userID = ?");
    $stmt->execute(array(password_hash($pass, PASSWORD_DEFAULT), $userID));
}

function setPasswordByCode($code, $pass){
    $stmt = $GLOBALS['db']->prepare("UPDATE myuser SET password = ? WHERE code = ?");
    $stmt->execute(array(password_hash($pass, PASSWORD_DEFAULT), $code));
}

function setCodeByEmail($email, $code){
    $stmt = $GLOBALS['db']->prepare("UPDATE myuser SET code = ? WHERE email = ?");
    $stmt->execute(array($code, $email));	
}


// Để có thể set được status thì cần phải có code được lấy từ gmail
// status = 0, chưa kích hoạt tài khoản, không làm được gì
// status = 1, đã kích hoạt tài khoản, có thể làm được mọi thứ, tuy nhiên không thể truy cập vào reset-password.php
// status = 2, không làm được gì, chỉ có thể truy cập vào trang reset-password.php
function setUserStatus($code, $status = 1){
    $stmt = $GLOBALS['db']->prepare("UPDATE myuser SET status = ? WHERE code = ?");
    $stmt->execute(array($status, $code));	
}

function unsetCurrentUser(){
    $GLOBALS['currentUser'] = null;
}

// sẽ reset password của user nào có status = 2, sau khi reset xong thì sẽ chuyển status thành 1
function resetPassword($newpass){
    $stmt = $GLOBALS['db']->prepare("UPDATE myuser SET password = ? AND status = ? WHERE status = ?");
    $stmt->execute(array(password_hash($newpass, PASSWORD_DEFAULT), 1, 2));
}

function getUserByID($id){
	$stmt = $GLOBALS['db']->prepare("SELECT * FROM myuser WHERE userID = ?");
	$stmt->execute(array($id));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['last_activity'] = time();

/*	setcookie('userID',  $userID , time()+3600);
	setcookie('email',  $row['email'] , time()+3600);
	setcookie('password',  $row['password'] , time()+3600);
	setcookie('username',  $row['username'] , time()+3600);
	setcookie('fullname',  $row['fullname'] , time()+3600);
	setcookie('phonenumber',  $row['phonenumber'] , time()+3600);
	setcookie('avatar',  $row['avatar'] , time()+3600);*/

	$currentUser['userID'] = $row['userID'];
	$currentUser['status'] = $row['status'];
	$currentUser['email'] = $row['email'];
	$currentUser['password'] = $row['password'];
	$currentUser['username'] = $row['username'];
	$currentUser['fullname'] = $row['fullname'];
	$currentUser['phonenumber'] = $row['phonenumber'];
	$currentUser['avatar'] = $row['avatar'];
	$currentUser['code'] = $row['code'];
	return $currentUser;
}

function getCurrentUser(){
	$userID = $_SESSION['userID'];
	$stmt = $GLOBALS['db']->prepare("SELECT * FROM myuser WHERE userID = ?");
	$stmt->execute(array($userID));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['last_activity'] = time();

/*	setcookie('userID',  $userID , time()+3600);
	setcookie('email',  $row['email'] , time()+3600);
	setcookie('password',  $row['password'] , time()+3600);
	setcookie('username',  $row['username'] , time()+3600);
	setcookie('fullname',  $row['fullname'] , time()+3600);
	setcookie('phonenumber',  $row['phonenumber'] , time()+3600);
	setcookie('avatar',  $row['avatar'] , time()+3600);*/

	$currentUser['userID'] = $row['userID'];
	$currentUser['status'] = $row['status'];
	$currentUser['email'] = $row['email'];
	$currentUser['password'] = $row['password'];
	$currentUser['username'] = $row['username'];
	$currentUser['fullname'] = $row['fullname'];
	$currentUser['phonenumber'] = $row['phonenumber'];
	$currentUser['avatar'] = $row['avatar'];
	$currentUser['code'] = $row['code'];
	return $currentUser;
}

function getCurrentUserByCode($code){
	$stmt = $GLOBALS['db']->prepare("SELECT * FROM myuser WHERE code = ?");
	$stmt->execute(array($code));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['last_activity'] = time();

/*	setcookie('userID',  $userID , time()+3600);
	setcookie('email',  $row['email'] , time()+3600);
	setcookie('password',  $row['password'] , time()+3600);
	setcookie('username',  $row['username'] , time()+3600);
	setcookie('fullname',  $row['fullname'] , time()+3600);
	setcookie('phonenumber',  $row['phonenumber'] , time()+3600);
	setcookie('avatar',  $row['avatar'] , time()+3600);*/

	$currentUser['userID'] = $row['userID'];
	$currentUser['status'] = $row['status'];
	$currentUser['email'] = $row['email'];
	$currentUser['password'] = $row['password'];
	$currentUser['username'] = $row['username'];
	$currentUser['fullname'] = $row['fullname'];
	$currentUser['phonenumber'] = $row['phonenumber'];
	$currentUser['avatar'] = $row['avatar'];
	$currentUser['code'] = $row['code'];
	return $currentUser;
}

function insertPost($userID, $content){
    global $db;
    $stmt = $db->prepare("INSERT INTO mypost(userID, content, timecreate) VALUES (?,?, now())");
    $stmt->execute(array($userID, $content));
    return $db->lastInsertId();
}

function getTotalLine($line){
	$array = explode("\n", $line);
	return count($array);
}

// chuyển file từ client lên server nếu kiểm tra đúng folder và đúng kiểu checkType thì mới úp 
function moveFile($name, $folder, $checkType){
	if (!isset($_FILES[$name])) {
		return "Không tìm thấy name: " . $name; 
	}
    if ($_FILES[$name]['error'] != 0)
    {
        return 'File Upload Bị Lỗi';
    }
    if (!file_exists($folder)) {
    	return "Không tìm thấy thư mục " . $folder;
    }
    if ($_FILES[$name]['type'] != $checkType) {
    	return $_FILES[$name]['type'] . " không phải kiểu file cần upload. Kiểu file cần upload là " . $checkType;
    }

	$fileName = $_FILES[$name]['name'];
	$fileSize = $_FILES[$name]['size'];
	$fileTemp = $_FILES[$name]['tmp_name'];
	$result = move_uploaded_file($fileTemp, $folder . '/' . $fileName);
    return 'true';
}

function renameAvatar($userID, $filepath){
	if (!file_exists($filepath)) {
		return "Error: Không tìm thấy đường dẫn " . $filepath;
	}
	$arrfilepath = explode('/', $filepath);
	$file = array_pop($arrfilepath);
	$filename = explode('.', $file)[0];

	$file = str_replace($filename, $userID, $file);

	array_push($arrfilepath, $file);
	$newfilepath = implode('/', $arrfilepath);
	rename($filepath, $newfilepath);
	return $newfilepath;
}

function resizeImage($filename, $max_width, $max_height)
{
	list($orig_width, $orig_height) = getimagesize($filename);

	$width = $orig_width;
	$height = $orig_height;

	# taller
	if ($height > $max_height) {
		$width = ($max_height / $height) * $width;
		$height = $max_height;
	}

	# wider
	if ($width > $max_width) {
		$height = ($max_width / $width) * $height;
		$width = $max_width;
	}

	$image_p = imagecreatetruecolor($width, $height);

	$image = imagecreatefromjpeg($filename);

	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

	return $image_p;
}

ob_end_flush(); // xóa các kí tự lạ cuối File
?>