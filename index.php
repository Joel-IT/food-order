<?php
session_start();  // Sitzungsstart, um die Benutzerdaten zu speichern

// Verbindung zur Datenbank
$host = 'localhost';
$db = 'restaurant';
$user = 'joel';
$pass = '1442';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

// Benutzerdaten speichern (Vorname, Nachname)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'], $_POST['last_name'])) {
    $_SESSION['first_name'] = $_POST['first_name'];
    $_SESSION['last_name'] = $_POST['last_name'];
}

// Überprüfen, ob Benutzername und Nachname gesetzt sind
if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
    // Bestellungen hinzufügen
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
        $menu_id = $_POST['menu_id'];
        $quantity = $_POST['quantity'];

        // Bestellung bestätigen und in die Datenbank eintragen
        $stmt = $pdo->prepare("INSERT INTO orders (first_name, last_name, menu_id, quantity, status) VALUES (:first_name, :last_name, :menu_id, :quantity, 'In Bearbeitung')");
        $stmt->execute([
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'menu_id' => $menu_id,
            'quantity' => $quantity
        ]);

        // Setze eine Session-Variable für die Bestellbestätigung
        $_SESSION['order_success'] = 'Bestellung erfolgreich!';
    }

    // Bestellungen anzeigen
    $orders = $pdo->prepare("SELECT orders.*, menu.title, menu.price FROM orders JOIN menu ON orders.menu_id = menu.id WHERE orders.first_name = :first_name AND orders.last_name = :last_name");
    $orders->execute([
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name']
    ]);
    $orders = $orders->fetchAll(PDO::FETCH_ASSOC);

    // Gesamtpreis berechnen
    $total = 0;
    foreach ($orders as $order) {
        $total += $order['price'] * $order['quantity'];
    }

    // Menü abrufen
    $menu = $pdo->query("SELECT * FROM menu")->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fehlermeldung, wenn Name und Nachname noch nicht gesetzt sind
    $menu = [];
    $orders = [];
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellen</title>
    <style>
        /* Allgemeine Einstellungen */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Container für die Seite */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        h1, h2 {
            font-size: 2em;
            color: #333;
            margin-bottom: 20px;
        }

        /* Bestellformular und Menüanzeige */
        form input, form button {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }

        form button:hover {
            background-color: #45a049;
        }

        form input[type="number"] {
            width: 80px;
            max-width: 100px;
        }

        input[type="text"], input[type="number"], button {
            font-size: 16px;
        }

        /* Menüliste */
        .menu-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .menu-item {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .menu-item:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .menu-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            object-fit: cover;
        }

        /* Bestellungsliste */
        .order-list {
            margin-top: 40px;
        }

        .order-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .order-item span {
            font-weight: bold;
        }

        .order-item p {
            margin: 5px 0;
        }

        /* Bestellbestätigung */
        .order-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 1.2em;
        }

        /* Eingabefelder und Buttons */
        input[type="text"], input[type="number"], input[type="submit"], button {
            border-radius: 5px;
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 16px;
        }

        input[type="text"], input[type="number"] {
            width: 300px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Responsivität */
        @media (max-width: 768px) {
            .menu-list {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .menu-list {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>
<body>

<?php if (!isset($_SESSION['first_name']) || !isset($_SESSION['last_name'])): ?>
    <h2>Geben Sie Ihre Daten ein</h2>
    <form method="post">
        <input type="text" name="first_name" placeholder="Vorname" required>
        <input type="text" name="last_name" placeholder="Nachname" required>
        <button type="submit" class="btn">Bestätigen</button>
    </form>
<?php else: ?>
    <h1>Bestellen</h1>
    <p>Willkommen, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?></p>

    <!-- Bestellbestätigung anzeigen -->
    <?php if (isset($_SESSION['order_success'])): ?>
        <div class="order-success">
            <p><?= $_SESSION['order_success']; ?></p>
        </div>
        <?php unset($_SESSION['order_success']); ?>
    <?php endif; ?>

    <!-- Menü anzeigen -->
    <h2>Menü</h2>
    <div class="menu-list">
        <?php foreach ($menu as $item): ?>
            <div class="menu-item">
                <div>
                    <strong><?= $item['title'] ?></strong><br>
                    <span><?= $item['price'] ?>€</span>
                </div>
                <img src="<?= $item['image_url'] ?>" alt="<?= $item['title'] ?>">
                <form method="post" onsubmit="return showConfirmModal(event)">
                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                    <input type="number" name="quantity" min="1" value="1" required>
                    <button type="submit" class="btn">Bestellen</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Meine Bestellungen anzeigen -->
    <h2>Meine Bestellungen</h2>
    <div class="order-list">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                    <p><strong>Gericht:</strong> <?= $order['title'] ?></p>
                    <p><strong>Menge:</strong> <?= $order['quantity'] ?></p>
                    <p><strong>Status:</strong> <?= $order['status'] ?></p>
                </div>
            <?php endforeach; ?>
            <h3>Gesamt: <?= $total ?>€</h3>
        <?php else: ?>
            <p>Noch keine Bestellungen!</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
