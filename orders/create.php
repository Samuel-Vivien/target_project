<?php

/**
 * Create a single-product order for a customer at a selected store.
 *
 * POST creates the order and its item and reduces inventory in one transaction.
 */

// Make database failures throw so transaction errors reach the rollback handler.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once "../config/database.php";

$message = "";


// Load customers in surname order for the selection list.
$customers = $conn->query(
    "SELECT
        customer_id,
        first_name,
        last_name
     FROM customers
     ORDER BY last_name, first_name"
);


// Load stores for the order location selector.
$stores = $conn->query(
    "SELECT
        store_id,
        store_name
     FROM stores
     ORDER BY store_name"
);


// Show catalog prices in the selector; re-read the chosen price when submitting.
$products = $conn->query(
    "SELECT
        product_id,
        product_name,
        price
     FROM products
     ORDER BY product_name"
);


// Process the selected customer, store, product, and quantity only on submission.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $customerId = intval($_POST["customer_id"]);
    $storeId = intval($_POST["store_id"]);
    $productId = intval($_POST["product_id"]);
    $quantity = intval($_POST["quantity"]);

    // Require positive identifiers and at least one unit before starting a transaction.
    if (
        $customerId <= 0 ||
        $storeId <= 0 ||
        $productId <= 0 ||
        $quantity <= 0
    ) {

        $message = "Please enter valid order information.";

    } else {

        try {

            // Keep the order, line item, and stock deduction atomic.
            // This relies on transaction-capable database tables.
            $conn->begin_transaction();


            // Use the database price instead of trusting a client-supplied amount.
            $stmt = $conn->prepare(
                "SELECT price
                 FROM products
                 WHERE product_id = ?"
            );

            $stmt->bind_param(
                "i",
                $productId
            );

            $stmt->execute();

            $productResult = $stmt->get_result();

            $product = $productResult->fetch_assoc();

            $stmt->close();


            if (!$product) {
                throw new Exception("Product not found.");
            }


            // Lock the store/product stock row until commit or rollback so concurrent
            // orders cannot both allocate the same available units.
            $stmt = $conn->prepare(
                "SELECT
                    inventory_id,
                    quantity
                 FROM inventory
                 WHERE store_id = ?
                 AND product_id = ?
                 FOR UPDATE"
            );

            $stmt->bind_param(
                "ii",
                $storeId,
                $productId
            );

            $stmt->execute();

            $inventoryResult = $stmt->get_result();

            $inventory = $inventoryResult->fetch_assoc();

            $stmt->close();


            if (!$inventory) {

                throw new Exception(
                    "This product is not available at the selected store."
                );

            }


            if ($inventory["quantity"] < $quantity) {

                throw new Exception(
                    "Not enough inventory available."
                );

            }


            // This form creates one line item, so its subtotal is the order total.
            $unitPrice = $product["price"];

            $totalAmount =
                $unitPrice * $quantity;


            // Create the order header and retain its generated ID for the line item.
            $status = "Completed";

            $stmt = $conn->prepare(
                "INSERT INTO orders
                (
                    customer_id,
                    store_id,
                    status,
                    total_amount
                )
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iisd",
                $customerId,
                $storeId,
                $status,
                $totalAmount
            );

            $stmt->execute();

            $orderId = $conn->insert_id;

            $stmt->close();


            // Store the purchase-time unit price so later catalog changes do not alter it.
            $stmt = $conn->prepare(
                "INSERT INTO order_items
                (
                    order_id,
                    product_id,
                    quantity,
                    unit_price
                )
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiid",
                $orderId,
                $productId,
                $quantity,
                $unitPrice
            );

            $stmt->execute();

            $stmt->close();


            // Deduct units from the inventory row locked and checked above.
            $stmt = $conn->prepare(
                "UPDATE inventory
                 SET quantity = quantity - ?
                 WHERE inventory_id = ?"
            );

            $stmt->bind_param(
                "ii",
                $quantity,
                $inventory["inventory_id"]
            );

            $stmt->execute();

            $stmt->close();


            // Persist all three writes together only after every step succeeds.
            $conn->commit();

            $message =
                "Order #" .
                $orderId .
                " created successfully!";

        } catch (Throwable $error) {

            // Undo partial writes and release the inventory lock on failure.
            $conn->rollback();

            $message =
                "Order failed: " .
                $error->getMessage();

        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Order</title>
</head>

<body>

<h1>Create Order</h1>

<?php

// Escape feedback before inserting it into the HTML response.
if (!empty($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}

?>

<form method="POST">

    <label>Customer:</label>

    <br>

    <select name="customer_id" required>

        <option value="">
            Select Customer
        </option>

        <?php while ($customer = $customers->fetch_assoc()) { ?>

            <option
                value="<?php echo $customer["customer_id"]; ?>"
            >

                <?php

                echo htmlspecialchars(
                    $customer["first_name"] .
                    " " .
                    $customer["last_name"]
                );

                ?>

            </option>

        <?php } ?>

    </select>

    <br><br>


    <label>Store:</label>

    <br>

    <select name="store_id" required>

        <option value="">
            Select Store
        </option>

        <?php while ($store = $stores->fetch_assoc()) { ?>

            <option
                value="<?php echo $store["store_id"]; ?>"
            >

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

            <option
                value="<?php echo $product["product_id"]; ?>"
            >

                <?php

                echo htmlspecialchars(
                    $product["product_name"]
                );

                echo " - $";

                echo number_format(
                    $product["price"],
                    2
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
        min="1"
        required
    >

    <br><br>


    <button type="submit">
        Complete Order
    </button>

</form>

<br>

<a href="view.php">
    View Orders
</a>

</body>

</html>