// type 1 = bar, type 2 = Area, type 3 = line, type 4 = Donut, type 5 = table
// size 0 = half, size 1 = full
/* -------------------------------------------------------------------
 Global Variables
 ------------------------------------------------------------------- */
var defaults = {
  barDefaults: {
    barColors: ['#DA650E'],
    resize: true
  },
  areaDefaults: {
    fillOpacity: 0.5,
    lineColors: ['#41A69B'],
    pointFillColors:['#ffffff'],
    pointStrokeColors: ['black'],
    resize: true
  },
  lineDefaults: {
    lineColors: ['#41A69B'],
    pointFillColors:['#ffffff'],
    pointStrokeColors: ['black'],
    resize: true
  },
  donutDefaults: {
    colors: ['#66D654','#0F7000','#23B40C','#44C82F','#159102'],
    resize: true
  },
  dtDefaultsSm: {
    pageLength: 5
  },
  dtDefaults: {
    pageLength: 5
  }};
var rawData = [];
var minimizedPageData = [];
var minimizedPageDataIDs = [];
var pageData = [];
var panelOrder = [];
var chartDataAPI = [];

/* -------------------------------------------------------------------
 Jquery Add-on functions
 ------------------------------------------------------------------- */
jQuery.fn.exists = function(){return this.length>0;};
jQuery.fn.hasAttr = function(name,val){if(val){return $(this).attr(name) === val;}return $(this).attr(name) !== undefined;};

/* -------------------------------------------------------------------
 Handlebars Templates
 ------------------------------------------------------------------- */
var halfPanelSource = $("#half-panel").html();
var halfPanelTemplate = Handlebars.compile(halfPanelSource);
var fullPanelSource = $("#full-panel").html();
var fullPanelTemplate = Handlebars.compile(fullPanelSource);
var tableSource = $("#table").html();
var tableTemplate = Handlebars.compile(tableSource);
var menuItemSource = $("#menu-item").html();
var menuItemTemplate = Handlebars.compile(menuItemSource);
var topNavSource = $("#top-nav").html();
var topNovTemplate = Handlebars.compile(topNavSource);
var bottomNavSource = $("#bottom-nav").html();
var bottomNavTemplate = Handlebars.compile(bottomNavSource);

/* -------------------------------------------------------------------
 Handlebars Helpers
 ------------------------------------------------------------------- */
Handlebars.registerHelper('makeHeading', function(heading) {
  heading = heading.replace(/_/g, ' ');
  return heading[0].toUpperCase() + heading.slice(1);
});

/* -------------------------------------------------------------------
 On Document Ready
 ------------------------------------------------------------------- */
var $body = $('body');
var $wrapper = $('#wrapper');
RestfulAPI.addDomain('http://edc2.auto-cms.com');
jQuery(document).ready(function($) {

  loginSingUpTools.check_login();

  $body.on('click','#button_for_login', function() {
    loginSingUpTools.login();
  });

  $body.on('blur', '#user_create', function() {
    if ($(this).val() != '') loginSingUpTools.check_user_name($(this).val());
  });

  $body.on('blur', '#email_create', function() {
    if ($(this).val() != '') loginSingUpTools.check_email($(this).val());
  });

  $body.on('keyup', '#user_create', function() {
    $('#user_create_error').fadeOut();
  });

  $body.on('keyup', '#email_create', function() {
    $('#email_create_error').fadeOut();
  });

  $body.on('click','#button_for_sing-up', function() {
    loginSingUpTools.sign_up();
  });

  /* -------------------------------------------------------------------
   Dragula
   ------------------------------------------------------------------- */
  if ($('#dragula').exists() && !window.mobilecheck()) {
    var dragulaAPI = dragula([document.getElementById('dragula')], {ignoreInputTextSelection: true});
    dragulaAPI.on('dragend', getSavePanelOrder);
  }

  /* -------------------------------------------------------------------
   Minimize and Maximize Buttons
   ------------------------------------------------------------------- */
  $body.on('click', '.clickToShow', function(e) {
    e.preventDefault();
    var objID = $(this).data('id');
    panelOrder.push(objID);
    minimizedPageDataIDs.splice(minimizedPageDataIDs.indexOf(objID),1);
    rawData.forEach(function(key) {
      if (key.id == objID) {
        drawPanelAndData(key);
        $('#minimized-' + objID).remove();
      }
    });
    Cookies.set('panelOrder', panelOrder.toString());

    /* -------------------------------------------------------------------
     Collapse all menus on mobile when menu maximize is clicked
     ------------------------------------------------------------------- */
    $('.navbar-toggle:visible').each(function(){
      if (!$(this).hasClass('collapsed')) $(this).click();
    });
  });

  $body.on('click', '.clickToMinimize', function(e) {
    e.preventDefault();
    var objID = $(this).data('id');
    minimizedPageDataIDs.push(objID);
    panelOrder.splice(panelOrder.indexOf(objID),1);
    rawData.forEach(function(key) {
      if (key.id == objID) {
        drawMinimizeItem(key);
        $('#panel-' + objID).remove();
      }
    });
    Cookies.set('panelOrder', panelOrder.toString());
  });

});

/* -------------------------------------------------------------------
 Function To Load Pages
 ------------------------------------------------------------------- */
function load(page) {
  if (page == 'home') {

    $wrapper.append(topNovTemplate());
    $wrapper.prepend(bottomNavTemplate());

    $.getJSON("/assets/js/data.json", function(dataFromAjax) {
      if (typeof Cookies.get('panelOrder') !== 'undefined') {
        panelOrder = Cookies.get('panelOrder').split(',');
      } else {
        var rawOrder = [];
        dataFromAjax.forEach(function (key) {
          rawOrder.push(key.id);
        });
        Cookies.set('panelOrder', rawOrder.toString());
        panelOrder = rawOrder;
      }
      initData(dataFromAjax);
      rawData = dataFromAjax;
      setUpRefreshData();
    });

  } else if (page == 'dashboard') {
    $('#dragula').empty();
    $('#navbar-collapse').empty();
    $('#minimized-menu').empty();
  }
}

