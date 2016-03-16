define(['config', 'jquery', 'can', 'pace', 'rest-api',
  'components/loginModal', 'components/signUpModal', 'components/forgotPasswordModal', 'components/settingsModal',
  'bootstrap-select',
  'bootstrap'
], function (config, $, can, pace, restAPI, loginModal, singUpModal, forgotPasswordModal, settingsModal) {


  $(function () {
    pace.start({ajax: false});

    var loginValues = new can.Map(loginModal);
    var singUpValues = new can.Map(singUpModal);
    var forgotPasswordValues = new can.Map(forgotPasswordModal);
    var settingsValues = new can.Map(settingsModal);

    restAPI.addDomain(config.server.domain);

    restAPI.request('GET', '/api/check-login/', {},
      function() {
        loginValues.attr("notLoggedIn", false);
      }, function() {
        loginValues.attr("notLoggedIn", true);
        restAPI.logout();
      });

    var $modals = $('#spa-app-modals');
    $modals.append(can.view('assets/js/templates/loginModal.stache', new can.Map(loginValues)));
    $modals.append(can.view('assets/js/templates/signUpModal.stache', singUpValues));
    $modals.append(can.view('assets/js/templates/forgotPasswordModal.stache', forgotPasswordValues));
    $modals.append(can.view('assets/js/templates/settingsModal.stache', settingsValues));

    $('#spa-app').html(can.view('assets/js/templates/main.stache', loginValues));

    $('.selectpicker').selectpicker({style: 'btn-form btn-sm'});
  });
});
