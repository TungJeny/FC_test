$(function(){
  $(window).scroll(function() {    
    var scroll = $(window).scrollTop();

    if (scroll >= 80) {
      $('.email_buttons').addClass('fixed-buttons');
    } else {
      $('.email_buttons').removeClass('fixed-buttons');
    }
  });
});

$(document).ready(function () {

/////////////////////////////////////////////////////////////////////////////imported js piluku

      var nice = $(".left-bar").niceScroll(); 
      $('.menu-bar').click(function(e){ 
       e.preventDefault();                 
        $(".wrapper").toggleClass('mini-bar');        

        $(".left-bar").getNiceScroll().remove();
        setTimeout(function() {
          $(".left-bar").niceScroll();
        }, 200);
      }); 

    /*
      Uncomment if we need to expand the sidebar when hover
        $(".sales-bar .left-bar").hover(
           function() {
            $(".wrapper").removeClass('mini-bar');        
           },
           function() {
            $(".wrapper").addClass('mini-bar');        
           }
        ); 
    */

      $('.menu-bar-mobile').on('click', function (e) {        
        // $(this).addClass('menu_appear');
				// $(this).animate({width:'toggle'},350);
				
        $(".left-bar").getNiceScroll().remove();
        
        $( ".left-bar" ).toggleClass("menu_appear" );
        $( ".overlay" ).toggleClass("show");
        setTimeout(function() {
          $(".left-bar").niceScroll();
        }, 200);
      });

      $(".overlay").on('click',function(){

        $( ".left-bar" ).toggleClass("menu_appear" );
        $(this).removeClass("show");
      });

      $('.right-bar-toggle').on('click', function(e){
        e.preventDefault();
        $('.wrapper').toggleClass('right-bar-enabled');
      });

          $('ul.menu-parent').accordion();

      
      new WOW().init(); 

        $("#employee_current_location_id").select2({
                placeholder: "Select",
                 containerCssClass : "error" 
            });


$('.timer').each(count);
  
      /////////////////////////////////////////////////////////////////////////////imported js piluku



  // PANELS
    // panel close
        $('.panel-close').on('click', function (e) {
          e.preventDefault();
          $(this).parent().parent().parent().parent().addClass(' animated fadeOutDown');
        });


        $('.panel-minimize').on('click', function (e) 
        {
          e.preventDefault();
          var $target = $(this).parent().parent().parent().next('.panel-body');
          if ($target.is(':visible')) {
            $('i', $(this)).removeClass('ti-angle-up').addClass('ti-angle-down');
          } else {
            $('i', $(this)).removeClass('ti-angle-down').addClass('ti-angle-up');
          }
          $target.slideToggle();
        });
        
        
        $('.panel-refresh').on('click', function (e) 
        {
          e.preventDefault();
          // alert('vj');
          var $target = $(this).closest('.panel-heading').next('.panel-body');
          $target.mask('<i class="fa fa-refresh fa-spin"></i> Loading...');

          setTimeout(function () {
            $target.unmask();
          },
          1000);
        });


});

function count(options) 
    {
        var $this = $(this);
        options = $.extend({}, options || {}, $this.data('countToOptions') || {});
        $this.countTo(options);
    }

function convert_2_formdata(object) {
    const formData = new FormData();
    Object.keys(object).forEach(key => formData.append(key, object[key]));
    return formData;
}

function listview_get_selected_ids(prefix) {
    var viewCookieRaw = Cookies.get(prefix);
    var viewCookie = {};
    viewCookie.selected_ids = [];
    var selectedIds = [];
    if (typeof viewCookieRaw != 'undefined') {
        viewCookie = JSON.parse(viewCookieRaw);
    }
    
    if (typeof viewCookie.selected_ids != 'undefined') {
        selectedIds = viewCookie.selected_ids;
    }
    
    return selectedIds;
}

function listview_add_selected_id(prefix, id) {
    var viewCookieRaw = Cookies.get(prefix);
    var viewCookie = {};
    viewCookie.selected_ids = [];
    if (typeof viewCookieRaw != 'undefined') {
        viewCookie = JSON.parse(viewCookieRaw);
    }
    var selectedIds = viewCookie.selected_ids;
    if(selectedIds.indexOf(id) == -1) {
        viewCookie.selected_ids.push(id);
    }
    Cookies.set(prefix, JSON.stringify(viewCookie));
}

