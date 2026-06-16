<?php include('server.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmerLink — Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">
  <style>
    body {
      background: linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.6)), url('photos/WhatsApp Image 2026-04-16 at 21.13.53 (1).jpeg') center/cover fixed !important;
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      background: white;
      box-shadow: 0 2px 10px rgba(255,255,255,0.7), rgba(167,207,160,0.16);
    }

    .auth-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .auth-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      max-width: 420px;
      width: 100%;
      padding: 40px;
    }

    .auth-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .auth-header h1 {
      font-size: 1.8rem;
      color: #333;
      margin: 0 0 10px 0;
      font-weight: 700;
    }

    .auth-header p {
      color: #666;
      font-size: 0.95rem;
      margin: 0;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    .input-group {
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
    }

    .input-group label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .input-group input,
    .input-group select {
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.95rem;
      transition: border-color 0.3s, box-shadow 0.3s;
    }

    .input-group input:focus,
    .input-group select:focus {
      outline: none;
      border-color: #4CAF50;
      box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }

    .input-group button {
      padding: 12px 15px;
      background: #4CAF50;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
      font-family: 'Poppins', sans-serif;
    }

    .input-group button:hover {
      background: #45a049;
      transform: translateY(-2px);
    }

    .input-group button:active {
      transform: translateY(0);
    }

    .auth-footer {
      text-align: center;
      margin-top: 20px;
    }

    .auth-footer p {
      color: #666;
      font-size: 0.95rem;
      margin: 0;
    }

    .auth-footer a {
      color: #4CAF50;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s;
    }

    .auth-footer a:hover {
      color: #45a049;
      text-decoration: underline;
    }

    .errors {
      background: #ffebee;
      border-left: 4px solid #f44336;
      color: #c62828;
      padding: 12px 15px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }

    .errors li {
      margin: 5px 0;
    }

    @media (max-width: 600px) {
      .auth-card {
        padding: 30px 20px;
      }

      .auth-header h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1>Welcome Back</h1>
      <p>Sign in to your FarmerLink account</p>
    </div>

    <form method="post" action="login.php">
      <?php include('errors.php'); ?>

      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="input-group">
        <button type="submit" name="login_user">Sign In</button>
      </div>
    </form>

    <div class="auth-footer">
      <p>Don't have an account? <a href="register.php">Create one</a></p>
    </div>
  </div>
</div>

</body>
</html>
