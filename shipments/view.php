<?php

require_once "../config/database.php";

/*
Get all shipments.

LEFT JOIN shipment_items is used so that
a newly-created shipment will still appear
even if no products have been added yet.
*/

$sql = "
    SELECT
        shipments.shipment_id,
        suppliers.supplier_name,
        stores.store_name,
        shipments.planned_date,
        shipments.received_date,
        shipments.status,
        shipments.tracking_number,

        COUNT(shipment_items.shipment_item_id)
            AS item_count,

        COALESCE(
            SUM(
                shipment_items.quantity_planned
                * shipment_items.unit_cost
            ),
            0
        ) AS estimated_cost

    FROM shipments

    JOIN suppliers
        ON shipments.supplier_id =
           suppliers.supplier_id

    JOIN stores
        ON shipments.store_id =
           stores.store_id

    LEFT JOIN shipment_items
        ON shipments.shipment_id =
           shipment_items.shipment_id

    GROUP BY
        shipments.shipment_id,
        suppliers.supplier_name,
        stores.store_name,
        shipments.planned_date,
        shipments.received_date,
        shipments.status,
        shipments.tracking_number

    ORDER BY
        shipments.planned_date DESC,
        shipments.shipment_id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Shipments</title>

</head>

<body>


<h1>Shipments</h1>


<a href="create.php">
    Create New Shipment
</a>

<br><br>


<table border="1" cellpadding="8" cellspacing="0">

    <tr>

        <th>Shipment #</th>

        <th>Supplier</th>

        <th>Destination Store</th>

        <th>Planned Arrival</th>

        <th>Received Date</th>

        <th>Status</th>

        <th>Tracking Number</th>

        <th>Products</th>

        <th>Estimated Cost</th>

        <th>Actions</th>

    </tr>


    <?php if ($result->num_rows > 0) { ?>


        <?php while ($row = $result->fetch_assoc()) { ?>


            <tr>


                <td>

                    <?php
                    echo $row["shipment_id"];
                    ?>

                </td>


                <td>

                    <?php

                    echo htmlspecialchars(
                        $row["supplier_name"]
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

                    if (!empty($row["planned_date"])) {

                        echo htmlspecialchars(
                            $row["planned_date"]
                        );

                    } else {

                        echo "Not Set";

                    }

                    ?>

                </td>


                <td>

                    <?php

                    if (!empty($row["received_date"])) {

                        echo htmlspecialchars(
                            $row["received_date"]
                        );

                    } else {

                        echo "-";

                    }

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

                    <?php

                    if (!empty($row["tracking_number"])) {

                        echo htmlspecialchars(
                            $row["tracking_number"]
                        );

                    } else {

                        echo "-";

                    }

                    ?>

                </td>


                <td>

                    <?php
                    echo $row["item_count"];
                    ?>

                </td>


                <td>

                    $<?php

                    echo number_format(
                        $row["estimated_cost"],
                        2
                    );

                    ?>

                </td>


                <td>

                    <!--
                    View shipment details
                    -->
                    <a href="details.php?id=<?php echo $row["shipment_id"]; ?>">
                        View
                    </a>


                    <?php if ($row["status"] != "Received") { ?>

                        |

                        <!--
                        Add products to shipment
                        -->
                        <a href="add_item.php?shipment_id=<?php echo $row["shipment_id"]; ?>">
                            Add Product
                        </a>


                        |

                        <!--
                        Receive shipment
                        -->
                        <a href="receive.php?id=<?php echo $row["shipment_id"]; ?>">
                            Receive
                        </a>

                    <?php } ?>


                </td>


            </tr>


        <?php } ?>


    <?php } else { ?>


        <tr>

            <td colspan="10">

                No shipments found.

            </td>

        </tr>


    <?php } ?>


</table>


<br><br>


<a href="../index.php">
    Back to Home
</a>


</body>

</html>