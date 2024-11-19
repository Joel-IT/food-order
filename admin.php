<?php
session_start();

$host = 'localhost';
$db = 'restaurant';
$user = 'root';
$pass = '';

// Verbindung zur Datenbank
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

// Admin-Login (einfaches Passwort)
$admin_password = "admin";
if (!isset($_SESSION['logged_in']) && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['logged_in'] = true;
    } else {
        echo "Falsches Passwort!";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Bestellung löschen
if (isset($_POST['delete_order'])) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = :id");
    $stmt->execute(['id' => $_POST['order_id']]);
}

// Status ändern
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $_POST['status'], 'id' => $_POST['order_id']]);
}

// Bestellungen abrufen
$orders = $pdo->query("SELECT orders.*, menu.title FROM orders JOIN menu ON orders.menu_id = menu.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bestellungen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .order-item {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        form {
            display: inline-block;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            margin: 4px;
            cursor: pointer;
            border: none;
        }
        .btn-danger {
            background-color: red;
        }
    </style>
</head>
<body>
<?php if (!isset($_SESSION['logged_in'])): ?>
    <h1>Admin-Login</h1>
    <form method="post">
        <label for="password">Passwort:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Einloggen</button>
    </form>
<?php else: ?>
    <h1>Bestellungen</h1>
    <a href="admin.php?logout=true">Logout</a>
    <?php foreach ($orders as $order): ?>
        <div class="order-item">
            <p><strong>Kunde:</strong> <?= htmlspecialchars($order['first_name']) ?> <?= htmlspecialchars($order['last_name']) ?></p>
            <p><strong>Tischnummer:</strong> <?= $order['table_number'] ?></p>
            <p><strong>Menü:</strong> <?= htmlspecialchars($order['title']) ?> (x<?= $order['quantity'] ?>)</p>
            <p><strong>Status:</strong> <?= $order['status'] ?></p>

            <form method="post">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <select name="status">
                    <option value="In Bearbeitung" <?= $order['status'] === 'In Bearbeitung' ? 'selected' : '' ?>>In Bearbeitung</option>
                    <option value="Zubereitet" <?= $order['status'] === 'Zubereitet' ? 'selected' : '' ?>>Zubereitet</option>
                    <option value="Ausgeliefert" <?= $order['status'] === 'Ausgeliefert' ? 'selected' : '' ?>>Ausgeliefert</option>
                </select>
                <button type="submit" name="update_status" class="btn">Status ändern</button>
            </form>

            <form method="post">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <button type="submit" name="delete_order" class="btn btn-danger">Löschen</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
