<?php

require_once "../config/database.php";

$sql = "
    SELECT
        supplier_id,
        supplier_name,
        contact_name,
        email,
        phone,
        city,
        state,
        active
    FROM suppliers
    ORDER BY supplier_name
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Suppliers</title>
</head>

<body>

<h1>Suppliers</h1>

<a href="add.php">
    Add Supplier
</a>

<br><br>

<table border="1">

<tr>
    <th>ID</th>
    <th>Supplier</th>
    <th>Contact</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Location</th>
    <th>Status</th>
</tr>

<?php while ($row = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <?php echo $row["supplier_id"]; ?>
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
            $row["contact_name"] ?? ""
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row["email"] ?? ""
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $row["phone"] ?? ""
        );
        ?>
    </td>

    <td>

        <?php

        echo htmlspecialchars(
            ($row["city"] ?? "") .
            ", " .
            ($row["state"] ?? "")
        );

        ?>

    </td>

    <td>

        <?php

        echo $row["active"]
            ? "Active"
            : "Inactive";

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