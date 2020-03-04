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


<div class="container">
  <h2 class="my-4">Doorbell Request</h2>
  <input type="hidden" name="token" value="<?= $token ?>">
  <div class="form-group">
    <label for="requester">名前</label>
    <input name="requester" value="anonymous" class="form-control">
  </div>
  <button id="btn-request" type="button" class="btn btn-primary form-control">
    呼び鈴を鳴らす
  </button>
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
          <div class="col-8"><button id="btn-done" type="button"
              class="btn btn-primary form-control">
              完了
          </button></div>
          <div class="col-4"><button id="btn-cancel" type="button"
              class="btn btn-secondary form-control">
              キャンセル
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

var inpRequester = $('input[name=requester]');

var modalDoorbell = $('#modal-doorbell');
var msgProgress = $('#msg-progress');
var msgRequester = $('#msg-requester');
var msgResponser = $('#msg-responser');

var token = $('input[name=token]').val();

var requestId = null;
var intervalId = null;

inpRequester.val(Cookies.get('username') || 'anonymous');

function checkResponse() {
  if (requestId == null) return;

  $.get(API_REQUEST_URL + '/' + requestId).done(data => {
    msgResponser.text(data.responser || '');

    if (data.status == STATUS_REQUESTED) {
      msgProgress.text('送信済み（応答待機中）');
    }
    else if (data.status == STATUS_RESPONSED) {
      msgProgress.text('応答あり');
    }
    else if (data.status == STATUS_CANCELED) {
      msgProgress.text('キャンセル済み');
      alert('リクエストがキャンセルされました');

      requestId = null;
      modalDoorbell.modal('hide');
    }
    else if (data.status == STATUS_DONE) {
      msgProgress.text('完了済み');
      alert('リクエストが完了報告されました');

      requestId = null;
      modalDoorbell.modal('hide');
    }

    console.log(data);
  }).fail(data => {
    msgProgress.text('送信済み（応答待機中：通信エラー）');
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

inpRequester.on('change', function() {
  let requester = inpRequester.val();
  Cookies.set('username', requester, { 'expires': 365, });
});

$('#btn-request').on('click', function() {
  let requester = inpRequester.val();
  Cookies.set('username', requester, { 'expires': 365, });

  msgProgress.text('送信中');
  msgRequester.text(requester);
  msgResponser.text('');

  modalDoorbell.modal({ 'backdrop': 'static', });

  $.post(API_REQUEST_URL, {
    'token': token,
    'requester': requester,
  }).done(data => {
    msgProgress.text('送信済み（応答待機中）');

    requestId = data.id;
    startChecking();

    console.log(data);
  }).fail(data => {
    msgProgress.text('送信失敗（通信エラー）');
    console.log(data);
  });
});

$('#btn-done').on('click', function() {
  if (requestId == null) return ;

  msgProgress.text('完了報告中');
  stopChecking();

  $.post(API_DONE_URL, {
    'token': token,
    'id': requestId,
  }).done(data => {
    msgProgress.text('完了報告済み');
    alert('リクエストを完了報告しました');

    requestId = null;
    modalDoorbell.modal('hide');
  }).fail(data => {
    msgProgress.text('完了報告失敗（通信エラー）');
    console.log(data);
    startChecking();
  });
});

$('#btn-cancel').on('click', function() {
  if (requestId == null) return ;

  msgProgress.text('キャンセル中');
  stopChecking();

  $.post(API_CANCEL_URL, {
    'token': token,
    'id': requestId,
  }).done(data => {
    msgProgress.text('キャンセル済み');
    alert('リクエストをキャンセルしました');

    requestId = null;
    modalDoorbell.modal('hide');
  }).fail(data => {
    msgProgress.text('キャンセル失敗（通信エラー）');
    console.log(data);
    startChecking();
  });
});

$(window).on('beforeunload', function() {
  if (requestId != null) {
    $.post(API_CANCEL_URL, {
      'token': token,
      'id': requestId,
    });
  }
});
</script>
