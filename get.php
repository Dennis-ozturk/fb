<?php
// session_start();

// var_dump($_SESSION['apikey']);

// // Create a stream
// $options = [ 
//     'http' => [
//         'method' => 'GET',
//         'header' => 'apikey:'.$_SESSION['apikey'],
//     ],
//     'ssl' => [
//         'verify_peer' => false,
//         'verify_peer_name' => false
//     ]
// ];
  
// $context = stream_context_create($options);

// $url = 'http://localhost/git/PHP/3part/fb/request.php';

// // Open the file using the HTTP headers set above
// $content = file_get_contents($url, false, $context);

// var_dump($content);