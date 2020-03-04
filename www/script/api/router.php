<?php
  header('Content-Type: application/json');

  function _json($d) {
    print(json_encode($d));
  }
  function _404($msg=NULL) {
    http_response_code(404);
    return _json([ 'status' => '404 Not Found', 'msg' => $msg, ]);
  }
  function _405($msg=NULL) {
    http_response_code(405);
    return _json([ 'status' => '405 Method Not Allowed', 'msg' => $msg, ]);
  }
  function _500($msg=NULL) {
    http_response_code(500);
    return _json([ 'status' => '500 Bad Request', 'msg' => $msg, ]);
  }

  if (preg_match('/^\/doorbell(.*)$/', $path, $matches)) {
    $path = $matches[1];
    include(__DIR__ . '/doorbell.php');
  }
  else if (preg_match('/^\/wimax(.*)$/', $path, $matches)) {
    $path = $matches[1];
    include(__DIR__ . '/wimax.php');
  }
  else if (preg_match('/^\/sensor(.*)$/', $path, $matches)) {
    $path = $matches[1];
    include(__DIR__ . '/sensor.php');
  }
  else if (preg_match('/^\/gpu(.*)$/', $path, $matches)) {
    $path = $matches[1];
    include(__DIR__ . '/gpu.php');
  }
  else if (preg_match('/^\/wan(.*)$/', $path, $matches)) {
    $path = $matches[1];
    include(__DIR__ . '/wan.php');
  }
  else if (preg_match('/^\/token(.*)$/', $path, $matches)) {
    $path = $matches[1];
    include(__DIR__ . '/token.php');
  }
  else {
    return _404();
  }
