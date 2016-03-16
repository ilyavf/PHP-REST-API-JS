requirejs.config({
  baseUrl: 'assets/js',
  "paths": {
    "jquery": "../../node_modules/jquery/dist/jquery",
    "bootstrap": "../../node_modules/bootstrap/dist/js/bootstrap",
    "bootstrap-select": "../../node_modules/bootstrap-select/dist/js/bootstrap-select",
    "pace": "../../node_modules/pace/pace",

    "can": "../../node_modules/can/dist/amd/can",
    "i18n": "../../node_modules/i18n/i18n",
    "text": "../../node_modules/text/text",

    "detect-mobile": "detectmobilebrowser",
    "rest-api": "restfulapi",

    "cryptojs.core": "../../node_modules/cryptojslib/components/core",
    "cryptojs.x64": "../../node_modules/cryptojslib/components/x64-core",
    "cryptojs.sha512": "../../node_modules/cryptojslib/components/sha512",
    "cryptojs.base64": "../../node_modules/cryptojslib/components/enc-base64",
    "cryptojs.hmac": "../../node_modules/cryptojslib/components/hmac",
    "cryptojs.pbkdf2": "../../node_modules/cryptojslib/components/pbkdf2"
  },
  "shim": {
    "bootstrap": {
      "deps": ["jquery"]
    },
    "bootstrap-select": {
      "deps": ["jquery", "bootstrap"]
    },
    "morrisjs": {
      "deps": ["raphael"]
    },
    "datatables": {
      "deps": ["jquery"]
    },
    "cryptojs.core": {
      "exports": "CryptoJS"
    },
    "cryptojs.x64": {
      "deps": ["cryptojs.core"],
      "exports": "CryptoJS.x64"
    },
    "cryptojs.sha512": {
      "deps": ["cryptojs.core", "cryptojs.x64"],
      "exports": "CryptoJS.sha512"
    },
    "cryptojs.base64": {
      "deps": ["cryptojs.core"],
      "exports": "CryptoJS.base64"
    },
    "cryptojs.hmac": {
      "deps": ["cryptojs.core"],
      "exports": "CryptoJS.hmac"
    },
    "cryptojs.pbkdf2": {
      "deps": ["cryptojs.core", "cryptojs.hmac", "cryptojs.sha512"],
      "exports": "CryptoJS.pbkdf2"
    }
  },
  "config": {
    "i18n": {
      "locale": localStorage.getItem('locale')
    }
  }
});
require(['config', 'jquery', 'can', 'pace', 'rest-api', 'components/loginModal', 'components/signUpModal', 'components/forgotPasswordModal', 'components/settingsModal', 'bootstrap-select', 'can/view', 'can/map'], function (config, $, can, pace, restAPI, loginModal, singUpModal, forgotPasswordModal, settingsModal) {
  'use strict';

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
    $modals.append(can.view('templates/loginModal.stache', loginValues));
    $modals.append(can.view('templates/signUpModal.stache', singUpValues));
    $modals.append(can.view('templates/forgotPasswordModal.stache', forgotPasswordValues));
    $modals.append(can.view('templates/settingsModal.stache', settingsValues));

    $('#spa-app').html(can.view('templates/main.stache', loginValues));

    $('.selectpicker').selectpicker({style: 'btn-form btn-sm'});
  });
});
