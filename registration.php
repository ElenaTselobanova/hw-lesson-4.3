<?php
session_start();
try {
    $pdo = new PDO("mysql:host=localhost;dbname=tasks", "root", "");
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if (isset($_POST['sign_in'])) {
    if (!empty($_POST['login']) && !empty($_POST['password'])) {
        $user_login = strip_tags($_POST['login']);
        $user_password = strip_tags($_POST['password']);
        $sql = "SELECT * FROM user WHERE login = :login";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["login" => "$user_login"]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData['password'] == $user_password) {
            $_SESSION['name'] = $userData['login'];
            header('Location: index.php');
            exit();
        }else {
            echo 'Неправильный логин или логин';
        }
    }
}
if (isset($_POST['sign_up'])) {
    $login = strip_tags($_POST['login']);
    $password = strip_tags($_POST['password']);
    $sql = "INSERT INTO user(login,password) VALUES ('$login', '$password')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$login, $password]);
    echo 'Вы успешно зарегистрированы! Войдите, используя ваш логин и пароль';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SELECT из нескольких таблиц</title>
</head>
<body>
<form method="POST">
    <input type="text" name="login" placeholder="Логин">
    <input type="password" name="password" placeholder="Пароль">
    <input type="submit" name="sign_in" value="Вход"/>
    <input type="submit" name="sign_up" value="Регистрация"/>
</form>
</body>
