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
		<form method="POST" action="create-post.php" enctype="multipart/form-data">
			<div class="form-group">
				<textarea style="width: 80%;" class="form-control" rows="3" id="post" name="content[]" placeholder="Bạn đang nghĩ gì ?"></textarea>
				<div class="custom-file">
					<input type='file' id="i_file" name='hinhanh[]' multiple>
				</div>
				<div>
					<label for="selectPrivacy">Chế độ: </label>	
					<select id="selectPrivacy" name="privacy">
						<option value="private" >Riêng tư</option>
						<option value="friend" >Bạn bè</option>
						<option value="public" selected>Công khai</option>
					</select>
				</div>
				<div>
					<button name="submit" type="submit" class="btn btn-primary">Đăng bài viết</button>
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
				<?php
					$plikeID = (string)("plike" . $row['postID']); 
					$fcmtID = (string)("fcmt" . $row['postID']);	// form cmt
					$dcmtID = (string)("dcmt" . $row['postID']);	// thẻ div chứa danh sách các cmt
					$tcmtID = (string)("tcmt" . $row['postID']);	// ô text để nhập cmt
					$privacy = $row['privacy'];			// lấy quyền riêng tư của bài viết
					$follow = checkFollow($row['userID'], $currentUser['userID']);	// biến kiểm tra xem current có theo dõi người dùng đó ko

					if ($row['userID'] != $currentUser['userID']) {
						
						// nếu như nd ko theo dõi thằng đó và bài viết không công khai thì nó không coi được
						if ($follow == false && $privacy != 'public') {
							continue;
						}	
						// nếu như bài viết đó riêng tư thì bỏ qua
						if ($privacy == 'private') {
							continue;
						}
						// nếu như bài viết bạn bè mà người dùng không là bạn bè với người đó thì cũng bỏ qua
						else if ($privacy == 'friend' && isFriend($currentUser['userID'], $row['userID']) == false) {
							continue;
						}
						
						// các bài công khai được phép hiện

					}
					else {
						// các bài viết của người dùng đó được hiển thị
					}

				?>

				<div class="card" style="margin-top: 10px; border: 2px solid grey; border-radius: 10px; height: auto;">
					 <div class="card-body">
					 	<div style="margin-bottom: 20px;">
						 	<img src="<?php echo $row['avatar']?>" alt="Avatar" class="avatar" style="float: left; border-radius: 20%; margin-right: 10px;">
							<a href="trang-ca-nhan.php?userID=<?php echo $row['userID'];?>">
								<h3><?php echo  $row['fullname']; ?></h3>
							</a>
							<p>Đăng lúc <?php echo date_format(date_create($row['timecreate']),"d/m/Y H:i:s"); ?></p>
							<select <?php 
										$cuaCurrent = checkPostOfUser($row['postID'], $currentUser['userID']);
										echo ($cuaCurrent == 'true' ? '' : 'disabled');
									?>
									onchange="privacyChanged(event,'<?php echo $row['postID'];?>')"
									onfocus="this.selectedIndex = '-1'; this.blur();">
								<option value="private" <?php echo ($privacy == 'private' ? 'selected' : ' ') ;?>>Riêng tư</option>
								<option value="friend" <?php echo ($privacy == 'friend' ? 'selected' : ' ') ;?>>Bạn bè</option>
								<option value="public" <?php echo ($privacy == 'public' ? 'selected' : ' ') ;?>>Công khai</option>
							</select>
					 	</div>
						<textarea class="form-control" rows="<?php echo getTotalLine($row['content']); ?>" readonly="readonly"><?php echo $row['content']; ?>
						</textarea>
						<?php echo inDSPicPostHTML($row['postID']); ?>
						<p style="margin-top: 10px;" id="<?php echo $plikeID;?>">
							<?php
								$tongLike = totalLike($row['postID']);	
								if ($tongLike == 0) {
									echo "Chưa có lượt thích";
								}
								else {
									$currentLike = checkLike($row['postID'], $currentUser['userID']);
									if ($currentLike) {
										if ($tongLike == 1) {
											echo "Bạn đã thích bài viết này";
										}
										else {
											echo "Bạn và " . (string)($tongLike - 1) . " người khác đã thích bài viết này";
										}
									}
									else {
										echo "Có " . $tongLike . " người đã thích bài viết này";
									}
								}								
							?>
						</p>
						<form id="<?php echo $fcmtID;?>">
							<div class="form-group">
								<div class="col-sm-2" style="float: left;">
									<button onclick="btnLike_Click(event)"
											class="<?php echo $plikeID;?> btn btn-outline-primary btn" 
											style="float: left;
													width: 100px;"
											type="button">
										<?php 
											if (checkLike($row['postID'], $currentUser['userID'])){
												echo "Bỏ thích";
											}
											else {
												echo "Thích";
											}
										?>					
									</button>
								</div>
								<div class="col-sm-7" style="float: left;">
									<textarea class="form-control" rows="1" style="width: 600px;float: left;" id="<?php echo $tcmtID ?>" name="cmtContent" placeholder="Bình luận"></textarea>
								</div>
								<div class="col-sm-1" style="float: left;">
									<button type="button" onclick="btnCmt_Click(event)" class="<?php echo $fcmtID;?> btn btn-primary">Đăng</button>
								</div>
								<div id="<?php echo $dcmtID; ?>" style="float: left;width: 100%; margin: 20px 0px 0px 200px">
									<?php 
										echo inDSCmtHTML($row['postID']);								
									?>
								</div>
							</div>
						</form>		
					 </div>
				</div>
		<?php endforeach;?>
	</div>
<?php include 'footer.php'; ?>