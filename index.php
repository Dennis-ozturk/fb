<?php
include_once 'db/db.php';
include_once 'src/api.php';
include_once 'src/user.inc.php'
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <?php
    // $token = bin2hex(openssl_random_pseudo_bytes(16));
    ?>

    <form action="" method="POST">
        <input type="email" name="email" placeholder="email">
        <br>
        <input type="password" name="password" placeholder="password">
        <br>
        <input type="submit" value="Register" name="register">
    </form>

    <br>

    <form action="" method="POST">
        <input type="email" name="email" placeholder="email">
        <br>
        <input type="password" name="password" placeholder="password">
        <br>
        <input type="submit" value="Login" name="login">
    </form>

    <?php

    if (isset($_POST['register'])) {
        $register = new User();

        $fields = [
            ':email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING),
            ':password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING),
        ];
        $register->checkUserExists($fields);
    }

    ?>

</body>

</html>