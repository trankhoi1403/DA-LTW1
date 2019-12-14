<?php
 ob_start(); // xóa kí tự lạ đầu file
  require_once ('init.php');
  require_once ('functions.php');
?>

<?php 
  include 'check-before-login.php';
  include 'header.php';
?>
	<div class="container">
		<h1>Chào mừng <?php echo $currentUser['fullname'] ?> đã quay trở lại</h1>
		<form method="POST" action="create-post.php">
			<div class="form-group">
				<textarea class="form-control" rows="3" id="post" name="content" placeholder="Bạn đang nghĩ gì ?"></textarea>
			</div>
			<div class="form-group row">
				<div class="col-sm-3">
					<button type="submit" class="btn btn-primary">Đăng</button>
				</div>
			</div>
		</form>
		<?php 
			$stmt = $db->prepare("SELECT * FROM mypost p, myuser u WHERE p.userID = u.userID ORDER BY p.postID DESC");
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		?>
		<?php	
			foreach ($rows as $row) :?>
				<div class="card" style="margin-top: 10px; border: 2px solid grey; border-radius: 10px;">
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
<?php include 'footer.php'; ?>