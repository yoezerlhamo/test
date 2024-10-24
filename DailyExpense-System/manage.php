<?php
include("session.php"); // Session includes session_start()
$exp_fetched = mysqli_query($con, "SELECT * FROM expenses WHERE user_id = '$userid'");


// Initialize variables
$update = false;
$del = false;
$amount = "";
$date = date("Y-m-d");
$category = "";
$description = "";
$user_id = $_SESSION['user_id']; // Get user_id from session

// Handle add expense form submission
if (isset($_POST['add'])) {
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $description = $_POST['description'];

    // Insert into the budget table
    $sql = "INSERT INTO budget (user_id, amount, date, category, description) 
            VALUES ('$user_id', '$amount', '$date', '$category', '$description')";
    
    if (mysqli_query($con, $sql)) {
        echo "Record added successfully.";
    } else {
        echo "ERROR: Could not execute $sql. " . mysqli_error($con);
    }
    header('location: manage_expense.php');
    exit();
}


// Handle update form submission
if (isset($_POST['update'])) {
    $id = $_GET['edit'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $description = $_POST['description'];

    // Update the budget table
    $sql = "UPDATE budget SET amount='$amount', date='$date', category='$category', description='$description' 
            WHERE id='$id' AND user_id='$user_id'";
    if (mysqli_query($con, $sql)) {
        echo "Records were updated successfully.";
    } else {
        echo "ERROR: Could not execute $sql. " . mysqli_error($con);
    }
    header('location: manage_expense.php');
    exit();
}


// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Delete from the budget table
    $sql = "DELETE FROM budget WHERE id='$id' AND user_id='$user_id'";
    if (mysqli_query($con, $sql)) {
        echo "Record deleted successfully.";
    } else {
        echo "ERROR: Could not execute $sql. " . mysqli_error($con);
    }
    header('location: manage_expense.php');
    exit();
}

// Fetch record for editing
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update = true;
    // Fetch the record for editing for the current user
    $record = mysqli_query($con, "SELECT * FROM budget WHERE id=$id AND user_id='$user_id'");
    if (mysqli_num_rows($record) == 1) {
        $n = mysqli_fetch_array($record);
        $amount = $n['amount'];
        $date = $n['date'];
        $category = $n['category'];
        $description = $n['description'];
    }
}

$exp_fetched = mysqli_query($con, "SELECT * FROM budget WHERE user_id='$user_id'");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Expense&Income - Dashboard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/feather.min.js"></script>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-right" id="sidebar-wrapper">
            <div class="sidebar-heading">Management</div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action sidebar"><span data-feather="home"></span> Dashboard</a>
                <a href="add_expense.php" class="list-group-item list-group-item-action"><span data-feather="plus-square"></span> Add Expenses</a>
                <a href="manage_expense.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="eye"></span> View Expenses</a>
                <a href="add_income.php" class="list-group-item list-group-item-action"><span data-feather="dollar-sign"></span> Add Incomes</a>
                <a href="manage_income.php" class="list-group-item list-group-item-action sidebar"><span data-feather="eye"></span> View Incomes</a>
                <a href="report.php" class="list-group-item list-group-item-action sidebar"><span data-feather="bar-chart-2"></span> Reports</a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light border-bottom">
                <button class="toggler" type="button" id="menu-toggle" aria-expanded="false">
                    <span data-feather="menu"></span>
                </button>
            </nav>
        
            <div class="container">
                <h3 class="mt-4 text-center">Add Your Daily Expense/Income</h3>
                <hr>
                <div class="row">
                    <div class="col-md-3"></div>

                    <div class="col-md" style="margin:0 auto;">
                        <form action="" method="POST">
                            <div class="form-group row">
                                <label for="date" class="col-sm-3 col-form-label"><b>Date</b></label>
                                <div class="col-md-6">
                                    <input type="date" class="form-control col-sm-12" value="<?php echo $date; ?>" name="date" id="date" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="category" class="col-sm-3 col-form-label"><b>Category</b></label>
                                <div class="col-md-6">
                                    <select class="form-control col-sm-12" name="category" id="category" required>
                                        <option></option>
                                        <option value="expense" <?php if($category == "expense") echo "selected"; ?>>Expense</option>
                                        <option value="income" <?php if($category == "income") echo "selected"; ?>>Income</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="amount" class="col-sm-3 col-form-label"><b>Enter Amount($)</b></label>
                                <div class="col-md-6">
                                    <input type="number" class="form-control col-sm-12" value="<?php echo $amount; ?>" id="amount" name="amount" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="description" class="col-sm-3 col-form-label"><b>Remarks</b></label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control col-sm-12" value="<?php echo $description; ?>" id="description" name="description" required>
                                </div>
                            </div>

                            <div class="form-group text-center">
                                <?php if ($update == true): ?>
                                    <button class="btn btn-info col-md-4" type="submit" name="update">Update</button>
                                <?php else: ?>
                                    <button class="btn btn-primary col-md-4" type="submit" name="add">Add</button>
                                <?php endif ?>
                            </div>
                        </form>
                    </div>
                </div>

                <hr>

                <h4>Overview</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th colspan="2">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = mysqli_fetch_array($exp_fetched)) { ?>
                            <tr>
                                <td><?php echo $row['amount']; ?></td>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td>
                                    <a href="manage_expense.php?edit=<?php echo $row['id']; ?>" class="btn btn-info">Edit</a>
                                    <a href="manage_expense.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/feather.min.js"></script>
    <script>
        feather.replace();
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
    </script>
</body>
</html>
