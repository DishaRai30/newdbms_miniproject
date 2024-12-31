<div class="navbar">
    <div class="title">SCEM EVENT MANAGEMENT SYSTEM</div>
    <div class="dropdown">
        <button><?php echo htmlspecialchars($usn); ?></button>
        <div class="dropdown-content">
            <a href="profile.php">Profile</a>
            <!-- <?php if ($_SESSION['type'] !== 'organizer'): ?>
                <a href="myevent.php">My Events</a>
            <?php endif; ?> -->
            <a href="logout.php">Logout</a>
        </div>
    </div>
</div>

<style>
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #333;
        color: #fff;
        padding: 10px 20px;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    .navbar .title {
        font-size: 1.5em;
    }

    .navbar .dropdown {
        position: relative;
        margin-right: 50px;
    }

    .navbar .dropdown button {
        background: #444;
        color: #fff;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        border-radius: 4px;
        width: 150px;
    }

    .navbar .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background: #fff;
        color: #333;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        overflow: hidden;
        z-index: 10;
        width: 100%;
        min-width: 150px;
        box-sizing: border-box;
    }

    .navbar .dropdown-content a {
        display: block;
        padding: 10px;
        color: #333;
        text-decoration: none;
    }

    .navbar .dropdown-content a:hover {
        background: #f0f0f0;
    }

    .navbar .dropdown:hover .dropdown-content {
        display: block;
    }

    body {
        padding-top: 60px;
    }
</style>
