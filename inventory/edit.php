<?php

/**
 * Edit the quantity of the inventory record identified by GET id.
 *
 * POST updates only the quantity; the store and product stay fixed.
 */

require_once "../config/database.php";

$message = "";

if (!isset($_GET["id"])) {
    die("Inventory ID not provided.");
}

$inventoryId = intval($_GET["id"]);


// Join display names while selecting the inventory record by its bound ID.
$stmt = $conn->prepare(
    "SELECT
        inventory.inventory_id,
        inventory.quantity,
        stores.store_name,
        products.product_name
     FROM inventory

     JOIN stores
        ON inventory.store_id = stores.store_id

     JOIN products
        ON inventory.product_id = products.product_id

     WHERE inventory.inventory_id = ?"
);

$stmt->bind_param(
    "i",
    $inventoryId
);

$stmt->execute();

$result = $stmt->get_result();

$inventory = $result->fetch_assoc();

$stmt->close();


if (!$inventory) {
    die("Inventory record not found.");
}


// Accept a replacement stock count for the selected inventory record.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $quantity = intval($_POST["quantity"]);

    // Zero stock is allowed, but negative stock is rejected.
    if ($quantity < 0) {

        $message = "Quantity cannot be negative.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE inventory
             SET quantity = ?
             WHERE inventory_id = ?"
        );

        $stmt->bind_param(
            "ii",
            $quantity,
            $inventoryId
        );

        if ($stmt->execute()) {

            $message = "Inventory updated successfully!";

            // Keep the form synchronized with the successfully saved quantity.
            $inventory["quantity"] = $quantity;

        } else {

            $message = "Error updating inventory.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Update Inventory</title>

</head>

<body>

<h1>Update Inventory</h1>


<?php

// Escape feedback before inserting it into the HTML response.
if (!empty($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}

?>


<p>

    <strong>Store:</strong>

    <?php
    echo htmlspecialchars(
        $inventory["store_name"]
    );
    ?>

</p>


<p>

    <strong>Product:</strong>

    <?php
    echo htmlspecialchars(
        $inventory["product_name"]
    );
    ?>

</p>


<form method="POST">

    <label>
        Quantity:
    </label>

    <br>

    <input
        type="number"
        name="quantity"
        min="0"
        value="<?php echo $inventory["quantity"]; ?>"
        required
    >

    <br><br>

    <button type="submit">
        Update Inventory
    </button>

</form>


<br>

<a href="view.php">
    Back to Inventory
</a>

</body>

</html>