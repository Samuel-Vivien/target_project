<?php

/**
 * List orders with customer and store names, newest first.
 */

require_once "../config/database.php";

// Resolve customer/store names for each order and show recent orders first.
$sql = "
    SELECT
        orders.order_id,

        customers.first_name,
        customers.last_name,

        stores.store_name,

        orders.order_date,
        orders.status,
        orders.total_amount

    FROM orders

    JOIN customers
        ON orders.customer_id =
           customers.customer_id

    JOIN stores
        ON orders.store_id =
           stores.store_id

    ORDER BY
        orders.order_date DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Orders</title>
</head>

<body>

<h1>Orders</h1>

<a href="create.php">
    Create New Order
</a>

<br><br>

<table border="1">

    <tr>
        <th>Order #</th>
        <th>Customer</th>
        <th>Store</th>
        <th>Date</th>
        <th>Status</th>
        <th>Total</th>
    </tr>

    <?php /* Escape database text at output so it is displayed as text rather than markup. */
    while ($row = $result->fetch_assoc()) { ?>

        <tr>

            <td>
                <?php echo $row["order_id"]; ?>
            </td>

            <td>

                <?php

                echo htmlspecialchars(
                    $row["first_name"] .
                    " " .
                    $row["last_name"]
                );

                ?>

            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $row["store_name"]
                );
                ?>
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $row["order_date"]
                );
                ?>
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $row["status"]
                );
                ?>
            </td>

            <td>

                $<?php
                echo number_format(
                    $row["total_amount"],
                    2
                );
                ?>

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