<?php
require '../../db.php';
require '../../functions.php';

try {
    // Get request payload
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if all required fields are present
    $requiredFields = ['name', 'email', 'latitude', 'longitude'];

    // If there are any missing fields, return an error message
    validateInput($data, $requiredFields);

    // Check if user already exists
    $db->where('email', $data['email']);
    $existingUser = $db->getOne('users', ['id']); // Fetch only 'id' to check existence

    if ($existingUser) {
        // If user already exists, throw an error
        throw new Exception('User with this email already exists.');
    }

    // Start transaction for data consistency
    $db->startTransaction();

    // Prepare location as a POINT
    $location = "POINT(" . $data['latitude'] . " " . $data['longitude'] . ")";

    // Construct the raw SQL query for inserting user data
    $sql = "INSERT INTO users (name, email, location) 
          VALUES (?, ?, ST_GeomFromText(?))";

    // Execute the raw query as point is not handled directly by ORM
    $insert = $db->rawQuery($sql, [
        $data['name'],
        $data['email'],
        $location
    ]);

    if ($db->getLastError()) {
        // Rollback transaction if insert fails 
        $db->rollback();
        throw new Exception('Failed to add new user' . $db->getLastError());
        exit;
    }

    // Commit transaction if everything is successful
    $db->commit();
    echo json_encode(['message' => 'New user added successfully.']);
} catch (\Throwable $th) {
    // Return error response if an exception is caught
    header("HTTP/1.0 400 Bad Request");
    echo json_encode(['error' => $th->getMessage()]);
}
