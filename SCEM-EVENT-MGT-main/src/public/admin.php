<?php
require '../config/config.php';
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['usn']) || $_SESSION['type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$usn = $_SESSION['usn'];

// Handle venue form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_venue'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO venue (venue_name, venue_capacity, venue_description, venue_location) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['venue_name'],
            $_POST['venue_capacity'],
            $_POST['venue_description'],
            $_POST['venue_location']
        ]);
        echo "<script>alert('Venue added successfully!');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle event type form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event_type'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO event_type (type_name, priority) VALUES (?, ?)");
        $stmt->execute([
            $_POST['type_name'],
            $_POST['priority']
        ]);
        echo "<script>alert('Event type added successfully!');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Fetch existing venues
try {
    $stmt = $conn->prepare("SELECT * FROM venue");
    $stmt->execute();
    $venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $venues = [];
}

// Fetch existing event types
try {
    $stmt = $conn->prepare("SELECT * FROM event_type");
    $stmt->execute();
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event_types = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
        }

        .admin-container {
            display: flex;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background: #6a11cb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #5a0cb9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <!-- Venue Management Section -->
        <div class="section">
            <h2>Manage Venues</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="venue_name">Venue Name:</label>
                    <input type="text" id="venue_name" name="venue_name" required>
                </div>
                <div class="form-group">
                    <label for="venue_capacity">Capacity:</label>
                    <input type="number" id="venue_capacity" name="venue_capacity" required>
                </div>
                <div class="form-group">
                    <label for="venue_description">Description:</label>
                    <input type="text" id="venue_description" name="venue_description" required>
                </div>
                <div class="form-group">
                    <label for="venue_location">Location:</label>
                    <input type="text" id="venue_location" name="venue_location" required>
                </div>
                <button type="submit" name="add_venue">Add Venue</button>
            </form>

            <table style="color: black;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Capacity</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($venues as $venue): ?>
                        <tr>
                            <td><?= htmlspecialchars($venue['venue_name']) ?></td>
                            <td><?= htmlspecialchars($venue['venue_capacity']) ?></td>
                            <td><?= htmlspecialchars($venue['venue_location']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Event Type Management Section -->
        <div class="section">
            <h2>Manage Event Types</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="type_name">Event Type Name:</label>
                    <input type="text" id="type_name" name="type_name" required>
                </div>
                <div class="form-group">
                    <label for="priority">Priority (1-10):</label>
                    <input type="number" id="priority" name="priority" min="1" max="10" required>
                </div>
                <button type="submit" name="add_event_type">Add Event Type</button>
            </form>

            <table style="color: black;">
                <thead>
                    <tr>
                        <th>Event Type</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($event_types as $type): ?>
                        <tr>
                            <td><?= htmlspecialchars($type['type_name']) ?></td>
                            <td><?= htmlspecialchars($type['priority']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>