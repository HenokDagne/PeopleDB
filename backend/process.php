<?php
require_once 'config.php';

header("Content-Type: application/json; charset=UTF-8");

// Ensure a single database connection
$conn = DatabaseConnection();

/**
 * Handles form submission (POST request).
 */
function processForm($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "Invalid request method"]);
        exit;
    }
    
    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Handle file upload
    $profileimage = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory with full permissions
        }

        $profileimage = $uploadDir . basename($_FILES['image']['name']);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $profileimage)) {
            echo json_encode(["error" => "Failed to upload image"]);
            exit;
        }
    }

    // Validation checks
    if (empty($name) || empty($email) || empty($password) || empty($description) || empty($phone) || empty($profileimage)) {
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "Invalid email format"]);
        exit;
    }

    // Check if email already exists
    if (userExists($conn, $email)) {
        echo json_encode(["error" => "Email is already registered"]);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (insertData($conn, $name, $email, $hashed_password, $description, $profileimage, $phone)) {
        echo json_encode(["success" => "Form submitted successfully!"]);
    } else {
        echo json_encode(["error" => "Error: Could not insert data"]);
    }
}

/**
 * Checks if a user already exists by email.
 */
function userExists($conn, $email) {
    if($_SERVER['REQUEST_METHOD'] !== 'GET') {
       
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
    }
}

/**
 * Inserts a new user into the database.
 */
function insertData($conn, $name, $email, $password, $description, $profileimage, $phone) {
    $stmt = $conn->prepare("INSERT INTO user (fullname, email, password, description, image, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $description, $profileimage, $phone);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("SQL Error: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

/**
 * Fetches and returns all users as JSON.
 */
function listUsers($conn) {
    $sql = "SELECT id, fullname, email, image, phone, description FROM user";
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["error" => "Database query failed: " . $conn->error]);
        exit;
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    processForm($conn);
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    listUsers($conn);
} else {
    echo json_encode(["error" => "Unsupported request method"]);
}

// Close database connection
$conn->close();
?>