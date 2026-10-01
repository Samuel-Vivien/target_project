<?php

/**
 * Display the navigation page for the database management screens.
 */

require_once "config/database.php";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Target Database System</title>
</head>

<body>

<h1>Target Database Management System</h1>

<h2>Management</h2>

<ul>

    <li>
        <a href="inventory/view.php">
            View Inventory
    </li>

    <li>
        <a href="products/view.php">
            View Products
        </a>
    </li>

    <li>
        <a href="stores/view.php">
            View Stores
        </a>
    </li>

    <li>
        <a href="customers/view.php">
            View Customers
        </a>
    </li>

    <li>
        <a href="orders/view.php">
            View Orders
        </a>
    </li>

</ul>
</body>
</html>