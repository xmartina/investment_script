//[Preview Menu Javascript]

//Project:  edulearn - Responsive Admin Template
//Primary use:   This file is for demo purposes only.

$(function () {
  'use strict'


  /**
   * Get access to plugins
   */

  $('[data-toggle="control-sidebar"]').controlSidebar()
  $('[data-toggle="push-menu"]').pushMenu()

  var $pushMenu       = $('[data-toggle="push-menu"]').data('lte.pushmenu')
  var $controlSidebar = $('[data-toggle="control-sidebar"]').data('lte.controlsidebar')
  var $layout         = $('body').data('lte.layout')

  /**
   * List of all the available themes
   *
   * @type Array
   */
  var mySkins = [
    'theme-primary',
  'theme-secondary',
  'theme-info',
  'theme-success',
  'theme-danger',
  'theme-warning',
  ]

  /**
   * Get a prestored setting
   *
   * @param String name Name of of the setting
   * @returns String The value of the setting | null
   */
  function get(name) {
    if (typeof (Storage) !== 'undefined') {
      return localStorage.getItem(name)
    } else {
      window.alert('Please use a modern browser to properly view this template!')
    }
  }

  /**
   * Store a new settings in the browser
   *
   * @param String name Name of the setting
   * @param String val Value of the setting
   * @returns void
   */
  function store(name, val) {
    if (typeof (Storage) !== 'undefined') {
      localStorage.setItem(name, val)
    } else {
      window.alert('Please use a modern browser to properly view this template!')
    }
  }

  /**
   * Toggles layout classes
   *
   * @param String cls the layout class to toggle
   * @returns void
   */
  function changeLayout(cls) {
    $('body').toggleClass(cls)
    if ($('body').hasClass('fixed') && cls == 'fixed') {
      $pushMenu.expandOnHover()
      $layout.activate()
    }
    $controlSidebar.fix()
  }

  /**
   * Replaces the old skin with the new skin
   * @param String cls the new skin class
   * @returns Boolean false to prevent link's default action
   */
  function changeSkin(cls) {
    $.each(mySkins, function (i) {
      $('body').removeClass(mySkins[i])
    })

    $('body').addClass(cls)
    store('theme', cls)
    return false
  }

  /**
   * Retrieve default settings and apply them to the template
   *
   * @returns void
   */
  function setup() {
    var tmp = get('theme')
    if (tmp && $.inArray(tmp, mySkins))
      changeSkin(tmp)

    // Add the change skin listener
    $('[data-theme]').on('click', function (e) {
      if ($(this).hasClass('knob'))
        return
      e.preventDefault()
      changeSkin($(this).data('theme'))
    })

    // Add the layout manager
    $('[data-layout]').on('click', function () {
      changeLayout($(this).data('layout'))
    })

    $('[data-controlsidebar]').on('click', function () {
      changeLayout($(this).data('controlsidebar'))
      var slide = !$controlSidebar.options.slide

      $controlSidebar.options.slide = slide
      if (!slide)
        $('.control-sidebar').removeClass('control-sidebar-open')
    })


    $('[data-enable="expandOnHover"]').on('click', function () {
      $(this).attr('disabled', true)
      $pushMenu.expandOnHover()
      if (!$('body').hasClass('sidebar-collapse'))
        $('[data-layout="sidebar-collapse"]').click()
    })

    $('[data-enable="rtl"]').on('click', function () {
      $(this).attr('disabled', true)
      $pushMenu.expandOnHover()
      if (!$('body').hasClass('rtl'))
        $('[data-layout="rtl"]').click()
    })

    $('[data-mainsidebarskin="toggle"]').on('click', function () {
      var $sidebar = $('body')
      if ($sidebar.hasClass('dark-skin')) {
        $sidebar.removeClass('dark-skin')
        $sidebar.addClass('light-skin')
      } else {
        $sidebar.removeClass('light-skin')
        $sidebar.addClass('dark-skin')
      }
    })



    //  Reset options
    if ($('body').hasClass('fixed')) {
      $('[data-layout="fixed"]').attr('checked', 'checked')
    }
    if ($('body').hasClass('layout-boxed')) {
      $('[data-layout="layout-boxed"]').attr('checked', 'checked')
    }
    if ($('body').hasClass('sidebar-collapse')) {
      $('[data-layout="sidebar-collapse"]').attr('checked', 'checked')
    }
    if ($('body').hasClass('rtl')) {
      $('[data-layout="rtl"]').attr('checked', 'checked')
    }
   // if ($('body').hasClass('dark')) {
//      $('[data-layout="dark"]').attr('checked', 'checked')
//    }

  }

  // Create the new tab
  var $tabPane = $('<div />', {
    'id'   : 'control-sidebar-theme-demo-options-tab',
    'class': 'tab-pane active'
  })

  // Create the tab button
  var $tabButton = $('<li />', { 'class': 'nav-item' })
    .html('<a href=\'#control-sidebar-theme-demo-options-tab\' class=\'active\' data-bs-toggle=\'tab\' title=\'Setting\'>'
      + '<i class="mdi mdi-settings"></i>'
      + '</a>')

  // Add the tab button to the right sidebar tabs
  $('[href="#control-sidebar-home-tab"]')
    .parent()
    .before($tabButton)

  // Create the menu
  var $demoSettings = $('<div />')
  
  
  jQuery(function () {
    jQuery("#radio1").click(function () {
        jQuery('body').removeClass('light-skin');
        jQuery('body').addClass('dark-skin');
    });
    jQuery("#radio2").click(function () {
        jQuery('body').removeClass('dark-skin');
        jQuery('body').addClass('light-skin');
    });
  }); 
  
  // Layout options
  $demoSettings.append(
    '<h4 class="control-sidebar-heading p-0">'
    + '</h4>'
    + '<div class="mb-10 pb-10 bb-1 light-on-off">'
      + '<label class="control-sidebar-subheading mb-10">'
      + 'Light or Dark Skin'
      + '</label>'
      + '<label>'      
      + '<input type="radio" id="radio2" name="" />'
      + '<img class="model_img me-10" src="../images/light-layout.png"/>'
      + '</label>'      
      + '<label>'
      + '<input type="radio" id="radio1" name=""/>'
      + '<img class="model_img" src="../images/dark-layout.png"/>'
      + '</label>'  
    + '</div>'  
  )
  

  jQuery(function () {
    jQuery("#rtl-button").click(function () {
        jQuery('body').addClass('rtl');
    });
    jQuery("#ltr-button").click(function () {
        jQuery('body').removeClass('rtl');
    });
  }); 

 // Layout options
  $demoSettings.append(
    '<h4 class="control-sidebar-heading p-0">'
    + '</h4>'
    // rtl layout
    + '<div class="mb-10 pb-10 bb-1">'
      + '<label for="rtl" class="control-sidebar-subheading mb-10">'
        + 'Turn RTL/LTR'
      + '</label>'
      + '<label>'      
        + '<input type="radio" id="rtl-button" name="" />'
        + '<img class="model_img me-10" src="../images/rtl-layout.png"/>'
      + '</label>'      
      + '<label>'
        + '<input type="radio" id="ltr-button" name=""/>'
        + '<img class="model_img" src="../images/ltr-layout.png"/>'
      + '</label>'    
    + '</div>'
  )



  // + '<div>'
  // + '<label class="switch switch-border switch-danger">'
  // + '<input type="checkbox" data-layout="rtl" id="rtl">'
  // + '<span class="switch-indicator ltr-butt"></span>'
  // + '<span class="switch-description"></span>'
  // + '</label>'
  // + '</div>'
  // )
  // Layout options
  $demoSettings.append(
    '<h4 class="control-sidebar-heading p-0">'
    + '</h4>'
    // Sidebar Toggle
  + '<div class="flexbox mb-10">'
  + '<label for="toggle_sidebar" class="control-sidebar-subheading">'
    + 'Toggle Sidebar'
    + '</label>'
  + '<label class="switch switch-border switch-danger">'
  + '<input type="checkbox" data-layout="sidebar-collapse" id="toggle_sidebar">'
  + '<span class="switch-indicator"></span>'
  + '<span class="switch-description"></span>'
  + '</label>'
  + '</div>'  
    // Control Sidebar Toggle
  + '<div class="flexbox mb-10">'
  + '<label for="toggle_right_sidebar" class="control-sidebar-subheading">'
    + 'Toggle Right Sidebar Slide'
    + '</label>'
  + '<label class="switch switch-border switch-danger">'
  + '<input type="checkbox" data-controlsidebar="control-sidebar-open" id="toggle_right_sidebar">'
  + '<span class="switch-indicator"></span>'
  + '<span class="switch-description"></span>'
  + '</label>'
  + '</div>'    
  
  )
  
  var $skinsList = $('<ul />', { 'class': 'list-unstyled clearfix theme-switch' })

  // Dark sidebar skins
  var $themePrimary =
        $('<li />', { style: 'padding: 5px;' })
          .append('<a href="javascript:void(0)" data-theme="theme-primary" style="background: #0052cc; display: block;vertical-align: middle;" class="clearfix rounded w-p100 h-40 mb-5" title="Theme primary">'
            + '</a>')
  $skinsList.append($themePrimary)

  var $themeInfo =
        $('<li />', { style: 'padding: 5px;' })
          .append('<a href="javascript:void(0)" data-theme="theme-info" style="background: #00baff; display: block;vertical-align: middle;" class="clearfix rounded w-p100 h-40 mb-5" title="Theme info">'
            + '</a>')
  $skinsList.append($themeInfo)

  var $themeSuccess =
        $('<li />', { style: 'padding: 5px;' })
          .append('<a href="javascript:void(0)" data-theme="theme-success" style="background: #51ce8a; display: block;vertical-align: middle;" class="clearfix rounded w-p100 h-40 mb-5" title="Theme success">'
            + '</a>')
  $skinsList.append($themeSuccess)

  var $themeDanger =
        $('<li />', { style: 'padding: 5px;' })
          .append('<a href="javascript:void(0)" data-theme="theme-danger" style="background: #f2426d; display: block;vertical-align: middle;" class="clearfix rounded w-p100 h-40 mb-5" title="Theme danger">'
            + '</a>')
  $skinsList.append($themeDanger)

  var $themeWarning =
        $('<li />', { style: 'padding: 5px;' })
          .append('<a href="javascript:void(0)" data-theme="theme-warning" style="background: #fec801; display: block;vertical-align: middle;" class="clearfix rounded w-p100 h-40 mb-5" title="Theme warning">'
            + '</a>')
  $skinsList.append($themeWarning)  

  $demoSettings.append('<h4 class="control-sidebar-heading">Skin Colors</h4>')
  $demoSettings.append($skinsList)  

  $tabPane.append($demoSettings)
  $('#control-sidebar-home-tab').after($tabPane)

  setup()

  $('[data-toggle="tooltip"]').tooltip()
});// End of use strict

