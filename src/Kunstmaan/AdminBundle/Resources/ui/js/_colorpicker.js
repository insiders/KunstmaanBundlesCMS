var kunstmaanbundles = kunstmaanbundles || {};

kunstmaanbundles.colorpicker = (function(window, undefined) {

    var init, reInit, initColorpicker;

    init = reInit = function() {
        $('.js-colorpicker').each(function() {
            if(!$(this).hasClass('js-colorpicker--enabled')) {
                initColorpicker($(this));
            }
        });
    };


    // Initialize
    initColorpicker = function($el) {
        $el.addClass('js-colorpicker--enabled');
        $el.colorpicker({
            template: '<div class="colorpicker dropdown-menu">' +
                '<div class="colorpicker-saturation"><i><b></b></i></div>' +
                '<div class="colorpicker-hue"><i></i></div>' +
                '<div class="colorpicker-alpha"><i></i></div>' +
                '<div class="colorpicker-color"><div></div></div>' +
                '<div class="colorpicker-selectors"></div>' +
                '</div>',
        });
    };


    return {
        init: init,
        reInit: reInit
    };

})(window);
