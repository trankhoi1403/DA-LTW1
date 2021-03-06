<html><head>
	<title>koichen</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<style type="text/css">
		h1 {
			text-align: center;
		}
		.avatar {
			vertical-align: middle;
			width: 40px;
			height: 40px;
			border-radius: 50%;
		}
	</style>
	<script>
		// kiểm tra inputPassword có trùng khớp với phần ô nhập lại reInputPassword không
		function checkRePassword(inputPassword, reInputPassword) {
			console.log('vào hàm checkRePassword');
			var pass = document.getElementById(inputPassword);
			var rePass = document.getElementById(reInputPassword);
			if (pass.value != rePass.value) {
				alert("Password không trùng khớp, nhập lại password");
				rePass.focus();
				return false;
			}
			else{
				return true;
			}
		}
		// kiểm tra độ mạnh yếu của pass và thể hiện ra bằng passState (là id của một label)
		function checkPasswordState(pass, passState) {
			console.log('Vào hàm checkPasswordState');
			var pass = document.getElementById(pass);
			var state = document.getElementById(passState);
			if (pass.value.length < 3) {
				state.innerHTML = "Yếu";
				state.style.color = 'red';
			}
			else if (pass.value.length < 6) {
				state.innerHTML = "Trung bình";
				state.style.color = 'blue';
			}
			else{
				state.innerHTML = "Mạnh";
				state.style.color = 'green';
			}
		}
	</script>
</head>
<body class="container">
	<div>
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		  <a class="navbar-brand" href="#">BTCN07</a>
		  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		    <span class="navbar-toggler-icon"></span>
		  </button>

		  <div class="collapse navbar-collapse" id="navbarSupportedContent">
		    <ul class="navbar-nav mr-auto">
		      <li class="nav-item <?php echo $page=='index' ? 'active' : '' ?>">
		        <a class="nav-link" href="index.php">Trang chủ<span class="sr-only">(current)</span></a>
		      </li>
		      <?php if (!isset($_SESSION['userID'])): ?>
			  <li class="nav-item <?php echo $page=='login' ? 'active' : '' ?>">
		        <a class="nav-link" href="login.php">Đăng nhập</a>
		      </li>
		      <?php else: ?>
		   	  <li class="nav-item <?php echo $page=='update-profile' ? 'active' : '' ?>">
		        <a class="nav-link" href="update-profile.php" style="padding: 2px 0px 0px 0px;">
		        	<img src="<?php echo $currentUser['avatar']?>" alt="Avatar" class="avatar">
					<?php echo $currentUser['fullname']?>
		        </a>
		   	  </li>
		      <li class="nav-item <?php echo $page=='change-password' ? 'active' : '' ?>">
		        <a class="nav-link" href="change-password.php">Đổi mật khẩu</a>
		      </li>
		   	  <li class="nav-item <?php echo $page=='logout' ? 'active' : '' ?>">
		        <a class="nav-link" href="logout.php">Đăng xuất</a>
		      </li>

		  	<?php endif; ?>
<!-- 			  <li class="nav-item">
		        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Đăng nhập</a>
		      </li> -->
		    </ul>
		  </div>
		</nav>
	</div>

