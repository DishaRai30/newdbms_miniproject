<?php
// login.php
session_start(); // Start the session at the very beginning
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $usn = $_POST['usn'];
    $password = $_POST['password'];
    $usn = strtolower($usn);

    try {
        $stmt = $conn->prepare("SELECT * FROM user WHERE USN = ? AND type = ?");
        $stmt->execute([$usn, $type]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['usn'] = $user['USN'];
            $_SESSION['type'] = $user['type'];

            echo($_SESSION['type']);

            if ($_SESSION['type'] == 'admin') {
                # code...
                header("Location: admin.php");
                exit();
            }

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid USN, password, or user type.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        }

        form {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #444;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            background-color: #6a11cb;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #2575fc;
        }

        p {
            text-align: center;
        }

        a {
            color: #6a11cb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>

    <script>
        function showError(message) {
            alert(message);
        }

        function updateLabel() {
            let typeValue = document.getElementById("type").value;
            let usnField = document.getElementById("usnLabel");

            // console.log(typeValue);
            // console.log(usnField.textContent);

            usnField.textContent = typeValue === "organizer" ? "Organizer ID" : "USN";
        }
    </script>
</head>

<body>
    <form method="POST" action="">
        <h2>Login</h2>
        <label for="type">User Type:</label>
        <select id="type" name="type" onChange="updateLabel()">
            <option value="student" selected>Student</option>
            <option value="organizer">Organizer</option>
            <option value="admin">Admin</option>
        </select>
        <label for="usn" id="usnLabel">USN</label>
        <input type="text" id="usn" name="usn" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit" name="login">Login</button>
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
    </form>
</body>

</html>