/* -------------------------------------------------------------------
 Function Initiate Data
 ------------------------------------------------------------------- */
function initData(data) {
  var pageOrderResult = [];

  panelOrder.forEach(function(key) {
    var found = false;
    data = data.filter(function(item) {
      if(!found && item.id == key) {
        pageOrderResult.push(item);
        found = true;
        return false;
      } else if (minimizedPageDataIDs.indexOf(item.id) == -1 && panelOrder.indexOf(item.id) == -1) {
        minimizedPageData.push(item);
        minimizedPageDataIDs.push(item.id);
        return true;
      } else {
        return true;
      }
    })
  });
  pageData = pageOrderResult;

  /* -------------------------------------------------------------------
   Add Panels for Morris JS Graphs/Charts and Data Tables
   ------------------------------------------------------------------- */
  pageData.forEach(function(key) {
    drawPanelAndData(key);
  });

  minimizedPageData.forEach(function(key) {
    drawMinimizeItem(key);
  });
}

/* -------------------------------------------------------------------
 Function Sets up data refresh rate
 ------------------------------------------------------------------- */
function setUpRefreshData() {
  rawData.forEach(function(key) {
    if (typeof key.refreshRate !== 'undefined') {
      console.log(key.refreshRate);
    }
  });
}

/* -------------------------------------------------------------------
 Function Draws Panels
 ------------------------------------------------------------------- */
function drawPanelAndData(key) {
  if (key.size == 0) {
    $("#dragula").append(halfPanelTemplate(key));
  } else {
    $("#dragula").append(fullPanelTemplate(key));
  }

  if (key.type == 1) {
    chartDataAPI[key.id] = Morris.Bar($.extend({}, defaults.barDefaults, key.data));
  } else if (key.type == 2) {
    chartDataAPI[key.id] = Morris.Area($.extend({}, defaults.areaDefaults, key.data));
  } else if (key.type == 3) {
    chartDataAPI[key.id] = Morris.Line($.extend({}, defaults.lineDefaults, key.data));
  } else if (key.type == 4) {
    chartDataAPI[key.id] = Morris.Donut($.extend({}, defaults.donutDefaults, key.data));
  } else if (key.type == 5) {
    $('#data-' + key.id).append(tableTemplate(key.data));
    $('#data-' + key.id + ' table').DataTable($.extend({}, defaults.dtDefaults, key.data));
  }
}

/* -------------------------------------------------------------------
 Function Minimized Menu Options
 ------------------------------------------------------------------- */
function drawMinimizeItem(key) {
  $("#minimized-menu").prepend(menuItemTemplate(key));
}

/* -------------------------------------------------------------------
 Function Gets current Panel Order and Saves it
 ------------------------------------------------------------------- */
function getSavePanelOrder() {
  panelOrder = [];
  $('[data-order-id]').each(function(e){
    panelOrder.push($(this).data('order-id'));
  });
  Cookies.set('panelOrder', panelOrder.toString());
}

/* -------------------------------------------------------------------
 Function Object for Login / SignUp / Forgot Password
 ------------------------------------------------------------------- */
var loginSingUpTools = {
  login: function() {
    var $loginError = $('#login-error');
    $loginError.fadeOut();
    var $secondFactor = $('#ask-for-second-factor');
    $secondFactor.fadeOut();
    if ($secondFactor.is(":visible")) {
      RestfulAPI.addSecondFactor($('#second_factor_login').val());
      RestfulAPI.request('GET', '/api/check-login/', {},
        function() {
          load('dashboard');
          $('#login-modal .close').click();
          if ($('#checkbox_share_login').prop('checked')) RestfulAPI.switchStoredDataLocally();
        }, function() {
          $loginError.fadeIn();
        });
    } else {
      RestfulAPI.addAuthUser($('#user_login').val());
      RestfulAPI.passwordToSecret($('#password_login').val());
      RestfulAPI.request('GET', '/api/initiate/', {},
        function(res, status, xhr){
          var newSecret = xhr.getResponseHeader('Auth-Secret');
          if (typeof newSecret !== 'undefined' && newSecret != '' && newSecret) RestfulAPI.addSecret(newSecret);

          var secondFactor = xhr.getResponseHeader('Auth-Second-Factor');
          if (typeof secondFactor !== 'undefined' && secondFactor != '' && secondFactor) {
            $secondFactor.fadeIn();
          } else {
            load('dashboard');
            $('#login-modal .close').click();
            if ($('#checkbox_share_login').prop('checked')) RestfulAPI.switchStoredDataLocally();
          }

          $loginError.fadeOut();
        },
        function(){
          $loginError.fadeIn();
        });
    }
  },
  check_login: function() {
    RestfulAPI.request('GET', '/api/check-login/', {},
      function() {
        load('dashboard');
      }, function() {
        load('home');
        RestfulAPI.logout();
      });
  },
  check_user_name: function(userName) {
    RestfulAPI.requestUnsigned('GET', '/api/check-username/'+userName+'/', {},
      function() {
        $('#user_create_error').fadeOut();
      }, function() {
        $('#user_create_error').fadeIn();
      });
  },
  check_email: function(email) {
    RestfulAPI.requestUnsigned('GET', '/api/check-email/'+email+'/', {},
      function() {
        $('#email_create_error').fadeOut();
      }, function() {
        $('#email_create_error').fadeIn();
      });
  },
  sign_up: function() {

  }
};