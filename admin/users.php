<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$users = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="icon" href="../assets/images/Logo white.png">
</head>
<body>

<h2>Users</h2>
<a href="add_user.php">➕ Add Player User</a>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Username</th>
    <th>Role</th>    
</tr>

<?php while ($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= $u['user_id'] ?></td>
    <td><?= $u['username'] ?></td>
    <td><?= $u['role'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
