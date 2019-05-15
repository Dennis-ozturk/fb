<?php

// If "classes/file.inc.php" exists, include "file.inc.php" Else show response with code 501.
spl_autoload_register(function ($class_name) {
    if (file_exists('classes/'. $class_name . '.inc.php')) {
        include 'classes/'. $class_name . '.inc.php';
    } else {
        http_response_code(501);
    }
});

// Get querystring.
$querystring = $_SERVER["QUERY_STRING"];
// Get the querystring parts.
$request_parts = explode('/', $querystring);

// Get request method. (GET, POST etc).
$request_method = strtolower($_SERVER['REQUEST_METHOD']);

// Get class from querystring
$class = $request_parts[0];

// Get id
$args_key = $request_parts[1] ?? null;

// Get input
$args_value = $request_part[2] ?? null;


// Get postman data
$postman_data = json_decode(file_get_contents('php://input'), true);

// Setup response items.
$response = [
    'info' => null,
    'results' => null
];

// If no value is typed in the querystring show response code 400. Else create new object from that string and setup router.
if (empty($class)) {
    http_response_code(400);
} else {
    $obj = new $class;
    // Setup request method for router.
    switch ($request_method) {
        
        // Create record.
        case 'post':
            if ($obj->create($postman_data)) {
                http_response_code(201);
                $response['results'] = $postman_data;
                $response['info']['no'] = 1;
                $response['info']['message'] = "Item created ok.";
            } else {
                http_response_code(503);
                $response['info']['no'] = 0;
                $response['info']['message'] = "Couldn't create item.";
            }
            break;
            
        // Update record.
        case 'put':
            if ($obj->put($args_key, $postman_data)) {
                http_response_code(200);
                $response['results'] = $postman_data;
                $response['info']['no'] = 1;
                $response['info']['message'] = "object updated";
            } else {
                http_response_code(503);
                $response['info']['no'] = 0;
                $response['info']['message'] = "failed to update object";
            }
            break;

        // Delete record.
        case 'delete':
            if ($obj->delete($args_key)) {
                http_response_code(200);
                $response['info']['message'] = "object deleted";
            } else {
                http_response_code(503);
                $response['info']['message'] = "failed to delete object";
            }
            break;

        // Read record.
        // case 'get'
        default:
            if ($data = $obj->get($args_key, $args_value)) {
                http_response_code(200);
                $response['info']['no'] = count($data);
                $response['info']['message'] = "Returned items.";
                $response['results'] = $data;
            } else {
                http_response_code(404);
                $response['info']['message'] = "Couldn't find any items.";
                $response['info']['no'] = 0;
            }
            break;
    }
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
