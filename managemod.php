<?php
// -------------------- SESSION & ACCESS --------------------
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// -------------------- DATABASE --------------------
include '../db.php'; // Make sure db.php uses mysql_connect and sets $con

// -------------------- SANITIZE INPUT --------------------
function sanitize($str) {
    return mysql_real_escape_string(trim($str));
}

// -------------------- INCLUDE PHPMailer --------------------
require '../PHPMailer/class.phpmailer.php';
require '../PHPMailer/class.smtp.php'; // Required for SMTP

// -------------------- ADD EMPLOYEE --------------------
if (isset($_POST['add'])) {
    $name   = sanitize($_POST['username']);
    $email  = sanitize($_POST['email']);
    $dept   = sanitize($_POST['department']);
    $plainPassword = $_POST['password'];

    // Hash password
    $password = crypt($plainPassword, '$2a$12$'.substr(md5(rand()),0,22));
    $password = mysql_real_escape_string($password);
    $resetToken = md5(uniqid(rand(), true));

    mysql_query("INSERT INTO employee (username, email, password, department, reset_token)
                 VALUES ('$name', '$email', '$password', '$dept', '$resetToken')", $con)
        or die("Error adding employee: " . mysql_error());

   // -------------------- SEND EMAIL --------------------
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->Host     = 'smtp.gmail.com';
$mail->Port     = 587;
$mail->SMTPSecure = 'tls';
$mail->Username = 'habeebjaffer3883@gmail.com'; // Your Gmail
$mail->Password = 'ztfvqnefxvmqovrs';           // Your App Password

$mail->SetFrom('habeebjaffer3883@gmail.com', 'Admin - Leave Management');
$mail->AddAddress($email, $name);

$mail->IsHTML(true);
$mail->Subject = 'Welcome to Leave Management System';

// create reset link using the reset_token you already generate
$resetLink = "http://localhost/Leavemanagementsystem/reset_password.php?token=$resetToken";

$mail->Body = "
    <h2>Welcome, $name!</h2>
    <p>Your employee account has been created successfully.</p>
    <p><b>Login Details:</b></p>
    <ul>
        <li>Email: $email</li>
        <li>Password: $plainPassword</li>
    </ul>
    <p><a href='$resetLink'>Click here to change your password</a></p>
    <p>Please login and change your password after first login.</p>
    <p>Regards,<br><b>Admin</b></p>
";
$mail->AltBody = "Hello $name,\nYour account has been created.\nEmail: $email\nPassword: $plainPassword\n\nReset your password here: $resetLink\n\nPlease change your password after first login.\n\nRegards,\nAdmin";

    if(!$mail->Send()) {
        echo "<div class='alert alert-warning'>Employee added but email not sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo) . "</div>";
    } else {
        echo "<div class='alert alert-success'>Employee added successfully! Email sent to " . htmlspecialchars($email) . "</div>";
    }
}

// -------------------- DELETE EMPLOYEE --------------------
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    mysql_query("DELETE FROM employee WHERE id='$deleteId'", $con) 
        or die("Error deleting employee: " . mysql_error());
    echo "<div class='alert alert-success'>Employee deleted successfully!</div>";
}

// -------------------- EDIT EMPLOYEE --------------------
if (isset($_POST['edit'])) {
    $editId = (int)$_POST['edit_id'];
    $name   = sanitize($_POST['edit_username']);
    $email  = sanitize($_POST['edit_email']);
    $dept   = sanitize($_POST['edit_department']);

    mysql_query("UPDATE employee 
                 SET username='$name', email='$email', department='$dept' 
                 WHERE id='$editId'", $con)
        or die("Error updating employee: " . mysql_error());
    echo "<div class='alert alert-success'>Employee updated successfully!</div>";
}

// -------------------- FETCH EMPLOYEES --------------------
$emps = mysql_query("SELECT * FROM employee ORDER BY id ASC", $con)
    or die("Error fetching employees: " . mysql_error());
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Employees</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-link">&larr; Back to Dashboard</a>
    <h3>Add Employee</h3>
    <form method="POST" class="row g-3 mb-5">
        <div class="col-md-6"><input name="username" class="form-control" required placeholder="Name" /></div>
        <div class="col-md-6"><input name="email" type="email" class="form-control" required placeholder="Email" /></div>
        <div class="col-md-6"><input name="department" class="form-control" required placeholder="Department" /></div>
        <div class="col-md-6"><input name="password" type="password" class="form-control" required placeholder="Password" /></div>
        <div class="col-12 text-center">
            <button name="add" type="submit" class="btn btn-primary px-4">Add Employee</button>
        </div>
    </form>

    <h3>Employees</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(mysql_num_rows($emps) > 0) {
            while ($row = mysql_fetch_assoc($emps)) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Employee</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>" />
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="edit_username" class="form-control" value="<?php echo htmlspecialchars($row['username']); ?>" required />
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="edit_email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required />
                        </div>
                        <div class="mb-3">
                            <label>Department</label>
                            <input type="text" name="edit_department" class="form-control" value="<?php echo htmlspecialchars($row['department']); ?>" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
        <?php } } else { ?>
            <tr><td colspan="5" class="text-center">No employees found.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
