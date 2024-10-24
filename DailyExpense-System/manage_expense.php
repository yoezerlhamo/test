<?php
include("session.php");

// Variables
$update = false;
$del = false;
$amount = "";
$date = date("Y-m-d");
$category = "";
$description = "";
$exp_fetched = mysqli_query($con, "SELECT * FROM budget WHERE user_id = '$userid'");

// Adding Expense/Income
if (isset($_POST['add'])) {
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    // my code
    $category_query = "INSERT INTO category (user_id, description) VALUES ('$userid', '$description')";
    $result = mysqli_query($con, $category_query) or die("Something Went Wrong!");

    $query = "INSERT INTO budget (user_id, amount, date, category, description) VALUES ('$userid', '$amount', '$date', '$category', '$description')";
    $result = mysqli_query($con, $query) or die("Something Went Wrong!");
    header('location: manage_expense.php');
}

// Updating Expense/Income
if (isset($_POST['update'])) {
    $id = $_GET['edit'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $description = $_POST['description'];

    // Update 'budget' table
    $budget_query = "UPDATE budget SET amount='$amount', date='$date', category='$category', description='$description' WHERE user_id='$userid' AND id='$id'";
    if (mysqli_query($con, $budget_query)) {
        echo "Records were updated successfully.";
    } else {
        echo "ERROR: Could not execute $budget_query. " . mysqli_error($con);
    }

    // Update 'category' table
    $category_query = "INSERT INTO category (user_id, description) VALUES ('$userid', '$description')
                       ON DUPLICATE KEY UPDATE description='$description'";
    mysqli_query($con, $category_query) or die("Something Went Wrong!");

    header('location: manage_expense.php');
}



// Deleting Expense/Income
if (isset($_POST['delete'])) {
    $id = $_GET['delete'];

    // Get the description to be deleted
    $record = mysqli_query($con, "SELECT description FROM budget WHERE user_id='$userid' AND id='$id'");
    if (mysqli_num_rows($record) == 1) {
        $row = mysqli_fetch_array($record);
        $description = $row['description'];

        // Delete from 'budget' table
        $budget_query = "DELETE FROM budget WHERE user_id='$userid' AND id='$id'";
        if (mysqli_query($con, $budget_query)) {
            echo "Record was deleted successfully.";
        } else {
            echo "ERROR: Could not execute $budget_query. " . mysqli_error($con);
        }

        // Delete from 'category' table if no other budget entries are using this description
        $category_check_query = "SELECT * FROM budget WHERE user_id='$userid' AND description='$description'";
        $category_check_result = mysqli_query($con, $category_check_query);
        
        // Only delete from the category table if no other entries are found
        if (mysqli_num_rows($category_check_result) == 0) {
            $category_query = "DELETE FROM category WHERE user_id='$userid' AND description='$description'";
            mysqli_query($con, $category_query) or die("Something Went Wrong!");
        }
    }

    header('location: manage_expense.php');
}



// Editing Expense/Income
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update = true;
    $record = mysqli_query($con, "SELECT * FROM budget WHERE user_id='$userid' AND id=$id");

    if (mysqli_num_rows($record) == 1) {
        $n = mysqli_fetch_array($record);
        $amount = $n['amount'];
        $date = $n['date'];
        $category = $n['category'];
        $description = $n['description'];
        $category_record = mysqli_query($con, "SELECT * FROM category WHERE user_id='$userid' AND description='$description'");
        if (mysqli_num_rows($category_record) == 1) {
            $cat = mysqli_fetch_array($category_record);
            $description = $cat['description'];
        }
    } else {
        echo "WARNING: AUTHORIZATION ERROR: Trying to access unauthorized data.";
    }
}

// Deleting Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $del = true;
    $record = mysqli_query($con, "SELECT * FROM budget WHERE user_id='$userid' AND id=$id");

    if (mysqli_num_rows($record) == 1) {
        $n = mysqli_fetch_array($record);
        $amount = $n['amount'];
        $date = $n['date'];
        $category = $n['category'];
        $description = $n['description'];
    } else {
        echo "WARNING: AUTHORIZATION ERROR: Trying to access unauthorized data.";
    }
}
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
            <div class="user">
                <img class="img img-fluid rounded-circle" src="<?php echo $userprofile ?>" width="120">
                <h5><?php echo $username ?></h5>
                <p><?php echo $useremail ?></p>
            </div>
            <div class="sidebar-heading">Management</div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action"><span data-feather="home"></span> Dashboard</a>
                <a href="manage_expense.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="eye"></span> Add Expenses/Incomes</a>
                <a href="report.php" class="list-group-item list-group-item-action sidebar"><span data-feather="bar-chart-2"></span> Reports</a>
            </div>
            <div class="sidebar-heading">Settings</div>
            <div class="list-group list-group-flush">
                <a href="profile.php" class="list-group-item list-group-item-action"><span data-feather="user"></span> Profile</a>
                <a href="logout.php" class="list-group-item list-group-item-action"><span data-feather="power"></span> Logout</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">

            <nav class="navbar navbar-expand-lg navbar-light border-bottom">
                <button class="toggler" type="button" id="menu-toggle" aria-expanded="false">
                    <span data-feather="menu"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="img img-fluid rounded-circle" src="<?php echo $userprofile ?>" width="25">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Your Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container">
                <h3 class="mt-4 text-center">Add Your Daily Expenses/Incomes</h3>
                <hr>
                <div class="row">
                    <div class="col-md-3"></div>

                    <div class="col-md-6">
                        <form action="" method="POST">
                            <div class="form-group row">
                                <label for="date" class="col-sm-3 col-form-label"><b>Date</b></label>
                                <div class="col-md-9">
                                    <input type="date" class="form-control" value="<?php echo $date; ?>" name="date" id="date" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="category" class="col-sm-3 col-form-label"><b>Category</b></label>
                                <div class="col-md-9">
                                    <select class="form-control" name="category" id="category" required>
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="expense" <?php if($category == "expense") echo "selected"; ?>>Expense</option>
                                        <option value="income" <?php if($category == "income") echo "selected"; ?>>Income</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="amount" class="col-sm-3 col-form-label"><b>Amount($)</b></label>
                                <div class="col-md-9">
                                    <input type="number" class="form-control" value="<?php echo $amount; ?>" id="amount" name="amount" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="description" class="col-sm-3 col-form-label"><b>Remarks</b></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" value="<?php echo $description; ?>" id="description" name="description" required>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <?php if ($update == true): ?>
                                    <button class="btn btn-info col-md-4" type="submit" name="update">Update</button>
                                <?php elseif ($del == true): ?>
                                    <button class="btn btn-danger col-md-4" type="submit" name="delete">Delete</button>
                                <?php else: ?>
                                    <button class="btn btn-primary col-md-4" type="submit" name="add">Add</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <hr>

                <div class="container">
                    <h3 class="text-center">Expenses & Incomes List</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Amount($)</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($exp_fetched)) { ?>
                                <tr>
                                    <td><?php echo $row['amount']; ?></td>
                                    <td><?php echo $row['category']; ?></td>
                                    <td><?php echo $row['date']; ?></td>
                                    <td><?php echo $row['description']; ?></td>
                                    <td>
                                        <a href="manage_expense.php?edit=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                        <a href="manage_expense.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.3.1.slim.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        feather.replace()
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
    </script>

</body>

</html>