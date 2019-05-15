<?php

// If "classes/file.inc.php" exists, include "file.inc.php" Else show response with code 501.
spl_autoload_register(function ($class_name) {
    if (file_exists('classes/'. $class_name . '.inc.php')) {
        include 'classes/'. $class_name . '.inc.php';
    } else {
        http_response_code(501);
    }
});

// Get URI.
$request_uri = $_SERVER['REQUEST_URI'];

// Get querystring
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);

// Get querystring.
$querystring = $_SERVER["QUERY_STRING"];

// Get the querystring parts.
$request_parts = explode('/', $querystring);

// Get request method. (GET, POST etc).
$request_method = strtolower($_SERVER['REQUEST_METHOD']);

$class = $request_parts[0];

$args = $request_parts[1] ?? null;

$api = explode('?api=', $querystring);
$api = $api[1];

$doubleQuestion = preg_match('/\?(.+)\?/', $_SERVER['REQUEST_URI'], $doubleQuestionMark);

$body_data = json_decode(file_get_contents('php://input'), true);

$response = [
    'info' => null,
    'results' => null,
    'api' => null,
];

if (empty($doubleQuestionMark)) {
    $obj = new $class;
} elseif (strpos($doubleQuestionMark[1], '/')) {
    $obj = new $class;
} else {
    $obj = new $doubleQuestionMark[1];
}

if (strlen($api) >= 32) {
    $auth = $obj->auth($api);
    
    if ($auth === false) {
        $response['info']['message'] = "Authentication didn't go as planed.";
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response);
        die;
    }
}

    // Setup request method for router.
switch ($request_method) {
    // Create record.
    case 'post':
        if ($obj->post($body_data)) {
            http_response_code(201);
            $response['results'] = $body_data;
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
        if ($obj->put($args, $body_data)) {
            http_response_code(200);
            $response['results'] = $body_data;
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
        if ($obj->delete($args)) {
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
        if ($data = $obj->get($args)) {
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
} // end switch

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
