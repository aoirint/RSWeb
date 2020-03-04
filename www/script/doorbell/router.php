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

  if (preg_match('/^\/request$/', $path)) {
    include(__DIR__ . '/request.php');
  }
  else if (preg_match('/^\/watch$/', $path)) {
    include(__DIR__ . '/watch.php');
  }
  else {
    return _404();
  }
