<?php
// signup.php
require '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #ff7eb3 0%, #ff758c 100%);
            color: #fff;
        }
        form {
            background: #ffffff;
            color: #333;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
        }
        h2 {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
        }
        label {
            margin: 3px 0;
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 8px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            background: #ff7eb3;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #ff758c;
        }
        p {
            text-align: center;
            margin-top: 5px;
        }
        a {
            color: #ff7eb3;
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
        <h2>Signup</h2>
        <label for="type">User Type:</label>
        <select id="type" name="type" onChange="updateLabel()">
            <option value="student" selected>Student</option>
            <option value="organizer">Organizer</option>
        </select>
        <label for="usn" id="usnLabel">USN</label>
        <input type="text" id="usn" name="usn" required>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <button type="submit" name="signup">Signup</button>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>

    <?php
    if (isset($_POST['signup'])) {
        $type = $_POST['type'];
        $usn = $_POST['usn'];
        $name = $_POST['name'];
        $dob = $_POST['dob'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $usn = strtolower($usn);

        if ($password !== $confirm_password) {
            echo "<script>showError('Passwords do not match.');</script>";
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO user (USN, password, name, date_of_birth, email, phone, type, event_register_count) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
                $stmt->execute([$usn, $hashed_password, $name, $dob, $email, $phone, $type]);
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                echo "<script>showError('Error: " . addslashes($e->getMessage()) . "');</script>";
            }
        }
    }
    ?>
</body>
</html>
