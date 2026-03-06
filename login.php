<?php

@include 'config.php';

session_start();

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM `users` WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $rowCount = $stmt->rowCount();  

   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($rowCount > 0){

      if($row['user_type'] == 'admin'){
         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');
      }elseif($row['user_type'] == 'user'){
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
      }else{
         $message[] = 'no user found!';
      }

   }else{
      $message[] = 'incorrect email or password!';
   }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
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
         background-color: #f8d7da;
         color: #721c24;
         padding: 10px 20px;
         border-left: 5px solid #f44336;
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
         color: #721c24;
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
   <form action="" method="POST">
      <h3>Login Now</h3>
      <input type="email" name="email" class="box" placeholder="Enter your email" required>
      <input type="password" name="pass" class="box" placeholder="Enter your password" required>
      <input type="submit" value="Login Now" class="btn" name="submit">
      <p>Don't have an account? <a href="register.php">Register now</a></p>
   </form>
</section>

</body>
</html>
