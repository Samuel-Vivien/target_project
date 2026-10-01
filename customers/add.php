<?php

/**
 * Display the customer form and insert a customer on POST.
 *
 * First name, last name, and email are required; phone is optional.
 */

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = trim($_POST["first_name"]);
    $lastName = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    // Validate required fields and email format before attempting the insert.
    if (
        empty($firstName) ||
        empty($lastName) ||
        empty($email)
    ) {

        $message = "Please complete all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        // Bind submitted values separately from the SQL statement.
        $stmt = $conn->prepare(
            "INSERT INTO customers
            (first_name, last_name, email, phone)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $firstName,
            $lastName,
            $email,
            $phone
        );

        if ($stmt->execute()) {

            $message = "Customer added successfully!";

        } else {

            $message = "Unable to add customer. The email may already exist.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Customer</title>
</head>

<body>

<h1>Add Customer</h1>

<?php

// Escape feedback before inserting it into the HTML response.
if (!empty($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}

?>

<form method="POST">

    <label>First Name:</label>
    <br>

    <input
        type="text"
        name="first_name"
        required
    >

    <br><br>

    <label>Last Name:</label>
    <br>

    <input
        type="text"
        name="last_name"
        required
    >

    <br><br>

    <label>Email:</label>
    <br>

    <input
        type="email"
        name="email"
        required
    >

    <br><br>

    <label>Phone:</label>
    <br>

    <input
        type="text"
        name="phone"
    >

    <br><br>

    <button type="submit">
        Add Customer
    </button>

</form>

<br>

<a href="view.php">
    View Customers
</a>

</body>
</html>