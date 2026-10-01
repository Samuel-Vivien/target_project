<?php

/**
 * Load the product identified by GET id and update its details on POST.
 */

require_once "../config/database.php";

$message = "";

// Require the product identifier for both form display and submission.
if (!isset($_GET["id"])) {
    die("Product ID not provided.");
}

$productId = intval($_GET["id"]);


// Load category choices used to display the current selection.
$categorySql = "
    SELECT category_id, category_name
    FROM categories
";

$categories = $conn->query($categorySql);


// Load current values using a bound product ID to populate the form.
$stmt = $conn->prepare(
    "SELECT product_id,
            category_id,
            product_name,
            description,
            price
     FROM products
     WHERE product_id = ?"
);

$stmt->bind_param("i", $productId);

$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();

$stmt->close();


// Stop before rendering or updating when no matching product exists.
if (!$product) {
    die("Product not found.");
}


// Apply submitted details to the product identified by the URL.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productName = trim($_POST["product_name"]);
    $description = trim($_POST["description"]);
    $price = $_POST["price"];
    $categoryId = $_POST["category_id"];

    // Require nonempty values; empty() also rejects a price of "0".
    if (
        empty($productName) ||
        empty($price) ||
        empty($categoryId)
    ) {

        $message = "Please complete all required fields.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE products
             SET category_id = ?,
                 product_name = ?,
                 description = ?,
                 price = ?
             WHERE product_id = ?"
        );

        $stmt->bind_param(
            "issdi",
            $categoryId,
            $productName,
            $description,
            $price,
            $productId
        );

        if ($stmt->execute()) {

            $message = "Product updated successfully!";

            // Reflect the saved values in the form without another database read.
            $product["category_id"] = $categoryId;
            $product["product_name"] = $productName;
            $product["description"] = $description;
            $product["price"] = $price;

        } else {

            $message = "Error updating product.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Edit Product</title>

</head>

<body>

<h1>Edit Product</h1>


<?php

// Escape feedback before inserting it into the HTML response.
if (!empty($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}

?>


<form method="POST">

    <label>
        Product Name:
    </label>

    <br>

    <input
        type="text"
        name="product_name"
        value="<?php echo htmlspecialchars($product["product_name"]); ?>"
        required
    >

    <br><br>


    <label>
        Description:
    </label>

    <br>

    <textarea
        name="description"
        rows="4"
        cols="40"
    ><?php echo htmlspecialchars($product["description"]); ?></textarea>

    <br><br>


    <label>
        Price:
    </label>

    <br>

    <input
        type="number"
        name="price"
        step="0.01"
        min="0"
        value="<?php echo htmlspecialchars($product["price"]); ?>"
        required
    >

    <br><br>


    <label>
        Category:
    </label>

    <br>

    <select
        name="category_id"
        required
    >

        <?php while ($category = $categories->fetch_assoc()) { ?>

            <option
                value="<?php echo $category["category_id"]; ?>"

                <?php

                if (
                    $category["category_id"]
                    == $product["category_id"]
                ) {
                    echo "selected";
                }

                ?>
            >

                <?php
                echo htmlspecialchars(
                    $category["category_name"]
                );
                ?>

            </option>

        <?php } ?>

    </select>

    <br><br>


    <button type="submit">
        Update Product
    </button>

</form>


<br>

<a href="view.php">
    Back to Products
</a>

</body>

</html>