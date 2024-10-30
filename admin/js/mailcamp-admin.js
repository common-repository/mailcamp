(function ($) {
    'use strict';
    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    // avoid the need for ctrl-click in a multi-select box
    // window.onmousedown = function (e) {
    //     var el = e.target;
    //     if (el.tagName.toLowerCase() == 'option' && el.parentNode.hasAttribute('multiple')) {
    //         e.preventDefault();
    //
    //         // toggle selection
    //         if (el.hasAttribute('selected')){
    //             el.removeAttribute('selected');
    // 	}  else {
    //         	el.setAttribute('selected', '');
    //         }
    //
    //         // hack to correct buggy behavior
    //         var select = el.parentNode.cloneNode(true);
    //         el.parentNode.parentNode.replaceChild(select, el.parentNode);
    //     }
    // }

    $(window).load(function () {
        // move the custom field option to the selected side
        $('body').on('click', '[data-deselected] option', function (e) {
            e.preventDefault();
            var elem = $(this);
            $('[data-selected]').append(elem);
            $('[data-selected] option').prop('selected', true);
            $('[data-deselected] option').prop('selected', false);
        });

        // move the custom field option to the selected side
        $('body').on('click', '[data-selected] option', function (e) {
            e.preventDefault();
            var elem = $(this);
            $('[data-deselected]').append(elem);
            $('[data-selected] option').prop('selected', true);
            $('[data-deselected] option').prop('selected', false);
        });

        $('body').on('click', '[data-copy]', function(e){
            e.preventDefault();
            var elem = $(this);
            var value = elem.data('copy');
            var temp = $("<input>");
            $("body").append(temp);
            temp.val(value).select();
            // copy the selection
            var succeed;
            try {
                succeed = document.execCommand("copy");
            } catch(e) {
                succeed = false;
            }
            temp.remove();
            if(succeed){
                console.log(elem);
                $('<p style="color: green" class="copy-result"><strong>Copied</strong></p>').insertAfter(elem);
            } else {
                $('<p style="color: red" class="copy-result"><strong>Could not copy</strong></p>').insertAfter(elem);
            }
            setTimeout( function(){
                // remove result
                $('.copy-result').remove();
            }  , 1000 );
        });
    });


    // window.onmousedown = function (e) {
    //     var el = e.target;
    //     if (el.tagName.toLowerCase() == 'option' && el.parentNode.hasAttribute('multiple')) {
    //         e.preventDefault();
    //
    //         // toggle selection
    //         if (el.hasAttribute('selected')) {
    //             el.removeAttribute('selected');
    //         } else {
    //             el.setAttribute('selected', '');
    //         }
    //
    //         // hack to correct buggy behavior
    //         var select = el.parentNode.cloneNode(true);
    //         el.parentNode.parentNode.replaceChild(select, el.parentNode);
    //     }
    // }

    $( "#rss-lists" )
        .change(function () {
            var str = "";
            $( "select option:selected" ).each(function() {
                str += $( this ).text() + " ";
            });
            console.log( str );
        })
        .change();

    $(function() {
        $('[name=\'mailcamp_options_wc[wc_signup_list]\']').change(function () {
            confirm('Are you sure you want to change the list? Any previous mapped custom fields could be lost.');
        });
    })

})(jQuery);