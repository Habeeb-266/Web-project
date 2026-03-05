<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// WAMP MySQL connection
$con = mysql_connect("localhost","root","");
if (!$con) { die("Connection failed: " . mysql_error()); }

if (!mysql_select_db("leavemanage",$con)) {
    die("Database selection failed: " . mysql_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background-color: #f8f9fa;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background-color: #0d6efd;
      padding-top: 20px;
      color: white;
    }
    .sidebar h3 {
      text-align: center;
      margin-bottom: 30px;
    }
    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      margin: 5px 0;
      border-radius: 4px;
      transition: background 0.2s;
    }
    .sidebar a:hover {
      background-color: #0b5ed7;
    }
    .content {
      margin-left: 250px;
      padding: 30px;
    }
    .stat-card {
      border-radius: 10px;
      padding: 25px;
      color: white;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: scale(1.03);
    }
    .bg-pending { background: #ffc107; }
    .bg-employee { background: #198754; }
    .bg-types { background: #0dcaf0; }
  </style>
</head>
<body>

<div class="sidebar">
  <h3>Admin Panel</h3>
  <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
  <a href="approve_leaves.php"><i class="bi bi-check-circle me-2"></i> Approve Leaves</a>
  <a href="managemod.php"><i class="bi bi-people me-2"></i> Employees</a>
  <a href="leave_types.php"><i class="bi bi-list-ul me-2"></i> Leave Types</a>
  <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</div>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Welcome, Admin</h2>
    <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="stat-card bg-pending" onclick="window.location='approve_leaves.php'">
        <h3><i class="bi bi-hourglass-split"></i> Pending Leaves</h3>
        <p>Check and manage leave requests</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card bg-employee" onclick="window.location='managemod.php'">
        <h3><i class="bi bi-people-fill"></i> Employees</h3>
        <p>View and manage employee records</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card bg-types" onclick="window.location='leave_types.php'">
        <h3><i class="bi bi-card-list"></i> Leave Types</h3>
        <p>Manage different leave types</p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
