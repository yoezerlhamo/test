<?php
include("session.php");
$exp_category_dc = mysqli_query($con, "SELECT category FROM budget WHERE user_id = '$userid' GROUP BY category");
$exp_amt_dc = mysqli_query($con, "SELECT SUM(expense) as total_expense FROM budget WHERE user_id = '$userid' GROUP BY category");

$exp_date_line = mysqli_query($con, "SELECT date FROM budget WHERE user_id = '$userid' GROUP BY date");
$exp_amt_line = mysqli_query($con, "SELECT SUM(expense) as total_expense FROM budget WHERE user_id = '$userid' GROUP BY date");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Expense/Income Manager - Dashboard</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <script src="js/feather.min.js"></script>
  <style>
    .card a {
      color: #000;
      font-weight: 500;
    }
    .card a:hover {
      color: #28a745;
      text-decoration: dotted;
    }
  </style>
</head>
<body>

<div class="d-flex" id="wrapper">
  <div class="border-right" id="sidebar-wrapper">
    <div class="user">
      <img class="img img-fluid rounded-circle" src="<?php echo $userprofile ?>" width="120">
      <h5><?php echo $username ?></h5>
      <p><?php echo $useremail ?></p>
    </div>
    <div class="sidebar-heading">Management</div>
    <div class="list-group list-group-flush">
    <a href="index.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="home"></span> Dashboard</a>
    <a href="manage_expense.php" class="list-group-item list-group-item-action sidebar"><span data-feather="eye"></span> Add Expenses/Incomes</a>
    <a href="report.php" class="list-group-item list-group-item-action sidebar"><span data-feather="bar-chart-2"></span> Reports</a>
    </div>
    <div class="sidebar-heading">Settings </div>
    <div class="list-group list-group-flush">
      <a href="profile.php" class="list-group-item list-group-item-action"><span data-feather="user"></span> Profile</a>
      <a href="logout.php" class="list-group-item list-group-item-action"><span data-feather="power"></span> Logout</a>
    </div>
  </div>
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
              <a class="dropdown-item" href="profile.php">Your Profile</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="logout.php">Logout</a>
            </div>
          </li>
        </ul>
      </div>
    </nav>

    <div class="container-fluid">
    <h3 class="mt-4">Dashboard</h3>
    <div class="row">
        <div class="col-md">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-center">
                            <a href="manage_expense.php"><span data-feather="plus-square" style="font-size: 57px;"></span>
                                <p>Expenses and Income</p>
                            </a>
                        </div>
                        <div class="col text-center">
                            <a href="report.php"><span data-feather="bar-chart-2" style="font-size: 57px;"></span>
                                <p>Generate Report</p>
                            </a>
                        </div>
                        <div class="col text-center">
                            <a href="profile.php"><span data-feather="user" style="font-size: 57px;"></span>
                                <p>User Profile</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
      
      <div class="row">
        <div class="col-md">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title text-center">Combined Income and Expense Report</h5>
              </div>
              <div class="card-body">
                  <canvas id="combined_line" height="100"></canvas>
              </div>
            </div>
        </div>
      </div>
        </div>
      </div>
    </div>
  </div>
</div>
  </div>
<script src="js/bootstrap.min.js"></script>
<script src="js/Chart.min.js"></script>
<script>
  $("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
  });
</script>
<script>
  feather.replace()
</script>
<script>
 var ctxCombinedLine = document.getElementById('combined_line').getContext('2d');

// Fetch the total expenses and incomes for each date in a structured way
var dates = [];
var expenses = [];
var incomes = [];

// Get unique dates from the budget
<?php 
$date_query = mysqli_query($con, "SELECT DISTINCT date FROM budget WHERE user_id = '$userid' ORDER BY date");
while ($date_row = mysqli_fetch_array($date_query)) {
    echo 'dates.push("' . $date_row['date'] . '");';
}
?>

// Initialize expenses and incomes arrays to 0 for each date
dates.forEach(function(date) {
    expenses.push(0);
    incomes.push(0);
});

// Populate the expenses array
<?php 
$expense_data = mysqli_query($con, "SELECT SUM(amount) as total_expense, date FROM budget WHERE user_id = '$userid' AND category='expense' GROUP BY date ORDER BY date");
while ($expense_row = mysqli_fetch_array($expense_data)) {
    echo 'expenses[dates.indexOf("' . $expense_row['date'] . '")] = ' . $expense_row['total_expense'] . ';';
}
?>

// Populate the incomes array
<?php 
$income_data = mysqli_query($con, "SELECT SUM(amount) as total_income, date FROM budget WHERE user_id = '$userid' AND category='income' GROUP BY date ORDER BY date");
while ($income_row = mysqli_fetch_array($income_data)) {
    echo 'incomes[dates.indexOf("' . $income_row['date'] . '")] = ' . $income_row['total_income'] . ';';
}
?>

// Create the chart
var combinedLineChart = new Chart(ctxCombinedLine, {
    type: 'line',
    data: {
        labels: dates,
        datasets: [
            {
                label: 'Expenses',
                data: expenses,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.2)',
                fill: false,
                borderWidth: 2
            },
            {
                label: 'Incomes',
                data: incomes,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                fill: false,
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});
</script>
</body>
</html>
