<?php
session_start();
if(empty($_SESSION['user']['id'])){
    header('location: registration.php');
}
$pdo = new PDO("mysql:host=localhost;dbname=tasks;charset=utf8", "root", "");
$username = $_SESSION['user']['login'];

if (!empty($_POST['description'])) {
    if (empty($_GET['action'])) {
        $description = strip_tags($_POST['description']);
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO task (user_id, assigned_user_id, description, date_added) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user']['id'], $_SESSION['user']['id'], $_POST['description'], $date]);
        header('Location: index.php');
    }
}
if ($_GET['action'] == 'done') {
    $sql = "UPDATE task SET is_done = 1 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['id']]);
    header('Location: index.php');
}
if ($_GET['action'] == 'delete') {
    $sql = "DELETE FROM task WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['id']]);
    header('Location: index.php');
}


if (!empty($_POST['assign'])) {
    if (!empty($_POST['assigned_user_id'])) {
        $assignedUserID = $_POST['assigned_user_id'];
        $userID = $pdo->query("SELECT id FROM user WHERE login = '$assignedUserID'")->fetch()['id'];
        $sql = $pdo->prepare("UPDATE task SET assigned_user_id = ? WHERE id = ?");
        $sql->execute([$userID, $_POST['id']]);
    }
}
if (!empty($_POST['sort']) && !empty($_POST['sort_by'])) {
    $sql = "SELECT * FROM task WHERE user_id = ? ORDER BY date_added";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
$newUsers = $pdo->query('SELECT login FROM user');
$users = $newUsers->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>SELECT из нескольких таблиц</title>
<body>
<h2>Здравствуйте, <?=$username?>!<br>Ваш список дел:</h2>
<form method="POST">
    <input type="text" name="description" placeholder="Описание задачи" value="<?php echo $_GET['description']; ?>">
    <button type="submit" name="add">Добавить</button>
</form>
<br>
<form method="POST">
    <label for="sort">Сортировать по:</label>
    <select name="sort_by">
        <option>Выберите тип сортировки</option>
        <option value="date_added">Дате добавления</option>
    </select>
    <input type="submit" name="sort" value="Отсортировать">
</form>

<table border="1">
    <thead>
    <tr>
        <th>Описание задачи</th>
        <th>Дата добавления</th>
        <th>Статус</th>
        <th></th>
        <th>Ответственный</th>
        <th>Автор</th>
        <th>Закрепить задачу за пользователем</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT *, t.id as task_id, u.id as author_id, u.login as author_name FROM task t 
                INNER JOIN user u ON u.id=t.user_id 
                INNER JOIN user auth ON t.assigned_user_id=auth.id 
                WHERE u.login = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    while ($result = $stmt->fetch()) : ?>
        <tr>
            <td><?=$result['description']?></td>
            <td><?=$result['date_added']?></td>
            <td>
                <?php
                if ($result['is_done'] == 0) {
                    echo 'В процессе';
                } else  {
                    echo 'Выполнено';
                }
                ?>
            </td>
            <td>
                <a href="index.php?id=<?=$result['task_id']?>&action=done">Выполнить</a>
                <a href="index.php?id=<?=$result['task_id']?>&action=delete">Удалить</a>
            </td>
            <td><?=$result['login']?></td>
            <td><?=$result['author_name']?></td>
            <td>
                <form method="post">
                    <select name="assigned_user_id">
                        <?php
                        foreach ($users as $user) {
                            echo '<option>' . $user['login'] . '</option>';
                        }
                        ?>
                    </select>
                    <input type="hidden" name="id" value="<?=$result['task_id']?>">
                    <input type="submit" name="assign" value="Переложить ответственность">
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<p><a href="logout.php">Выход</a></p>
</body>
</html>