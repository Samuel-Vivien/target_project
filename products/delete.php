<?php

/**
 * Delete the product identified by GET id and redirect after a successful execution.
 *
 * This endpoint performs the deletion when requested; it renders no confirmation form.
 */

require_once "../config/database.php";


// Require an ID parameter before attempting the delete.
if (!isset($_GET["id"])) {

    die("Product ID not provided.");

}


$productId = intval($_GET["id"]);


// Bind the requested ID so it is treated as data in the DELETE statement.
$stmt = $conn->prepare(
    "DELETE FROM products
     WHERE product_id = ?"
);

$stmt->bind_param(
    "i",
    $productId
);


if ($stmt->execute()) {

    // Redirect before any response body is emitted and stop further processing.
    header(
        "Location: view.php"
    );

    exit;

} else {

    echo "Error deleting product.";

}


$stmt->close();

?>