<?php
// kollar om klassen finns, om den gör det så ska den inkluderas. Annars visas felkod 501.
spl_autoload_register(function ($class_name) {
    if (file_exists('classes/' . $class_name . '.inc.php')) {
        include 'classes/' . $class_name . '.inc.php';
    } else {
        http_response_code(501);
    }
});

// Get URI.
$request_uri = $_SERVER['REQUEST_URI'];
//var_dump($request_uri);

// Get querystring
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
//var_dump($request_uri);

// Get querystring.
$querystring = $_SERVER["QUERY_STRING"];
//var_dump($querystring);

// Get the querystring parts.
$request_parts = explode('/', $querystring);
//var_dump($request_parts);

//get last part of querystring
$last_part = $request_parts[count($request_parts) - 1];
//var_dump($last_part);

// Get request method. (GET, POST etc).
$request_method = strtolower($_SERVER['REQUEST_METHOD']);
//var_dump($request_method);

$class = $request_parts[0];
//var_dump($class);

$args = $request_parts[1] ?? null;
//var_dump($args);


$api = explode('?api=', $querystring);
$api = $api[1];

$doubleQuestion = preg_match('/\?(.+)\?/', $_SERVER['REQUEST_URI'], $doubleQuestionMark);

$body_data = json_decode(file_get_contents('php://input'));
//var_dump($body_data);

$response = [
    'info' => null,
    'results' => null,
    'api' => null,
];


if(empty($doubleQuestionMark)){
    $obj = new $class;
}elseif(strpos($doubleQuestionMark[1], '/')){
    $obj = new $class;
}else {
    $obj = new $doubleQuestionMark[1];
}


// Setup router.
switch ($request_method) {
        // Create record.
    case 'post':
        if ($obj->create($body_data)) {
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
        // Everything else: GET.
    default:
        $data = $obj->get($args);
        if (strlen($api) >= 32) {
            $auth = $obj->auth($api);
        } else {
            $auth = false;
        }

        if ($auth === true) {
            if ($data) {
                http_response_code(200);
                $response['info']['no'] = count($data);
                $response['info']['message'] = "Returned items.";
                $response['results'] = $data;
                $response['api'] = "Success";
            } else {
                http_response_code(404);
                $response['info']['message'] = "Couldn't find any items.";
                $response['info']['no'] = 0;
            }
        } else {
            $response['info']['message'] = "Authentication didn't go as planed.";
        }

        break;
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
