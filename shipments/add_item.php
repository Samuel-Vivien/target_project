<?php

require_once "../config/database.php";

$message = "";


/*
Make sure shipment ID exists
*/
if (!isset($_GET["shipment_id"])) {
    die("Shipment ID not provided.");
}

$shipmentId = intval(
    $_GET["shipment_id"]
);


/*
Get shipment and its supplier
*/
$stmt = $conn->prepare(
    "SELECT
        shipments.shipment_id,
        shipments.supplier_id,
        suppliers.supplier_name,
        stores.store_name

     FROM shipments

     JOIN suppliers
        ON shipments.supplier_id =
           suppliers.supplier_id

     JOIN stores
        ON shipments.store_id =
           stores.store_id

     WHERE shipments.shipment_id = ?"
);

$stmt->bind_param(
    "i",
    $shipmentId
);

$stmt->execute();

$result = $stmt->get_result();

$shipment = $result->fetch_assoc();

$stmt->close();


if (!$shipment) {
    die("Shipment not found.");
}


/*
Get products supplied by this supplier
*/
$stmt = $conn->prepare(
    "SELECT
        products.product_id,
        products.product_name,
        supplier_products.unit_cost

     FROM supplier_products

     JOIN products
        ON supplier_products.product_id =
           products.product_id

     WHERE supplier_products.supplier_id = ?

     ORDER BY products.product_name"
);

$stmt->bind_param(
    "i",
    $shipment["supplier_id"]
);

$stmt->execute();

$products =
    $stmt->get_result();


/*
Process form
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productId =
        intval($_POST["product_id"]);

    $quantity =
        intval($_POST["quantity"]);

    if ($quantity <= 0) {

        $message =
            "Quantity must be greater than zero.";

    } else {

        /*
        Get supplier cost
        */
        $costStmt = $conn->prepare(
            "SELECT unit_cost
             FROM supplier_products
             WHERE supplier_id = ?
             AND product_id = ?"
        );

        $costStmt->bind_param(
            "ii",
            $shipment["supplier_id"],
            $productId
        );

        $costStmt->execute();

        $costResult =
            $costStmt->get_result();

        $supplierProduct =
            $costResult->fetch_assoc();

        $costStmt->close();


        if (!$supplierProduct) {

            $message =
                "This supplier does not supply that product.";

        } else {

            $unitCost =
                $supplierProduct["unit_cost"];


            /*
            Add shipment item
            */
            $insertStmt = $conn->prepare(
                "INSERT INTO shipment_items
                (
                    shipment_id,
                    product_id,
                    quantity_planned,
                    unit_cost
                )
                VALUES (?, ?, ?, ?)"
            );

            $insertStmt->bind_param(
                "iiid",
                $shipmentId,
                $productId,
                $quantity,
                $unitCost
            );


            if ($insertStmt->execute()) {

                $message =
                    "Product added to shipment successfully!";

            } else {

                $message =
                    "Unable to add product. It may already exist in this shipment.";

            }

            $insertStmt->close();
        }
    }
}

?>
<!DOCTYPE html>

<html>

<head>
    <title>Add Shipment Item</title>
</head>

<body>

<h1>Add Product to Shipment</h1>

<p>
    <strong>Shipment:</strong>
    #<?php echo $shipment["shipment_id"]; ?>
</p>

<p>
    <strong>Supplier:</strong>

    <?php
    echo htmlspecialchars(
        $shipment["supplier_name"]
    );
    ?>
</p>

<p>
    <strong>Destination:</strong>

    <?php
    echo htmlspecialchars(
        $shipment["store_name"]
    );
    ?>
</p>


<?php

if (!empty($message)) {

    echo "<p>" .
         htmlspecialchars($message) .
         "</p>";
}

?>


<form method="POST">

    <label>Product:</label>

    <br>

    <select
        name="product_id"
        required
    >

        <option value="">
            Select Product
        </option>


        <?php
        while (
            $product =
            $products->fetch_assoc()
        ) {
        ?>

            <option
                value="<?php
                echo $product["product_id"];
                ?>"
            >

                <?php

                echo htmlspecialchars(
                    $product["product_name"]
                );

                echo " - Cost: $";

                echo number_format(
                    $product["unit_cost"],
                    2
                );

                ?>

            </option>

        <?php } ?>

    </select>


    <br><br>


    <label>
        Quantity Planned:
    </label>

    <br>

    <input
        type="number"
        name="quantity"
        min="1"
        required
    >


    <br><br>


    <button type="submit">
        Add to Shipment
    </button>

</form>


<br>

<a href="details.php?id=<?php echo $shipmentId; ?>">
    Back to Shipment
</a>


</body>

</html>