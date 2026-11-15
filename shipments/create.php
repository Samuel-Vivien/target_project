<?php

require_once "../config/database.php";

$message = "";

$suppliers = $conn->query(
    "SELECT supplier_id, supplier_name
     FROM suppliers
     WHERE active = 1
     ORDER BY supplier_name"
);

$stores = $conn->query(
    "SELECT store_id, store_name
     FROM stores
     ORDER BY store_name"
);


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $supplierId =
        intval($_POST["supplier_id"]);

    $storeId =
        intval($_POST["store_id"]);

    $plannedDate =
        $_POST["planned_date"];

    $trackingNumber =
        trim($_POST["tracking_number"]);

    $status = "Planned";


    $stmt = $conn->prepare(
        "INSERT INTO shipments
        (
            supplier_id,
            store_id,
            planned_date,
            status,
            tracking_number
        )
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "iisss",
        $supplierId,
        $storeId,
        $plannedDate,
        $status,
        $trackingNumber
    );


    if ($stmt->execute()) {

        $shipmentId =
            $conn->insert_id;

        header(
            "Location: details.php?id="
            . $shipmentId
        );

        exit;

    } else {

        $message =
            "Unable to create shipment.";

    }

}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Shipment</title>
</head>

<body>

<h1>Create Shipment</h1>

<?php

if ($message != "") {
    echo "<p>" .
         htmlspecialchars($message) .
         "</p>";
}

?>

<form method="POST">

<label>Supplier:</label>
<br>

<select name="supplier_id" required>

<option value="">
Select Supplier
</option>

<?php while ($supplier = $suppliers->fetch_assoc()) { ?>

<option value="<?php echo $supplier["supplier_id"]; ?>">

<?php
echo htmlspecialchars(
    $supplier["supplier_name"]
);
?>

</option>

<?php } ?>

</select>

<br><br>


<label>Destination Store:</label>
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


<label>Planned Arrival Date:</label>

<br>

<input
    type="date"
    name="planned_date"
    required
>


<br><br>


<label>Tracking Number:</label>

<br>

<input
    type="text"
    name="tracking_number"
>


<br><br>

<button type="submit">
Create Shipment
</button>

</form>

</body>

</html>