<?php
// move to API
/*
  $PYTHON_PATH = getenv('PYTHON_PATH');
  if ($PYTHON_PATH == FALSE) $PYTHON_PATH = '###PYTHON_PATH###/usr/bin/python3';
  else $PYTHON_PATH = $PYTHON_PATH . '/python3';

  $cmd = sprintf('%s ./script/graph/wimax.py 2>&1', $PYTHON_PATH);

  exec($cmd, $output);
*/
?>
<!DOCTYPE html>
<meta charset="utf-8">
<meta name="referrer" content="origin">
<script src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
<link rel="stylesheet"
  href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
  integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
  crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
  integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
  crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>

<div class="container">
  <h2 class="my-4">Room WiMAX</h2>
  <div id="graphs" style="text-align: center;">
  </div>

  <pre><?php print_r($output); ?></pre>
</div>

<script>
function formatDate(date) {
  var list = [];
  for (var item of [ date.getFullYear(), date.getMonth()+1, date.getDate(), ]) {
    var s = new String(item);
    if (s.length == 1) s = '0' + s;
    list.push(s);
  }
  return list.join('-')
}
function formatTime(date) {
  var list = [];
  for (var item of [ date.getHours(), date.getMinutes(), date.getSeconds(), ]) {
    var s = new String(item);
    if (s.length == 1) s = '0' + s;
    list.push(s);
  }
  return list.join(':');
}
function formatDatetime(date) {
  return formatDate(date) + ' ' + formatTime(date);
}

var today = new Date(Date.now());

var box = $('#graphs');
for (var i=0; i<7; i++) {
  var date = new Date(today - 1000*3600*24*i);
  var dateString = formatDate(date);

  var img = $('<img>');
  img.addClass('graph');
  img[0].src = '/static/graph/wimax/' + dateString + '.png';

  box.append(img);
}

</script>
