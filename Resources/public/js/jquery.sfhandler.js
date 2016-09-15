/******************************************
 *
 * jQuery plugin for toggling fields in symfony forms
 *
 * @author          Martin Schindler
 * @license         This jQuery Plugin is licensed under the MIT licenses.
 * @link            https://github.com/barbieswimcrew/symfony-form-ruleset-bundle
 * @version         1.0.0
 *
 ******************************************/

(function ($) {
    "use strict";//This strict text prevents certain actions from being taken and throws more exceptions
    $.fn.sfhandler = function (option, settings) {
        if (typeof option === 'object') {
            settings = option;
        }

        return this.each(function () {
            var $elem = $(this);
            var $settings = $.extend({}, $.fn.sfhandler.defaultSettings, settings || {});
            var main = new sfhandler($settings, $elem);
            main.init();
        });
    };

    /**
     * Default Parameter
     * @type {{trackPoints: null, wayPoints: null}}
     */
    $.fn.sfhandler.defaultSettings = {
        idSelector: "data-sfhandler-id",
        targetsSelectorShow: "data-sfhandler-targets-show",
        targetsSelectorHide: "data-sfhandler-targets-hide",
        hiddenClass: "hidden"
    };

    /**
     * Class definition
     * @param settings
     * @param $elem
     * @returns {sfhandler}
     */
    function sfhandler(settings, $elem) {
        this.settings = settings;
        this.$elem = $elem;
        return this;
    }

    sfhandler.prototype =
    {

        /**
         * Initialize the plugin
         */
        init: function () {
            var me = this;
            me.registerEvents();
        },

        /**
         * Registering user interaction events
         */
        registerEvents: function () {
            var me = this;

            // listening to radio click events
            me.radioClickEvent();

            // listening to selectbox change events
            me.selectChangeEvent();
        },

        /**
         * Event handling method on radio input click event
         */
        radioClickEvent: function () {
            var me = this;
            var selector = "*[" + me.settings.targetsSelectorShow + "],*[" + me.settings.targetsSelectorHide + "]";

            $(selector).on('click', document, function () {
                me.execute(this);
            });
        },

        /**
         * Event handling method on selectbox change event
         */
        selectChangeEvent: function () {
            var me = this;
            var selector = "option[" + me.settings.targetsSelectorShow + "],*[" + me.settings.targetsSelectorHide + "]";

            $(selector).parent('select').on('change', document, function () {
                var selected = $(this).find(':selected');
                me.execute(selected);
            });
        },

        /**
         * Wrapper method to define what has to be done on user interaction event
         * @param element
         */
        execute: function (element) {
            var me = this;
            var showFields = $(element).attr(me.settings.targetsSelectorShow);
            var hideFields = $(element).attr(me.settings.targetsSelectorHide);

            me.toggleElements(showFields, "show");
            me.toggleElements(hideFields, "hide");
        },

        /**
         * Method that will be used to toggle fields
         * @param data
         * @param type
         */
        toggleElements: function (data, type) {
            var me = this;

            if (typeof data === typeof undefined) {
                return;
            }

            var fields = data.split(',');

            $(fields).each(function () {
                var $elementSelector = $("*[" + me.settings.idSelector + "*='" + this + "']");
                var $labelSelector = $("label[for*='" + this + "']");
                if (type === "show") {
                    me.showElement($elementSelector);
                    me.showElement($labelSelector);
                }
                if (type === "hide") {
                    me.hideElement($elementSelector);
                    me.hideElement($labelSelector);
                }
            });
        },

        /**
         * Wrapper methopd to define how to show a field
         * in a way it could be overridden in a custom use case
         * @param $element
         */
        showElement: function ($element) {
            var me = this;
            $element.removeClass(me.settings.hiddenClass);
        },

        /**
         * Wrapper method to define how to hide a field
         * in a way it could be overridden in a custom use case
         * @param $element
         */
        hideElement: function ($element) {
            var me = this;
            if (!$element.hasClass(me.settings.hiddenClass)) {
                $element.addClass(me.settings.hiddenClass);
            }
        }
    }
})(jQuery);

$(document).sfhandler();