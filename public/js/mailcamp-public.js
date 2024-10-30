(function ($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
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

	$(window).on('load', function(scope) {
        var self = scope.mailCampForm = {};

        /**
         * Simple Sum Captcha
         * Uses two (unique id's) html elements, one for displaying the sum and one for fill in the result
         * example html: <span id="mailcamp-form-captcha-sum"></span>
         * <input id="mailcamp-form-captcha-field" type="text" name="" value=""  />
         *
         * Author: Silas de Rooy
         * @type {number}
         */
            // create sum
        var nr1 = Math.floor(Math.random() * (10 - 0 + 1) + 0);
        var nr2 = Math.floor(Math.random() * (10 - 0 + 1) + 0);
        // create sumfield var
        var sumfield = $('.mailcamp-form-captcha-sum');
        // create captcha field 1
        var captcha_val_1 = $('#captcha_val_1');
		captcha_val_1.val(nr1);
        // create captcha field 2
        var captcha_val_2 = $('#captcha_val_2');
		captcha_val_2.val(nr2);
        // create global submit btn var
        self.submit_btn = sumfield.closest('form').find('[type="submit"]');
        // disable form on pageload
        sumfield.closest('form').find('[type="submit"]').attr('disabled', true);
        // create styling for the disabled submit button
        self.submit_btn_disabled_style = '{"opacity": "0.5","cursor": "default"}';
        self.submit_btn_disabled_style = JSON.parse(self.submit_btn_disabled_style);
        // create styling for the enabled submit button
        self.submit_btn_enabled_style = '{"opacity": "1","cursor": "pointer"}';
        self.submit_btn_enabled_style = JSON.parse(self.submit_btn_enabled_style);
        // add disabled styling as default
        self.submit_btn.css(self.submit_btn_disabled_style);
        // add sum to field
        sumfield.text(nr1 + ' + ' + nr2 + ' = ');
        // create result to global scope
        self.result_sum = nr1 + nr2;
        // the function that enables, disables the submit button
        self.checkCaptchaField = function () {
            var elem = $(this);
            var result_agent = elem.val();

            if (self.result_sum == result_agent) {
                // disable form
                self.submit_btn.css(self.submit_btn_enabled_style).removeAttr('disabled');
            } else {
                // enable form
                self.submit_btn.css(self.submit_btn_disabled_style).attr('disabled', true);
            }
        };
        // trigger the checkCaptchaField function on change in answer field
        $(".mailcamp-form-captcha-field")
            .keyup(self.checkCaptchaField)
            .change(self.checkCaptchaField)
            .each(self.checkCaptchaField
            );

    });
})(jQuery);
