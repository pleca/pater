//$(function () {
//    $("#menu-left ul.submenu li.active").parents('.submenu').css("display", "block");
//
//    var span = $("#menu-left > ul > li.active > a").find('span');
//    span.removeClass("glyphicon-menu-up");
//    span.addClass("glyphicon-menu-down");
//
//    var span = $("#menu-left li.active").parents('.submenu').siblings('a').children('span');
//    span.removeClass("glyphicon-menu-up");
//    span.addClass("glyphicon-menu-down");
//});
//
//$(function () {
//    $("#menu-top .dropdown-menu li.active").parents('.dropdown').addClass("active");
//});

function getSummary() {
    var method = 'getSummary';
    $.post(CMS_URL + "/ajax/basket.php", {method: method}, function (data) {
        $("#basket-price").html(data);
    });
}

function getMiniBasketList() {
    var method = 'getMiniBasketList';

    $.post(CMS_URL + "/ajax/basket.php", {method: method}, function (data) {
        $(".basket-list").html(data);               
    });
}

function getCost() {
    var delivery_service = $('input[name=delivery_service]:checked', '#basket-form').val();
    var payment = $('input[name=payment]:checked', '#basket-form').val();
    
    var method = 'getCost';
    $.post(CMS_URL + "/ajax/basket.php", {method: method, id: delivery_service, payment: payment}, function (data) {
        $("#basket-cost").html(data);
    });    
}

function getDiscount(delivery_service) {
    var delivery_service = $('input[name=delivery_service]:checked', '#basket-form').val();

    var method = 'getDiscount';
    $.post(CMS_URL + "/ajax/basket.php", {method:method, id: delivery_service}, function( data ) {
        $("#discount").html(data);
    });
}

function addProductToBasket(id, variation_id) {
    var qty = getProductQuantity();

    if (!qty) {
        var qty = 1;
    }

    var method = "add";
    $.post(CMS_URL + "/ajax/basket.php", {method: method, id: id, variation_id: variation_id, qty: qty}, function (data) {       
        $('#notify').html(data);        
        $('#notify-alert').show().fadeOut(3000);
    });    

    setTimeout(function(){   
        getMiniBasketList(); 
        getSummary();                       
    }, 100);   
}

function getProductQuantity() {
    var quantity = $("#quantityBasket").val();
    return quantity;
}

$(function () {
    var selectedSubmenu = $('#menuLeftList').find('.submenu li.active');
    selectedSubmenu.parent().addClass('in');
    selectedSubmenu.parent().parent().addClass('active');
});
        
//$(function () {
//    $('.basket-price').hover(
//        function () {
//            $('.basket-list').show();
//        },
//        function () {
//            $('.basket-list').hide();
//        }
//    );
//});


//$(function () {
//   $('.product-desc img').addClass('img-responsive'); 
//});
//
//$(function () {
//    $('li.menu-module[data-display-mode=2]').hover(
//        function () {
//            $(this).find('.nav-layer').show();
//            $('.overlay').show();
//        },
//        function () {
//            $(this).find('.nav-layer').hide();
//            $('.overlay').hide();
//        }
//    );
//});


