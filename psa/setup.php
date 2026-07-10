<?php
$pdo = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? '127.0.0.1');
    $port = trim($_POST['port'] ?? '3306');
    $name = trim($_POST['name'] ?? 'psa_qr');
    $user = trim($_POST['user'] ?? 'root');
    $password = trim($_POST['password'] ?? '');

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->query('SELECT 1');
        $message = 'Database connection successful.';
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PSA Setup</title>
  <style>
    body{font-family:Arial,sans-serif;max-width:700px;margin:40px auto;padding:20px;line-height:1.5}input,button{padding:10px;width:100%;margin:6px 0}button{cursor:pointer} .ok{color:green}.error{color:red}
  </style>
</head>
<body>
  <h1>PSA Database Setup</h1>
  <p>Use this page to verify your MySQL connection before launching the app.</p>
  <form method="post">
    <input name="host" placeholder="Database host" value="127.0.0.1" required>
    <input name="port" placeholder="Port" value="3306" required>
    <input name="name" placeholder="Database name" value="psa_qr" required>
    <input name="user" placeholder="Database user" value="root" required>
    <input name="password" placeholder="Database password" type="password">
    <button type="submit">Test Connection</button>
  </form>
  <?php if (!empty($message)) { echo '<p class="ok">' . htmlspecialchars($message) . '</p>'; } ?>
  <?php if (!empty($errors)) { echo '<ul class="error">'; foreach ($errors as $error) { echo '<li>' . htmlspecialchars($error) . '</li>'; } echo '</ul>'; } ?>
</body>
</html>
