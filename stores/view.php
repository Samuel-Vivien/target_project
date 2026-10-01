<?php

/**
 * List stores and their locations alphabetically by store name.
 */

require_once "../config/database.php";

// Retrieve locations in alphabetical order for the store directory.
$sql = "
    SELECT
        store_id,
        store_name,
        city,
        state
    FROM stores
    ORDER BY store_name
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Stores</title>

</head>

<body>

<h1>Stores</h1>

<table border="1">

    <tr>
        <th>ID</th>
        <th>Store</th>
        <th>City</th>
        <th>State</th>
    </tr>

    <?php /* Escape database text at output so it is displayed as text rather than markup. */
    while ($row = $result->fetch_assoc()) { ?>

        <tr>

            <td>
                <?php echo $row["store_id"]; ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["store_name"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["city"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["state"]); ?>
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