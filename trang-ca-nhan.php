<?php
ob_start(); // xóa kí tự lạ đầu file
require_once 'init.php';
require_once 'functions.php';

?>

<?php 
  include 'check-before-login.php';
  include 'header.php';
?>		

<?php if (isset($_GET['userID'])):?>
		<?php 
			$getUser=getUserByID($_GET['userID']); 
		?>
	<div class="sidenav">
		<div class="card" style="width: 18rem;">
			<img src="<?php echo $getUser['avatar']?>" class="card-img-top" alt="...">
			<div class="card-body">
				<h5 class="card-title"><?php echo $getUser['fullname']?></h5>
				<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
				
				<?php if ($getUser['userID'] == $currentUser['userID']): ?>
					<a href="update-profile.php" class="btn btn-primary">Cập nhật thông tin</a>
				<?php else: ?>
					<?php $rela = checkRelationship($currentUser['userID'], $getUser['userID']); ?>

					<a href="xulyFriends.php?currentUserID=<?php echo $currentUser['userID']?>&userID=<?php echo $getUser['userID']?>&rela=<?php echo $rela?>" 
						class="btn btn-primary"
						id="btnAddFr">
						<?php 
							//var_dump($rela);
							switch ($rela) {
								case 'NotFriend':
									echo "Kết bạn";
									break;
								case 'Friend':
									echo "Bạn bè";
									break;
								case 'currentWaitingForAccept':
									echo "Đã gửi lời mời";
									break;
								case 'currentReciveFriendRequest':
									echo "Chấp nhận lời mời";
									break;
								default:
									echo "lỗi";
									break;
							}
						?>
					</a>						
					<?php if ($rela == "currentReciveFriendRequest"): ?>
						<a href="trang-ca-nhan.php?currentUserID=<?php echo $currentUser['userID']?>&userID=<?php echo $getUser['userID']?>&rela=<?php echo $rela?>&deny=true" class="btn btn-primary" id="btnDeny">Từ chối</a>
					<?php else: ?>
						<a href="trang-ca-nhan.php" class="btn btn-primary" id="btnDeny">Theo dõi</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="main">
		<?php 
				$stmt = $db->prepare("SELECT * 
		                                FROM mypost p, myuser u 
		                                WHERE p.userID = u.userID and u.userID = ?
		                                ORDER BY p.postID DESC");
				$stmt->execute(array($getUser['userID']));
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			?>
		<?php	
			foreach ($rows as $row) :?>
				<div class="card" style="top: auto; margin-top: 10px; border: 2px solid grey; border-radius: 10px;">
					 <div class="card-body">
					 	<div style="margin-bottom: 20px;">
						 	<img src="<?php echo $row['avatar']?>" alt="Avatar" class="avatar" style="float: left; border-radius: 20%; margin-right: 10px;">
							<h3><?php echo  $row['fullname']; ?></h3>					 		
							<p>Đăng lúc <?php echo  $row['timecreate']; ?></p>
					 	</div>
						<textarea class="form-control" rows="<?php echo getTotalLine($row['content']); ?>" readonly="readonly"><?php echo  $row['content']; ?></textarea>
					 </div>
				</div>
		<?php endforeach;?>
	</div>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        Đường dẫn bị lỗi
    </div>
<?php endif; ?>
<?php
include "footer.php";
ob_end_flush(); // xóa các kí tự lạ cuối file
?>