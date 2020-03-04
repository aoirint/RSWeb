<?php
  $table = ORM::for_table('sensor');
  $now = date('Y-m-d H:i:s');

  function _pack_sensor($record) {
    $d = [
      'id' => $record->id,
      'light' => $record->light,
      'temperature' => $record->temperature,
      'timestamp' => $record->timestamp,
    ];
    return $d;
  }
  function _sensor($record) {
    $d = _pack_sensor($record);
    return _json($d);
  }
  function _sensors($records) {
    $d = [];
    foreach ($records as $record) {
      $d[] = _pack_sensor($record);
    }
    return _json($d);
  }

  if (preg_match('/^$/', $path)) {
    if ($method == 'GET') {
      $records = $table->find_many();
      return _sensors($records);
    }
    else if ($method == 'POST') {
      $light = isset($_POST['light']) ? $_POST['light'] : NULL;
      $temperature = isset($_POST['temperature']) ? $_POST['temperature'] : NULL;
      $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : NULL;
      $ts = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $timestamp);

      $record = $table->create();
      $record->light = $light;
      $record->temperature = $temperature;
      $record->timestamp = $ts->format('Y-m-d H:i:s');

      $record->save();

      $PYTHON_PATH = getenv('PYTHON_PATH');
      if ($PYTHON_PATH == FALSE) $PYTHON_PATH = '###PYTHON_PATH###/usr/bin/python3';
      else $PYTHON_PATH = $PYTHON_PATH . '/python3';

      $cmd = sprintf('%s ./script/graph/light.py 2>&1', $PYTHON_PATH);
      exec($cmd, $output);
      $cmd = sprintf('%s ./script/graph/temperature.py 2>&1', $PYTHON_PATH);
      exec($cmd, $output);

      return _sensor($record);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/latest$/', $path)) {
    if ($method == 'GET') {
      $record = $table->order_by_desc('timestamp')->find_one();
      return _sensor($record);
    }
    else {
      return _405();
    }
  }
  else {
    return _404();
  }
