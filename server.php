<?php
require_once 'marketplace_common.php';

// initializing variables
$username = "";
$email    = "";
$phone    = "";
$county   = "";
$role     = "";
$errors = array(); 

// connect to the database
$db = marketplace_db();

// REGISTER USER
if (isset($_POST['reg_user'])) {
  // receive all input values from the form
  $username   = trim((string) ($_POST['username'] ?? ''));
  $email      = trim((string) ($_POST['email'] ?? ''));
  $phone      = trim((string) ($_POST['phone'] ?? ''));
  $county     = trim((string) ($_POST['county'] ?? ''));
  $role       = trim((string) ($_POST['role'] ?? ''));
  $password_1 = (string) ($_POST['password_1'] ?? '');
  $password_2 = (string) ($_POST['password_2'] ?? '');

  // form validation
  if (empty($username))   { array_push($errors, "Username is required"); }
  if (empty($email))      { array_push($errors, "Email is required"); }
  if (empty($phone))      { array_push($errors, "Phone number is required"); }
  if (empty($county))     { array_push($errors, "Please select your county"); }
  if (empty($role))       { array_push($errors, "Please select your role (Farmer or Buyer)"); }
  if (empty($password_1)) { array_push($errors, "Password is required"); }
  if ($password_1 != $password_2) {
    array_push($errors, "The two passwords do not match");
  }

  if (!in_array($role, array('farmer', 'buyer'), true)) {
    array_push($errors, "Please register as a farmer or buyer.");
  }

  // check if username or email already exists
  $result = marketplace_query_result(
    $db,
    "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1",
    "ss",
    array($username, $email)
  );
  $user = mysqli_fetch_assoc($result);

  if ($user) {
    if ($user['username'] === $username) {
      array_push($errors, "Username already exists");
    }
    if ($user['email'] === $email) {
      array_push($errors, "Email already exists");
    }
  }

  // register user if no errors
  if (count($errors) == 0) {
    $password = password_hash($password_1, PASSWORD_DEFAULT);

    marketplace_execute(
      $db,
      "INSERT INTO users (username, email, phone, password, role, county)
       VALUES (?, ?, ?, ?, ?, ?)",
      "ssssss",
      array($username, $email, $phone, $password, $role, $county)
    );

    $user_id = mysqli_insert_id($db);

    $_SESSION['user_id']  = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role']     = $role;
    $_SESSION['success']  = "Registration successful. Welcome!";

    // redirect based on role
    marketplace_redirect_to_dashboard($role);
  }
}

// LOGIN USER
if (isset($_POST['login_user'])) {
  $username = trim((string) ($_POST['username'] ?? ''));
  $password = (string) ($_POST['password'] ?? '');

  if (empty($username)) { array_push($errors, "Username is required"); }
  if (empty($password)) { array_push($errors, "Password is required"); }

  if (count($errors) == 0) {
    $results = marketplace_query_result(
      $db,
      "SELECT * FROM users WHERE username = ? LIMIT 1",
      "s",
      array($username)
    );

    if ($results && mysqli_num_rows($results) == 1) {
      $user = mysqli_fetch_assoc($results);

      $stored_password = (string) $user['password'];
      $password_ok = password_verify($password, $stored_password);
      $needs_rehash = $password_ok && password_needs_rehash($stored_password, PASSWORD_DEFAULT);

      if (!$password_ok && hash_equals(md5($password), $stored_password)) {
        $password_ok = true;
        $needs_rehash = true;
      }

      if ($password_ok) {
        if ($needs_rehash) {
          $new_hash = password_hash($password, PASSWORD_DEFAULT);
          marketplace_execute(
            $db,
            "UPDATE users SET password = ? WHERE user_id = ?",
            "si",
            array($new_hash, $user['user_id'])
          );
        }

        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['success']  = "You are now logged in";

        // redirect based on role
        marketplace_redirect_to_dashboard($user['role']);
      } else {
        array_push($errors, "Wrong username/password combination");
      }
    } else {
      array_push($errors, "Wrong username/password combination");
    }
  }
}
?>
