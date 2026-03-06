<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select->execute([$email]);

   if($select->rowCount() > 0){
      $message[] = 'user email already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         $insert = $conn->prepare("INSERT INTO `users`(name, email, password, image) VALUES(?,?,?,?)");
         $insert->execute([$name, $email, $pass, $image]);

         if($insert){
            if($image_size > 2000000){
               $message[] = 'image size is too large!';
            }else{
               move_uploaded_file($image_tmp_name, $image_folder);
               $message[] = 'registered successfully!';
               header('location:register.php');
            }
         }
      }
   }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }

      body {
         background: #e8f5e9;
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 100vh;
         padding: 20px;
      }

      .message {
         background-color: #d4edda;
         color: #155724;
         padding: 10px 20px;
         border-left: 5px solid #28a745;
         margin-bottom: 15px;
         position: relative;
         border-radius: 5px;
         width: 100%;
         max-width: 400px;
         animation: slideDown 0.4s ease;
      }

      .message i {
         position: absolute;
         top: 50%;
         right: 15px;
         transform: translateY(-50%);
         cursor: pointer;
         color: #155724;
      }

      @keyframes slideDown {
         from { opacity: 0; transform: translateY(-10px); }
         to { opacity: 1; transform: translateY(0); }
      }

      .form-container {
         background: #ffffff;
         border-radius: 12px;
         box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
         padding: 40px 30px;
         width: 100%;
         max-width: 420px;
      }

      .form-container h3 {
         text-align: center;
         color: #2e7d32;
         margin-bottom: 25px;
         font-size: 26px;
      }

      .box {
         width: 100%;
         padding: 12px 15px;
         margin-bottom: 18px;
         border: 1px solid #ccc;
         border-radius: 8px;
         font-size: 15px;
         outline: none;
         transition: 0.3s;
      }

      .box:focus {
         border-color: #4caf50;
         box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
      }

      .btn {
         width: 100%;
         background: #4caf50;
         color: #fff;
         padding: 12px;
         border: none;
         border-radius: 8px;
         font-size: 16px;
         font-weight: bold;
         cursor: pointer;
         transition: background 0.3s ease;
      }

      .btn:hover {
         background: #388e3c;
      }

      .form-container p {
         text-align: center;
         margin-top: 15px;
         font-size: 14px;
         color: #444;
      }

      .form-container a {
         color: #2e7d32;
         text-decoration: none;
         font-weight: 600;
      }

      .form-container a:hover {
         text-decoration: underline;
      }
   </style>
</head>
<body>

<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container">
   <form action="" enctype="multipart/form-data" method="POST">
      <h3>Register Now</h3>
      <input type="text" name="name" class="box" placeholder="Enter your name" required>
      <input type="email" name="email" class="box" placeholder="Enter your email" required>
      <input type="password" name="pass" class="box" placeholder="Enter your password" required>
      <input type="password" name="cpass" class="box" placeholder="Confirm your password" required>
      <input type="file" name="image" class="box" required accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="Register Now" class="btn" name="submit">
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </form>
</section>

</body>
</html>
