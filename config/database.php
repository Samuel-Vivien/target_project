<?php

/**
 * Open the shared MySQL connection used by the application.
 *
 * Including this file makes $conn available in the caller's scope.
 */

// Connection settings for the local target_mock database.
$host = "localhost";
$username = "root";
$password = "";
$database = "target_mock";

/** @var mysqli $conn Shared connection for queries and transactions. */
$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

// Stop the request if the connection reports an error.
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>