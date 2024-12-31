<?php
require '../config/config.php';
if (!isset($_SESSION))
    session_start();
if (!isset($_SESSION['usn']) || $_SESSION['type'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$usn = $_SESSION['usn'];

// Fetch venues for dropdown
try {
    $stmt = $conn->prepare("SELECT * FROM venue");
    $stmt->execute();
    $venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error loading venues: " . addslashes($e->getMessage()) . "');</script>";
    $venues = [];
}


// Fetch event types for dropdown
try {
    $stmt = $conn->prepare("SELECT * FROM event_type ORDER BY priority DESC");
    $stmt->execute();
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error loading event types: " . addslashes($e->getMessage()) . "');</script>";
    $event_types = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Get priority of new event
        $stmt = $conn->prepare("SELECT priority FROM event_type WHERE type_id = ?");
        $stmt->execute([$_POST['event_type_id']]);
        $new_priority = $stmt->fetchColumn();

        // Check for existing events at the same time and venue
        $stmt = $conn->prepare("
            SELECT e.eventid, e.event_name, et.priority 
            FROM event e
            JOIN event_type et ON e.event_type_id = et.type_id
            WHERE e.event_date = ? 
            AND e.event_time = ?
            AND e.venue_id = ?
        ");
        $stmt->execute([
            $_POST['event_date'],
            $_POST['event_time'],
            $_POST['venue_id']
        ]);
        $existing_event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_event) {
            // If existing event has higher or equal priority, prevent new insertion
            if ($existing_event['priority'] <= $new_priority) {
                throw new Exception("Cannot schedule event: Conflict with existing event of same or higher priority");
            } else {
                // If new event has higher priority, delete the existing lower priority event
                $stmt = $conn->prepare("DELETE FROM event WHERE eventid = ?");
                $stmt->execute([$existing_event['eventid']]);
            }
        }

        // Insert the new event
        $stmt = $conn->prepare("
            INSERT INTO event (
                event_name, event_description, event_location, 
                event_date, event_time, event_resource_person, 
                event_image, event_max_entries, venue_id, event_type_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['event_name'],
            $_POST['event_description'],
            $_POST['event_location'],
            $_POST['event_date'],
            $_POST['event_time'],
            $_POST['event_resource_person'],
            $_POST['event_image'],
            $_POST['event_max_entries'],
            $_POST['venue_id'],
            $_POST['event_type_id']
        ]);

        $conn->commit();
        echo "<script>alert('Event added successfully!'); window.location.href='dashboard.php';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
            padding-top: 60px;
            /* Ensures content does not overlap navbar */
        }

        form {
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }

        label {
            display: block;
            margin: 5px 0 3px;
            font-weight: bold;
        }

        select,
        input,
        textarea,
        button {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background: #6a11cb;
            color: #fff;
            border: none;
            cursor: pointer;
            padding: 10px;
            font-size: 1rem;
        }

        button:hover {
            background: #2575fc;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
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
    </style>
</head>

<body>

    <form method="POST" action="">
        <h2>Add Event</h2>
        <label for="event_name">Event Name:</label>
        <input type="text" id="event_name" name="event_name" required>

        <label for="event_description">Event Description:</label>
        <textarea id="event_description" name="event_description" rows="3" required></textarea>

        <label for="event_location">Location:</label>
        <input type="text" id="event_location" name="event_location" required>

        <label for="event_date">Date:</label>
        <input type="date" id="event_date" name="event_date" required>

        <label for="event_time">Time:</label>
        <input type="time" id="event_time" name="event_time" required>

        <label for="event_resource_person">Resource Person:</label>
        <input type="text" id="event_resource_person" name="event_resource_person" required>

        <label for="event_image">Image URL:</label>
        <input type="text" id="event_image" name="event_image" required>

        <label for="event_max_entries">Max Entries:</label>
        <input type="number" id="event_max_entries" name="event_max_entries" required>

        <label for="venue_id">Venue:</label>
        <select id="venue_id" name="venue_id" required>
            <option value="">Select Venue</option>
            <?php foreach ($venues as $venue): ?>
                <option value="<?= htmlspecialchars($venue['venue_id']) ?>">
                    <?= htmlspecialchars($venue['venue_name']) ?>
                    (Capacity: <?= htmlspecialchars($venue['venue_capacity']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="event_type_id">Event Type:</label>
        <select id="event_type_id" name="event_type_id" required>
            <option value="">Select Event Type</option>
            <?php foreach ($event_types as $type): ?>
                <option value="<?= htmlspecialchars($type['type_id']) ?>">
                    <?= htmlspecialchars($type['type_name']) ?>
                    (Priority: <?= htmlspecialchars($type['priority']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Add Event</button>
        <a href="dashboard.php" class="back-button">&larr; Back to Dashboard</a>
    </form>
</body>

</html>