<?php
require '../config/config.php';
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usn'])) {
    header("Location: login.php");
    exit();
}

$usn = $_SESSION['usn'];

try {
    $stmt = $conn->prepare("SELECT * FROM user WHERE usn = ?");
    $stmt->execute([$usn]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>alert('User not found'); window.location.href='dashboard.php';</script>";
        exit();
    }
} catch (PDOException $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save'])) {
        $name = $_POST['name'] ?? $user['name'];
        $email = $_POST['email'] ?? $user['email'];
        $phone = $_POST['phone'] ?? $user['phone'];
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!empty($password)) {
            if ($password !== $confirm_password) {
                echo "<script>alert('Passwords do not match');</script>";
            } elseif (password_verify($password, $user['password'])) {
                echo "<script>alert('New password cannot be the same as the old password');</script>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $user['password'] = $hashed_password;
            }
        }

        try {
            $stmt = $conn->prepare("UPDATE user SET name = ?, email = ?, phone = ?, password = ? WHERE usn = ?");
            $stmt->execute([$name, $email, $phone, $user['password'], $usn]);

            echo "<script>alert('Profile updated successfully'); window.location.href='profile.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }

    if (isset($_POST['delete'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM user WHERE usn = ?");
            $stmt->execute([$usn]);

            session_destroy();
            echo "<script>alert('Account deleted successfully'); window.location.href='login.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
        }
        .profile-container {
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
        }
        label {
            font-weight: bold;
            margin: 10px 0 5px;
            display: block;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .readonly {
            background-color: #f0f0f0;
            color: #555;
            cursor: not-allowed;
        }
        .readonly::-webkit-input-placeholder {
            color: #aaa;
        }
        button {
            width: 100%;
            background: #6a11cb;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #2575fc;
        }
        .back-button {
            display: block;
            /* Ensures it behaves like a block-level element */
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            background: #444;
            color: #fff;
            padding: 10px;
            /* Matches the padding of the Add Event button */
            border-radius: 5px;
            font-size: 1rem;
            transition: background 0.2s;
            width: 100%;
            /* Ensures it matches the full width of the Add Event button */
            box-sizing: border-box;
            /* Ensures padding doesn't affect the width */
        }

        .back-button:hover {
            background: #666;
        }
        .delete-button {
            background: #ff4d4d;
            color: #fff;
        }
        .delete-button:hover {
            background: #e60000;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Profile</h2>
        <form method="POST">
            <label>USN:</label>
            <input type="text" value="<?= htmlspecialchars($usn ?? '') ?>" readonly class="readonly">

            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">

            <label>New Password:</label>
            <input type="password" name="password">

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password">

            <button type="submit" name="save">Save</button>
            <button type="submit" name="delete" class="delete-button">Delete Account</button>
            <a href="dashboard.php" class="back-button">&larr; Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
