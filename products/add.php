<?php

/**
 * Display category choices and insert a product on POST.
 */

require_once "../config/database.php";

// Load category IDs and labels for the product category selector.
$categorySql = "SELECT category_id, category_name FROM categories";
$categories = $conn->query($categorySql);

$message = "";

// Handle form values only on POST; GET displays the form.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productName = trim($_POST["product_name"]);
    $description = trim($_POST["description"]);
    $price = $_POST["price"];
    $categoryId = $_POST["category_id"];

    // Require nonempty name, price, and category values; empty() also rejects "0".
    if (
        empty($productName) ||
        empty($price) ||
        empty($categoryId)
    ) {
        $message = "Please complete all required fields.";
    } else {

        // Bind submitted values separately from the SQL statement.
        $stmt = $conn->prepare(
            "INSERT INTO products
            (category_id, product_name, description, price)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issd",
            $categoryId,
            $productName,
            $description,
            $price
        );

        if ($stmt->execute()) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Product</title>
</head>

<body>

<h1>Add Product</h1>

<?php
// Escape feedback before inserting it into the HTML response.
if (!empty($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<form method="POST" action="">

    <label>Product Name:</label>
    <br>

    <input
        type="text"
        name="product_name"
        required
    >

    <br><br>

    <label>Description:</label>
    <br>

    <textarea
        name="description"
        rows="4"
        cols="40"
    ></textarea>

    <br><br>

    <label>Price:</label>
    <br>

    <input
        type="number"
        name="price"
        step="0.01"
        min="0"
        required
    >

    <br><br>

    <label>Category:</label>
    <br>

    <select name="category_id" required>

        <option value="">
            Select Category
        </option>

        <?php while ($category = $categories->fetch_assoc()) { ?>

            <option
                value="<?php echo $category['category_id']; ?>"
            >
                <?php
                echo htmlspecialchars(
                    $category['category_name']
                );
                ?>
            </option>

        <?php } ?>

    </select>

    <br><br>

    <button type="submit">
        Add Product
    </button>

</form>

<br>

<a href="view.php">
    Back to Products
</a>

</body>

</html>