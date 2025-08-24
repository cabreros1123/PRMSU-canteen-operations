<div id="customConfirm" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:99999;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;">
  <div style="background:#fff;padding:32px 24px;border-radius:12px;max-width:340px;width:90vw;box-shadow:0 2px 16px #0004;text-align:center;position:relative;">
    <div id="customConfirmMsg" style="font-size:1.1em;margin-bottom:18px;"></div>
    <button id="customConfirmOk" class="btn btn-primary" style="min-width:80px;margin-right:12px;">OK</button>
    <button id="customConfirmCancel" class="btn btn-secondary" style="min-width:80px;">Cancel</button>
  </div>
</div>
<script>
window.customConfirm = function(msg, callback) {
    document.getElementById('customConfirmMsg').textContent = msg;
    document.getElementById('customConfirm').style.display = 'flex';
    window._customConfirmCallback = callback;
};
document.getElementById('customConfirmOk').onclick = function() {
    document.getElementById('customConfirm').style.display = 'none';
    if (window._customConfirmCallback) window._customConfirmCallback(true);
};
document.getElementById('customConfirmCancel').onclick = function() {
    document.getElementById('customConfirm').style.display = 'none';
    if (window._customConfirmCallback) window._customConfirmCallback(false);
};
</script>