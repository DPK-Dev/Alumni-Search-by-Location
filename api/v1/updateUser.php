<?php
require '../../db.php';
require '../../functions.php';

try {


    if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
        throw new Exception('Please use PATCH method for update.');
    }

    // get request payload
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // check if all required fields are present
    $requiredFields = ['id', 'name', 'email', 'latitude', 'longitude', 'network_ids'];

    // If there are any mising fields, return an error message
    validateInput($data, $requiredFields);

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format.');
    }

    // Validate latitude and longitude
    if (!is_numeric($data['latitude']) || $data['latitude'] < -90 || $data['latitude'] > 90) {
        throw new Exception('Invalid latitude value.');
    }
    if (!is_numeric($data['longitude']) || $data['longitude'] < -180 || $data['longitude'] > 180) {
        throw new Exception('Invalid longitude value.');
    }

    // Sanitize inputs
    $data['name'] = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
    $data['email'] = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);


    // Check if user exists
    $db->where('id', $data['id']);
    $user = $db->getOne('users', ['id']);

    if (!$user) {
        // If user does not exist, return error
        throw new Exception('User not found.');
    }

    $db->startTransaction(); //Start transaction for data consistency

    $db->where('id', $data['id']); //using directly id to update specific user else auth session is used to get the logged in user id
    $update = $db->update('users', [
        'name' => $data['name'],
        'email' => $data['email']
    ]);

    if (!$update) {
        $db->rollback();
        echo json_encode(['error' => 'Failed to update user details.']);
        exit;
    }

    //Updating locaiton using raw query as point is not handled directly by ORM
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];
    $db->rawQuery("UPDATE users SET location = POINT(?, ?) WHERE email = ?", [$latitude, $longitude, $data['email']]);


    // Remove old network
    $db->where('user_id', $user['id']);
    $db->delete('user_networks');

    foreach ($data['network_ids'] as $network_id) {
        $db->insert('user_networks', [
            'user_id' => $user['id'],
            'network_id' => $network_id
        ]);
    }

    $db->commit();
    echo json_encode(['message' => 'User details and location updated successfully.']);
} catch (\Throwable $th) {
    header("HTTP/1.0 400 Bad Request");
    echo json_encode(['error' => $th->getMessage()]);
}
