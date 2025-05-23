<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../DatabaseConnection.php';
require_once '../PasswordManager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_POST['plain_password']) ||
    empty($_POST['site_name'])
) {
    header("Location: generate_password.php");
    exit;
}

$plainPass = $_POST['plain_password'];
$siteName = trim($_POST['site_name']);

$dbConn = new DatabaseConnection();
$db = $dbConn->getConnection();
$pm = new PasswordManager($db, $_SESSION['master_key'], $_SESSION['user_id']);

try {
    $encData = $pm->encryptPassword($plainPass);
    $success = $pm->saveEntry($siteName, $encData['cipher_text'], $encData['iv']);
    if ($success) {
        header("Location: view_passwords.php?saved=1");
        exit;
    } else {
        throw new Exception("Failed to save to database");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Save Password &mdash; Password Manager</title>
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
    <h2>Save Password</h2>
    <div class="errors">
      <ul>
        <li><?= htmlspecialchars($error) ?></li>
      </ul>
    </div>
    <p class="text-center"><a href="generate_password.php">Back to Generate</a></p>
  </div>
</body>
</html>
