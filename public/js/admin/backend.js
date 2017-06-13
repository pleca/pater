$(function () {
    $('li.dropdown').hover(
            function () {
                $(this).addClass("open");
            }, function () {
        $(this).removeClass("open");
    }
    );
});


function getOrderProduct(product_id, variation_id) {
    var method = 'getOrderProduct';

    $("div#ajaxDiv").slideDown("slow");

    $.post(CMS_URL + "/admin/ajax/order.php", {method: method, product_id: product_id, variation_id: variation_id}, function (data) {
        $("div#ajaxDiv").html(data).show();
    });
}
