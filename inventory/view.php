<?php

/**
 * List inventory by store and product, including stock status and edit links.
 */

require_once "../config/database.php";

// Join store/product details and group the display by store, then product name.
$sql = "
    SELECT
        inventory.inventory_id,
        stores.store_name,
        products.product_name,
        products.price,
        inventory.quantity
    FROM inventory

    JOIN stores
        ON inventory.store_id = stores.store_id

    JOIN products
        ON inventory.product_id = products.product_id

    ORDER BY
        stores.store_name,
        products.product_name
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Inventory</title>

</head>

<body>

<h1>Inventory</h1>

<a href="add.php">Add New Inventory</a>

<br><br>

<table border="1">

    <tr>
        <th>Store</th>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php /* Escape database text at output so it is displayed as text rather than markup. */
    while ($row = $result->fetch_assoc()) { ?>

        <tr>

            <td>
                <?php echo htmlspecialchars($row["store_name"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["product_name"]); ?>
            </td>

            <td>
                $<?php echo number_format($row["price"], 2); ?>
            </td>

            <td>
                <?php echo $row["quantity"]; ?>
            </td>

            <td>

                <?php

                // Stock bands: up to 5 is low, 6-15 is medium, and above 15 is in stock.
                if ($row["quantity"] <= 5) {

                    echo "Low Stock";

                } elseif ($row["quantity"] <= 15) {

                    echo "Medium Stock";

                } else {

                    echo "In Stock";

                }

                ?>

            </td>

            <td>

                <a href="edit.php?id=<?php echo $row["inventory_id"]; ?>">
                    Update
                </a>

            </td>

        </tr>

    <?php } ?>

</table>

<br>

<a href="../index.php">
    Back to Home
</a>

</body>

</html>