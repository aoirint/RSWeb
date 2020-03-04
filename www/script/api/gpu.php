<?php
  $table = ORM::for_table('gpu');
  $now = date('Y-m-d H:i:s');

  function _pack_gpu($record) {

    $d = [
      'id' => $record->id,
      'pc' => $record->pc,
      'os' => $record->os,
      'index' => $record->index,
      'name' => $record->name,
      'fan.speed' => $record->fan_speed,
      'temperature.gpu' => $record->temperature_gpu,
      'pstate' => $record->pstate,
      'power.draw' => $record->power_draw,
      'power.limit' => $record->power_limit,
      'memory.used' => $record->memory_used,
      'memory.total' => $record->memory_total,
      'utilization.gpu' => $record->utilization_gpu,
      'timestamp' => $record->timestamp,
    ];
    return $d;
  }
  function _gpu($record) {
    $d = _pack_gpu($record);
    return _json($d);
  }
  function _gpus($records) {
    $d = [];
    foreach ($records as $record) {
      $d[] = _pack_gpu($record);
    }
    return _json($d);
  }

  if (preg_match('/^$/', $path)) {
    if ($method == 'GET') {
      $records = $table->find_many();
      return _gpus($records);
    }
    else if ($method == 'POST') {
      $js = isset($_POST['data']) ? $_POST['data'] : NULL;
      if ($js == NULL) {
          return _500('no data');
      }

      $data = json_decode($js, TRUE);
      $gpus = $data['gpus'];

      $records = [];
      foreach ($gpus as $gpu) {
        $timestamp = $gpu['timestamp'];
        $ts = DateTime::createFromFormat('Y/m/d H:i:s.u', $timestamp);

        $record = $table->create();
        $record->pc = $gpu['pc'];
        $record->os = $gpu['os'];
        $record->index = $gpu['index'];
        $record->name = $gpu['name'];
        $record->fan_speed = $gpu['fan.speed'];
        $record->temperature_gpu = $gpu['temperature.gpu'];
        $record->pstate = $gpu['pstate'];
        $record->power_draw = $gpu['power.draw'];
        $record->power_limit = $gpu['power.limit'];
        $record->memory_used = $gpu['memory.used'];
        $record->memory_total = $gpu['memory.total'];
        $record->utilization_gpu = $gpu['utilization.gpu'];

        $record->timestamp = $ts->format('Y-m-d H:i:s');

        $record->save();
        $records[] = $record;
      }

      // generate graph
      $PYTHON_PATH = getenv('PYTHON_PATH');
      if ($PYTHON_PATH == FALSE) $PYTHON_PATH = '###PYTHON_PATH###/usr/bin/python3';
      else $PYTHON_PATH = $PYTHON_PATH . '/python3';

      $cmd = sprintf('%s ./script/graph/gputemp.py 2>&1', $PYTHON_PATH);
      exec($cmd, $output);

      $cmd = sprintf('%s ./script/graph/gpumem.py 2>&1', $PYTHON_PATH);
      exec($cmd, $output);

      return _gpus($records);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/latest$/', $path)) {
    if ($method == 'GET') {
      $gpus = $table->distinct()->select('index')->find_many();

      $records = [];
      foreach ($gpus as $gpu) {
        $record = $table->where('index', $gpu->index)->order_by_desc('timestamp')->find_one();
        $records[] = $record;
      }

      return _gpus($records);
    }
    else {
      return _405();
    }
  }
  else {
    return _404();
  }
