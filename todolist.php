<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<script type='text/javascript'>alert('Error: User not logged in'); window.location.href='login.html';</script>";
    exit;
}

$servername = "127.0.0.1";  // Localhost IP address
$username = "root";  // MySQL username
$password = "";  // MySQL password (empty in this case)
$dbname = "todolistesnnl";  // Your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch and filter tasks from the database
$task_type_filter = $_GET['task_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$sql = "SELECT task_id, task_type, task_name, task_date, completed FROM tasks WHERE user_id = " . $_SESSION['user_id'];

if ($task_type_filter) {
    $sql .= " AND task_type = '" . $conn->real_escape_string($task_type_filter) . "'";
}

if ($date_from) {
    $sql .= " AND task_date >= '" . $conn->real_escape_string($date_from) . "'";
}

if ($date_to) {
    $sql .= " AND task_date <= '" . $conn->real_escape_string($date_to) . "'";
}

$sql .= " ORDER BY task_date ASC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do Liste</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <style>
        .custom-heading {
            font-size: 2.2rem; /* Slightly smaller size */
            margin-top: 2rem; /* Margin above the heading */
            margin-bottom: 10rem; /* Margin beneath the heading */
            text-align: center; /* Center the heading */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="index.php">StayOrganized</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="create_task.php">Aufgabe erstellen</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="todolist.php">Aufgaben anzeigen</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="calendar.php">Kalender</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="imprint.php">Impressum</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="help.php">Hilfe</a>
            </li>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.html">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="registration.php">Registrieren</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="container">
    <h1 class="custom-heading">To-Do Liste</h1>
    <form method="get" class="form-inline mb-3">
        <label for="task_type" class="mr-2">Aufgabentyp:</label>
        <select id="task_type" name="task_type" class="form-control mr-3">
            <option value="">Alle</option>
            <option value="business" <?php if ($task_type_filter === 'business') echo 'selected'; ?>>Geschäftlich</option>
            <option value="private" <?php if ($task_type_filter === 'private') echo 'selected'; ?>>Privat</option>
        </select>

        <label for="date_from" class="mr-2">Von:</label>
        <input type="date" id="date_from" name="date_from" class="form-control mr-3" value="<?php echo htmlspecialchars($date_from); ?>">

        <label for="date_to" class="mr-2">Bis:</label>
        <input type="date" id="date_to" name="date_to" class="form-control mr-3" value="<?php echo htmlspecialchars($date_to); ?>">

        <button type="submit" class="btn btn-primary">Filter anwenden</button>
    </form>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Typ</th>
                <th>Aufgabenname</th>
                <th>Datum</th>
                <th>Status</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['task_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['task_date']); ?></td>
                        <td><?php echo $row['completed'] ? 'Erledigt' : 'Offen'; ?></td>
                        <td>
                            <form action="update_task.php" method="post" style="display:inline;">
                                <input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
                                <input type="hidden" name="completed" value="<?php echo $row['completed'] ? 0 : 1; ?>">
                                <button type="submit" class="btn btn-sm btn-success"><?php echo $row['completed'] ? 'Mark as Open' : 'Mark as Completed'; ?></button>
                            </form>
                            <form action="delete_task.php" method="post" style="display:inline;">
                                <input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Keine Aufgaben gefunden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
