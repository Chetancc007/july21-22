/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_ProductTableAttribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/prompt',
    'uiRegistry',
    'collapsable'
], function ($, alert, prompt, rg) {
    'use strict';

    return function (optionConfig) {
        var activePanelClass = 'selected-type-options',
            tableProduct = {
                frontendInput: $('#frontend_input'),
                isFilterable: $('#is_filterable'),
                isFilterableInSearch: $('#is_filterable_in_search'),
                backendType: $('#backend_type'),
                usedForSortBy: $('#used_for_sort_by'),
                frontendClass: $('#frontend_class'),
                isWysiwygEnabled: $('#is_wysiwyg_enabled'),
                isHtmlAllowedOnFront: $('#is_html_allowed_on_front'),
                isRequired: $('#is_required'),
                isUnique: $('#is_unique'),
                defaultValueText: $('#default_value_text'),
                defaultValueTextarea: $('#default_value_textarea'),
                defaultValueDate: $('#default_value_date'),
                defaultValueYesno: $('#default_value_yesno'),
                isGlobal: $('#is_global'),
                useProductImageForSwatch: $('#use_product_image_for_swatch'),
                updateProductPreviewImage: $('#update_product_preview_image'),
                usedInProductListing: $('#used_in_product_listing'),
                isVisibleOnFront: $('#is_visible_on_front'),
                position: $('#position'),
                attrTabsFront: $('#product_attribute_tabs_front'),

                /**
                 * @returns {*|jQuery|HTMLElement}
                 */
                get tabsFront() {
                    return this.attrTabsFront.length ? this.attrTabsFront.closest('li') : $('#front_fieldset-wrapper');
                },
                selectFields: ['table'],

                /**
                 * @this {swatchProductAttributes}
                 */
                toggleApplyVisibility: function (select) {
                    if ($(select).val() === 1) {
                        $(select).next('select').removeClass('no-display');
                        $(select).next('select').removeClass('ignore-validate');
                    } else {
                        $(select).next('select').addClass('no-display');
                        $(select).next('select').addClass('ignore-validate');
                        $(select).next('select option:selected').each(function () {
                            this.selected = false;
                        });
                    }
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                checkOptionsPanelVisibility: function () {
                    var tableOptionsPanel = $('#table-columns-options-panel');

                    this._hidePanel(tableOptionsPanel);

                    switch (this.frontendInput.val()) {
                        case 'table':
                            this._showPanel(tableOptionsPanel);
                            break;
                    }
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                bindAttributeInputType: function () {
                    this.checkOptionsPanelVisibility();
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                switchIsFilterable: function () {
                    if (this.isFilterable.selectedIndex === 0) {
                        this._disable(this.position);
                    } else {
                        this._enable(this.position);
                    }
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                switchDefaultValueField: function () {
                    var currentValue = this.frontendInput.val(),
                        defaultValueTextVisibility = false,
                        defaultValueTextareaVisibility = false,
                        defaultValueDateVisibility = false,
                        defaultValueYesnoVisibility = false,
                        scopeVisibility = true,
                        useProductImageForSwatch = false,
                        defaultValueUpdateImage = false,
                        optionDefaultInputType = '',
                        isFrontTabHidden = false,
                        thing = this;

                    if (!this.frontendInput.length) {
                        return;
                    }

                    switch (currentValue) {
                        case 'select':
                            optionDefaultInputType = 'radio';
                            break;

                        case 'multiselect':
                            optionDefaultInputType = 'checkbox';
                            break;

                        case 'date':
                            defaultValueDateVisibility = true;
                            break;

                        case 'boolean':
                            defaultValueYesnoVisibility = true;
                            break;

                        case 'textarea':
                        case 'texteditor':
                            defaultValueTextareaVisibility = true;
                            break;

                        case 'media_image':
                            defaultValueTextVisibility = false;
                            break;

                        case 'price':
                            scopeVisibility = false;
                            break;

                        case 'swatch_visual':
                            useProductImageForSwatch = true;
                            defaultValueUpdateImage = true;
                            defaultValueTextVisibility = false;
                            break;

                        case 'swatch_text':
                            useProductImageForSwatch = false;
                            defaultValueUpdateImage = true;
                            defaultValueTextVisibility = false;
                            break;
                        default:
                            defaultValueTextVisibility = true;
                            break;
                    }

                    delete optionConfig.hiddenFields['swatch_visual'];
                    delete optionConfig.hiddenFields['swatch_text'];

                    if (currentValue === 'media_image') {
                        this.tabsFront.hide();
                        this.setRowVisibility(this.isRequired, false);
                        this.setRowVisibility(this.isUnique, false);
                        this.setRowVisibility(this.frontendClass, false);
                    } else if (optionConfig.hiddenFields[currentValue]) {
                        $.each(optionConfig.hiddenFields[currentValue], function (key, option) {
                            switch (option) {
                                case '_front_fieldset':
                                    thing.tabsFront.hide();
                                    isFrontTabHidden = true;
                                    break;

                                case '_default_value':
                                    defaultValueTextVisibility = false;
                                    defaultValueTextareaVisibility = false;
                                    defaultValueDateVisibility = false;
                                    defaultValueYesnoVisibility = false;
                                    break;

                                case '_scope':
                                    scopeVisibility = false;
                                    break;
                                default:
                                    thing.setRowVisibility($('#' + option), false);
                            }
                        });

                        if (!isFrontTabHidden) {
                            thing.tabsFront.show();
                        }
                    } else {
                        this.tabsFront.show();
                        this.showDefaultRows();
                    }

                    this.setRowVisibility(this.defaultValueText, defaultValueTextVisibility);
                    this.setRowVisibility(this.defaultValueTextarea, defaultValueTextareaVisibility);
                    this.setRowVisibility(this.defaultValueDate, defaultValueDateVisibility);
                    this.setRowVisibility(this.defaultValueYesno, defaultValueYesnoVisibility);
                    this.setRowVisibility(this.isGlobal, scopeVisibility);

                    /* swatch attributes */
                    this.setRowVisibility(this.useProductImageForSwatch, useProductImageForSwatch);
                    this.setRowVisibility(this.updateProductPreviewImage, defaultValueUpdateImage);

                    $('input[name=\'default[]\']').each(function () {
                        $(this).attr('type', optionDefaultInputType);
                    });
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                showDefaultRows: function () {
                    this.setRowVisibility(this.isRequired, true);
                    this.setRowVisibility(this.isUnique, true);
                    this.setRowVisibility(this.frontendClass, true);
                },

                /**
                 * @param {Object} el
                 * @param {Boolean} isVisible
                 * @this {swatchProductAttributes}
                 */
                setRowVisibility: function (el, isVisible) {
                    if (isVisible) {
                        el.show();
                        el.closest('.field').show();
                    } else {
                        el.hide();
                        el.closest('.field').hide();
                    }
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _disable: function (el) {
                    el.attr('disabled', 'disabled');
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _enable: function (el) {
                    if (!el.attr('readonly')) {
                        el.removeAttr('disabled');
                    }
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _showPanel: function (el) {
                    el.closest('.fieldset').show();
                    el.addClass(activePanelClass);
                    this._render(el.attr('id'));
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _hidePanel: function (el) {
                    el.closest('.fieldset').hide();
                    el.removeClass(activePanelClass);
                },

                /**
                 * @param {String} id
                 * @this {swatchProductAttributes}
                 */
                _render: function (id) {
                    rg.get(id, function () {
                        $('#' + id).trigger('render');
                    });
                },

                /**
                 * @param {String} promptMessage
                 * @this {swatchProductAttributes}
                 */
                saveAttributeInNewSet: function (promptMessage) {

                    prompt({
                        content: promptMessage,
                        actions: {

                            /**
                             * @param {String} val
                             * @this {actions}
                             */
                            confirm: function (val) {
                                var rules = ['required-entry', 'validate-no-html-tags'],
                                    newAttributeSetNameInputId = $('#new_attribute_set_name'),
                                    editForm = $('#edit_form'),
                                    newAttributeSetName = val,
                                    i;

                                if (!newAttributeSetName) {
                                    return;
                                }

                                for (i = 0; i < rules.length; i++) {
                                    if (!$.validator.methods[rules[i]](newAttributeSetName)) {
                                        alert({
                                            content: $.validator.messages[rules[i]]
                                        });

                                        return;
                                    }
                                }

                                if (newAttributeSetNameInputId.length) {
                                    newAttributeSetNameInputId.val(newAttributeSetName);
                                } else {
                                    editForm.append(new Element('input', {
                                            type: 'hidden',
                                            id: newAttributeSetNameInputId,
                                            name: 'new_attribute_set_name',
                                            value: newAttributeSetName
                                        }));
                                }
                                // Temporary solution will replaced after refactoring of attributes functionality
                                editForm.triggerHandler('save');
                            }
                        }
                    });
                }
            };

        $(function () {
            var editForm = $('#edit_form'),
                tablePanel = $('#table-columns-options-panel'),
                tableBody = $(),
                activePanel = $();

            $('#frontend_input').bind('change', function () {
                tableProduct.bindAttributeInputType();
            });
            // $('#is_filterable').bind('change', function () {
            //     tableProduct.switchIsFilterable();
            // });

            tableProduct.bindAttributeInputType();

            // @todo: refactor collapsable component
            $('.attribute-popup .collapse, [data-role="advanced_fieldset-content"]')
                .collapsable()
                .collapse('hide');

            editForm.on('beforeSubmit', function () {
                var optionContainer, optionsValues;

                activePanel = tablePanel;
                optionContainer = activePanel.find('table tbody');

                if (activePanel.hasClass(activePanelClass)) {
                    optionsValues = $.map(
                        optionContainer.find('tr'),
                        function (row) {
                            return $(row).find('input, select, textarea').serialize();
                        }
                    );
                    $('<input>')
                        .attr({
                            type: 'hidden',
                            name: 'serialized_options'
                        })
                        .val(JSON.stringify(optionsValues))
                        .prependTo(editForm);
                }

                tableBody = optionContainer.detach();
            });

            editForm.on('afterValidate.error highlight.validate', function () {
                if (activePanel.hasClass(activePanelClass)) {
                    activePanel.find('table').append(tableBody);
                    $('input[name="serialized_options"]').remove();
                }
            });
        });

        window.saveAttributeInNewSet = tableProduct.saveAttributeInNewSet;
        window.toggleApplyVisibility = tableProduct.toggleApplyVisibility;
    };
});
