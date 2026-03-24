<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$search = $_GET['search'] ?? '';

$sql = "SELECT id, fullname, username, email, status 
        FROM users 
        WHERE username LIKE ? OR email LIKE ?";

        $stmt = $conn->prepare($sql);
        $like = "%$search%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
?>
<html>
    <head>
        <title>Manage Users</title>
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
    margin-bottom: 28px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-form {
    margin-bottom: 28px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.search-form input {
    flex: 1;
    min-width: 250px;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    transition: var(--transition);
}

.search-form input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(31, 143, 76, 0.1);
}

.search-form button {
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 4px 12px rgba(31, 143, 76, 0.25);
}

.search-form button:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.users-table {
    width: 100%;
    border-collapse: collapse;
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
    white-space: nowrap;
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

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.badge.active {
    background: #d1fae5;
    color: #065f46;
}

.badge.blocked {
    background: #fee2e2;
    color: #991b1b;
}

.action-links {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

a.action-btn {
    padding: 8px 14px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    transition: var(--transition);
    display: inline-block;
    border: none;
    cursor: pointer;
}

a.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.view { background: linear-gradient(135deg, var(--blue), #0956ca); box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25); }
.toggle { background: linear-gradient(135deg, var(--orange), #e67e22); box-shadow: 0 4px 12px rgba(253, 126, 20, 0.25); }
.delete { background: linear-gradient(135deg, var(--red), #bd2130); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25); }

@media (max-width: 768px) {
    .container {
        padding: 16px;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 16px;
    }

    .search-form {
        flex-direction: column;
    }

    .search-form input {
        min-width: 100%;
    }

    th, td {
        padding: 10px 8px;
        font-size: 12px;
    }

    a.action-btn {
        padding: 6px 10px;
        font-size: 11px;
    }

    .action-links {
        gap: 4px;
    }
}
        </style>
    </head>
    <body>
        <div class="container">
            <h2>👥 Manage Users</h2>

            <!-- SEARCH -->
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="🔍 Search by username or email..."
                    value="<?= htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>

            <!-- USERS TABLE -->
            <table class="users-table">
                <thead>
                <tr>
                    <th>📋 ID</th>
                    <th>👤 Full Name</th>
                    <th>📝 Username</th>
                    <th>📧 Email</th>
                    <th>✅ Status</th>
                    <th>⚙️ Actions</th>
                </tr>
                </thead>
                <tbody>

                        <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id']; ?></td>
                            <td><?= htmlspecialchars($user['fullname']); ?></td>
                            <td><?= htmlspecialchars($user['username']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>

                            <td>
                                <span class="badge <?= $user['status']; ?>">
                                    <?= ucfirst($user['status']); ?>
                                </span>
                            </td>

                            <td>
                                <div class="action-links">
                                    <a href="user_details.php?id=<?= $user['id']; ?>" 
                                       class="action-btn view" 
                                       title="View Details">
                                       👁 View
                                    </a>
                                    <a href="toggle_user_status.php?id=<?= $user['id']; ?>"
                                       class="action-btn toggle"
                                       title="<?= $user['status'] === 'active' ? 'Block User' : 'Unblock User'; ?>">
                                       <?= $user['status'] === 'active' ? '🔒 Block' : '🔓 Unblock'; ?>
                                    </a>
                                    <a href="delete_user.php?id=<?= $user['id']; ?>"
                                       class="action-btn delete"
                                       onclick="return confirm('Are you sure you want to delete this user?');"
                                       title="Delete User">
                                       🗑 Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </body>
</html>
