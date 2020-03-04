<?php
  header('Content-Type: text/html');

  function _html($snippet) {
    $html = <<<EOT
<!DOCTYPE html>
<meta charset="utf-8">

{$snippet}
EOT;
    print($html);
  }
  function _status($status, $msg) {
    $html = <<<EOT
<h2>{$status}</h2>
<p>{$msg}
EOT;
    print($html);
  }
  function _404($msg=NULL) {
    http_response_code(404);
    return _status('404 Not Found', $msg);
  }
  function _500($msg=NULL) {
    http_response_code(500);
    return _status('500 Bad Request', $msg);
  }

  if (preg_match('/^$/', $path)) {
    include(__DIR__ . '/room.php');
  }
  else if (preg_match('/^\/wimax$/', $path)) {
    include(__DIR__ . '/wimax.php');
  }
  else if (preg_match('/^\/light$/', $path)) {
    include(__DIR__ . '/light.php');
  }
  else if (preg_match('/^\/temperature$/', $path)) {
    include(__DIR__ . '/temperature.php');
  }
  else if (preg_match('/^\/gputemp$/', $path)) {
    include(__DIR__ . '/gputemp.php');
  }
  else if (preg_match('/^\/gpumem$/', $path)) {
    include(__DIR__ . '/gpumem.php');
  }
  else {
    return _404();
  }
