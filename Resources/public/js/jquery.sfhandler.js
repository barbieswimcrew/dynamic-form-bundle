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
        targetsSelectorLocked: "data-sfhandler-locked",
        hiddenClass: "hidden",
        hasErrorClass: "has-error"
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
            var self = this;
            self.registerEvents();
        },

        /**
         * Registering user interaction events
         */
        registerEvents: function () {
            var self = this;

            // listening to radio click events
            self.radioClickEvent();

            // listening to checkbox click events
            self.checkboxClickEvent();

            // listening to selectbox change events
            self.selectChangeEvent();
        },

        /**
         * Event handling method on radio input click event
         */
        radioClickEvent: function () {
            var self = this;
            var selector = "input[type='radio'][" + self.settings.targetsSelectorShow + "],input[type='radio'][" + self.settings.targetsSelectorHide + "]";

            $(selector).on('click', document, function () {
                self.execute(this);
            });
        },

        /**
         * Event handling method on checkbox input click event
         */
        checkboxClickEvent: function () {
            var self = this;
            var selector = "input[type='checkbox'][" + self.settings.targetsSelectorShow + "],input[type='checkbox'][" + self.settings.targetsSelectorHide + "]";

            $(selector).on('click', document, function () {
                var fields = $(this).attr(self.settings.targetsSelectorShow) || $(this).attr(self.settings.targetsSelectorHide);
                if ($(this).is(':checked')) {
                    self.toggleElements(fields, "show");
                } else {
                    self.toggleElements(fields, "hide");
                }
            });
        },

        /**
         * Event handling method on selectbox change event
         */
        selectChangeEvent: function () {
            var self = this;
            var selector = "option[" + self.settings.targetsSelectorShow + "],*[" + self.settings.targetsSelectorHide + "]";

            $(selector).parent('select').on('change', document, function () {
                var selected = $(this).find(':selected');
                self.execute(selected);
            });
        },

        /**
         * Wrapper method to define what has to be done on user interaction event
         * @param element
         */
        execute: function (element) {
            var self = this;
            var id = $(element).attr(self.settings.idSelector);
            var showFields = $(element).attr(self.settings.targetsSelectorShow);
            var hideFields = $(element).attr(self.settings.targetsSelectorHide);

            self.toggleElements(id, showFields, "show");
            self.toggleElements(id, hideFields, "hide");
        },

        /**
         * Method that will be used to toggle fields
         * @param id
         * @param data
         * @param type
         */
        toggleElements: function (id, data, type) {
            var self = this;

            if (typeof data === typeof undefined) {
                return;
            }

            var fields = data.split(',');

            $(fields).each(function () {
                var $elementSelector = $("*[" + self.settings.idSelector + "*='" + this + "']");
                var $errorSelector = $elementSelector.parent("." + self.settings.hasErrorClass);
                var $labelSelector = $("label[for*='" + this + "']");
                var $parentLabelSelector = $elementSelector.parent('label');
                var $prevLabelSelector = $elementSelector.prev('label');

                if (type === "show") {
                    self.showElement(id, $elementSelector);
                    self.showElement(id, $labelSelector);
                    self.showElement(id, $parentLabelSelector);
                    self.showElement(id, $prevLabelSelector);
                    self.showElement(id, $errorSelector);
                }
                if (type === "hide") {
                    self.hideElement(id, $elementSelector);
                    self.hideElement(id, $labelSelector);
                    self.hideElement(id, $parentLabelSelector);
                    self.hideElement(id, $prevLabelSelector);
                    self.hideElement(id, $errorSelector);
                }
            });
        },

        /**
         * Wrapper method to define how to show a field
         * in a way it could be overridden in a custom use case
         * @param id
         * @param $element
         */
        showElement: function (id, $element) {
            var self = this;

            self._updateLockedAttribute(id, $element, "show");

            // don't show element if still locked by at least one other handler
            if(self._getLockedAttributes($element).length > 0){
                return;
            }

            $element.removeClass(self.settings.hiddenClass);
            $element.removeAttr('disabled');
            $element.trigger('show');
        },

        /**
         * Wrapper method to define how to hide a field
         * in a way it could be overridden in a custom use case
         * @param id
         * @param $element
         */
        hideElement: function (id, $element) {
            var self = this;
            if (!$element.hasClass(self.settings.hiddenClass)) {
                $element.addClass(self.settings.hiddenClass);
                $element.attr('disabled', 'disabled');
                $element.trigger('hide');
            }
            self._updateLockedAttribute(id, $element, "hide");
        },

        /**
         * Helper method to update the lockedBy element id's in the underlying form elements locked attribute
         * @param id
         * @param $element
         * @param type
         * @private
         */
        _updateLockedAttribute: function (id, $element, type) {
            var self = this;
            var lockedBy = self._getLockedAttributes($element);

            if (type === "show") {
                lockedBy = $.grep(lockedBy, function (tag) {
                    return tag !== id;
                });
            }
            if (type === "hide") {
                lockedBy.push(id);
                lockedBy = $.unique(lockedBy);
            }
            self._setLockedAttributes($element, lockedBy);
        },

        /**
         * Helper method to retrieve the lockedBy element id's from locked attribute
         * @param $element
         * @returns {Array}
         * @private
         */
        _getLockedAttributes: function ($element) {
            var self = this;
            var lockedString = $element.attr(self.settings.targetsSelectorLocked);
            if (typeof lockedString === "undefined" || lockedString.length == 0) {
                return [];
            } else {
                return lockedString.trim().split(',');
            }
        },

        /**
         * Helper method to redefine the lockedBy element id's in locked attribute
         * @param $element
         * @param lockedBy
         * @private
         */
        _setLockedAttributes: function ($element, lockedBy) {
            var self = this;
            $element.attr(self.settings.targetsSelectorLocked, lockedBy.join());
        }
    }
})(jQuery);

$(document).sfhandler();