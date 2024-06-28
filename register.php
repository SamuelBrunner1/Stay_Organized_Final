<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validate password match
    if ($password != $password_confirm) {
        $message = "Passwörter stimmen nicht überein.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT Username FROM user WHERE Username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Diese E-Mail-Adresse ist bereits registriert.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO user (Username, Password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hashed_password);

            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("Location: login.php");
                exit();
            } else {
                $message = "Fehler: " . $stmt->error;
            }
        }

        // Close the statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const message = "<?php echo $message; ?>";
            if (message) {
                alert(message);
                window.location.href = "register.php"; // Refresh the registration page
            }
        });

        function validateForm() {
            const password = document.getElementById("password").value;
            const passwordConfirm = document.getElementById("password_confirm").value;

            if (password !== passwordConfirm) {
                alert("Passwörter stimmen nicht überein.");
                return false;
            }
            return true;
        }
    </script>
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
        <h1>Registrierungsformular</h1>
        <form action="register.php" method="post" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="email">E-Mail-Adresse:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Passwort bestätigen:</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
            </div>
            <input type="submit" class="btn btn-primary" value="Registrieren">
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
