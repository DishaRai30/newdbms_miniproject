<?php
require '../config/config.php';
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usn'])) {
    header("Location: login.php");
    exit();
}

$usn = $_SESSION['usn'];

try {
    $query = "SELECT e.*, 
              COALESCE(COUNT(r.register_id), 0) as registration_count,
              et.type_name,
              et.priority,
              v.venue_name
              FROM event e 
              LEFT JOIN register r ON e.eventid = r.event_id
              LEFT JOIN event_type et ON e.event_type_id = et.type_id
              LEFT JOIN venue v ON e.venue_id = v.venue_id
              GROUP BY e.eventid";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    exit();
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .events {
            display: flex;
            overflow: hidden;
            padding: 80px 20px 20px;
            /* Added top padding to avoid overlap with navbar */
            height: calc(100vh - 120px);
            /* Adjust to make cards full page */
        }

        .event-card {
            flex: 0 0 calc(33.33% - 20px);
            margin-right: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-image {
            width: 100%;
            height: 75%;
            /* Adjusted to 3:1 ratio */
            object-fit: cover;
        }

        .event-content {
            padding: 15px;
            height: 25%;
            /* Adjusted to 3:1 ratio */
        }

        .event-title {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .event-description {
            font-size: 0.9em;
            color: #555;
        }

        .view-details {
            background: #007BFF;
            color: #fff;
            border: none;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            font-size: 1em;
            margin-top: auto;
            /* Push to bottom of card */
            border-radius: 4px;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        .view-details:hover {
            background: #0056b3;
        }

        .add-event-card {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf7e0;
            border: 2px dashed #4caf50;
            text-align: center;
            cursor: pointer;
            font-size: 1.5em;
            color: #4caf50;
        }

        .add-event-card:hover {
            background: #d4f0c8;
        }

        .nav-buttons {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            width: 100%;
            pointer-events: none;
        }

        .nav-buttons button {
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: none;
            padding: 15px;
            cursor: pointer;
            border-radius: 50%;
            pointer-events: auto;
        }

        .nav-buttons button:hover {
            background: rgba(0, 0, 0, 0.7);
        }

        .nav-buttons button[aria-label="Previous"] {
            left: 10px;
            position: fixed;
        }

        .nav-buttons button[aria-label="Next"] {
            right: 10px;
            position: fixed;
        }

        event-stats {
            display: flex;
            justify-content: space-between;
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 0.9em;
        }

        .registration-count {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            background: #e3f2fd;
            border-radius: 15px;
            font-size: 0.85em;
            color: #1976d2;
        }

        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            background: #fff3e0;
            border-radius: 15px;
            font-size: 0.85em;
            color: #f57c00;
        }

        .venue-info {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            background: #e8f5e9;
            border-radius: 15px;
            font-size: 0.85em;
            color: #388e3c;
        }
    </style>
</head>

<body>
    <div class="events">
        <?php if ($_SESSION['type'] === 'organizer'): ?>
            <div class="event-card add-event-card" onclick="window.location.href='add_event.php';">
                <div class="event-title">+ Add New Event</div>
            </div>
        <?php endif; ?>

        <?php foreach ($events as $event): ?>
            <div class="event-card">
                <img class="event-image" src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="Event Image">
                <div class="event-content">
                    <div class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></div>
                    <div class="event-description">
                        <?php echo htmlspecialchars(substr($event['event_description'], 0, 100)); ?>...
                    </div>

                    <?php if ($_SESSION['type'] === 'organizer'): ?>
                        <div class="event-stats">
                            <div class="registration-count">
                                <span>👥</span>
                                <?php echo $event['registration_count']; ?>/<?php echo $event['event_max_entries']; ?> registered
                            </div>
                            <div class="priority-badge">
                                <span>🎯</span>
                                Priority: <?php echo htmlspecialchars($event['priority']); ?>
                            </div>
                        </div>
                        <div class="event-stats">
                            <div class="venue-info">
                                <span>📍</span>
                                <?php echo htmlspecialchars($event['venue_name']); ?>
                            </div>
                            <div class="venue-info">
                                <span>📅</span>
                                <?php echo htmlspecialchars($event['event_date']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button class="view-details" onclick="viewDetails(<?php echo $event['eventid']; ?>)">View Details</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="nav-buttons">
        <button onclick="scrollEvents(-1)" aria-label="Previous">&#8249;</button>
        <button onclick="scrollEvents(1)" aria-label="Next">&#8250;</button>
    </div>

    <script>
        let scrollPosition = 0;
        const eventsContainer = document.querySelector('.events');
        const eventCards = document.querySelectorAll('.event-card');
        const cardsPerRow = 3;

        function scrollEvents(direction) {
            const cardWidth = eventCards[0].offsetWidth + 20; // Include margin
            const maxScroll = Math.max(0, eventCards.length - cardsPerRow) * cardWidth;

            scrollPosition = Math.max(0, Math.min(scrollPosition + direction * cardWidth * cardsPerRow, maxScroll));
            eventsContainer.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        }

        function viewDetails(eventId) {
            window.location.href = `event_details.php?eventid=${eventId}`;
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight') {
                scrollEvents(1);
            } else if (event.key === 'ArrowLeft') {
                scrollEvents(-1);
            }
        });
    </script>
</body>

</html>