function listview_remove_ids_listview(prefix, ids) {
    var viewCookieRaw = Cookies.get(prefix);
    var viewCookie = {};
    viewCookie.selected_ids = [];
    if (typeof viewCookieRaw != 'undefined') {
        viewCookie = JSON.parse(viewCookieRaw);
    }
    var oldSelectedIds = viewCookie.selected_ids;
    viewCookie.selected_ids = array_difference(oldSelectedIds, ids);
    Cookies.set(prefix, JSON.stringify(viewCookie));
}

function listview_clear_all(prefix) {
    var viewCookie = {};
    viewCookie.selected_ids = [];
    Cookies.set(prefix, JSON.stringify(viewCookie));
}

function array_difference(a1, a2) {
    var result = [];
    for (var i = 0; i < a1.length; i++) {
        if (a2.indexOf(a1[i]) === -1) {
            result.push(a1[i]);
        }
    }
    return result;
}

function array_contains_another_array(needle, haystack){
    for(var i = 0; i < needle.length; i++){
        if(haystack.indexOf(needle[i]) === -1)
           return false;
    }
    return true;
}

function copy_object(obj){
    var nobj = {};
    for (let key in obj) {
        if (typeof obj[key] === 'object'){
            nobj[key] = copy_object(obj[key]);
        } else {
            nobj[key] = obj[key];
        }
    }
    return nobj;
}

function http_build_query(params) {
    if (typeof params === 'undefined' || typeof params !== 'object') {
        params = {};
        return params;
    }
    var query = '?';
    var index = 0;
    for (var i in params) {
        index++;
        var param = i;
        var value = params[i];
        if (index == 1) {
            query += param + '=' + value;
        } else {
            query += '&' + param + '=' + value;
        }
    }
    return query;
}

function fixscroll() {
//    document.getElementById("fixedcolumnbody").scrollLeft = 0;
//    document.getElementById("fixedcolumnbody").scrollTop = 0;
}

var prev_left = 0;
function contentscroll(mode) {
    var current_left = document.getElementById("contentbody-" + mode).scrollLeft;
    if (prev_left === current_left){
        document.getElementById("fixedcolumnbody-" + mode).scrollTop = document.getElementById("contentbody-" + mode).scrollTop;
    } else {
        document.getElementById("contenthead-" + mode).scrollLeft = document.getElementById("contentbody-" + mode).scrollLeft;
        prev_left = current_left;
    }
}

function correct_fixed_table(mode) {
    if ($('#'+ mode +' .fixed-column-outter').length == 0) {
        return;
    }
    var trWidth = $('#'+ mode +' .fixed-column-outter').width();
    var thNameWidth = trWidth;
    $('#'+ mode +' .fixed-column-outter table thead tr th:eq(0)').css({"width": thNameWidth});
    $('#'+ mode +' .fixed-column-outter table tbody tr td:eq(0)').css({"width": thNameWidth});
    
    $('#summary .table-outter').css({"top": ($('.summary_content_outter table').height() - getScrollbarWidth())});
    $('#summary .fixed-column-outter th').css({"height": ($('.summary_content_outter table').height() - getScrollbarWidth() - 2)});
}

function format_currentcy(n, currency) {
    return currency + " " + n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
}

function format_number(n) {
    return n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
}

function getScrollbarWidth() {
    var outer = document.createElement("div");
    outer.style.visibility = "hidden";
    outer.style.width = "100px";
    document.body.appendChild(outer);
    
    var widthNoScroll = outer.offsetWidth;
    // force scrollbars
    outer.style.overflow = "scroll";
    
    // add innerdiv
    var inner = document.createElement("div");
    inner.style.width = "100%";
    outer.appendChild(inner);        
    
    var widthWithScroll = inner.offsetWidth;
    
    // remove divs
    outer.parentNode.removeChild(outer);
    
    return widthNoScroll - widthWithScroll;
}

function show_page_loading() {
    $('#page_loader').removeClass('hide');
}

function hide_page_loading() {
    $('#page_loader').addClass('hide');
}