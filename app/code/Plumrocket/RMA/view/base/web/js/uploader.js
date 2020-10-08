/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

/*global byteConvert*/

define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery/file-uploader',
    'varien/js'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('prrma.uploader', {

        /**
         *
         * @private
         */
        _create: function () {
            var
                self = this,
                progressTmpl = mageTemplate('[data-template="' + this.options.fieldName + '-uploader"]');

            this.element.find('input[type=file]').fileupload({
                dataType: 'json',
                formData: {
                    'form_key': window.FORM_KEY
                },
                dropZone: '[data-tab-panel=image-management]',
                sequentialUploads: true,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: this.options.maxFileSize,

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                add: function (e, data) {
                    var
                        fileSize,
                        tmpl;

                    if (self.element.find('.file-row').size() >= self.options.maxFilesCount) {
                        alert({
                            content: $.mage.__('Maximum count of attached files is ' + self.options.maxFilesCount)
                        });
                        return false;
                    }

                    $.each(data.files, function (index, file) {
                        fileSize = typeof file.size == 'undefined' ?
                            $.mage.__('We could not detect a size.') :
                            byteConvert(file.size);

                        data.fileId = Math.random().toString(33).substr(2, 18);

                        tmpl = progressTmpl({
                            data: {
                                name: file.name,
                                size: fileSize,
                                id: data.fileId
                            }
                        });

                        $(tmpl).appendTo(self.element);
                    });

                    $(this).fileupload('process', data).done(function () {
                        data.submit();
                    });
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                done: function (e, data) {
                    var progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';
                    if (data.result && !data.result.error) {
                        $('#' + data.fileId).addClass('done');
                        $(progressSelector).removeClass('upload-progress').addClass('upload-success');
                        // self.element.find('#' + data.fileId + ' .file-delete').show();
                        self.element.find('#' + data.fileId + ' input.filename').val(data.result.file);
                        self.element.trigger('addItem', data.result);
                    } else {
                        var error = $.mage.__('We don\'t recognize or support this file extension type.');
                        if (-1 === data.result.errorcode && data.result.error) {
                            error = data.result.error;
                        }
                        alert({
                            content: error
                        });
                        // $(progressSelector).removeClass('upload-progress').addClass('upload-failure');
                        self.element.find('#' + data.fileId).remove();
                    }

                    // self.element.find('#' + data.fileId).remove();
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                progress: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10),
                        progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';

                    self.element.find(progressSelector).css('width', progress + '%');
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                fail: function (e, data) {
                    var progressSelector = '#' + data.fileId;

                    self.element.find(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                        .delay(2000)
                        .hide('highlight')
                        .remove();
                }
            });

            this.element.find('input[type=file]').fileupload('option', {
                process: [{
                    action: 'load',
                    fileTypes: /^image\/(gif|jpeg|png)$/
                }, {
                    action: 'resize',
                    maxWidth: this.options.maxWidth,
                    maxHeight: this.options.maxHeight
                }, {
                    action: 'save'
                }]
            });

            // File delete event.
            this.element.on('click', '.file-delete', function() {
                $(this).closest('.file-row').remove();
                return false;
            });

            // Autofill uploaded files after page refresh.
            if (self.options.fileList) {
                $.each(self.options.fileList, function(index, file) {
                    var tmpl = progressTmpl({
                        data: {
                            name: file.name,
                            size: file.size,
                            id: index,
                            filename: file.filename,
                            rowclass: 'done'
                        }
                    });

                    $(tmpl).appendTo(self.element);
                });
            }
        }
    });

    return $.prrma.uploader;
});
