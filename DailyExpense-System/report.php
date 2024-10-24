<?php
include("session.php");

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');  
$month = isset($_GET['month']) ? $_GET['month'] : date('m'); 

// Query to get total income and expenses along with descriptions for the selected year and month
$report_fetched = mysqli_query($con, "
    SELECT 
        date, 
        SUM(CASE WHEN category = 'income' THEN amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN category = 'expense' THEN amount ELSE 0 END) AS total_expense,
        GROUP_CONCAT(CASE WHEN category = 'income' THEN description END) AS income_descriptions,
        GROUP_CONCAT(CASE WHEN category = 'expense' THEN description END) AS expense_descriptions
    FROM 
        budget 
    WHERE 
        user_id = '$userid' 
        AND YEAR(date) = '$year' 
        AND MONTH(date) = '$month'
    GROUP BY 
        date
    ORDER BY 
        date
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Income and Expense Report</title>
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
                <a href="manage_expense.php" class="list-group-item list-group-item-action sidebar"><span data-feather="eye"></span> Add Expenses/Incomes</a>
                <a href="report.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="bar-chart-2"></span> Reports</a>
            </div>
            <div class="sidebar-heading">Settings </div>
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
            </nav>

            <div class="container-fluid">
                <h3 class="mt-4 text-center">Income and Expense Report</h3>
                <hr>
                <form method="GET" class="row justify-content-center mb-4">
                    <div class="form-group col-md-3">
                        <label for="year">Year</label>
                        <select class="form-control" id="year" name="year">
                            <?php for ($i = date('Y'); $i >= 2000; $i--) { ?>
                                <option value="<?php echo $i; ?>" <?php if ($i == $year) echo 'selected'; ?>><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="month">Month</label>
                        <select class="form-control" id="month" name="month">
                        <?php 
                        $months = [
                            '01' => 'January', 
                            '02' => 'February', 
                            '03' => 'March', 
                            '04' => 'April', 
                            '05' => 'May', 
                            '06' => 'June', 
                            '07' => 'July', 
                            '08' => 'August', 
                            '09' => 'September', 
                            '10' => 'October', 
                            '11' => 'November', 
                            '12' => 'December'
                        ];

                        foreach ($months as $month_num => $month_name) { ?>
                            <option value="<?php echo $month_num; ?>" <?php if ($month_num == $month) echo 'selected'; ?>>
                                <?php echo $month_name; ?>
                            </option>
                        <?php } ?>
                        </select>

                    </div>
                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>

                <!-- Combined Income and Expenses Table -->
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Income Amount</th>
                                    <th>Expense Amount</th>
                                    <th>Income Remarks</th>
                                    <th>Expense Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($report_fetched) > 0) {
                                    $count = 1;
                                    $total_income = 0;
                                    $total_expense = 0;
                                    while ($row = mysqli_fetch_array($report_fetched)) { 
                                        $total_income += $row['total_income'];
                                        $total_expense += $row['total_expense']; ?>
                                        <tr>
                                            <td><?php echo $count; ?></td>
                                            <td><?php echo $row['date']; ?></td>
                                            <td><?php echo $row['total_income'] ? '$' . number_format($row['total_income'], 2) : '-'; ?></td>
                                            <td><?php echo $row['total_expense'] ? '$' . number_format($row['total_expense'], 2) : '-'; ?></td>
                                            <td><?php echo $row['income_descriptions'] ?: '-'; ?></td>
                                            <td><?php echo $row['expense_descriptions'] ?: '-'; ?></td>
                                        </tr>
                                    <?php $count++; } ?>
                                    <!-- Total Row -->
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Total:</strong></td>
                                        <td><strong><?php echo '$' . number_format($total_income, 2); ?></strong></td>
                                        <td><strong><?php echo '$' . number_format($total_expense, 2); ?></strong></td>
                                    </tr>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No incomes or expenses found for the selected period.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript -->
    <script src="js/jquery.slim.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        feather.replace()
    </script>

</body>

</html>
