<?php
require '../../db.php';
require '../../functions.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Please use POST method for searching.');
    }

    // get request payload
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // check if all required fields are present
    $requiredFields = ['user_id', 'radius'];

    // If there are any mising fields, return an error message
    validateInput($data, $requiredFields);

    // Validate the user_id exists and is numeric
    if (!is_numeric($data['user_id']) || $data['user_id'] <= 0) {
        throw new Exception('Invalid user ID.');
    }

    // Validate that radius is a valid number
    if (!is_numeric($data['radius']) || $data['radius'] <= 0) {
        throw new Exception('Invalid radius value.');
    }

    // Get the networks the user is connected to
    $db->where('user_id', $data['user_id']);
    $networks = $db->get('user_networks', null, ['network_id']);
    $network_ids = array_column($networks, 'network_id');

    // If the user is not part of any network, return an error
    if (empty($network_ids)) {
        throw new Exception('User is not part of any networks.');
    }

    // fetch the names of the networks the user is part of
    $db->where('id', $network_ids, 'IN');
    $userNetworks = $db->get('alumni_networks', null, ['name']);

    // Get the user's location
    $db->where('id', $data['user_id']);
    $user = $db->getOne('users', ['ST_AsText(location) as location']); // FETCH USER'S LOCATION  

    // Check if the user exists
    if (!$user) {
        throw new Exception('User not found.');
    }

    // Extract latitude and longitud from user data
    preg_match('/POINT\(([^ ]+) ([^ ]+)\)/', $user['location'], $matches);
    $longitude = $matches[1];
    $latitude = $matches[2];
    $radius = $data['radius'] * 1000; // Convert radius from km to meters

    // Query with ST_Distance_Sphere for geospatial filtering
    $query = "SELECT  
        u.id, u.name, u.email, 
        ST_Distance_Sphere(u.location, POINT(?, ?)) / 1000 AS distance,
        an.name as network
        FROM users u
        JOIN user_networks un ON u.id = un.user_id
        JOIN alumni_networks an ON an.id = un.network_id
        WHERE un.network_id IN (" . implode(',', $network_ids) . ") 
        AND u.id != ? 
        AND ST_Distance_Sphere(u.location, POINT(?, ?)) <= ?
        ORDER BY distance ASC";

    $results = $db->rawQuery($query, [
        $longitude,
        $latitude,
        $data['user_id'], // Exclude the requesting user
        $longitude,
        $latitude,
        $radius
    ]);



    // Round the distance to 2 decimal places
    // foreach ($results as &$result) {
    //     $result['distance'] = round($result['distance'], 2); // Round the distance to 2 decimal places
    // }

    $userNetworkNames = array_map(function ($network) {
        return $network['name'];
    }, $userNetworks);

    // Group networks for each user in PHP as using group by in query may slow down the response
    $groupResults = [];
    foreach ($results as $result) {
        if (!isset($groupResults[$result['id']])) { // If the user is not in the group then add user to the group 
            $groupResults[$result['id']] = [
                'id' => $result['id'],
                'name' => $result['name'],
                'email' => $result['email'],
                'distance' => round($result['distance'], 2),
                'networks' => []
            ];
        }
        // Add the network to the user's networks list
        $groupResults[$result['id']]['networks'][] = $result['network'];
    }

    $finalResults = array_values($groupResults); // Reindex the array

    // Get the total number of results
    $totalResults = count($finalResults);

    echo json_encode([
        'total_results' => $totalResults,
        'user_networks' =>  $userNetworkNames, // List of the networks the user is connected to
        'alumni' => $finalResults,
    ]);
} catch (\Throwable $th) {
    // Return error response if an exception is caught
    header("HTTP/1.0 400 Bad Request");
    echo json_encode(['error' => $th->getMessage()]);
}
