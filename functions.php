<?php
function validateInput($input, $requiredFields)
{
    $missingFields = []; // to collect missing fields

    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        // header("HTTP/1.0 400 Bad Request");
        // echo json_encode([
        //     'error' => 'Missing required fields: ' . implode(', ', $missingFields)
        // ]);
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
    }

    return true;
}
