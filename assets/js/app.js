requirejs.config({
  baseUrl: 'assets/js',
  "paths": {
    "jquery": "../lib/jquery/jquery-2.1.4.min",
    "can": "../lib/canjs/can",
    "cookies": "../lib/jscookie/js.cookie",
    "bootstrap": "../lib/bootstrap/js/bootstrap",
    "bootstrap-select": "../lib/bootstrap-select/js/bootstrap-select",
    "pace": "../lib/pace/pace.min",
    "i18n": "../lib/i18n/i18n",
    "data-tables": "../lib/data-tables/datatables",
    "morris": "../lib/morrisjs/morris",
    "raphael": "../lib/raphael/raphael",
    "gragula": "../lib/dragula/dragula",
    "detect-mobile": "../lib/detectmobile/detectmobilebrowser",
    "text": "../lib/text/text",
    "rest-api": "../lib/rest-api-js/restfulapi",
    "cryptojs.core": "../lib/rest-api-js/core",
    "cryptojs.x64": "../lib/rest-api-js/x64-core",
    "cryptojs.sha512": "../lib/rest-api-js/sha512",
    "cryptojs.base64": "../lib/rest-api-js/enc-base64",
    "cryptojs.hmac": "../lib/rest-api-js/hmac",
    "cryptojs.pbkdf2": "../lib/rest-api-js/pbkdf2"
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
require(['config', 'jquery', 'can', 'pace', 'rest-api', 'components/loginModal', 'components/signUpModal', 'components/forgotPasswordModal', 'components/settingsModal', 'bootstrap-select', 'can/view/stache', 'can/map'], function (config, $, can, pace, restAPI, loginModal, singUpModal, forgotPasswordModal, settingsModal) {
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
