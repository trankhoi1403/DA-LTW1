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
			$rows = totalUserFriendRequest($currentUser['userID']);
		?>
		<div class="container">
			<?php foreach ($rows as $row): ?>
				<?php 
					$getUser=getUserByID($row['userIDSend']); 
					$rela = checkRelationship($currentUser['userID'], $getUser['userID']);
				?>
				<div class="card" style="width: 18rem; float: left;">
					<img src="<?php echo $getUser['avatar']?>" class="card-img-top" alt="...">
					<div class="card-body">
						<h5 class="card-title"><?php echo $getUser['fullname']?></h5>
						<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
						<a href="xulyFriends.php?currentUserID=<?php echo $currentUser['userID']?>&userID=<?php echo $getUser['userID']?>&rela=<?php echo $rela?>" 
							class="btn btn-primary"
							id="btnAddFr">
							Chấp nhận
						</a>
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