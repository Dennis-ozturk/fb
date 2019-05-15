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

// var_dump($request_uri);
// Get querystring
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);

// Get querystring.
$querystring = $_SERVER["QUERY_STRING"];

// Get the querystring parts.
$request_parts = explode('/', $querystring);
//var_dump($request_parts); 

// Get request method. (GET, POST etc).
$request_method = strtolower($_SERVER['REQUEST_METHOD']);

// Get class from querystring
$class = $request_parts[0];

// Get id
//vad innehåller 1:an? id, lastname? 
//Dennis gjort?! args endast av Micke
$args = $request_parts[1] ?? null;

//Dennis gjort?!
// Get input //selekterade efternamnet i author 
//$args_value = $request_part[2] ?? null;

// Get postman data, det data som vi skickar med requestet //file_get_contents. vanligast med URl. input. läsa in det postdata som skickat till oss
$postman_data = json_decode(file_get_contents('php://input'), true);
//$body_data = json_decode(file_get_contents('php://input'));enligt Micke. utan true så blir det vilken visibility det är på properties/funktionerna
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
            if ($obj->post($postman_data)) { //skickar postman data
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
            
        // Update record. //lägger till en ny router, PUT
        //case - PUT hur fungerar en switch? - request method. samma som create. 
        case 'put':
            if ($obj->put($postman_data)) {
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
            //Handle delete
            //vad behöver vi ha för data för att ta bort ett record från db, is
            if ($obj->delete($args)) {
                http_response_code(200);
                $response['info']['message'] = "object deleted";
            } else {
                http_response_code(503);
                $response['info']['message'] = "failed to delete object";
            }
            break;

        // Read record. Omvandlar data till postman. Här kan du köra lastname eller firstname-filter vid behov
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
    }
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
