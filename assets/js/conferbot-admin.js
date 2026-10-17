/**
 * Conferbot settings page (Settings > Conferbot). Enqueued on that page only,
 * in the footer. "Try our demo bot" fills in the public demo bot ID and
 * switches the chat on.
 */
(function () {
  var demo = document.getElementById('cb-use-demo');
  if (!demo) { return; }
  demo.addEventListener('click', function (e) {
    e.preventDefault();
    var input = document.getElementById('cb-id');
    input.value = this.getAttribute('data-demo-id');
    var toggle = document.querySelector('.cb-switch input');
    if (toggle) { toggle.checked = true; }
    input.focus();
  });
})();
