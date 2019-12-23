<?php
    ob_start();
    require_once ('init.php');
    require_once ('functions.php');
?>

<?php
	if (isset($_GET['userIDSend']) && isset($_GET['userIDRecive'])) {
		$userIDSend = $_GET['userIDSend'];
		$userIDRecive = $_GET['userIDRecive'];
		$sucess = sendFriendRequest($userIDSend, $userIDRecive);
		if ($sucess == false) {
			// đã gửi lời mời kết bạn rồi thì sẽ bị fail khi insert nữa, cho nên nếu click vô nữa thì có nghĩa là người ta muốn hủy gửi lời mời kết bạn
			cancelFriendRequest($userIDSend, $userIDRecive);
		}
		header("Location: trang-ca-nhan.php?userID=$userIDRecive");
	}
?>


<?php include 'footer.php'; ?>