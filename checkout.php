<?php

@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit();
}

$has_stock_issue = false; // Flag to prevent checkout if stock is low

if(isset($_POST['order'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = 'Flat No. '.$_POST['flat'].' '.$_POST['street'].' '.$_POST['city'].' '.$_POST['state'].' '.$_POST['country'].' - '.$_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];

   $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $cart_query->execute([$user_id]);

   if($cart_query->rowCount() > 0){
      while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
         // Check available stock
         $check_stock = $conn->prepare("SELECT quantity FROM `products` WHERE id = ?");
         $check_stock->execute([$cart_item['pid']]);
         $product = $check_stock->fetch(PDO::FETCH_ASSOC);

         if($product['quantity'] < $cart_item['quantity']){
            $has_stock_issue = true;
            break;
         }

         $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $sub_total = $cart_item['price'] * $cart_item['quantity'];
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ', $cart_products);

   if($cart_total == 0){
      $message[] = 'Your cart is empty!';
   } elseif($has_stock_issue){
      $message[] = 'Some products are out of stock or have less quantity than required!';
   } else {
      $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
      $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

      if($order_query->rowCount() > 0){
         $message[] = 'Order has already been placed!';
      } else {
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);

         // Update stock
         $cart_query->execute([$user_id]);
         while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
            $product_id = $cart_item['pid'];
            $ordered_qty = $cart_item['quantity'];

            $get_product = $conn->prepare("SELECT quantity FROM `products` WHERE id = ?");
            $get_product->execute([$product_id]);
            $product = $get_product->fetch(PDO::FETCH_ASSOC);

            if($product){
               $new_qty = max(0, $product['quantity'] - $ordered_qty);
               $update_product = $conn->prepare("UPDATE `products` SET quantity = ? WHERE id = ?");
               $update_product->execute([$new_qty, $product_id]);
            }
         }

         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = 'Order placed successfully!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Checkout</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="display-orders">
   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);

      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $total_price = $fetch_cart_items['price'] * $fetch_cart_items['quantity'];
            $cart_grand_total += $total_price;
            echo "<p>{$fetch_cart_items['name']} <span>(₹{$fetch_cart_items['price']} x {$fetch_cart_items['quantity']})</span></p>";

            // Check if quantity available is enough
            $stock_check = $conn->prepare("SELECT quantity FROM `products` WHERE id = ?");
            $stock_check->execute([$fetch_cart_items['pid']]);
            $stock_data = $stock_check->fetch(PDO::FETCH_ASSOC);

            if($stock_data && $stock_data['quantity'] < $fetch_cart_items['quantity']){
               $has_stock_issue = true;
               echo "<p class='error' style='color:red;'>⚠ Not enough stock for <strong>{$fetch_cart_items['name']}</strong>. Available: {$stock_data['quantity']}</p>";
            }
         }
         echo "<div class='grand-total'>Grand Total: <span>₹{$cart_grand_total}/-</span></div>";
      } else {
         echo '<p class="empty">Your cart is empty!</p>';
         $has_stock_issue = true;
      }
   ?>
</section>

<?php if(!$has_stock_issue): ?>
<section class="checkout-orders">
   <form action="" method="POST">
      <h3>Place your order</h3>
      <div class="flex">
         <div class="inputBox"><span>Your name :</span><input type="text" name="name" required class="box"></div>
         <div class="inputBox"><span>Your number :</span><input type="number" name="number" required class="box"></div>
         <div class="inputBox"><span>Your email :</span><input type="email" name="email" required class="box"></div>
         <div class="inputBox">
            <span>Payment method :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash on Delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="paytm">Paytm</option>
               <option value="paypal">PayPal</option>
            </select>
         </div>
         <div class="inputBox"><span>Address line 01 :</span><input type="text" name="flat" required class="box"></div>
         <div class="inputBox"><span>Address line 02 :</span><input type="text" name="street" required class="box"></div>
         <div class="inputBox"><span>City :</span><input type="text" name="city" required class="box"></div>
         <div class="inputBox"><span>State :</span><input type="text" name="state" required class="box"></div>
         <div class="inputBox"><span>Country :</span><input type="text" name="country" required class="box"></div>
         <div class="inputBox"><span>Pin code :</span><input type="number" name="pin_code" required class="box"></div>
      </div>
      <input type="submit" name="order" class="btn" value="Place Order">
   </form>
</section>
<?php else: ?>
   <section class="checkout-orders">
      <h3 style="color:red; text-align:center;">Cannot proceed to checkout due to stock issues!</h3>
   </section>
<?php endif; ?>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
