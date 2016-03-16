//define(['config', 'jquery', 'can', 'pace', 'rest-api',
//  'components/loginModal', 'components/signUpModal', 'components/forgotPasswordModal', 'components/settingsModal',
//  'bootstrap-select',
//  'bootstrap'
//], function (config, $, can, pace, restAPI, loginModal, singUpModal, forgotPasswordModal, settingsModal) {

import config from 'config';
import $ from 'jquery';
import can from 'can';
import pace from 'pace';
import restAPI from 'rest-api';
import 'components/login-component/';
import loginModalVM from 'components/login-component/view-model';
import singUpModal from 'components/signUpModal';
import forgotPasswordModal from 'components/forgotPasswordModal';
import settingsModal from 'components/settingsModal';
import 'bootstrap-select';
import 'bootstrap';

  $(function () {
    pace.start({ajax: false});

    var loginValues = new loginModalVM;
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
    $modals.append(can.view.stache('<login-component></login-component>'));
    $modals.append(can.view('assets/js/templates/signUpModal.stache', singUpValues));
    $modals.append(can.view('assets/js/templates/forgotPasswordModal.stache', forgotPasswordValues));
    $modals.append(can.view('assets/js/templates/settingsModal.stache', settingsValues));

    $('#spa-app').html(can.view('assets/js/templates/main.stache', loginValues));

    $('.selectpicker').selectpicker({style: 'btn-form btn-sm'});
  });

//});
