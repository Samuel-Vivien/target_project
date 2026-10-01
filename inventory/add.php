<?php

/**
 * Display store and product choices and create an inventory record on POST.
 *
 * Each submission sets the initial quantity for a store/product pair.
 */

require_once "../config/database.php";

$message = "";


// Load alphabetized stores for the selection list.
$stores = $conn->query(
    "SELECT store_id, store_name
     FROM stores
     ORDER BY store_name"
);


// Load catalog products for the selection list.
$products = $conn->query(
    "SELECT product_id, product_name
     FROM products
     ORDER BY product_name"
);


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $storeId = intval($_POST["store_id"]);

    $productId = intval($_POST["product_id"]);

    $quantity = intval($_POST["quantity"]);


    // Zero stock is allowed, but negative stock is rejected.
    if ($quantity < 0) {

        $message = "Quantity cannot be negative.";

    } else {

        // Insert a new store/product record; this does not replenish an existing row.
        $stmt = $conn->prepare(
            "INSERT INTO inventory
             (store_id, product_id, quantity)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "iii",
            $storeId,
            $productId,
            $quantity
        );


        if ($stmt->execute()) {

            $message = "Inventory added successfully!";

        } else {

            $message =
                "Unable to add inventory. The product may already exist at this store.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Add Inventory</title>

</head>

<body>

<h1>Add Inventory</h1>


<?php

// Escape feedback before inserting it into the HTML response.
if (!empty($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}

?>


<form method="POST">


    <label>Store:</label>

    <br>

    <select name="store_id" required>

        <option value="">
            Select Store
        </option>

        <?php while ($store = $stores->fetch_assoc()) { ?>

            <option value="<?php echo $store["store_id"]; ?>">

                <?php
                echo htmlspecialchars(
                    $store["store_name"]
                );
                ?>

            </option>

        <?php } ?>

    </select>


    <br><br>


    <label>Product:</label>

    <br>

    <select name="product_id" required>

        <option value="">
            Select Product
        </option>

        <?php while ($product = $products->fetch_assoc()) { ?>

            <option value="<?php echo $product["product_id"]; ?>">

                <?php
                echo htmlspecialchars(
                    $product["product_name"]
                );
                ?>

            </option>

        <?php } ?>

    </select>


    <br><br>


    <label>Quantity:</label>

    <br>

    <input
        type="number"
        name="quantity"
        min="0"
        required
    >


    <br><br>


    <button type="submit">
        Add Inventory
    </button>

</form>


<br>

<a href="view.php">
    Back to Inventory
</a>


</body>

</html>