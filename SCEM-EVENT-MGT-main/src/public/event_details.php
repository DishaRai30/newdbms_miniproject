<?php
require '../config/config.php';

if (!isset($_SESSION))
    session_start();
if (!isset($_SESSION['usn'])) {
    header("Location: login.php");
    exit();
}

$usn = $_SESSION['usn'];
$type = $_SESSION['type'] ?? 'student';
$event_id = $_GET['eventid'] ?? null;

if (!$event_id) {
    echo "<script>alert('Invalid event ID'); window.location.href='dashboard.php';</script>";
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM event WHERE eventid = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo "<script>alert('Event not found'); window.location.href='dashboard.php';</script>";
        exit();
    }

    if ($type === 'student') {
        $stmt = $conn->prepare("SELECT * FROM register WHERE usn = ? AND event_id = ?");
        $stmt->execute([$usn, $event_id]);
        $is_registered = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'organizer') {
        if (isset($_POST['save'])) {
            try {
                $stmt = $conn->prepare("UPDATE event 
                    SET event_name = ?, event_description = ?, event_location = ?, event_date = ?, 
                        event_time = ?, event_resource_person = ?, event_image = ?, event_max_entries = ?
                    WHERE eventid = ?");
                $stmt->execute([
                    $_POST['event_name'],
                    $_POST['event_description'],
                    $_POST['event_location'],
                    $_POST['event_date'],
                    $_POST['event_time'],
                    $_POST['event_resource_person'],
                    $_POST['event_image'],
                    $_POST['event_max_entries'],
                    $event_id
                ]);

                echo "<script>alert('Event updated successfully'); window.location.href='event_details.php?eventid=$event_id';</script>";
            } catch (PDOException $e) {
                echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
            }
        }

        if (isset($_POST['delete'])) {
            try {
                $stmt = $conn->prepare("DELETE FROM event WHERE eventid = ?");
                $stmt->execute([$event_id]);

                echo "<script>alert('Event deleted successfully'); window.location.href='dashboard.php';</script>";
            } catch (PDOException $e) {
                echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
            }
        }
    }

    if ($type === 'student') {
        if (isset($_POST['register'])) {
            try {
                $stmt = $conn->prepare("INSERT INTO register (usn, event_id) VALUES (?, ?)");
                $stmt->execute([$usn, $event_id]);

                $stmt = $conn->prepare("UPDATE user SET event_register_count = event_register_count + 1 WHERE usn = ?");
                $stmt->execute([$usn]);

                echo "<script>alert('Registered successfully'); window.location.href='event_details.php?eventid=$event_id';</script>";
            } catch (PDOException $e) {
                echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
            }
        }

        if (isset($_POST['deregister'])) {
            try {
                $stmt = $conn->prepare("DELETE FROM register WHERE usn = ? AND event_id = ?");
                $stmt->execute([$usn, $event_id]);

                $stmt = $conn->prepare("UPDATE user SET event_register_count = event_register_count - 1 WHERE usn = ?");
                $stmt->execute([$usn]);

                echo "<script>alert('Deregistered successfully'); window.location.href='event_details.php?eventid=$event_id';</script>";
            } catch (PDOException $e) {
                echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
        }

        .event-details {
            display: flex;
            flex-direction: column;
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 800px;
            box-sizing: border-box;
            margin-top: 20px;
        }

        .event-details-student {
            display: flex;
            flex-direction: row;
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 800px;
            box-sizing: border-box;
            margin-top: 20px;
        }
        
        .image-section-student {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-right: 20px;
            border-right: 1px solid #ccc;
        }

        .image-section-student img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 10px;
        }

        .details-section-student {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding-left: 20px;
        }

        label {
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .details {
            margin-bottom: 15px;
        }


        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            resize: none;
        }

        button {
            background: #6a11cb;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            margin-top: 10px;
            font-size: 1rem;
        }

        button:hover {
            background: #2575fc;
        }

        button[name="delete"] {
            background: #dc3545;
        }

        button[name="delete"]:hover {
            background: #c82333;
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
    <?php include 'navbar.php'; ?>

    <div class="event-details">
        <?php if ($type === 'student'): ?>
            <div class="image-section-student">
                <img src="<?= htmlspecialchars($event['event_image']) ?>" alt="Event Image">
            </div>
            <div class="details-section-student">
                <h2><?= htmlspecialchars($event['event_name']) ?></h2>
                <div class="details">
                    <label>Description:</label>
                    <p><?= htmlspecialchars($event['event_description']) ?></p>
                </div>
                <div class="details">
                    <label>Location:</label>
                    <p><?= htmlspecialchars($event['event_location']) ?></p>
                </div>
                <div class="details">
                    <label>Date:</label>
                    <p><?= htmlspecialchars($event['event_date']) ?></p>
                </div>
                <div class="details">
                    <label>Time:</label>
                    <p><?= htmlspecialchars($event['event_time']) ?></p>
                </div>
                <div class="details">
                    <label>Resource Person:</label>
                    <p><?= htmlspecialchars($event['event_resource_person']) ?></p>
                </div>
                <form method="POST">
                    <?php if (!$is_registered): ?>
                        <button type="submit" name="register">Register</button>
                    <?php else: ?>
                        <button type="submit" name="deregister">Deregister</button>
                    <?php endif; ?>
                </form>
                <a href="dashboard.php" class="back-button">&larr; Back to Dashboard</a>
            </div>
        <?php elseif ($type === 'organizer'): ?>
            <form method="POST">
                <label for="event_name">Event Name:</label>
                <input type="text" id="event_name" name="event_name" value="<?= htmlspecialchars($event['event_name']) ?>" required>

                <label for="event_description">Event Description:</label>
                <textarea id="event_description" name="event_description" rows="3" required><?= htmlspecialchars($event['event_description']) ?></textarea>

                <label for="event_location">Location:</label>
                <input type="text" id="event_location" name="event_location" value="<?= htmlspecialchars($event['event_location']) ?>" required>

                <label for="event_date">Date:</label>
                <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required>

                <label for="event_time">Time:</label>
                <input type="time" id="event_time" name="event_time" value="<?= htmlspecialchars($event['event_time']) ?>" required>

                <label for="event_resource_person">Resource Person:</label>
                <input type="text" id="event_resource_person" name="event_resource_person" value="<?= htmlspecialchars($event['event_resource_person']) ?>" required>

                <label for="event_image">Image URL:</label>
                <input type="text" id="event_image" name="event_image" value="<?= htmlspecialchars($event['event_image']) ?>" required>

                <label for="event_max_entries">Max Entries:</label>
                <input type="number" id="event_max_entries" name="event_max_entries" value="<?= htmlspecialchars($event['event_max_entries']) ?>" required>

                <button type="submit" name="save">Save</button>
                <button type="submit" name="delete">Delete</button>
                <a href="dashboard.php" class="back-button">&larr; Back to Dashboard</a>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
