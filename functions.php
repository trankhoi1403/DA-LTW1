<?php 
ob_start(); // xóa kí tự lạ đầu file

include "PHPMailer-master/src/PHPMailer.php";
include "PHPMailer-master/src/Exception.php";
include "PHPMailer-master/src/OAuth.php";
include "PHPMailer-master/src/POP3.php";
include "PHPMailer-master/src/SMTP.php";
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// trả về số lượng tin nhắn mới chưa đọc (hasRead='no')
function totalInboxRequest($userID){
	global $db;
	$stmt = $db->prepare("SELECT * FROM messages WHERE hasRead='no' and toUserID=?");
	$stmt->execute(array($userID));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return sizeof($row);
}

function getMessage($messageID){
	global $db;
	$stmt = $db->prepare("SELECT * FROM messages WHERE messageID=?");
	$stmt->execute(array($messageID));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row;
}

// trả về mảng những người đã từng nhắn tin với $userID
function totalFriendHasSendMessage($userID){
	global $db;
    $arrID = array(1);
    array_pop($arrID);

    // những id mình gửi tin nhắn
	$stmt = $db->prepare("SELECT toUserID 
							FROM messages 
							WHERE fromUserID=?");
	$stmt->execute(array($userID));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
    	array_push($arrID, $row['toUserID']); 
    }

    // những id họ gửi cho mình
	$stmt = $db->prepare("SELECT fromUserID
							FROM messages 
							WHERE toUserID=?");
	$stmt->execute(array($userID));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
    	array_push($arrID, $row['fromUserID']); 
    }


    $arrID = array_unique($arrID);
    return $arrID;
}

