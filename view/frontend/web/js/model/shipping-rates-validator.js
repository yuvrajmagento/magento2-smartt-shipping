/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/postcode-validator',
    'Magento_Checkout/js/model/default-validator',
    'mage/translate',
    'uiRegistry',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry'
], function (
    $,
    ko,
    shippingRatesValidationRules,
    addressConverter,
    selectShippingAddress,
    postcodeValidator,
    defaultValidator,
    $t,
    uiRegistry,
    formPopUpState,
    quote,
    rateRegistry
) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        validators = [],
        observedElements = [],
        postcodeElement = null,
        postcodeElementName = 'postcode',
        cityElement = null,
        cityElementName = 'city',
        telephoneElement = null,
        telephoneElementName = 'telephone',
        regionidElement = null,
        regionidElementName = 'region_id',
        countryElement = null,
        countryElementName = 'country_id';

    validators.push(defaultValidator);

    return {
        validateAddressTimeout: 0,
        validateDelay: 2000,

        /**
         * @param {String} carrier
         * @param {Object} validator
         */
        registerValidator: function (carrier, validator) {
            if (checkoutConfig.activeCarriers.indexOf(carrier) !== -1) {
                validators.push(validator);
            }
        },

        /**
         * @param {Object} address
         * @return {Boolean}
         */
        validateAddressData: function (address) {
            return validators.some(function (validator) {
                return validator.validate(address);
            });
        },

        /**
         * Perform postponed binding for fieldset elements
         *
         * @param {String} formPath
         */
        initFields: function (formPath) {
            var self = this,
                elements = shippingRatesValidationRules.getObservableFields();
            
            
            if ($.inArray(postcodeElementName, elements) === -1) {
                // Add postcode field to observables if not exist for zip code validation support
                elements.push(postcodeElementName);
            }
            if ($.inArray(cityElementName, elements) === -1) {
                // Add postcode field to observables if not exist for zip code validation support
                elements.push(cityElementName);
            }
            if ($.inArray(telephoneElementName, elements) === -1) {
                // Add postcode field to observables if not exist for zip code validation support
                elements.push(telephoneElementName);
            }
            if ($.inArray(regionidElementName, elements) === -1) {
                // Add postcode field to observables if not exist for zip code validation support
                elements.push(regionidElementName);
            }
            if ($.inArray(countryElementName, elements) === -1) {
                // Add postcode field to observables if not exist for zip code validation support
                elements.push(countryElementName);
            }
            

            $.each(elements, function (index, field) {
                uiRegistry.async(formPath + '.' + field)(self.doElementBinding.bind(self));
            });
        },

        /**
         * Bind shipping rates request to form element
         *
         * @param {Object} element
         * @param {Boolean} force
         * @param {Number} delay
         */
        doElementBinding: function (element, force, delay) {
            var observableFields = shippingRatesValidationRules.getObservableFields();

            if (element && (observableFields.indexOf(element.index) !== -1 || force)) {
                if (element.index !== postcodeElementName) {
                    this.bindHandler(element, delay);
                }
            }

            if (element.index === postcodeElementName) {
                this.bindHandler(element, delay);
                postcodeElement = element;
            }
            if (element.index === cityElementName) {
                this.bindHandler(element, delay);
                cityElement = element;
            }
            if (element.index === telephoneElementName) {
                this.bindHandler(element, delay);
                telephoneElement = element;
            }
            if (element.index === regionidElementName) {
                this.bindHandler(element, delay);
                regionidElement = element;
            }
            if (element.index === countryElementName) {
                this.bindHandler(element, delay);
                countryElement = element;
            }
        },

        /**
         * @param {*} elements
         * @param {Boolean} force
         * @param {Number} delay
         */
        bindChangeHandlers: function (elements, force, delay) {
            var self = this;

            $.each(elements, function (index, elem) {
                self.doElementBinding(elem, force, delay);
            });
        },

        /**
         * @param {Object} element
         * @param {Number} delay
         */
        bindHandler: function (element, delay) {
            var self = this;
            delay = typeof delay === 'undefined' ? self.validateDelay : delay;

            if (element.component.indexOf('/group') !== -1) {
                $.each(element.elems(), function (index, elem) {
                    self.bindHandler(elem);
                });
            } else {
                element.on('value', function () {
                    if (!formPopUpState.isVisible()) {
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function () {
                            self.postcodeValidation();
                            self.validateFields();
                            self.cityValidation();
                        }, delay);
                    }
                });
                observedElements.push(element);
            }
        },

        /**
         *  city validation
         */
        cityValidation: function () {
            var cityfield = $('[name="city"]').val();
            if (cityfield=='') {
                return true;
            }
            var telephonefield = $('[name="telephone"]').val();
            if (telephonefield=='') {
                return true;
            }
            var country_id = $('[name="country_id"]').val();
            if (country_id=='') {
                return true;
            }
            var address = quote.shippingAddress();
            /*address.trigger_reload = new Date().getTime();*/
            rateRegistry.set(address.getKey(), null);
            rateRegistry.set(address.getCacheKey(), null);
            quote.shippingAddress(address);
        },

        /**
         * @return {*}
         */
        postcodeValidation: function () {
            var countryId = $('select[name="country_id"]').val(),
                validationResult,
                warnMessage;

            if (postcodeElement == null || postcodeElement.value() == null) {
                return true;
            }

            postcodeElement.warn(null);
            validationResult = postcodeValidator.validate(postcodeElement.value(), countryId);

            if (!validationResult) {
                warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

                if (postcodeValidator.validatedPostCodeExample.length) {
                    warnMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                }
                warnMessage += $t('If you believe it is the right one you can ignore this notice.');
                postcodeElement.warn(warnMessage);
            }

            return validationResult;
        },

        /**
         * Convert form data to quote address and validate fields for shipping rates
         */
        validateFields: function () {
            var addressFlat = addressConverter.formDataProviderToFlatData(
                this.collectObservedData(),
                'shippingAddress'
            ),
                address;
            if (this.validateAddressData(addressFlat)) {
                addressFlat = uiRegistry.get('checkoutProvider').shippingAddress;
                address = addressConverter.formAddressDataToQuoteAddress(addressFlat);
                selectShippingAddress(address);
            }
        },

        /**
         * Collect observed fields data to object
         *
         * @returns {*}
         */
        collectObservedData: function () {
            var observedValues = {};
            $.each(observedElements, function (index, field) {
                observedValues[field.dataScope] = field.value();
            });

            return observedValues;
        }
    };
});
