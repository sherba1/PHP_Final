<?php
require_once '../DatabaseConnection.php';
require_once '../User.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    if (empty($username) || empty($password)) {
        $errors[] = "Username and password cannot be empty";
    } elseif ($password !== $passwordConfirm) {
        $errors[] = "Passwords do not match";
    } else {
        $dbConn = new DatabaseConnection();
        $db = $dbConn->getConnection();
        $user = new User($db);
        try {
            $user->register($username, $password);
            header("Location: login.php?registered=1");
            exit;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register &mdash; Password Manager</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body {
      height: 100%;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #d0eaff;
      overflow: hidden;
    }
    .background-overlay {
      position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(208, 234, 255, 0.6);
      z-index: -1;
    }
    .container {
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      width: 90%; max-width: 380px;
      background: rgba(255, 255, 255, 0.85);
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      padding: 30px 25px;
    }
    h2 {
      text-align: center;
      font-size: 24px;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    form { display: flex; flex-direction: column; }
    label {
      font-size: 14px;
      font-weight: 600;
      color: #34495e;
      margin-bottom: 6px;
    }
    input[type="text"], input[type="password"] {
      padding: 12px 14px;
      margin-bottom: 18px;
      border: 1px solid #bcc0c4;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    input[type="text"]:focus, input[type="password"]:focus {
      border-color: #3498db;
      box-shadow: 0 0 6px rgba(52, 152, 219, 0.3);
      outline: none;
    }
    button, input[type="submit"] {
      background-color: #777777;
      color: #ffffff;
      border: none;
      border-radius: 6px;
      padding: 12px 0;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.2s ease, transform 0.1s ease;
      margin-top: 10px;
    }
    button:hover, input[type="submit"]:hover {
      background-color: #555555;
      transform: translateY(-1px);
    }
    button:active, input[type="submit"]:active {
      transform: translateY(1px);
    }
    .errors {
      background-color: #fdecea;
      border: 1px solid #e74c3c;
      color: #c0392b;
      padding: 10px 14px;
      margin-bottom: 18px;
      border-radius: 6px;
      font-size: 13px;
    }
    p.text-center {
      text-align: center;
      margin-top: 15px;
      font-size: 13px;
      color: #2c3e50;
    }
    p.text-center a {
      color: #3498db;
      text-decoration: none;
      font-weight: 600;
    }
    p.text-center a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="background-overlay"></div>
  <div class="container">
    <h2>Register</h2>
    <?php if (!empty($errors)): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="post" action="">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" required>
      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>
      <label for="password_confirm">Confirm Password</label>
      <input type="password" name="password_confirm" id="password_confirm" required>
      <button type="submit">Register</button>
    </form>
    <p class="text-center">
      Already have an account?
      <a href="login.php">Login here</a>
    </p>
  </div>
</body>
</html>
