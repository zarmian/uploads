/*
	FlexNav.js 0.8

	Copyright 2013, Jason Weaver http://jasonweaver.name
	Released under http://unlicense.org/

//
*/

(function() {
  $.fn.flexNav = function(options) {
    var $nav, breakpoint, isDragging, nav_open, resizer, settings;
    settings = $.extend({
      'animationSpeed': 100
    }, options);
    $nav = $(this);
    nav_open = false;
    isDragging = false;
    $nav.find("li").each(function() {
      if ($(this).has("ul").length) {
        return $(this).addClass("item-with-ul").find("ul").hide();
      }
    });
    if ($nav.data('breakpoint')) {
      breakpoint = $nav.data('breakpoint');
    }
    resizer = function() {
      if ($(window).width() <= breakpoint) {
        $nav.removeClass("lg-screen").addClass("sm-screen");
        $('.one-page li a').on('click', function() {
          return $nav.removeClass('show');
        });
        return $('.item-with-ul').off();
      } else {
        $nav.removeClass("sm-screen").addClass("lg-screen");
        $nav.removeClass('show');
        return $('.item-with-ul').on('mouseenter', function() {
          return $(this).find('>ul').addClass('show').stop(true, true).slideDown(settings.animationSpeed);
        }).on('mouseleave', function() {
  
          return $(this).find('>ul').removeClass('show').stop(true, true).slideUp(settings.animationSpeed);
        });
      }
    };
    $('.item-with-ul, .menu-button').append('<span class="touch-button"></span>');
    $('.menu-button, .menu-button .touch-button').on('touchstart mousedown', function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log(isDragging);
      return $(this).on('touchmove mousemove', function(e) {
        var msg;
        msg = e.pageX;
        isDragging = true;
        return $(window).off("touchmove mousemove");
      });
    }).on('touchend mouseup', function(e) {
      var $parent;
      e.preventDefault();
      e.stopPropagation();
      isDragging = false;
      $parent = $(this).parent();
      if (isDragging === false) {
        console.log('clicked');
      }
      if (nav_open === false) {
        $nav.addClass('show');
        return nav_open = true;
      } else if (nav_open === true) {
        $nav.removeClass('show');
        return nav_open = false;
      }
    });

    $('.touch-button').on('touchstart mousedown', function(e) {
      e.stopPropagation();
      e.preventDefault();
      return $(this).on('touchmove mousemove', function(e) {
        isDragging = true;
        return $(window).off("touchmove mousemove");
      });
    }).on('touchend mouseup', function(e) {
      var $sub;
      e.preventDefault();
      e.stopPropagation();
      $sub = $(this).parent('.item-with-ul').find('>ul');
      if ($nav.hasClass('lg-screen') === true) {
        $(this).parent('.item-with-ul').siblings().find('ul.show').removeClass('show').hide();
      }
      if ($sub.hasClass('show') === true) {
        $sub.siblings('.touch-button').removeClass('submenu-open');
        return $sub.removeClass('show').slideUp(settings.animationSpeed);
      } else if ($sub.hasClass('show') === false) {
        $sub.siblings('.touch-button').addClass('submenu-open');
        return $sub.addClass('show').slideDown(settings.animationSpeed);
      }
    });
    $('.item-with-ul *').focus(function() {
      $(this).parent('.item-with-ul').parent().find(".open").not(this).removeClass("open").hide();
      return $(this).parent('.item-with-ul').find('>ul').addClass("open").show();
    });
    resizer();
    return $(window).on('resize', resizer);
  };

}).call(this);
