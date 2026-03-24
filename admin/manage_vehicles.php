<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$result = $conn->query("SELECT * FROM vehicles ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
:root {
    --primary: #1f8f4c;
    --primary-light: #2da85d;
    --blue: #0d6efd;
    --green: #198754;
    --orange: #fd7e14;
    --red: #dc3545;
    --light: #f8fafc;
    --light-bg: #f0f4f8;
    --border: #e0e7ff;
    --text-dark: #1a202c;
    --text-muted: #6b7280;
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
    --radius: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', sans-serif;
}

body {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 100%);
    color: var(--text-dark);
    padding: 20px;
    min-height: 100vh;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 32px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
}

h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 24px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}

.btn {
    padding: 11px 18px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    display: inline-block;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.add { background: linear-gradient(135deg, var(--green), #20c997); box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25); }
.edit { background: linear-gradient(135deg, var(--blue), #0a58ca); box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25); }
.delete { background: linear-gradient(135deg, var(--red), #bd2130); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25); }
.available { background: linear-gradient(135deg, var(--green), #20c997); }
.unavailable { background: linear-gradient(135deg, #6c757d, #5a6268); }

table {
    width: 100%;
    border-collapse: collapse;
    overflow-x: auto;
}

th {
    background: linear-gradient(135deg, var(--light-bg), #e9ecef);
    padding: 14px 12px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    border-bottom: 2px solid var(--border);
}

td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    color: var(--text-muted);
    font-size: 13px;
}

tbody tr:hover {
    background: var(--light);
    transition: var(--transition);
}

img {
    width: 70px;
    height: 50px;
    border-radius: 6px;
    object-fit: cover;
    box-shadow: var(--shadow-sm);
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: white;
}

.status-badge.available { background: #d1fae5; color: #065f46; }
.status-badge.unavailable { background: #f3f4f6; color: #374151; }

@media (max-width: 768px) {
    .container {
        padding: 16px;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 16px;
    }

    th, td {
        padding: 10px 8px;
        font-size: 12px;
    }

    .btn {
        padding: 8px 12px;
        font-size: 11px;
    }

    img {
        width: 60px;
        height: 45px;
    }
}
    </style>
</head>
<body>

<div class="container">
    <div class="header-actions">
        <h2>🚗 Manage Vehicles</h2>
        <a href="add_vehicle.php" class="btn add">+ Add Vehicle</a>
    </div>

<table>
    <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Vehicle Name</th>
        <th>Type</th>
        <th>Seats</th>
        <th>Fuel</th>
        <th>Transmission</th>
        <th>Price</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

<?php while ($v = $result->fetch_assoc()): ?>
<tr>
    <td><?= $v['id']; ?></td>
    <td><img src="../<?= $v['image']; ?>"></td>
    <td><?= htmlspecialchars($v['vehicle_name']); ?></td>
    <td><?= $v['vehicle_type']; ?></td>
    <td><?= $v['seats']; ?></td>
    <td><?= $v['fuel_type']; ?></td>
    <td><?= $v['transmission']; ?></td>
    <td>Rs <?= $v['price']; ?></td>

    <td>
        <span class="status-badge <?= $v['status']; ?>">
            <?= ucfirst($v['status']); ?>
        </span>
    </td>

    <td style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <a href="edit_vehicle.php?id=<?= $v['id']; ?>" class="btn edit" title="Edit">✏ Edit</a>
        <a href="toggle_vehicle.php?id=<?= $v['id']; ?>" 
           class="btn <?= $v['status']; ?>"
           title="Toggle Status">
           <?= $v['status'] === 'available' ? '🔒 Unavailable' : '🔓 Available'; ?>
        </a>
        <a href="delete_vehicle.php?id=<?= $v['id']; ?>" 
           class="btn delete"
           onclick="return confirm('Delete vehicle?')"
           title="Delete">
           🗑 Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>
</div>
</body>
</html>
