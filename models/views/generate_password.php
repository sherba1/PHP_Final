<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../DatabaseConnection.php';
require_once '../PasswordManager.php';

$generatedPassword = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $length = (int)$_POST['length'];
    $countLower = (int)$_POST['count_lower'];
    $countUpper = (int)$_POST['count_upper'];
    $countDigits = (int)$_POST['count_digits'];
    $countSpecial = (int)$_POST['count_special'];

    if ($length <= 0) {
        $error = "Length must be a positive number";
    } else {
        $options = [
            'length' => $length,
            'countLower' => $countLower,
            'countUpper' => $countUpper,
            'countDigits' => $countDigits,
            'countSpecial' => $countSpecial
        ];
        try {
            $dbConn = new DatabaseConnection();
            $db = $dbConn->getConnection();
            $pm = new PasswordManager($db, $_SESSION['master_key'], $_SESSION['user_id']);
            $generatedPassword = $pm->generatePassword($options);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Generate Password &mdash; Password Manager</title>
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
      width: 90%; max-width: 400px;
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
    input[type="number"], input[type="text"] {
      padding: 12px 14px;
      margin-bottom: 18px;
      border: 1px solid #bcc0c4;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    input[type="number"]:focus, input[type="text"]:focus {
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
    input[readonly] {
      background-color: #f0f0f0;
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
    <h2>Generate Password</h2>
    <?php if ($error): ?>
      <div class="errors"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label for="length">Total Length:</label>
      <input type="number" name="length" id="length" value="8" required>
      <label for="count_lower">Lowercase Count:</label>
      <input type="number" name="count_lower" id="count_lower" value="2" required>
      <label for="count_upper">Uppercase Count:</label>
      <input type="number" name="count_upper" id="count_upper" value="2" required>
      <label for="count_digits">Digits Count:</label>
      <input type="number" name="count_digits" id="count_digits" value="2" required>
      <label for="count_special">Special Count:</label>
      <input type="number" name="count_special" id="count_special" value="2" required>
      <button type="submit">Generate</button>
    </form>
    <?php if ($generatedPassword): ?>
      <form method="post" action="save_password.php">
        <label for="generated">Generated Password:</label>
        <input type="text" id="generated" value="<?= htmlspecialchars($generatedPassword) ?>" readonly>
        <input type="hidden" name="plain_password" value="<?= htmlspecialchars($generatedPassword) ?>">
        <label for="site_name">Site or App Name:</label>
        <input type="text" name="site_name" id="site_name" required>
        <button type="submit">Save</button>
      </form>
    <?php endif; ?>
    <p class="text-center"><a href="dashboard.php">Back to Dashboard</a></p>
  </div>
</body>
</html>