$(function () {
  'use strict'
  
  $('.theme-switch li a').click(function () {
    $('.theme-switch li a').removeClass('active').addClass('inactive');
    $(this).toggleClass('active inactive');
  });
  
});// End of use strict


// Sidebar pin method
(function () {
  const pinTitle = document.querySelector(".pin-title");
  let pinIcon = document.querySelectorAll(".sidebar-menu .fa-thumb-tack");
  function togglePinnedName() {
    if (document.getElementsByClassName("pined").length) {
      if (!pinTitle.classList.contains("show")) pinTitle.classList.add("show");
    } else {
      pinTitle.classList.remove("show");
    }
  }

  pinIcon.forEach((item, index) => {
    var linkName = item.parentNode.querySelector("span").innerHTML;
    var InitialLocalStorage = JSON.parse(localStorage.getItem("pins") || false);

    if (InitialLocalStorage && InitialLocalStorage.includes(linkName)) {
      item.parentNode.classList.add("pined");
    }
    item.addEventListener("click", (event) => {
      var localStoragePins = JSON.parse(localStorage.getItem("pins") || false);
      item.parentNode.classList.toggle("pined");

      if (localStoragePins?.length) {
        if (item.parentNode.classList.contains("pined")) {
          !localStoragePins?.includes(linkName) &&
            (localStoragePins = [...localStoragePins, linkName]);
        } else {
          localStoragePins?.includes(linkName) &&
            localStoragePins.splice(localStoragePins.indexOf(linkName), 1);
        }
        localStorage.setItem("pins", JSON.stringify(localStoragePins));
      } else {
        localStorage.setItem("pins", JSON.stringify([linkName]));
      }

      var elem = item;
      var topPos = elem.offsetTop;
      togglePinnedName();
      if (item.parentElement.parentElement.classList.contains("pined")) {
        scrollTo(
          document.getElementsByClassName("main-sidebar")[0],
          topPos - 30,
          600
        );
      } else {
        scrollTo(
          document.getElementsByClassName("main-sidebar")[0],
          elem.parentNode.offsetTop - 30,
          600
        );
      }
    });

    function scrollTo(element, to, duration) {
      var start = element.scrollTop,
        change = to - start,
        currentTime = 0,
        increment = 20;
      animateScroll();
    }
  });
  togglePinnedName();
})();

