<?php

/**
 * List products with category names, prices, and edit/delete links.
 */

require_once "../config/database.php";

// Resolve the category label for each product in the listing.
$sql = "
    SELECT
        products.product_id,
        products.product_name,
        categories.category_name,
        products.price
    FROM products
    JOIN categories
        ON products.category_id = categories.category_id
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Products</title>
</head>

<body>

<tr>
    <th>ID</th>
    <th>Product</th>
    <th>Category</th>
    <th>Price</th>
    <th>Actions</th>
</tr>

<h1>Products</h1>

<a href="add.php">Add New Product</a>

<br><br>

<table border="1">

    <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Category</th>
        <th>Price</th>
    </tr>

    <?php /* Escape database text at output so it is displayed as text rather than markup. */
    while ($row = $result->fetch_assoc()) { ?>

        <tr>

            <td>
                <?php echo $row["product_id"]; ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["product_name"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["category_name"]); ?>
            </td>

            <td>
                $<?php echo number_format($row["price"], 2); ?>
            </td>

            <td>
                <a href="edit.php?id=<?php echo $row["product_id"]; ?>">
                    Edit
                </a>
                
                |
                
                <a href="delete.php?id=<?php echo $row["product_id"]; ?>"
                   onclick="return confirm('Are you sure you want to delete this product?');"
                    >
                    Delete
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