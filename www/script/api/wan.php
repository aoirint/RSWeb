<?php
  $table = ORM::for_table('wan');
  $now = date('Y-m-d H:i:s');

  function _pack_wan($record) {
    $d = [
      'id' => $record->id,
      'wan_ip' => $record->wan_ip,
      'timestamp' => $record->timestamp,
    ];
    return $d;
  }
  function _wan($record) {
    $d = _pack_wan($record);
    return _json($d);
  }
  function _wans($records) {
    $d = [];
    foreach ($records as $record) {
      $d[] = _pack_wan($record);
    }
    return _json($d);
  }

  if (preg_match('/^$/', $path)) {
    if ($method == 'GET') {
      $records = $table->find_many();
      return _wans($records);
    }
    else if ($method == 'POST') {
      $wan_ip = isset($_POST['wan_ip']) ? $_POST['wan_ip'] : NULL;
      $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : NULL;
      $ts = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $timestamp);

      $record = $table->create();
      $record->wan_ip = $wan_ip;
      $record->timestamp = $ts->format('Y-m-d H:i:s');

      $record->save();

      return _wan($record);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/latest$/', $path)) {
    if ($method == 'GET') {
      $record = $table->order_by_desc('timestamp')->find_one();
      return _wan($record);
    }
    else {
      return _405();
    }
  }
  else {
    return _404();
  }
