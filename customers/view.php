<?php

/**
 * List customers alphabetically by last name, then first name.
 */

require_once "../config/database.php";

// Keep the directory ordered by surname, then given name.
$sql = "
    SELECT
        customer_id,
        first_name,
        last_name,
        email,
        phone,
        created_at
    FROM customers
    ORDER BY last_name, first_name
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Customers</title>
</head>

<body>

<h1>Customers</h1>

<a href="add.php">Add Customer</a>

<br><br>

<table border="1">

    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Created</th>
    </tr>

    <?php /* Escape database text at output so it is displayed as text rather than markup. */
    while ($row = $result->fetch_assoc()) { ?>

        <tr>

            <td>
                <?php echo $row["customer_id"]; ?>
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $row["first_name"] . " " . $row["last_name"]
                );
                ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["email"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["phone"] ?? ""); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["created_at"]); ?>
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