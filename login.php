<?php
session_start();
//DB connection
include 'config.php';
//A vareable for storege. All error msg will be stored here
$msg = '';
//It will check data in form is submited or not
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  //$_POST it will retrives data
    $user = $_POST['user']; // email or mobile
    $password = $_POST['password'];
  //To find user in users table bye email and phone number
    $sql = $conn->prepare("SELECT id, username, email, password, profile_picture FROM users WHERE email=? OR mobile=?");
  //ss both are strings
    $sql->bind_param("ss", $user, $user);
  //execute uper sql query
    $sql->execute();
  //it will fetch the reslt from upper sql query 
    $result = $sql->get_result();
  //If there is a row present with matching data in sql DB
    if ($result->num_rows == 1) {
  //Fetches the matching user's data as an associative array
        $row = $result->fetch_assoc();
  //compaire password with db password
        if (password_verify($password, $row['password'])) {
  //Here I am storing data of user so I can use it like showing profile pic name etc.          
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['image'] = $row['image'];
  //navigating to the Dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $msg = "Invalid Password!";
        }
    } else {
        $msg = "No account found with that email or mobile!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="col-md-6 mx-auto card p-4 shadow bg-yellow-200">
    <h1 class="mb-3 text-center text-2xl font-bold">Login Here Please</h1>
    <!-- all the error $msg will be displayed here -->
    <?php if ($msg): ?>
      <div class="alert alert-danger mt-3"><?= $msg ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="mb-3">
        <label>Email or Mobile</label>
        <input type="text" name="user" required class="form-control">
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" required class="form-control">
      </div>
      <button type="submit" class="btn btn-success w-100">Login</button>
    </form>
    <div class="mt-3 text-center">
      Don't have an account? <a href="index.php " class="text-green-600 font-semibold">Sign Up!</a>
    </div>
  </div>
</div>
</body>
</html>
