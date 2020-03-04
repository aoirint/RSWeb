<!DOCTYPE html>
<meta charset="utf-8">
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

<script>
var bell = $('<audio>');
bell[0].preload = 'auto';
bell.append($('<source>').attr('src', '/static/bell.mp3'));
bell = bell[0];

bell.load()
bell.volume = 0;
bell.play();

function playBell() {
  bell.currentTime = 0.01;
  bell.volume = 1.0;

  bell.play();
}
</script>


<div class="container">
  <h2 class="my-4">Doorbell Watch</h2>
  <input type="hidden" name="token" value="<?= $token ?>">
  <div class="form-group">
    <label for="responser">名前</label>
    <input name="responser" value="anonymous" class="form-control">
  </div>

</div>

<div id="modal-doorbell"
  class="modal fade" tabindex="-1" role="dialog"
  aria-labelledby="modal-doorbell" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h3>リクエスト</h3>
      </div>
      <div class="modal-body">
        <table cellpadding="9">
          <tr><td>進行状況<td><span id="msg-progress"></span>
          <tr><td>依頼者<td><span id="msg-requester"></span>
          <tr><td>応答者<td><span id="msg-responser"></span>
        </table>

        <div class="row mt-4">
          <div class="col-6"><button id="btn-response" type="button"
              class="btn btn-success form-control">
              応答
          </button></div>
          <div class="col-6"><button id="btn-done" type="button"
              class="btn btn-primary form-control">
              完了
          </button></div>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
var API_URL = '/api/doorbell';
var API_REQUEST_URL = API_URL + '/request';
var API_RESPONSE_URL = API_URL + '/response';
var API_CANCEL_URL = API_URL + '/cancel';
var API_DONE_URL = API_URL + '/done';

var STATUS_REQUESTED = 'requested';
var STATUS_RESPONSED = 'responsed';
var STATUS_CANCELED = 'canceled';
var STATUS_DONE = 'done';

var inpResponser = $('input[name=responser]');

var modalDoorbell = $('#modal-doorbell');
var msgProgress = $('#msg-progress');
var msgRequester = $('#msg-requester');
var msgResponser = $('#msg-responser');
var btnResponse = $('#btn-response');

var token = $('input[name=token]').val();

var requestId = null;
var intervalId = null;
var waitCounter = 0;

inpResponser.val(Cookies.get('username') || 'anonymous');

inpResponser.on('change', function() {
  let responser = inpResponser.val();
  Cookies.set('username', responser, { 'expires': 365, });
});

function checkResponse() {
  if (requestId == null) return;

  waitCounter += 3;
  if (waitCounter > 60*10) {
    sendDone(); // automatically done
    return;
  }

  $.get(API_REQUEST_URL + '/' + requestId).done(data => {
    msgProgress.text('応答待機中');

    if (data.status != STATUS_REQUESTED) {
      msgProgress.text('応答済み');
      btnResponse.attr('disabled', true);
    }

    if (data.status == STATUS_DONE) {
      msgProgress.text('完了済み');
      //alert('リクエストが完了報告されました');

      requestId = null;
      modalDoorbell.modal('hide');
    }

    console.log(data);
  }).fail(data => {
    msgProgress.text('通信エラー');
    console.log(data);
  });
}
function stopChecking() {
  if (intervalId != null) clearInterval(intervalId);
}
function startChecking() {
  stopChecking();
  intervalId = setInterval(checkResponse, 3000);
}

btnResponse.on('click', function() {
  if (requestId == null) return ;
  let responser = inpResponser.val();

  msgProgress.text('応答中');
  stopChecking();

  $.post(API_RESPONSE_URL, {
    'token': token,
    'id': requestId,
    'responser': responser,
  }).done(data => {
    msgProgress.text('応答済み');
    msgResponser.text(data.responser);
    alert('リクエストに応答しました');

    btnResponse.attr('disabled', true);

    startChecking();
  }).fail(data => {
    msgProgress.text('応答失敗（通信エラー）');
    console.log(data);
    startChecking();
  });
});

function sendDone() {
  if (requestId == null) return ;
  let responser = inpResponser.val();

  msgProgress.text('完了報告中');
  stopChecking();

  $.post(API_DONE_URL, {
    'token': token,
    'id': requestId,
    'responser': '(auto) ' + responser,
  }).done(data => {
    msgProgress.text('完了報告済み');
    //alert('リクエストを完了報告しました');

    requestId = null;
    modalDoorbell.modal('hide');
  }).fail(data => {
    msgProgress.text('完了報告失敗（通信エラー）');
    console.log(data);
    startChecking();
  });
}

$('#btn-done').on('click', function() {
  sendDone();
});
$('#btn-ignore').on('click', function() {
  if (requestId == null) return ;

  stopChecking();
  requestId = null;
  modalDoorbell.modal('hide');
});

$(window).on('beforeunload', function() {
  if (requestId != null) {
    $.post(API_CANCEL_URL, {
      'token': token,
      'id': requestId,
    });
  }
});


function watchRequest() {
  if (requestId != null) return;

  $.get(API_REQUEST_URL).done(data => {
    let request = null;
    for (let d of data) {
      if (d.status == STATUS_REQUESTED) {
        request = d;
      }
    }

    if (request != null) {
      requestId = request.id;

      waitCounter = 0;
      msgProgress.text('発見');
      msgRequester.text(request.requester);
      msgResponser.text(request.responser || '');
      btnResponse.attr('disabled', false);

      modalDoorbell.modal({ 'backdrop': 'static', });

      playBell();
    }

    console.log(data);
  });
}
watchRequest();

setInterval(watchRequest, 20000);

</script>
