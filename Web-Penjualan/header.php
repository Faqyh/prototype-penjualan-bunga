<?php
  session_start();
  include '../koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Flower Shop</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700,700i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC:400,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">

    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/ionicons.min.css">

    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">

    
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body class="goto-here">
	
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
	      <a class="navbar-brand" href="index.php">Flower by Khansa</a>
	      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> Menu
	      </button>

	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item active"><a href="index.php" class="nav-link">Home</a></li>
	          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Shop</a>
              <div class="dropdown-menu" aria-labelledby="dropdown04">
              	<a class="dropdown-item" href="shop.php">Shop</a>
                <a class="dropdown-item" href="cart.php">Cart</a>
                <a class="dropdown-item" href="checkout.php">Checkout</a>
              </div>
            </li>
	          <li class="nav-item"><a href="daftarorder.php" class="nav-link">Daftar Order</a></li>

	          

	        </ul>
			<ul class="navbar-nav mx-auto">
        <?php
          if(!isset($_SESSION['log'])){
            echo '
            <li class="nav-item"><a href="../AdminPanel/login2.php" class="nav-link">Login</a></li>
            ';
          } else{
            if($_SESSION['role']=='Member'){
              echo '
                <li class="nav-item cta cta-colored"><a href="cart.php" class="nav-link"><span class="icon-shopping_cart"></span>| Cart</a></li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="dropdown06" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$_SESSION["name"].'</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown06">
              	      <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
              ';
            } else{
              echo '
              <li class="nav-item cta cta-colored"><a href="../AdminPanel/index.php" class="nav-link"><span class="icon-shopping_cart"></span>| Admin Panel</a></li>
              <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdown05" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$_SESSION["name"].'</a>
              <div class="dropdown-menu" aria-labelledby="dropdown05">
              	<a class="dropdown-item" href="logout.php">Logout</a>
              </div>
              ';
            };
          }
        ?>
				
			</ul>
	      </div>
	    </div>
	  </nav>
    <!-- END nav -->