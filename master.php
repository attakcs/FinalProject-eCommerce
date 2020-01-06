<?php
  $bg = array('00.jpg', '01.jpg', '02.jpg', '03.jpg', '04.jpg', '05.jpg'); // array of filenames

  $i = rand(0, count($bg)-1); // generate random number size of the array
  $selectedBg = "$bg[$i]"; // set variable equal to which random filename was chosen
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title><?= WEBSITE_TITLE ?></title>

    <!-- SOME CSS LOVE COMES HERE <3 -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- jQuery in header -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="/js/ajax.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/cart-manager.js"></script>

    <style>
        body {
            background: url('assets/hexs/<?php echo $selectedBg; ?>') no-repeat;
            background-size: cover;
            background-attachment: fixed;
        }       
    </style>
</head>
<body>

    <?php include __DIR__ . '/header.php'?>

<div class="container">
            
    <main>
    <?php include $pageURL ?>
    </main>

</div>
<!-- CONTAINER CLOSING TAG -->
    <?php include __DIR__ . '/footer.php'?>

    <div id="bottomSpacing"></div>

<script src="/js/smooth-scroll.polyfills.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
</body>
</html>