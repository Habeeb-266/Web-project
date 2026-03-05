<?php
session_start();
if ($_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// --- Database connection ---
include '../db.php'; // Make sure this file defines $con properly

/*
 Example db.php content:
 <?php
 $con = mysql_connect("localhost", "root", "") or die("Database connection failed!");
 mysql_select_db("your_database_name", $con) or die("Database not found!");
 ?>
*/

// --- Sanitize input ---
function sanitize($str) {
    return mysql_real_escape_string(trim($str));
}

// --- Add Employee ---
if (isset($_POST['add'])) {
    $name  = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $dept  = sanitize($_POST['department']);
    $password = crypt($_POST['password'], '$2a$12$'.substr(md5(rand()),0,22));
    $password = mysql_real_escape_string($password);

    mysql_query("INSERT INTO employee (username, email, password, department)
                 VALUES ('$name', '$email', '$password', '$dept')")
        or die("Error adding employee: " . mysql_error());

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- Edit Employee ---
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $name  = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $dept  = sanitize($_POST['department']);

    if (!empty($_POST['password'])) {
        $password = crypt($_POST['password'], '$2a$12$'.substr(md5(rand()),0,22));
        $password = mysql_real_escape_string($password);
        mysql_query("UPDATE employee SET username='$name', email='$email', department='$dept', password='$password' WHERE id=$id")
            or die("Error updating employee: " . mysql_error());
    } else {
        mysql_query("UPDATE employee SET username='$name', email='$email', department='$dept' WHERE id=$id")
            or die("Error updating employee: " . mysql_error());
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- Delete Employee ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysql_query("DELETE FROM leave_balance WHERE employee_id=$id") or die(mysql_error());
    mysql_query("DELETE FROM employee WHERE id=$id") or die(mysql_error());
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- Fetch Employees ---

$emps = mysql_query("SELECT * FROM employee ORDER BY id ASC") or die("Error fetching employees: " . mysql_error());

if(!$emps){
    die("Query failed: " . mysql_error());
} else {
    echo "<p>Found ".mysql_num_rows($emps)." employee.</p>";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Employees</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
body { background-color: #f8f9fa; }
.container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 6px 25px rgba(0,0,0,0.1); }
h3 { margin-bottom: 20px; text-align: center; }
.back-btn { display: inline-block; margin-bottom: 25px; color: #0d6efd; text-decoration: none; font-weight: 500; }
.back-btn:hover { text-decoration: underline; color: #0a58ca; }
</style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="back-btn">&larr; Back to Dashboard</a>

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
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
               <?php if (mysql_num_rows($emps) == 0): ?>
    <tr>
        <td colspan="5">No employees found.</td>
    </tr>
<?php else: ?>
    <?php while ($row = mysql_fetch_assoc($emps)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['department']); ?></td>
            <td>
                <button class="btn btn-sm btn-warning edit-btn"
                    data-id="<?php echo $row['id']; ?>"
                    data-name="<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>"
                    data-email="<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>"
                    data-department="<?php echo htmlspecialchars($row['department'], ENT_QUOTES); ?>">
                    Edit
                </button>
                <a href="?delete=<?php echo $row['id']; ?>"
                   onclick="return confirm('Are you sure you want to delete this employee?');"
                   class="btn btn-sm btn-danger ms-2">Delete</a>
            </td>
        </tr>
    <?php } ?>
<?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
            <input type="hidden" name="id" id="edit-id" />
            <div class="mb-3">
                <label for="edit-name" class="form-label">Name</label>
                <input type="text" name="username" id="edit-name" class="form-control" required />
            </div>
            <div class="mb-3">
                <label for="edit-email" class="form-label">Email</label>
                <input type="email" name="email" id="edit-email" class="form-control" required />
            </div>
            <div class="mb-3">
                <label for="edit-department" class="form-label">Department</label>
                <input type="text" name="department" id="edit-department" class="form-control" required />
            </div>
            <div class="mb-3">
                <label for="edit-password" class="form-label">Password <small>(leave blank to keep unchanged)</small></label>
                <input type="password" name="password" id="edit-password" class="form-control" />
            </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var editButtons = document.querySelectorAll('.edit-btn');
    var modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));

    for (var i = 0; i < editButtons.length; i++) {
        editButtons[i].addEventListener('click', function() {
            document.getElementById('edit-id').value = this.getAttribute('data-id');
            document.getElementById('edit-name').value = this.getAttribute('data-name');
            document.getElementById('edit-email').value = this.getAttribute('data-email');
            document.getElementById('edit-department').value = this.getAttribute('data-department');
            document.getElementById('edit-password').value = '';
            modal.show();
        });
    }
});
</script>

</body>
</html>
	