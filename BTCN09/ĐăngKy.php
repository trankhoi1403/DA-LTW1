<?php    require_once __DIR__. "/admin/layout/header.php";?>
 <link rel="stylesheet" href="">
<br><br><br>
<div class="container">
   <?php 
   if(isset($_SESSION['name_id']))
   {
   echo "<script>alert('Bạn Đã Đăng Nhập, Vui lòng Đăng Xuất để tiếp tục thực hiện !');location.href='home.php'</script>";
      }
      //xử lý
$data=
      [
           "name" => postInput('name'),
           "email" => postInput('email'),
           "phone" => postInput('phone'),
           "password" => md5(postInput('password')),
              "address" => postInput('address')
      ];
      if ($_SERVER["REQUEST_METHOD"]=="POST" && $json['success'] =1)
      {
         $responseKey = $_POST['g-recaptcha-response'];
         $userIP = $_SERVER['REMOTE_ADDR'];
         $list=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfgmIMUAAAAAF7NNAb7bRHYgUZUxl-7ExwTSTbP&response=$responseKey&remoteip=$userIP");
         $json=json_decode($list,true);
         //tiến hành validate & đăng ký
         if(postInput('name')=='')
      {
         $error['name']="Mời bạn nhập họ và tên";
      }
      if($json['success'] !=1){
         $error['capcha']="Nhập Capcha";
      }
      if(postInput('address')=='')
      {
         $error['address']="Mời bạn nhập địa chỉ";
      }
      if(postInput('email')=='')
      {
         $error['email']="Mời bạn nhập địa chỉ email";
      }
      else
      {
         $is_check = $db->fetchOne("users"," email = '".$data['email']."' ");
         if($is_check != NULL)
         {
            $error['email']= "Email đã tồn tại";
         }
      }
      if(postInput('phone')=='')
      {
         $error['phone']="Mời bạn nhập số điện thoại";
      }
      else
      {
         $is_check = $db->fetchOne("users"," phone = '".$data['phone']."' ");
         if($is_check != NULL)
         {
            $error['phone']= "Số điện thoại đã tồn tại";
         }
      }
      if(postInput('password')=='')
      {
         $error['password']="Mời bạn nhập mật khẩu";
      }
      if($data['password']!= MD5(postInput("re_password")))
      {
         $error['password'] = "Mật khẫu không khớp";
      }
         if(empty($error))
         {
            $id_insert = $db->insert("users",$data);
           if($id_insert)
            {
               echo "<script>alert('Bạn đăng ký thành công! Đăng nhập để tiếp tục thực hiện !');location.href='dang-nhap.php'</script>";
            }
            else
            {
               $_SESSION['error']="Thêm Mới thất bại";
            }
         }
         }
      ?>
      <script src='https://www.google.com/recaptcha/api.js'></script>
   <div style=" margin-top:50px" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
      <div class="panel panel-info">
         <div class="panel-heading">
            <div class="panel-title"><strong>Đăng ký</strong></div>
            <div style="float:right; font-size: 85%;position: relative; top:-10px"><a id="signinlink" href="/SS4UREALSTATE/dang-nhap.php" onclick="$('#signupbox').hide(); $('#loginbox').show()">Đăng Nhập</a></div>
         </div>
         <div class="panel-body" >
            <form id="signupform" class="form-horizontal" role="form" action="" method="POST">
               <div id="signupalert" style="display:none" class="alert alert-danger">
                  <p>Error:</p>
                  <span></span>
               </div>
               <div class="form-group">
                  <label for="email" class="col-md-3 control-label">Email</label>
                  <div class="col-md-9">
                     <input type="email" class="form-control" name="email" placeholder="Email Address" value="<?php echo  $data['email'] ?>">
                     <?php if (isset($error['email'])): ?>
                     <p class="text-danger"> <?php echo $error['email'] ?></p>
                     <?php endif?>
                  </div>
               </div>
               <div class="form-group">
                  <label for="firstname" class="col-md-3 control-label">Họ Và Tên</label>
                  <div class="col-md-9">
                     <input type="text" class="form-control" name="name" placeholder="Trần Nhật Phương" value="<?php echo  $data['name'] ?>">
                     <?php if (isset($error['name'])): ?>
                        <p class="text-danger"> <?php echo $error['name'] ?></p>
                     <?php endif?>
                  </div>
               </div>
               <div class="form-group">
                  <label for="lastname" class="col-md-3 control-label">Số Điện Thoại</label>
                  <div class="col-md-9">
                     <input type="number" class="form-control" name="phone" placeholder="0346394242" value="<?php echo  $data['phone'] ?>">
                     <?php if (isset($error['phone'])): ?>
                     <p class="text-danger"> <?php echo $error['phone'] ?></p>
                     <?php endif?>
                  </div>
               </div>
               <div class="form-group">
                  <label for="lastname" class="col-md-3 control-label">Địa chỉ</label>
                  <div class="col-md-9">
                     <input type="text" class="form-control" name="address" placeholder="Hồ Chí Minh" value="<?php echo  $data['address'] ?>">
                     <?php if (isset($error['address'])): ?>
                     <p class="text-danger"> <?php echo $error['address'] ?></p>
                     <?php endif?>
                  </div>
               </div>
               <div class="form-group">
                  <label for="password" class="col-md-3 control-label">Mật Khẩu</label>
                  <div class="col-md-9">
                     <input type="password" class="form-control" name="password" placeholder="Password">
                     <?php if (isset($error['password'])): ?>
                     <p class="text-danger"> <?php echo $error['password'] ?></p>
                     <?php endif?>

                  </div>
               </div>
               <div class="form-group">
                  <label for="password" class="col-md-3 control-label">Nhập Lại Mật Khẩu</label>
                  <div class="col-md-9">
                     <input type="password" class="form-control" id="inputAddress" placeholder="re password" name="re_password" required="" >
                           <?php if (isset($error['re_password'])):?>
                           <p class="text-danger"> <?php echo $error['re_password'] ?></p>
                           <?php endif?>
                  </div>
               </div>
               <div class="form-group">
               <div class="g-recaptcha" data-sitekey="6LfgmIMUAAAAAIRFgaU0huIh75-909XoTbS0hYtk"></div>
                  <?php if (isset($error['capcha'])):?>
                  <p class="text-danger"> <?php echo $error['capcha'] ?></p>
                  <?php endif?>
                  </div>
               <div class="form-group">
                  <!-- Button -->                                        
                  <div class="col-md-offset-3 col-md-9">
                     <button id="signupbox" type="submit" class="btn btn-info"><i class="icon-hand-right"></i> Đăng Ký</button>
                     <span style="margin-left:8px;">hoặc</span>  
                  </div>
               </div>
               <div class="form-group">
                  <div class="col-md-offset-2 col-md-9">
                       <a class="btn btn-danger">
    <span type="button" value="login with Google" class="fa fa-google"> Đăng Ký Bằng Tài Khoản Google  </span>
  </a>
                  </div>
                  <br>
                  
               </div>
               <div class="form-group">
               <div class="col-md-offset-2 col-md-9">
                       <a class="btn btn-primary">
    <span class="fa fa-facebook"> Đăng Ký Bằng Tài Khoản Facebook</span>
  </a>
                  </div></div>
            </form>
         </div>
      </div>
   </div>
</div>
<?php 
   require_once __DIR__. "/admin/layout/footer.php";
   ?>