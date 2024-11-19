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
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; // Wichtig: Script nach Weiterleitung beenden
}

// Bestellung hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'], $_POST['quantity'], $_POST['confirm_order'])) {
    if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
        $menu_id = $_POST['menu_id'];
        $quantity = $_POST['quantity'];

        // Bestellung in die Datenbank eintragen
        $stmt = $pdo->prepare("INSERT INTO orders (first_name, last_name, menu_id, quantity, status) VALUES (:first_name, :last_name, :menu_id, :quantity, 'In Bearbeitung')");
        $stmt->execute([
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'menu_id' => $menu_id,
            'quantity' => $quantity
        ]);

        // Setze eine Session-Variable für die Bestellbestätigung
        $_SESSION['order_success'] = 'Bestellung erfolgreich!';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit; // Wichtig: Script nach Weiterleitung beenden
    }
}

// Überprüfen, ob Benutzername und Nachname gesetzt sind
$menu = [];
$orders = [];
$total = 0;

if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
    // Bestellungen anzeigen
    $orders_stmt = $pdo->prepare("SELECT orders.*, menu.title, menu.price FROM orders JOIN menu ON orders.menu_id = menu.id WHERE orders.first_name = :first_name AND orders.last_name = :last_name");
    $orders_stmt->execute([
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name']
    ]);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gesamtpreis berechnen
    foreach ($orders as $order) {
        $total += $order['price'] * $order['quantity'];
    }

    // Menü abrufen
    $menu = $pdo->query("SELECT * FROM menu")->fetchAll(PDO::FETCH_ASSOC);
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f1f1; /* Hellerer Hintergrund für bessere Lesbarkeit */
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px; /* Mehr Abstand für mehr Luft im Layout */
        }

        /* Überschrift */
        h1, h2 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
        }

        h1 {
            font-size: 3em;
            margin-top: 30px;
        }

        /* Eingabeformulare */
        form input, form button {
            padding: 12px 20px;
            margin: 12px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 100%;
            font-size: 1.1em;
        }

        form button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        form button:hover {
            background-color: #45a049;
            transform: scale(1.05); /* Schaltflächen werden leicht größer bei Hover */
        }

        form input:focus {
            border-color: #4CAF50;
        }

        /* Bestellbestätigung */
        .order-success {
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 1.3em;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Menü-Item Stil */
        .menu-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .menu-item {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
            text-align: center;
        }

        .menu-item:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .menu-item img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .menu-item strong {
            font-size: 1.6em;
            color: #333;
            margin-bottom: 10px;
        }

        .menu-item span {
            font-size: 1.3em;
            color: #666;
        }

        /* Modale Fenster */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 70%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }

        /* Gesamtpreis oben rechts */
        .total-price {
            position: absolute;
            top: 30px;
            right: 30px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.4em;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .order-list {
            margin-top: 40px;
            position: relative; /* Container wird relativ positioniert */
            padding-top: 50px; /* Platz schaffen für den fixierten Gesamtpreis */
        }

        .order-item {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .order-item p {
            margin: 8px 0;
        }

        .order-item strong {
            color: #333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .total-price {
                font-size: 1.2em;
                padding: 10px;
                right: 10px;
                top: 20px;
            }

            .order-list {
                padding-top: 30px;
            }

            h1, h2 {
                font-size: 2em;
            }

            .menu-list {
                grid-template-columns: 1fr;
            }

            .menu-item {
                padding: 15px;
            }

            .modal-content {
                width: 90%;
            }

            form input, form button {
                font-size: 1em;
            }
        }

    </style>
    <script>
        function openModal(event, menuId, quantity) {
            event.preventDefault();

            document.getElementById("menu_id_modal").value = menuId;
            document.getElementById("quantity_modal").value = quantity;

            document.getElementById("confirmationModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("confirmationModal").style.display = "none";
        }

        function confirmOrder() {
            document.getElementById("orderForm").submit();
        }
    </script>
</head>
<body>

<?php if (!isset($_SESSION['first_name']) || !isset($_SESSION['last_name'])): ?>
    <h2>Geben Sie Ihre Daten ein</h2>
    <form method="post">
        <input type="text" name="first_name" placeholder="Vorname" required>
        <input type="text" name="last_name" placeholder="Nachname" required>
        <button type="submit">Bestätigen</button>
    </form>
<?php else: ?>
    <h1>Bestellen</h1>
    <p>Willkommen, <?= htmlspecialchars($_SESSION['first_name']) ?> <?= htmlspecialchars($_SESSION['last_name']) ?></p>

    <?php if (isset($_SESSION['order_success'])): ?>
        <div class="order-success">
            <p><?= htmlspecialchars($_SESSION['order_success']) ?></p>
        </div>
        <?php unset($_SESSION['order_success']); ?>
    <?php endif; ?>

    <h2>Menü</h2>
    <div class="menu-list">
        <?php foreach ($menu as $item): ?>
            <div class="menu-item">
                <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                <span><?= htmlspecialchars($item['price']) ?>€</span><br>
                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                <form method="post" onsubmit="openModal(event, <?= htmlspecialchars($item['id']) ?>, document.getElementById('quantity_<?= htmlspecialchars($item['id']) ?>').value)">
                    <input type="hidden" name="menu_id" value="<?= htmlspecialchars($item['id']) ?>">
                    <input type="number" id="quantity_<?= htmlspecialchars($item['id']) ?>" name="quantity" min="1" value="1" required>
                    <button type="submit">Bestellen</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Bestellung bestätigen</h2>
            <form method="post" id="orderForm">
                <input type="hidden" name="confirm_order" value="1">
                <input type="hidden" name="menu_id" id="menu_id_modal">
                <input type="hidden" name="quantity" id="quantity_modal">
                <button type="button" onclick="confirmOrder()">Ja, Bestellung abschicken</button>
                <button type="button" onclick="closeModal()">Abbrechen</button>
            </form>
        </div>
    </div>

    <h2>Meine Bestellungen</h2>
    <div class="order-list">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                    <p><strong>Gericht:</strong> <?= htmlspecialchars($order['title']) ?></p>
                    <p><strong>Menge:</strong> <?= htmlspecialchars($order['quantity']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                </div>
            <?php endforeach; ?>
            <div class="total-price">
                Gesamt: <?= htmlspecialchars($total) ?>€
            </div>
        <?php else: ?>
            <p>Noch keine Bestellungen!</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
