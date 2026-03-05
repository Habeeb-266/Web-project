<?php
session_start();
if ($_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

// Approve / Reject action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = ($_GET['action'] == 'approve') ? 'Approved' : 'Rejected';

    if ($status == 'Approved') {
        $appRes = mysql_query("SELECT * FROM apply_leave WHERE id=$id", $con);
        $app = mysql_fetch_assoc($appRes);

        // Use 'days' from table (already stores 0.5 for half day, 1 for full day, etc.)
        $days = isset($app['days']) ? (float)$app['days'] : 1.0;

        // Update balance
        mysql_query("UPDATE leave_balance 
                        SET balance = balance - $days 
                        WHERE employee_id={$app['employee_id']}", $con);
    }

    mysql_query("UPDATE apply_leave SET status='$status' WHERE id=$id", $con);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch pending applications
$applications = mysql_query("
    SELECT 
        al.id, 
        e.username AS name, 
        lt.type_name, 
        al.from_date, 
        al.to_date, 
        al.reason,
        al.is_half_day,
        al.half_day_slot,
        al.days
    FROM apply_leave al
    JOIN employee e ON al.employee_id = e.id
    JOIN leave_type lt ON al.leave_type_id = lt.id
    WHERE al.status = 'Pending'
", $con) or die(mysql_error());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Approve Leave Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .container {
            max-width: 1000px; margin: 50px auto; background: #fff;
            padding: 30px 40px; border-radius: 10px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.1);
        }
        h2 { margin-bottom: 30px; text-align: center; }
        table { table-layout: fixed; }
        td, th { word-wrap: break-word; }
        .btn-approve { color:#fff; background:#198754; padding:5px 12px; border-radius:5px; text-decoration:none; }
        .btn-reject { color:#fff; background:#dc3545; padding:5px 12px; border-radius:5px; text-decoration:none; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn btn-link">&larr; Back to Dashboard</a>
    <h2>Pending Leave Applications</h2>

    <?php if (mysql_num_rows($applications) == 0) { ?>
        <p class="text-center text-muted">No pending leave applications.</p>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Timing</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysql_fetch_assoc($applications)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['from_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['to_date']); ?></td>
                            <td>
                                <?php
                                if ($row['is_half_day']) {
                                    echo ucfirst($row['half_day_slot']) . " (Half Day)";
                                } else {
                                    echo "Full Day";
                                }
                                ?>
                            </td>
                            <td><?php echo $row['days']; ?></td>
                            <td style="max-width: 250px;"><?php echo nl2br(htmlspecialchars($row['reason'])); ?></td>
                            <td class="text-center">
                                <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn-approve btn-sm">Approve</a>
                                <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn-reject btn-sm">Reject</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

</body>
</html>
