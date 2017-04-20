/**
 * Plugin boilerplate by http://lab.abhinayrathore.com/jquery-standards/#Plugins
 */
(function($) {

    /**
     * add the plugin to the jQuery.fn object
     * @param options
     * @returns {*}
     * @constructor
     */
    $.fn.FormToggler = function(options) {
        return this.each(function() {
            if (undefined === $(this).data('FormToggler')) {
                var plugin = new $.FormToggler(this, options);
                $(this).data('FormToggler', plugin);
            }
        });
    };

    /**
     * FormToggler plugin for dynamic-form-bundle
     * @param element
     * @param options
     * @constructor
     */
    $.FormToggler = function(element, options) {

        var defaults = {
            foo: 'bar',
            attributes: {
                id: "data-sfhandler-id",
                show: "data-sfhandler-targets-show",
                hide: "data-sfhandler-targets-hide",
                locked: "data-sfhandler-locked"
            },
            classes: {
                error: 'has-error',
                hidden: 'hidden'
            },
            selectors: {
                radio: 'input[type=radio]',
                checkbox: 'input[type=checkbox]',
                select: 'select'
            }
        };

        var plugin = this;
        plugin.settings = {};

        /**
         * constructor method
         */
        plugin.init = function() {
            plugin.settings = $.extend({}, defaults, options);
            registerEvents();
        };

        /**
         * Register all UI events
         * @private
         */
        var registerEvents = function() {
            var $body = $('body');

            // register clicking radio button
            $body.on('click', plugin.settings.selectors.radio, onClickRadioButton);

            // register clicking checkbox
            $body.on('click', plugin.settings.selectors.checkbox, onClickCheckbox);

            // register changing selectbox
            $body.on('change', plugin.settings.selectors.select, onChangeSelectBox);

        };

        /**
         * Method to handle radio button click event
         * @private
         */
        var onClickRadioButton = function() {
            // console.log('clicked radio button...');

            var id = $(this).attr(plugin.settings.attributes.id);
            var show = $(this).attr(plugin.settings.attributes.show);
            var hide = $(this).attr(plugin.settings.attributes.hide);

            toggle(id, hide, false);
            toggle(id, show, true);
        };

        /**
         * Method to handle checkbox click event
         * @private
         */
        var onClickCheckbox = function() {
            // console.log('clicked checkbox...');

            var id = $(this).attr(plugin.settings.attributes.id);
            var fields = $(this).attr(plugin.settings.attributes.show) || $(this).attr(plugin.settings.attributes.hide);

            toggle(id, fields, $(this).is(':checked'));
        };

        /**
         * Method to handle selectbox change event
         * @private
         */
        var onChangeSelectBox = function() {
            console.log('changed selectbox...');

            //@todo resette alle options
            //@todo verarbeite alle selected options
            // var retval = [];
            // $('option:selected', this).each(function(){
            //     retval .push($(this).attr('data-sfhandler-targets-show'));
            // });
            // console.log(retval);
        };

        /**
         * Method that will be used to toggle fields
         * Runs over all given fields and shows/hides them
         * @param id
         * @param data
         * @param show boolean
         * @private
         */
        var toggle = function(id, data, show) {

            if (typeof data === typeof undefined) return;

            var method = (show === true) ? "showFields" : "hideFields";

            $(data.split(',')).each(function() {
                var $element = $("*[" + plugin.settings.attributes.id + "*='" + this + "']");
                eval(method + "(id, $element)");
            });
        };

        /**
         * Wrapper method to define how to show a field
         * in a way it could be overridden in a custom use case
         * @param id
         * @param $element
         * @private
         */
        var showFields = function(id, $element) {

            updateLocked(id, $element, true);

            // don't show element if still locked by at least one other handler
            if (getLocked($element).length > 0) return;

            $element.removeClass(plugin.settings.classes.hidden);
            $element.removeAttr('disabled');
            $element.trigger('show');
        };

        /**
         * Wrapper method to define how to hide a field
         * in a way it could be overridden in a custom use case
         * @param id
         * @param $element
         * @private
         */
        var hideFields = function(id, $element) {
            if (!$element.hasClass(plugin.settings.classes.hidden)) {
                $element.addClass(plugin.settings.classes.hidden);
                $element.attr('disabled', 'disabled');
                $element.trigger('hide');
            }
            updateLocked(id, $element, false);
        };

        /**
         * Helper method to update the lockedBy element id's in the underlying form elements locked attribute
         * @param id
         * @param $element
         * @param show boolean
         * @private
         */
        var updateLocked = function(id, $element, show) {
            var lockedBy = getLocked($element);

            if (show === true) {
                lockedBy = $.grep(lockedBy, function(tag) {
                    return tag !== id;
                });
            } else {
                lockedBy.push(id);
                lockedBy = $.unique(lockedBy);
            }

            setLocked($element, lockedBy);
        };

        /**
         * Helper method to retrieve the lockedBy element id's from locked attribute
         * @param $element
         * @returns {Array}
         * @private
         */
        var getLocked = function($element) {
            var lockedString = $element.attr(plugin.settings.attributes.locked);
            var lockedFields = [];

            if (typeof lockedString !== "undefined" && lockedString.length > 0) {
                lockedFields = lockedString.trim().split(',');
            }

            return lockedFields;
        };

        /**
         * Helper method to redefine the lockedBy element id's in locked attribute
         * @param $element
         * @param lockedBy
         * @private
         */
        var setLocked = function($element, lockedBy) {
            $element.attr(plugin.settings.attributes.locked, lockedBy.join());
        };

        /**
         * Call the constructor
         */
        plugin.init();

    };

})(jQuery);

$('body').FormToggler();