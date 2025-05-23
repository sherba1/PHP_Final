<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../DatabaseConnection.php';
require_once '../PasswordManager.php';

$dbConn = new DatabaseConnection();
$db = $dbConn->getConnection();
$pm = new PasswordManager($db, $_SESSION['master_key'], $_SESSION['user_id']);
$entries = $pm->getEntries();

$msg = '';
if (isset($_GET['saved'])) {
    $msg = "Password saved successfully";
}
if (isset($_GET['deleted'])) {
    $msg = "Password deleted";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Saved Passwords &mdash; Password Manager</title>
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
    }
    h2 {
      text-align: center;
      font-size: 24px;
      margin-bottom: 20px;
      color: #2c3e50;
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
    .errors {
      background-color: #fdecea;
      border: 1px solid #e74c3c;
      color: #c0392b;
      padding: 10px 14px;
      margin-bottom: 18px;
      border-radius: 6px;
      font-size: 13px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    table thead tr {
      background-color: #ecf0f1;
    }
    table th, table td {
      padding: 10px 8px;
      border: 1px solid #ddd;
      text-align: left;
      font-size: 14px;
      color: #2c3e50;
    }
    table tbody tr:nth-child(even) {
      background-color: #fafafa;
    }
    table tbody tr:hover {
      background-color: #f1f5f8;
    }
    button {
      background-color: #777777;
      color: #ffffff;
      border: none;
      border-radius: 6px;
      padding: 6px 14px;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.2s ease, transform 0.1s ease;
    }
    button:hover {
      background-color: #555555;
      transform: translateY(-1px);
    }
    button:active {
      transform: translateY(1px);
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
    <h2>My Saved Passwords</h2>
    <?php if ($msg): ?>
      <div class="success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if (empty($entries)): ?>
      <p>No saved passwords yet.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Site/App</th>
            <th>Password</th>
            <th>Created At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($entries as $entry): ?>
            <tr>
              <td><?= $entry['id'] ?></td>
              <td><?= htmlspecialchars($entry['siteName']) ?></td>
              <td><code><?= htmlspecialchars($entry['password']) ?></code></td>
              <td><?= htmlspecialchars($entry['created']) ?></td>
              <td>
                <form style="display:inline;" method="post" action="delete_password.php">
                  <input type="hidden" name="entry_id" value="<?= $entry['id'] ?>">
                  <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <p class="text-center"><a href="dashboard.php">Back to Dashboard</a></p>
  </div>
</body>
</html>
