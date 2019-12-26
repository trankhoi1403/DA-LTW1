<?php
 ob_start(); // xóa kí tự lạ đầu file
  require_once ('init.php');
  require_once ('functions.php');
?>

<?php 
  include 'check-before-login.php';
  include 'header.php';
?>

<?php
	if (isset($currentUser)) :?>
		<?php 
			// lấy ra id những người đã từng nhắn tin với current
			$rows = totalFriendHasSendMessage($currentUser['userID']);
			if (sizeof($rows) == 0) {
				die("Không có tin nhắn");
			}
		?>
		<div class="container">
			<?php foreach ($rows as $row): ?>
				<?php 
					$getUser=getUserByID($row); 
					$message=layTinNhanMoiNhat($currentUser['userID'], $row);
				?>
					<div class="card" style="margin: 10px 50px 50px 100px; border: 2px solid grey; border-radius: 10px; height: auto; width: 500px;">
						 <div class="card-body">
						 	<div style="margin-bottom: 20px;">
							 	<img src="<?php echo $getUser['avatar']?>" alt="Avatar" class="avatar" style="float: left; border-radius: 20%; margin-right: 10px;">
								<a href="trang-ca-nhan.php?userID=<?php echo $getUser['userID'];?>">
									<h3><?php echo  $getUser['fullname']; ?></h3>
								</a>
								<a style="float: right;" href="messenger.php?userID=<?php echo $getUser['userID'];?>">
									Trả lời
								</a>
								<span><?php echo date_format(date_create($message['timecreate']),"d/m/Y H:i:s"); ?></span>
							</div>
							<div style="background-color: gray;">
								<?php echo $message['content'];  ?>
							</div>
						</div>
					</div>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
		<?php 
			header("Location: login.php");
		?>
	<?php endif; ?>
<?php
include "footer.php";
ob_end_flush(); // xóa các kí tự lạ cuối file
?>