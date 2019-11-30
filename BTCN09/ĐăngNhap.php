<?php 
   require_once __DIR__. "/admin/autoload/autoload.php";
   //login bằng google +
   require_once __DIR__. "/admin/modules/User/loginGoogle/config.php";
   require_once __DIR__. "/admin/modules/User/loginFacebook/config.php";
   if (isset($_SESSION['access_token'])) 
   {
      header('Location: home.php');
      exit();
   }
   if (isset($_SESSION['access_token1'])) 
   {
      header('Location: home.php');
      exit();
   }
   //login facebook
   $redirectURL = "http://localhost:8888/SS4UREALSTATE/admin/modules/User/loginFacebook/fb-callback.php";
   $permissions = ['email'];
   $loginURL1 = $helper->getLoginUrl($redirectURL, $permissions);
   //end
   $data =
   [
         'email' => postInput("email"),
         'password' => postInput("password")
   ];
   $loginURL = $gClient->createAuthUrl();
   $error = [];
   if ($_SERVER["REQUEST_METHOD"]=="POST" && $json['success'] =1 )
      {
         //google re_capcha V2
         $responseKey = $_POST['g-recaptcha-response'];
         $userIP = $_SERVER['REMOTE_ADDR'];
         $list=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfgmIMUAAAAAF7NNAb7bRHYgUZUxl-7ExwTSTbP&response=$responseKey&remoteip=$userIP");
         $json=json_decode($list,true);
         //end
         if(postInput('email')=='')
      {
         $error['email']="Nhập email";
      }
      if(postInput('password')=='')
      {
         $error['password']="Nhập password";
      }
      if($json['success'] !=1){
         $error['capcha']="Nhập Capcha";
      }
      if (empty($error)) 
      {
         
         $is_check = $db->fetchOne("users"," email = '".$data['email']."' AND password = '".MD5($data['password'])."' ");
               
         if($is_check != NULL )
         {
            $_SESSION['name_user'] = $is_check['name'];
            $_SESSION['name_id'] = $is_check['id'];
            echo "<script>alert('Đăng Nhập Thành Công !');location.href='home.php'</script>";
         }
         else
         {
            $_SESSION['error'] = " Sai Tên Tài Khoản Hoặc Mật Khẩu";
         }
   
      }
      }
   ?>
<?php    require_once __DIR__. "/admin/layout/header.php";?>
<title>Đăng Nhập</title>>
<script src='https://www.google.com/recaptcha/api.js'></script>
<br><br><br>
<div class="container">
   <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
      <div class="panel panel-info" >
         <div class="panel-heading">
            <!--Đăng Nhập Thất Bại-->
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
               <strong>Error!</strong> <?php echo $_SESSION['error'] ;unset($_SESSION['error'])?>
            </div>
            <?php endif ?>
            <div class="panel-title"><strong>Đăng Nhập</strong></div>
            <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="/SS4UREALSTATE/admin/modules/User/forgot-Password/forgot-password.php">Quên mật khẩu?</a></div>
         </div>
         <div style="padding-top:30px" class="panel-body" >
            <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
            <form id="loginform" class="form-horizontal" role="form" method="POST">
               <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input id="login-username" type="text" class="form-control" name="email" value="" placeholder="username or email">
                  <?php if (isset($error['email'])):?>
                  <p class="text-danger"> <?php echo $error['email'] ?></p>
                  <?php endif?>                                       
               </div>
               <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                  <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                  <?php if (isset($error['password'])):?>
                  <p class="text-danger"> <?php echo $error['password'] ?></p>
                  <?php endif?>
               </div>
               <div class="input-group">
                  <div class="g-recaptcha" data-sitekey="6LfgmIMUAAAAAIRFgaU0huIh75-909XoTbS0hYtk"></div>
                  <?php if (isset($error['capcha'])):?>
                  <p class="text-danger"> <?php echo $error['capcha'] ?></p>
                  <?php endif?>  
                  <div class="checkbox">
                     <!--<?php if (isset($_SESSION['msg']))echo $_SESSION['msg'];?>-->
                     <label>
                     <input id="login-remember" type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập
                     </label>
                  </div>
               </div>

               <div style="margin-top:10px" class="form-group">
                  <!-- Button -->
                  <div class="col-sm-12 controls">
                     <button type="submit" class="btn btn-success" id="loginbox" name="submit">Đăng Nhập</button>
                     <a class="btn btn-danger">
    <span type="button" value="login with Google" class="fa fa-google" onclick="window.location='<?php echo $loginURL ?>';"> Google  </span>
  </a>
  <a class="btn btn-primary">
    <span type="button" value="login with facebook" class="fa fa-facebook"onclick="window.location='<?php echo $loginURL1 ?>';"> facebook  </span>
  </a>
                  </div>
               </div>
               
               <div class="form-group">
                  <div class="col-md-12 control">
                     <div style="border-top: 1px solid#888; padding-top:15px; font-size:85%" >
                        Bạn có tài khoản chưa ?
                        <a href="/SS4UREALSTATE/dang-ky.php">
                        Đăng ký tài khoản
                        </a>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
   <?php 
      if(isset($_SESSION['name_id']))
      {
      echo "<script>alert('Bạn Đã Đăng Nhập, Vui lòng Đăng Xuất để tiếp tục thực hiện !');location.href='home.php'</script>";
         }
         ?>
</div>
<?php 
   require_once __DIR__. "/admin/layout/footer.php";
   ?>