<?php
  session_start();

  require_once __DIR__ . '/script/vendor/idiorm.php';

  $method = $_SERVER['REQUEST_METHOD'];
  $uri = $_SERVER['REQUEST_URI'];
  $path = substr($uri, 0); # /
  $token = sha1(session_id());

  $dburl = 'sqlite:data/db.sqlite3';
  ORM::configure($dburl);

  if ($method == 'POST') {
    if (!isset($_POST['token']) || $token !== $_POST['token']) {
      http_response_code('403');
      print('CSRF Validation Failed');
      return;
    }
  }

  if (preg_match('/^\/doorbell(.*)/', $path, $matches)) {
    $path = $matches[1];
    include __DIR__ . '/script/doorbell/router.php';
  }
  else if (preg_match('/^\/room(.*)/', $path, $matches)) {
    $path = $matches[1];
    include __DIR__ . '/script/room/router.php';
  }
  else if (preg_match('/^\/api(.*)$/', $path, $matches)) {
    $path = $matches[1];
    include __DIR__ . '/script/api/router.php';
  }
  else {
    return false;
  }

  # $db = new SQLite3('data/db.sqlite3');