// lấy tin nhắn (content) mới nhất của hai người
function layTinNhanMoiNhat($fromUserID, $toUserID){
	global $db;
	$stmt = $db->prepare("SELECT * 
							FROM messages
							WHERE ((fromUserID=? and toUserID=?) or (toUserID=? and fromUserID=?))
							ORDER BY timecreate DESC ");
	$stmt->execute(array($fromUserID, $toUserID, $fromUserID, $toUserID));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	return $row;
}

// trả về một tin nhắn mới nhất
function getNewMessageHTML($fromUserID, $toUserID){
	global $db;
	$stmt = $db->prepare("SELECT * 
							FROM messages m, myuser u
							WHERE ((m.fromUserID=? and m.toUserID=?) or (m.toUserID=? and m.fromUserID=?))
									and m.fromUserID=u.userID
							ORDER BY timecreate DESC ");
	$stmt->execute(array($fromUserID, $toUserID, $fromUserID, $toUserID));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	$html = '';
	$content = $row['content'];
	$avatarPath = $row['avatar'];

	// chỉ lấy dòng mới nhất mà chưa đọc thôi
	if ($row['hasRead'] == 'no') {

		// nếu như content là của người gửi thì hiển thị bên form người gửi
		if ($row['fromUserID'] == $fromUserID) {
			$html = "<div class='row'>
						  <div class='col-3'></div>
						  <div class='col-7 fromMessage'>
						  	<p>" . $content . "</p>
						  </div>
						  <div class='col-1'><img src='" . $avatarPath . "' class='avatar'></div>
				    </div>";
		}
		else{
			$html = "<div class='row'>
						  <div class='col-1'><img src='" . $avatarPath . "' class='avatar'></div>
						  <div class='col-7 toMessage'>
						  	<p>" . $content . "</p>
						  </div>
						  <div class='col-3'></div>
				    </div>";
		}

			// lấy xong rồi thi đánh dấu là đã đọc rồi
		$stmt = $db->prepare("UPDATE messages SET hasRead='yes' WHERE messageID=?");
		$stmt->execute(array($row['messageID']));
	}
	return $html;
}

function loadMessageToHTML($fromUserID, $toUserID){
	global $db;
	$stmt = $db->prepare("SELECT * 
							FROM messages m, myuser u
							WHERE ((m.fromUserID=? and m.toUserID=?) or (m.toUserID=? and m.fromUserID=?))
									and m.fromUserID=u.userID
							ORDER BY timecreate ASC ");
	$stmt->execute(array($fromUserID, $toUserID, $fromUserID, $toUserID));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$result = '';
	foreach ($rows as $row) {
		$html = '';
		$content = $row['content'];
		$avatarPath = $row['avatar'];
		// nếu như content là của người gửi thì hiển thị bên form người gửi
		if ($row['fromUserID'] == $fromUserID) {
			$html = "<div class='row'>
						  <div class='col-3'></div>
						  <div class='col-7 fromMessage'>
						  	<p>" . $content . "</p>
						  </div>
						  <div class='col-1'><img src='" . $avatarPath . "' class='avatar'></div>
				    </div>";
		}
		else{
			$html = "<div class='row'>
						  <div class='col-1'><img src='" . $avatarPath . "' class='avatar'></div>
						  <div class='col-7 toMessage'>
						  	<p>" . $content . "</p>
						  </div>
						  <div class='col-3'></div>
				    </div>";
		}
		$result = $result . "<br>" . $html;
	}

	return $result;
}

function insertMessage($fromUserID, $toUserID, $content){
	global $db;
	$stmt = $db->prepare("INSERT INTO messages(fromUserID, toUserID, content, timecreate) values (?, ?, ?, now())");
	$stmt->execute(array($fromUserID, $toUserID, $content));
}

// kiểm tra xem phải post của người đó hay không
function checkPostOfUser($postID, $userID){
	global $db;
	$stmt = $db->prepare("SELECT * 
							FROM mypost
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

// trả về privacy của bài post()
function getPrivacy($postID){
	global $db;
	$stmt = $db->prepare("SELECT * 
							FROM mypost
							WHERE postID = ?");
	$stmt->execute(array($postID));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	return $row['privacy'];
}

// trả về html các hình ảnh của bài post đó
//	<img src="pictures/30_0.jpg" style="width: 100px; height: 100px;">
function inDSPicPostHTML($postID){
	global $db;
	$stmt = $db->prepare("SELECT * 
							FROM post_picture
							WHERE postID = ?");
	$stmt->execute(array($postID));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$htmlResult = '';
	foreach ($rows as $row) {
		$htmlResult = $htmlResult . "<img src='" . $row['picturePath'] . "' style='width: 100px; height: 100px;'>";
	}
	return $htmlResult;
}

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
//<p style='width: 800px;'><strong><a href='trang-ca-nhan.php?userID=2'>tên gì đó</a></strong>: <abbr title='3:34:4'>fsdf</abbr></p>

function inDSCmtHTML($postID){
	$rows = loadCmtForPost($postID);
	$result = "";
	foreach ($rows as $row) {
		$time = date_format(date_create($row['timecreate']),"d/m/Y H:i:s");
		$uid = $row['userID'];
		$fname = $row['fullname'];
		$content = $row['content'];
		$result = $result . 
		"<p style='width: 800px;'>
			<strong>
				<a href='trang-ca-nhan.php?userID=" . $uid . "'>" . $fname . "</a>
			</strong> : " . 
			"<abbr title='" . "Đã bình luận lúc " . $time . "'>" . $content . "</abbr>
		</p>";
	}
	return $result;
}
//		<abbr title='3:34:4'><p style='width: 800px;'><strong><a href='trang-ca-nhan.php?userID=2'>tên gì đó</a></strong> : fsdf</p></abbr>

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

// kiểm tra xem $follwer có theo dõi userID không
function checkFollow($userID, $follower){
	global $db;
	$stmt = $db->prepare("SELECT * FROM follows WHERE userID=? and follower=?");
	$stmt->execute(array($userID, $follower));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row == null) {
    	return false;
    }
    else {
    	return true;
    }
}

// thêm vào db
function sendFollow($userID, $follower){
	global $db;
	$stmt = $db->prepare("INSERT INTO follows(userID, follower, timecreate) values (?, ?, now())");
	$stmt->execute(array($userID, $follower));
}

function cancelFollow($userID, $follower){
	global $db;
	$stmt = $db->prepare("DELETE FROM follows WHERE userID=? and follower=?");
	$stmt->execute(array($userID, $follower));
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


// trả về những người có mối quan hệ (gửi lời mời, theo dõi, bạn bè)
function totalUserRela($userID){
	global $db;
	$arrID = array(1);
	array_pop($arrID);

    // những người gửi lời mời cho mình, dù đã hay chưa chấp nhận
	$stmt = $db->prepare("	SELECT userIDSend
							FROM friends 
							WHERE 	userIDRecive=?");
	$stmt->execute(array($userID));
    $row1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row1 as $r) {
		array_push($arrID, $r['userIDSend']);
    }    

    // những người mình gửi lời mời cho họ
	$stmt = $db->prepare("	SELECT userIDRecive
							FROM friends 
							WHERE 	userIDSend=?");
	$stmt->execute(array($userID));
    $row2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row2 as $r) {
		array_push($arrID, $r['userIDRecive']);
    }    
    

    // những người họ theo dõi mình
	$stmt = $db->prepare("	SELECT follower
							FROM follows 
							WHERE userID=?");
	$stmt->execute(array($userID));
    $row3 = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row3 as $r) {
		array_push($arrID, $r['follower']);
    }    

    // những người mình theo dõi
	$stmt = $db->prepare("	SELECT userID
							FROM follows 
							WHERE follower=?");
	$stmt->execute(array($userID));
    $row4 = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row4 as $r) {
		array_push($arrID, $r['userID']);
    }    

    // lọc hết những id trùng
    $row = array_unique($arrID);

    return $row;	
}

// kiểm tra hai người có là bạn của nhau không
function isFriend($userIDSend, $userIDRecive){
	global $db;
	$stmt = $db->prepare("SELECT * 
							FROM friends 
							WHERE status=? and ((userIDSend=? and userIDRecive=?) or (userIDRecive=? and userIDSend=?))");
	$stmt->execute(array(true, $userIDSend, $userIDRecive, $userIDSend, $userIDRecive));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row == null) {
    	return false;
    }
    else{
    	return true;
    }
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

function insertPost($userID, $content, $privacy){
    global $db;
    $stmt = $db->prepare("INSERT INTO mypost(userID, content, timecreate, privacy) VALUES (?,?, now(), ?)");
    $stmt->execute(array($userID, $content, $privacy));
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

// đổi tên file trong đường dẫn filepath thành postID_index (index: thứ tự hình ảnh trong bài post, tính từ 1 trở đi)
function renamePicturePost($filepath, $postID, $index){
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