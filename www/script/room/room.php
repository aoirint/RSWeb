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
  <h2 class="my-4">Room Status</h2>

  <dl>
    <dt>明るさ（0 - 1023）
    <dd id="msg-light">
    <dt>室温（推定℃）
    <dd id="msg-temperature">
    <dt>データ取得時刻
    <dd id="msg-timestamp-ino">
  </dl>

  <p>
    <a href="/room/light">グラフ（明るさ）</a>
    <a href="/room/temperature">グラフ（室温）</a>

  <hr>

  <dl>
    <dt>今日までの3日間通信量（MB）
    <dd id="msg-today-download">
    <dt>昨日までの3日間通信量（MB）
    <dd id="msg-yesterday-download">
    <dt>今日1日の通信量（MB）
    <dd id="msg-just-today-download">
    <dt>昨日1日の通信量（MB）
    <dd id="msg-just-yesterday-download">
    <dt>一昨日1日の通信量（MB）
    <dd id="msg-just-yesteryesterday-download">
    <dt>データ取得時刻
    <dd id="msg-timestamp-wimax">
    <dt>WAN側IPアドレス
    <dd id="msg-wan-ip">
    <dt>データ取得時刻
    <dd id="msg-timestamp-wan">
  </dl>
  <p>
  <a href="/room/wimax">グラフ</a>

  <hr>

  <table id="msg-gpu" cellpadding="4">
    <tr>
      <th>PC
      <th>OS
      <th>Index
      <th>Name
      <th>Fan
      <th>Temp.
      <th>Perf. P0>P9
      <th>Pow. Curr.
      <th>Pow. Limit
      <th>Mem. Used
      <th>Mem. Total
      <th>Volat. Util
      <th>Timestamp
  </table>

    <p>
  <a href="/room/gputemp">グラフ（温度）</a>
  <a href="/room/gpumem">グラフ（メモリ）</a>

</div>



<script>
var API_URL = '/api';
var API_INO_URL = API_URL + '/sensor';
var API_INO_LATEST_URL = API_INO_URL + '/latest';
var API_WIMAX_URL = API_URL + '/wimax';
var API_WIMAX_LATEST_URL = API_WIMAX_URL + '/latest';
var API_WIMAX_TODAY_FIRST_URL = API_WIMAX_URL + '/today/first';
var API_WIMAX_YESTERDAY_FIRST_URL = API_WIMAX_URL + '/yesterday/first';
var API_GPU_URL = API_URL + '/gpu';
var API_GPU_LATEST_URL = API_GPU_URL + '/latest';
var API_WAN_URL = API_URL + '/wan';
var API_WAN_LATEST_URL = API_WAN_URL + '/latest';

var msgLight = $('#msg-light');
var msgTemperature = $('#msg-temperature');
var msgTimestampIno = $('#msg-timestamp-ino');

var msgTodayDownload = $('#msg-today-download');
var msgYesterdayDownload = $('#msg-yesterday-download');
var msgJustTodayDownload = $('#msg-just-today-download');
var msgJustYesterdayDownload = $('#msg-just-yesterday-download');
var msgJustYesterYesterdayDownload = $('#msg-just-yesteryesterday-download');
var msgTimestampWimax = $('#msg-timestamp-wimax');
var msgWanIp = $('#msg-wan-ip');
var msgTimestampWan = $('#msg-timestamp-wan');

var msgGpu = $('#msg-gpu');

// https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/Math/round
function round(number, precision) {
  var shift = function (number, precision, reverseShift) {
    if (reverseShift) {
      precision = -precision;
    }
    var numArray = ("" + number).split("e");
    return +(numArray[0] + "e" + (numArray[1] ? (+numArray[1] + precision) : precision));
  };
  return shift(Math.round(shift(number, precision, false)), precision, true);
}

function checkStatus() {
  $.get(API_INO_LATEST_URL).done(data => {
    var raw = data.temperature;
    var temp = ((raw/1023*5.0)-0.6)/0.01;

    var temp = round(temp - 11.3, 2);
    msgLight.text(data.light || '');
    msgTemperature.text(temp || '');
    msgTimestampIno.text(data.timestamp || '');

    console.log(data);
  }).fail(data => {
    console.log(data);
  });

  $.get(API_WIMAX_LATEST_URL).done(data => {
    var O_MB = 1000 * 1000;
    var today_dl = data.today_download / O_MB;
    var yesterday_dl = data.yesterday_download / O_MB;

    msgTodayDownload.text(round(today_dl, 2));
    msgYesterdayDownload.text(round(yesterday_dl, 2));
    msgTimestampWimax.text(data.timestamp || '');

    console.log(data);

    $.get(API_WIMAX_TODAY_FIRST_URL).done(tdata => {
      var tfirstDL = tdata.today_download / O_MB;
      var tdl = today_dl - tfirstDL;
      msgJustTodayDownload.text(round(tdl, 2));

      console.log(tdata);

      $.get(API_WIMAX_YESTERDAY_FIRST_URL).done(ydata => {
        var yfirstDL = ydata.today_download / O_MB;
        var ydl = yesterday_dl - yfirstDL;
        msgJustYesterdayDownload.text(round(ydl, 2));

        var yydl = today_dl - tdl - ydl;
        msgJustYesterYesterdayDownload.text(round(yydl, 2));

        console.log(ydata);
      }).fail(ydata => {
        console.log(ydata);
      });

    }).fail(tdata => {
      console.log(tdata);
    });
  }).fail(data => {
    console.log(data);
  });

  $.get(API_GPU_LATEST_URL).done(data => {
    msgGpu.find('.gpu-item').remove();

    for (var gpu of data) {
      msgGpu.append(
        $('<tr>').addClass('gpu-item').append(
          $('<td>').attr('datatype-gpu', 'pc').text(gpu.pc)
        ).append(
          $('<td>').attr('datatype-gpu', 'os').text(gpu.os)
        ).append(
          $('<td>').attr('datatype-gpu', 'index').text(gpu.index)
        ).append(
          $('<td>').attr('datatype-gpu', 'name').text(gpu.name)
        ).append(
          $('<td>').attr('datatype-gpu', 'fan.speed').text(gpu['fan.speed'])
        ).append(
          $('<td>').attr('datatype-gpu', 'temperature.gpu').text(gpu['temperature.gpu'] + 'C')
        ).append(
          $('<td>').attr('datatype-gpu', 'pstate').text(gpu.pstate)
        ).append(
          $('<td>').attr('datatype-gpu', 'power.draw').text(gpu['power.draw'])
        ).append(
          $('<td>').attr('datatype-gpu', 'power_limit').text(gpu['power.limit'])
        ).append(
          $('<td>').attr('datatype-gpu', 'memory.used').text(gpu['memory.used'])
        ).append(
          $('<td>').attr('datatype-gpu', 'memory.total').text(gpu['memory.total'])
        ).append(
          $('<td>').attr('datatype-gpu', 'utilization.gpu').text(gpu['utilization.gpu'])
        ).append(
          $('<td>').attr('datatype-gpu', 'gpi.timestamp').text(gpu.timestamp)
        )
      );
    }

    console.log(data);
  }).fail(data => {
    console.log(data);
  });

  $.get(API_WAN_LATEST_URL).done(data => {
    msgWanIp.text(data.wan_ip);
    msgTimestampWan.text(data.timestamp || '');
  }).fail(data => {
    console.log(data);
  });
}

checkStatus();
setInterval(checkStatus, 60 *1000 * 5);

</script>
