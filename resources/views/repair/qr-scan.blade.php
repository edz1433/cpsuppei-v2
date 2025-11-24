@extends('layouts.master')
@section('body')
<script src="https://unpkg.com/html5-qrcode"></script>
{{-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"> --}}
<style>
:root {
  --white: #ffffff;
  --offwhite: #fafafa;
  --light-gray: #f5f5f5;
  --dark-gray: #333333;
  --text-primary: #1a1a1a;
  --text-secondary: #666666;
  --accent-blue: #007bff;
  --success-green: #28a745;
  --error-red: #dc3545;
  --shadow-light: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-heavy: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: 'Inter', sans-serif;
  display: flex; flex-direction: column; justify-content: center; align-items: center;
  min-height: 100vh; background: var(--offwhite); color: var(--text-primary);
  overflow: hidden; padding: 20px 10px 120px 10px;
}
#qr-reader {
  width: 90%; max-width: 400px; aspect-ratio: 1/1; border-radius: 20px; overflow: hidden;
  box-shadow: var(--shadow-heavy); background-color: #000; margin-bottom: 20px;
  border: 2px solid var(--white);
  margin-left: 8px;
}
.tap-focus { margin-bottom: 15px; font-size: 14px; font-weight: 400; color: var(--text-secondary); text-align: center; }
.footer {
  text-align: center; margin-bottom: 15px; font-size: 11px; font-weight: 300; color: var(--text-secondary);
  flex-shrink: 0; position: fixed; bottom: 0; left: 0; right: 0;
  margin: 0 auto 15px auto; max-width: 400px; z-index: 1000;
}
.footer img { width: 50px; margin-top: 8px; filter: drop-shadow(var(--shadow-light)); cursor: pointer; transition: opacity 0.2s ease; }
.footer img:hover { opacity: 0.8; }
@media (max-width: 480px) {
  body { padding: 15px 5px 100px 5px; }
  #qr-reader { width: 95%; margin-bottom: 15px; }
  .tap-focus { font-size: 12px; margin-bottom: 10px; }
  .footer { font-size: 10px; margin-bottom: 10px; }
  .footer img { width: 60px; }
}
</style>

@if (session('success'))
<script>toastr.success('{{ session('success') }}');</script>
@endif
@if (session('error'))
<script>toastr.error('{{ session('error') }}');</script>
@endif

<div id="qr-reader"></div>
<div class="tap-focus">Tap to focus the camera</div>
<div class="footer">
  Maintained and Managed by the Management Information System (MIS)<br>
  <img src="{{ asset('uploads/mislogo.png') }}" alt="MIS Logo" onclick="showLogoutModal()" style="cursor: pointer;">
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-sign-out-alt me-2"></i>Confirm Logout</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body text-center">
        Are you sure you want to logout? Any unsaved changes will be lost.
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-danger" onclick="performLogout()">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Release Modal -->
<div class="modal fade" id="releaseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Confirm Release</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body text-center">
        Are you sure you want to release this item? This action cannot be undone.
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-success" onclick="performRelease()">
          <i class="fas fa-check me-1"></i>Release
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let pendingPropno = null;
let lastScanTime = 0;
function onScanSuccess(decodedText) {
  const now = Date.now();
  if (now - lastScanTime < 1500) return;
  lastScanTime = now;
  const url = "{{ route('qr-scan-check', ['propno' => '__PROPNO__']) }}".replace('__PROPNO__', encodeURIComponent(decodedText));
  fetch(url).then(r => r.json()).then(data => {
    if (data.status === 'error') return toastr.error(data.message || 'An error occurred while checking the item status.');
    if (['create', 'diagnose'].includes(data.status)) {
      return scanner.stop().then(() => { window.location.href = data.url; }).catch(() => { window.location.href = data.url; });
    }
    if (data.status === 'release') {
      return scanner.stop().then(() => { pendingPropno = decodedText; $('#releaseModal').modal('show'); }).catch(() => { pendingPropno = decodedText; $('#releaseModal').modal('show'); });
    }
    if (data.status === 'released') return toastr.warning(data.message || 'This item has already been released.');
    toastr.warning('Unknown status for this item. Please try again.');
  }).catch(err => { console.error('QR check failed:', err); toastr.error('Unable to check item status. Please try again.'); });
}
function onScanFailure(error) { console.log(`QR scan attempt failed: ${error}`); }
const config = { fps: 30, qrbox: { width: 300, height: 300 }, experimentalFeatures: { useBarCodeDetectorIfSupported: true }, rememberLastUsedCamera: true, aspectRatio: 1.0, supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] };
const scanner = new Html5Qrcode("qr-reader");
Html5Qrcode.getCameras().then(cameras => {
  if (cameras?.length) scanner.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure);
  else document.querySelector('.tap-focus').textContent = "No camera found on this device.";
}).catch(err => { console.error("Camera initialization failed:", err); document.querySelector('.tap-focus').textContent = "Unable to access camera. Please allow camera permissions."; });
function showLogoutModal() { $('#logoutModal').modal('show'); }
function performLogout() {
  const form = document.createElement('form'); form.method = 'POST'; form.action = '{{ route("logout") }}';
  const csrf = document.createElement('input'); csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
  form.appendChild(csrf); document.body.appendChild(form); form.submit();
}
function performRelease() {
  if (!pendingPropno) return toastr.error('No item selected for release.');
  const url = "{{ route('repairRelease', ['propno' => '__PROPNO__']) }}".replace('__PROPNO__', encodeURIComponent(pendingPropno));
  const form = document.createElement('form'); form.method = 'POST'; form.action = url;
  const csrf = document.createElement('input'); csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
  form.appendChild(csrf); document.body.appendChild(form); form.submit();
}
function restartScanner() {
  scanner.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure).catch(err => {
    console.error("Failed to restart scanner:", err);
    document.querySelector('.tap-focus').textContent = "Unable to restart camera. Please refresh the page.";
  });
}
$(document).ready(function() {
  $('#logoutModal').on('hidden.bs.modal', function() {
    pendingPropno = null;
  });
  $('#releaseModal').on('hidden.bs.modal', function() {
    restartScanner();
    pendingPropno = null;
  });
});
</script>
@endsection