function moveTo( dest , source ){
    if( dest.children().length == 0 ){
            source.children().detach().appendTo( dest );
    }
}

function itemGalleryInit(){

    // init or reattach main image wrapper slideshow
    if ($('.product-image-wrapper .cycle-slideshow').data('cycle.opts') != undefined ){
        $('.product-image-wrapper .cycle-slideshow').cycle('destroy');
    }

    $('.product-image-wrapper .cycle-slideshow').cycle({
        slides: "> div",
        timeout: 0,
        autoHeight: false,
        log: false
    });


    // init or reattach main image caption slideshow
    if( $('.product-image-captions .cycle-slideshow').data('cycle.opts') != undefined ){
            $('.product-image-captions .cycle-slideshow').cycle('destroy');
    }
    
    $('.product-image-captions .cycle-slideshow').find('.product-img').each(function(){
        if($(this).is(':empty')){
                $(this).remove();
        }
    });
    $('.product-image-captions .cycle-slideshow').cycle({
        slides: "> div",
        timeout: 0,
        log: false,
        prev: ".product-image-captions .cycle-prev",
        next: ".product-image-captions .cycle-next",
        fx: "carousel",
        autoHeight: false,
        carouselVisible: 4,
        carouselFluid: true,
        allowWrap: false
    });


    // slideshow dependency
    var slideshows = $('.cycle-slideshow').on('cycle-next cycle-prev', function(e, opts) {
        slideshows.not(this).cycle('goto', opts.currSlide);
    });


    // slideshow captions
    $('.product-image-captions .cycle-slide').click(function(){
        var index = $('.product-image-captions .cycle-slideshow').data('cycle.API').getSlideIndex(this);
        slideshows.cycle('goto', index);
    });

    $('.ilightbox').iLightBox().destroy();
    
    // init lightboxa
    $('.ilightbox').iLightBox({
            skin: 'dark',
            overlay: { opacity: 0.5 },
            controls: {
                    arrows: true,
                    slideshow: true
            },
            thumbnails: {
                    normalOpacity: 1,
                    activeOpacity: 1
            },
            path: 'horizontal',
            text: {
                    close: 'Zamknij',
                    next: 'Następne',
                    previous: 'Poprzednie',
                    enterFullscreen: 'Przejdź do widoku pełnoekranowego',
                    exitFullscreen: 'Zamknij widok pełnoekranowy',
                    slideShow: 'Pokaz slajdów'
            }
    });
}


$(document).ready(function(){
	
    "use strict";

    // init bootstrap tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // init select2
    $('select[data-plugin="select2"]').select2({
            theme: "bootstrap"
    });

    // cookies info
    if( !Cookies.get('cookies-agree') ){
            $(".cookies-info").fadeIn( 500 );
    }

    $('body').on('click', '*[data-cookie-agree]', function(){
            $(".cookies-info").fadeOut( 500 );
            Cookies.set('cookies-agree', 'true', { expires: 365 });
    });

    itemGalleryInit();

});