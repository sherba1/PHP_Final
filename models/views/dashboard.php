<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard &mdash; Password Manager</title>
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
      width: 90%; max-width: 500px;
      background: rgba(255, 255, 255, 0.85);
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      padding: 30px 25px;
      text-align: center;
    }
    h2 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    .btn {
      display: inline-block;
      margin: 8px 5px;
      padding: 12px 20px;
      background-color: #777777;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.2s ease, transform 0.1s ease;
    }
    .btn:hover {
      background-color: #555555;
      transform: translateY(-1px);
    }
    .btn:active {
      transform: translateY(1px);
    }
  </style>
</head>
<body>
  <div class="background-overlay"></div>
  <div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
    <a class="btn" href="generate_password.php">Generate Password</a>
    <a class="btn" href="view_passwords.php">View Passwords</a>
    <a class="btn" href="change_password.php">Change Password</a>
    <a class="btn" href="../logout.php">Logout</a>
  </div>
</body>
</html>
