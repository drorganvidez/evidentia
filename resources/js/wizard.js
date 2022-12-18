//
// wizard.js
// Dashkit module
//

import { Tab } from 'bootstrap';

const toggles = document.querySelectorAll('[data-toggle="wizard"]');

toggles.forEach((toggle) => {
  const tab = new Tab(toggle);
  const panes = toggle.closest('.tab-content').querySelectorAll('.tab-pane');

  toggle.addEventListener('click', function (e) {
    e.preventDefault();

    // Hide all tabs
    panes.forEach((tab) => {
      tab.classList.remove('active');
    });

    // Toggle new tab
    tab.show();

    // Remove active state
    toggle.classList.remove('active');
  });
});
