<?php
session_start();
include 'config.php';

//if user is not loged in then page will not be assesable
//if user_id is not set then navigate to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//stroing username in username variable
$username = $_SESSION['username'];


$image = ''; // Default fallback
//fetchs the profile pic from db
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
//bind the session user_id to the SQL query as an integer
$stmt->bind_param("i", $_SESSION['user_id']);
//execute the uper sql query
$stmt->execute();
//it will store result in $res
$res = $stmt->get_result();
//if there is a single row is present with matching details then  it will store that profile pic
if ($res->num_rows === 1) {
    $user = $res->fetch_assoc();
    $image = $user['profile_picture'];
}

// qr code library for makeing QR code png imgs
require_once 'phpqrcode/qrlib.php';
//here I am holding the session values in variables and msg
$uid = $_SESSION['user_id'];
$username = $_SESSION['username'];
$msg = "";

// QR Code Creation
//It will check the form is sumbited or not and also check that both inputs are filed or not
// This is also used cause whenever I was refreshing page a qr code with privious entrys genrated by it selfh for overcoming tha I used && isset($_POST['qr_name'], $_POST['qr_url'] this part
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_name'], $_POST['qr_url'])) {
  //name of qr code in db
    $qr_name = $_POST['qr_name'];   
  // url in db
    $qr_url = $_POST['qr_url'];
  //validates that both are not empty
    if (!empty($qr_name) && !empty($qr_url)) {
  //it will create a file with `qr+cruenttime+.png`    
        $filename = 'qr_' . time() . '.png';
  //assecs to the file path
        $filepath = 'qrcodes/' . $filename;
  //it will create and store qr code img in to the qrcodes     
        QRcode::png($qr_url, $filepath, QR_ECLEVEL_H, 4);
  //for storing qr code in db
        $stmt = $conn->prepare("INSERT INTO qrcodes (name, url, image, created_at, created_by) VALUES (?, ?, ?, NOW(), ?)");
  //sssi (3 strings and one intger)      
        $stmt->bind_param("sssi", $qr_name, $qr_url, $filename, $uid);
  //execute query and show msg and navigate to PHP_SELF page
        if ($stmt->execute()) {
            $_SESSION['msg'] = "QR Code created successfully!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['msg'] = "Database Error!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['msg'] = "Please fill all fields.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}


// Pagination
$limit = 5;
//to show one page from query string
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
//for which page should be displayed in front end
$offset = ($page - 1) * $limit;
//for selecting or counting qr codes from all qr code in DB for that loged in user
$result = $conn->query("SELECT COUNT(*) AS total FROM qrcodes WHERE created_by = $uid");
//selected or counted qr code will will fetched here 
$totalRows = $result->fetch_assoc()['total'];
//for round off page calculation
$totalPages = ceil($totalRows / $limit);
//fetchs qr code from current page
$qrs = $conn->query("SELECT * FROM qrcodes WHERE created_by = $uid ORDER BY id DESC LIMIT $offset, $limit");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-yellow-100">
  <div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap">
    <!-- user Img and Name -->
      <div class="d-flex align-items-center gap-3 mb-2">
        <img src="uploads/<?= htmlspecialchars($image) ?>" alt="Profile" class="rounded-circle border border-black" style="width: 48px; height: 48px;">
        <span class="fs-5 fw-semibold"><?= htmlspecialchars($username) ?></span>
      </div>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <hr class="mt-3">

    <!-- QR Code Form -->
    <h4 class="mt-3 font-bold">Create QR Code</h4>
    <form method="POST" class="row g-3 mt-2">
      <div class="col-md-4">
        <input type="text" name="qr_name" class="form-control" placeholder="QR Code Name" required>
      </div>
      <div class="col-md-4">
        <input type="url" name="qr_url" class="form-control" placeholder="QR URL" required>
      </div>
      <div class="col-md-4">
        <button type="submit" name="generate" class="btn btn-success w-100">Generate QR</button>
      </div>
    </form>
<!-- For showing all error msg -->
    <?php if ($msg): ?>
      <div class="alert alert-info mt-3 bg-yellow-200"><?= $msg ?></div>
    <?php endif; ?>

    <hr class="mt-4">

    <!-- QR Code List -->
    <h4 class="mt-3 font-bold">Your QR Codes</h4>
    <div class="row mt-3">
      <?php while ($row = $qrs->fetch_assoc()): ?>
        <div class="col-md-4 col-sm-6 mb-4">
          <div class="bg-pink-100 rounded-lg shadow-lg p-4 text-center h-100">
            <h5 class="text-blue-600 font-bold text-lg mb-3"><?= htmlspecialchars($row['name']) ?></h5>
            <img 
              src="qrcodes/<?= htmlspecialchars($row['Image']) ?>" 
              alt="QR Code for <?= htmlspecialchars($row['name']) ?>" 
              class="mx-auto rounded border mb-3" 
              style="max-height: 140px;"
            />
            <p>
              <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" class="text-green-500 font-medium underline break-words w-full block">
                <?= htmlspecialchars($row['url']) ?>
              </a>
            </p>
            <small class="text-gray-500 block mt-2"><?= $row['created_at'] ?></small>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- Export Buttons -->
    <div class="d-flex gap-2 flex-wrap mt-3">
      <a href="export_excel.php" class="btn btn-success">Export to Excel</a>
      <a href="export_pdf.php" class="btn btn-danger">Export to PDF</a>
    </div>

    <!-- Pagination -->
    <nav class="mt-4">
      <ul class="pagination flex-wrap">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
  <button id="backToTopBtn" class="fixed bottom-5 right-5 bg-green-600 text-white p-3 rounded-full shadow-lg hidden md:hidden">
  ↑
  </button>
  <script>
    const backToTopBtn = document.getElementById('backToTopBtn');
    window.addEventListener('scroll', () => {
      if(window.scrollY > 200){
        backToTopBtn.classList.remove('hidden');
      } else {
        backToTopBtn.classList.add('hidden');
      }
      });
    backToTopBtn.addEventListener( 'click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  </script>
</body>
</html>
