<?php
  $table = ORM::for_table('wimax');
  $now = date('Y-m-d H:i:s');

  function _pack_wimax($record) {
    $d = [
      'id' => $record->id,
      'today_download' => $record->today_download,
      'today_upload' => $record->today_upload,
      'yesterday_download' => $record->yesterday_download,
      'yesterday_upload' => $record->yesterday_upload,
      'timestamp' => $record->timestamp,
    ];
    return $d;
  }
  function _wimax($record) {
    $d = _pack_wimax($record);
    return _json($d);
  }
  function _wimaxs($records) {
    $d = [];
    foreach ($records as $record) {
      $d[] = _pack_wimax($record);
    }
    return _json($d);
  }

  if (preg_match('/^$/', $path)) {
    if ($method == 'GET') {
      $records = $table->find_many();
      return _wimaxs($records);
    }
    else if ($method == 'POST') {
      $today_download = isset($_POST['today_download']) ? $_POST['today_download'] : NULL;
      $today_upload = isset($_POST['today_upload']) ? $_POST['today_upload'] : NULL;
      $yesterday_download = isset($_POST['yesterday_download']) ? $_POST['yesterday_download'] : NULL;
      $yesterday_upload = isset($_POST['yesterday_upload']) ? $_POST['yesterday_upload'] : NULL;
      $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : NULL;
      $ts = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $timestamp);

      $record = $table->create();
      $record->today_download = $today_download;
      $record->today_upload = $today_upload;
      $record->yesterday_download = $yesterday_download;
      $record->yesterday_upload = $yesterday_upload;
      $record->timestamp = $ts->format('Y-m-d H:i:s');

      $record->save();

      // generate graph
      $PYTHON_PATH = getenv('PYTHON_PATH');
      if ($PYTHON_PATH == FALSE) $PYTHON_PATH = '###PYTHON_PATH###/usr/bin/python3';
      else $PYTHON_PATH = $PYTHON_PATH . '/python3';

      $cmd = sprintf('%s ./script/graph/wimax.py 2>&1', $PYTHON_PATH);
      exec($cmd, $output);


      return _wimax($record);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/latest$/', $path)) {
    if ($method == 'GET') {
      $record = $table->order_by_desc('timestamp')->find_one();
      return _wimax($record);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/today\/first$/', $path)) {
    if ($method == 'GET') {
      $today = time();//+3600*9;

      $record = $table->where_like('timestamp', date('Y-m-d', $today).'%')->order_by_asc('timestamp')->find_one();
      if ($record != NULL) {
        return _wimax($record);
      }
      else {
        return _404();
      }
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/yesterday\/first$/', $path)) {
    if ($method == 'GET') {
      $today = time();//+3600*9;
      $yesterday = $today - 3600*24;

      $record = $table->where_like('timestamp', date('Y-m-d', $yesterday).'%')->order_by_asc('timestamp')->find_one();
      if ($record != NULL) {
        return _wimax($record);
      }
      else {
        return _404();
      }
    }
    else {
      return _405();
    }
  }
  else {
    return _404();
  }
