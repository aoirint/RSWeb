<?php
  if (preg_match('/^$/', $path)) {
    if ($method == 'GET') {
      $d = [
        'token' => $token,
      ];
      return _json($d);
    }
    else {
      return _405();
    }
  }
  else {
    return _404();
  }
