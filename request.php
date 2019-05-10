<?php

// Get URI.
$request_uri = $_SERVER['REQUEST_URI'];
// var_dump($request_uri);

// Get querystring
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
// var_dump($request_uri);

// Get querystring.
$querystring = $_SERVER["QUERY_STRING"];
// var_dump($querystring);

// Get the querystring parts.
$request_parts = explode('/', $querystring);

// Get request method. (GET, POST etc).
$request_method = strtolower($_SERVER['REQUEST_METHOD']);
// var_dump($request_method);

// kollar om filen finns, om den gör det så ska den inkluderas. Annars visas felkod 501.
spl_autoload_register(function ($class_name) {
    if (file_exists('classes/'. $class_name . '.inc.php')) {
        include 'classes/'. $class_name . '.inc.php';
    } else {
        http_response_code(501);
    }
});

$class = $request_parts[0];
// var_dump($class);
$args = $request_parts[1] ?? null;
// var_dump($args);
$body_data = json_decode(file_get_contents('php://input'));
// var_dump($body_data);

$response = [
    'info' => null,
    'results' => null
];

if (empty($class)) {
    http_response_code(400);
} else {
    $obj = new $class;
    // Setup router.
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
        
        case 'delete':
            if ($obj->delete($args)) {
                http_response_code(200);
                $response['info']['message'] = "object deleted";
            } else {
                http_response_code(503);
                $response['info']['message'] = "failed to delete object";
            }
            break;

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

        // Everything else: GET.
        default:
            if ($data) {
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
