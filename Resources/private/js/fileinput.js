import Sortable from 'sortablejs';
import 'bootstrap-fileinput';
import 'bootstrap-fileinput/themes/fa/theme';
import 'bootstrap-fileinput/js/locales/fr';
import 'bootstrap/js/modal';

(function ($, Sortable) {
    function handleFileinputs(container) {
        var inputs = container.querySelectorAll('input[type=file].fileinput');
        for (var i = 0; i < inputs.length; i++) {
            handleFileinput(inputs.item(i));
        }
    }

    function handleFileinput(input) {
        var maxFileSize = input.dataset.maxFileSize;
        var deletedFiles = document.getElementById(input.dataset.delete);
        var deletedFileIndexes = [];

        var layoutTemplates = {};
        layoutTemplates.footer = '<div class="file-thumbnail-footer">';


        var isSortable = input.dataset.position && input.multiple;

        if (input.dataset.legend) {
            layoutTemplates.footer +=  '<div class="input-group"><input type="text" data-type="legend" data-name="' + input.dataset.legend + (input.multiple ? '[_dataKey_]' : '') + '" placeholder="Name" class="form-control">{actions}</div>';
            layoutTemplates.actions = '<div class="input-group-btn">{zoom} {delete}</div>';
            layoutTemplates.actionDelete = '<button type="button" class="kv-file-remove btn btn-sm btn-warning" title="{removeTitle}"{dataKey}>{removeIcon}</button>';
        }

        if (isSortable) {
            layoutTemplates.footer += '<input type="hidden" data-type="position" data-name="' + input.dataset.position + '[_dataKey_]">';
        }

        layoutTemplates.footer += '</div>';

        var files = JSON.parse(input.dataset.files);
        var initialPreviewConfig = files.map(function (file) {
            return {key: file.id.toString(), position: file.position, name: file.name};
        });
        var initialPreview = files.map(function (file) {
            return '<img src="' + file.path + '" class="file-preview-image kv-preview-data"/>';
        });

        var wrapper, dropZone, sortable = null;

        var $fileinput = $(input)
            .fileinput({
                theme: 'fa',
                language: input.dataset.locale,
                initialPreview: initialPreview,
                initialPreviewConfig: initialPreviewConfig,
                maxFileSize: maxFileSize,
                initialPreviewCount: true,
                fileActionSettings: {
                    showUpload: false,
                    showRemove: true,
                    showPreview: true,
                    showDrag: false
                },
                showUpload: false,
                showRemove: input.multiple,
                validateInitialCount: true,
                overwriteInitial: !input.multiple,
                layoutTemplates: layoutTemplates,
                dropZoneEnabled: input.dataset.dropZone,
                previewFileIconSettings: {
                    doc: '<i class="fa fa-file-word-o text-primary"></i>',
                    xls: '<i class="fa fa-file-excel-o text-success"></i>',
                    ppt: '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                    jpg: '<i class="fa fa-file-photo-o text-warning"></i>',
                    pdf: '<i class="fa fa-file-pdf-o text-danger"></i>',
                    zip: '<i class="fa fa-file-archive-o text-muted"></i>',
                    htm: '<i class="fa fa-file-code-o text-info"></i>',
                    txt: '<i class="fa fa-file-text-o text-info"></i>',
                    mov: '<i class="fa fa-file-movie-o text-warning"></i>',
                    mp3: '<i class="fa fa-file-audio-o text-warning"></i>',
                },
                previewFileExtSettings: {
                    doc: function (ext) { return ext.match(/(doc|docx)$/i); },
                    xls: function (ext) { return ext.match(/(xls|xlsx)$/i); },
                    ppt: function (ext) { return ext.match(/(ppt|pptx)$/i); },
                    jpg: function (ext) { return ext.match(/(jp?g|png|gif|bmp)$/i); },
                    zip: function (ext) { return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i); },
                    htm: function (ext) { return ext.match(/(php|js|css|htm|html)$/i); },
                    txt: function (ext) { return ext.match(/(txt|ini|md)$/i); },
                    mov: function (ext) { return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i); },
                    mp3: function (ext) { return ext.match(/(mp3|wav)$/i); }
                }
            })
            .on('fileimageloaded', onFileImageLoaded)
            .on('fileimagesloaded', onFileImagesLoaded);

        wrapper = input.parentNode.parentNode.parentNode.parentNode;
        dropZone = wrapper.querySelector('.file-preview-thumbnails');

        function onFileImagesLoaded(event) {
            var files = $fileinput.fileinput('getPreview');
            files.config.forEach(function(data, idx) {
                var preview = dropZone.querySelector(`[data-fileindex="init_${idx}"]`);

                if (deletedFileIndexes.indexOf('init_' + idx) >= 0) {
                    preview.remove();
                    return;
                }

                Array.prototype.forEach.call(preview.querySelectorAll('[data-name*="_dataKey_"]'), function(element) {
                    element.dataset.key = data.key;
                    element.name = element.dataset.name.replace('_dataKey_', element.dataset.key);

                    if (element.dataset.type === 'legend') {
                        element.value = data.name;
                    }

                    delete(element.dataset.name);
                });

                var btn = preview.querySelector('button.kv-file-remove');
                if (btn) {
                    btn.addEventListener('click', function (event) {
                        onRemove(event, preview);
                    }, true);
                }
            });
        }

        if (!input.multiple) {
            wrapper.classList.add('file-input-small');
        }

        if (isSortable) {
            sortable = new Sortable(dropZone, {
                handle: ".file-preview-frame",
                onEnd: onSortEnd
            });
        }

        function onFileImageLoaded(event) {
            var files = $fileinput.fileinput('getFileStack');
            files.forEach(function(file, idx) {
                var preview = dropZone.querySelector(`[data-fileindex="${idx}"]`);
                Array.prototype.forEach.call(preview.querySelectorAll('[data-name*="_dataKey_"]'), function(element) {
                    element.dataset.key = file.name;
                    element.name = element.dataset.name.replace('_dataKey_', file.name);
                    delete(element.dataset.name);
                });
            });

            if (isSortable) {
                sortable.toArray();
                onSortEnd();
            }
        }

        function onRemove(event, preview) {
            event.stopPropagation();
            event.preventDefault();

            var button = event.currentTarget;

            deletedFileIndexes.push(preview.dataset.fileindex);

            var deletedFileIds = deletedFiles.value.length > 0 ? JSON.parse(deletedFiles.value) : [];
            deletedFileIds.push(button.dataset.key);

            deletedFiles.value = JSON.stringify(deletedFileIds);

            preview.remove();
        }

        function onSortEnd(event) {
            Array.prototype.forEach.call(dropZone.querySelectorAll(`[data-fileindex] input[type="hidden"][data-type="position"]`), function(element, idx) {
                element.value = idx;
            });
        }

        onFileImagesLoaded();
        onSortEnd();
    }

    window.addEventListener('load', function (event) {
        handleFileinputs(document);
    });

    window.handleFileinput = handleFileinput;
    window.handleFileinputs = handleFileinputs;
})(jQuery, Sortable);
