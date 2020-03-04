<?php
  $table = ORM::for_table('doorbell');
  $now = date('Y-m-d H:i:s');

  define('STATUS_REQUESTED', 'requested');
  define('STATUS_CANCELED', 'canceled');
  define('STATUS_RESPONSED', 'responsed');
  define('STATUS_DONE', 'done');

  function _pack_doorbell($record) {
    $d = [
      'id' => $record->id,
      'requester' => $record->requester,
      'responser' => $record->responser,
      'status' => $record->status,
      'requested_at' => $record->requested_at,
      'responsed_at' => $record->responsed_at,
      'done_at' => $record->done_at,
    ];
    return $d;
  }
  function _doorbell($record) {
    $d = _pack_doorbell($record);
    return _json($d);
  }
  function _doorbells($records) {
    $d = [];
    foreach ($records as $record) {
      $d[] = _pack_doorbell($record);
    }
    return _json($d);
  }

    if (preg_match('/^\/request$/', $path)) {
    if ($method == 'GET') {
      $records = ORM::for_table('doorbell')->where('status', STATUS_REQUESTED)->find_many();
      return _doorbells($records);
    }
    else if ($method == 'POST') {
      $requester = isset($_POST['requester']) ? $_POST['requester'] : NULL;

      $record = $table->create();
      $record->requester = $requester;
      $record->status = STATUS_REQUESTED;
      $record->requested_at = $now;

      $record->save();

      return _doorbell($record);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/request\/all$/', $path)) {
    if ($method == 'GET') {
      $records = ORM::for_table('doorbell')->find_many();
      return _doorbells($records);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/request\/(\\d+)$/', $path, $matches)) {
    $id = $matches[1];

    if ($method == 'GET') {
      $record = ORM::for_table('doorbell')->where('id', $id)->find_one();
      return _doorbell($record);
    }
    else {
      return _405();
    }
  }
  else if (preg_match('/^\/response$/', $path)) {
    if (!isset($_POST['id'])) return _500('Request ID is required');

    $id = $_POST['id'];
    $responser = isset($_POST['responser']) ? $_POST['responser'] : NULL;

    $record = $table->where('id', $id)->find_one();
    if ($record == NULL) return _500('No such request');

    $record->status = STATUS_RESPONSED;
    $record->responser = $responser;
    $record->responsed_at = $now;
    $record->save();

    return _doorbell($record);
  }
  else if (preg_match('/^\/cancel$/', $path)) {
    if (!isset($_POST['id'])) return _500('Request ID is required');

    $id = $_POST['id'];

    $record = $table->where('id', $id)->find_one();
    if ($record == NULL) return _500('No such request');

    $record->status = STATUS_CANCELED;
    $record->done_at = $now;
    $record->save();

    return _doorbell($record);
  }
  else if (preg_match('/^\/done$/', $path)) {
    if (!isset($_POST['id'])) return _500('Request ID is required');

    $id = $_POST['id'];

    $record = $table->where('id', $id)->find_one();
    if ($record == NULL) return _500('No such request');

    $record->status = STATUS_DONE;
    $record->done_at = $now;
    $record->save();

    return _doorbell($record);
  }
  else {
    return _404();
  }
