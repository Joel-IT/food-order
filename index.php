<?php
session_start();  // Sitzungsstart, um die Benutzerdaten zu speichern

// Verbindung zur Datenbank
$host = 'localhost';
$db = 'restaurant';
$user = 'root';
$pass = '';

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

        echo "<script>alert('Bestellung erfolgreich!'); window.location='index.php';</script>";
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

        /* Bestellbestätigung Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .modal h2 {
            margin-top: 0;
        }

        .modal .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        .modal .btn-danger {
            background-color: red;
        }

        .modal .btn:hover, .modal .btn-danger:hover {
            opacity: 0.8;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            float: right;
            cursor: pointer;
        }

        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
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

        /* Styling für die Bestellübersicht */
        .order-list {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .order-item {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .order-item p {
            margin: 5px 0;
        }

        .order-item span {
            font-weight: bold;
        }

        /* Responsivität */
        @media (max-width: 768px) {
            .menu-list {
                grid-template-columns: 1fr 1fr;
            }

            .modal-content {
                width: 90%;
            }

            input[type="text"], input[type="number"] {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .menu-list {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 90%;
            }

            input[type="text"], input[type="number"] {
                width: 100%;
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
            <p><strong>Gesamtpreis: <?= number_format($total, 2, ',', '.') ?>€</strong></p>
        <?php else: ?>
            <p>Du hast noch keine Bestellungen aufgegeben.</p>
        <?php endif; ?>
    </div>

    <!-- Modal für Bestellbestätigung -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Bestellung bestätigen</h2>
            <p>Willst du diese Bestellung wirklich aufgeben?</p>
            <form id="confirmForm" method="post">
                <input type="hidden" name="menu_id" id="modalMenuId">
                <input type="hidden" name="quantity" id="modalQuantity">
                <button type="submit" name="confirm_order" class="btn">Bestätigen</button>
                <button type="button" class="btn btn-danger" onclick="closeModal()">Abbrechen</button>
            </form>
        </div>
    </div>

    <script>
        function showConfirmModal(event) {
            event.preventDefault();
            const menuId = event.target.querySelector('input[name="menu_id"]').value;
            const quantity = event.target.querySelector('input[name="quantity"]').value;

            document.getElementById('modalMenuId').value = menuId;
            document.getElementById('modalQuantity').value = quantity;
            document.getElementById('confirmModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('confirmModal').style.display = "none";
        }
    </script>
<?php endif; ?>

</body>
</html>
