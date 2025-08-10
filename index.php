<?php
//DB connection
include 'config.php';
//msg variable for all message which will store in this
$msg = '';
//It will check the form is submited or not
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  //The data which is collected from form
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $mobile   = $_POST['mobile'];
    $password = $_POST['password'];
    $cpassword= $_POST['cpassword'];
    $profile  = $_FILES['profile']['name'];

    // Validate file
    //File extensions for profile pic in a array 
    $allowed = ['png', 'jpg', 'jpeg', 'avif', 'webp'];
    //it will extrat the extension from $profile and converts it to lowercase
    $ext = strtolower(pathinfo($profile, PATHINFO_EXTENSION));
    //it will get file size in bytes
    $size = $_FILES['profile']['size']; 

    //validation check
    //password and confirm passwoard should be same if not then 
    if ($password !== $cpassword) {
        $msg = "Passwords do not match!";
    //it will check file extations    
    } elseif (!in_array($ext, $allowed)) {
        $msg = "Only PNG, JPG, AVIF, WEBP files allowed!";
    //file size less then 2048 kb or 2mb
    } elseif ($size > 2 * 1024 * 1024) {
        $msg = "File size must be under 2MB!";
    //It will check user in DB
    } else {
      //for checking that the email and mobile is present or not in sql db
        $check = $conn->prepare("SELECT id FROM users WHERE email=? OR mobile=?");
      //it will bind value
        $check->bind_param("ss", $email, $mobile);
      //it will exeecute value which is binded
        $check->execute();
      //it will store the result of number of rows present in sql db
        $check->store_result();
      //If yes then ....
        if ($check->num_rows > 0) {
            $msg = "Email or Mobile already registered!";
        } else {
          //Hashed password using php built in function
            $hashed = password_hash($password, PASSWORD_DEFAULT);
          //It will create a file name for profile pic
            $newName = uniqid() . "."  . $ext;
          //uploaded file from temp directory to uploads/ with the new name
            move_uploaded_file($_FILES['profile']['tmp_name'], "uploads/$newName");
          //It will sotr data from form in SQL DB which is enterd in signup form
            $stmt = $conn->prepare("INSERT INTO users (username, email, mobile, password, profile_picture) VALUES (?, ?, ?, ?, ?)");
          //Bind the values to the SQL statement
            $stmt->bind_param("sssss", $username, $email, $mobile, $hashed, $newName);
          //If Data is saved successfully in DB then it will navigate user to login page
            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $msg = "Something went wrong!";
            }
        }
    }
    }
?>


<!-- SIGNUP FRONTEND -->
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sign Up</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-300">

<div class="container mt-5">
  <div class="col-md-6 mx-auto card p-4 shadow bg-yellow-100">
    <h3 class="mb-3 text-center text-2xl font-bold">Register Here Please !</h3>
   <?php if ($msg): ?>
      <div class="alert alert-danger mt-3"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="form">
      <div class="mb-2"><label>Username</label><input type="text" name="username" required class="form-control"></div>
      <div class="mb-2"><label>Email</label><input type="email" name="email" required class="form-control"></div>
      <div class="mb-2"><label>Mobile</label><input type="tel" name="mobile" maxlength="10" pattern="\d{10}" required class="form-control"></div>
      <div class="mb-2"><label>Password</label><input type="password" name="password" id="password" required minlength="6" class="form-control"></div>
      <div class="mb-2"><label>Confirm Password</label><input type="password" name="cpassword" id="cpassword" required class="form-control"></div>
      <div class="mb-3"><label>Profile Picture</label><input type="file" name="profile" accept=".png,.jpg,.jpeg,.avif,.webp" required class="form-control"></div>
      <div class="flex justify-center"><button type="submit" class="btn btn-info w-80">Sign Up</button></div>
    </form>

    <div class="text-center mt-5">
      <h6 class="text-xl">Already have an account?
        <a href="login.php" class="text-green-600 font-semibold">Login Here!</a>
      </h6>
    </div>
  </div>
</div>

</body>
</html>
