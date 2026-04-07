// show/hide reply form
(function () {
  var toggles = document.querySelectorAll('.comment-reply-toggle');
  for (var i = 0; i < toggles.length; i++) {
    toggles[i].addEventListener('click', function () {
      var btn = this;
      var panelId = btn.getAttribute('data-target');
      var panel = document.getElementById(panelId);
      if (!panel) {
        return;
      }
      var allPanels = document.querySelectorAll('.comment-reply-panel');
      for (var j = 0; j < allPanels.length; j++) {
        if (allPanels[j] !== panel) {
          allPanels[j].hidden = true;
        }
      }
      panel.hidden = !panel.hidden;
    });
  }

  var cancels = document.querySelectorAll('.comment-reply-cancel');
  for (var k = 0; k < cancels.length; k++) {
    cancels[k].addEventListener('click', function () {
      var box = this.closest('.comment-reply-panel');
      if (box) {
        box.hidden = true;
        var ta = box.querySelector('textarea');
        if (ta) {
          ta.value = '';
        }
      }
    });
  }
})();
