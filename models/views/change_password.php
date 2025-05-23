<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../DatabaseConnection.php';
require_once '../User.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];
    $newPassConfirm = $_POST['new_password_confirm'];

    if (empty($oldPass) || empty($newPass)) {
        $errors[] = "Old and new password cannot be empty";
    } elseif ($newPass !== $newPassConfirm) {
        $errors[] = "New passwords do not match";
    } else {
        $dbConn = new DatabaseConnection();
        $db = $dbConn->getConnection();
        $user = new User($db);
        try {
            // Mevcut eski parola doğrulaması için login() çağırıyoruz
            $user->login($_SESSION['username'], $oldPass);
            $result = $user->changePassword($oldPass, $newPass);
            if ($result) {
                $success = true;
            } else {
                $errors[] = "Password change failed";
            }
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
  <title>Change Password &mdash; Password Manager</title>
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
    input[type="password"] {
      padding: 12px 14px;
      margin-bottom: 18px;
      border: 1px solid #bcc0c4;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    input[type="password"]:focus {
      border-color: #3498db;
      box-shadow: 0 0 6px rgba(52, 152, 219, 0.3);
      outline: none;
    }
    button {
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
    button:hover {
      background-color: #555555;
      transform: translateY(-1px);
    }
    button:active {
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
    .success {
      background-color: #e9f7ef;
      border: 1px solid #27ae60;
      color: #1e8449;
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
    <h2>Change My Password</h2>
    <?php if ($success): ?>
      <div class="success">Your password has been changed successfully</div>
      <p class="text-center"><a href="dashboard.php">Back to Dashboard</a></p>
    <?php else: ?>
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
        <label for="old_password">Old Password</label>
        <input type="password" name="old_password" id="old_password" required>
        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" required>
        <label for="new_password_confirm">Confirm New Password</label>
        <input type="password" name="new_password_confirm" id="new_password_confirm" required>
        <button type="submit">Change Password</button>
      </form>
      <p class="text-center"><a href="dashboard.php">Back to Dashboard</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
