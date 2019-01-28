import Sortable from 'sortablejs';
import 'bootstrap-fileinput';
import 'bootstrap-fileinput/themes/fa/theme';
import 'bootstrap-fileinput/js/locales/fr';
import 'bootstrap/js/src/modal';

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

        var isMultiple = input.multiple;
        var isSortable = input.dataset.position && isMultiple;

        var positionInput = isSortable ? document.querySelector('input[name="' + input.dataset.position + '"]') : null;
        var legendInput = input.dataset.legend ? document.querySelector('input[name="' + input.dataset.legend + '"]') : null;

        layoutTemplates.footer +=  '<div class="input-group"{dataKey}>' + (input.dataset.legend ? '<input type="text" data-type="legend" placeholder="Name" class="form-control">' : '') + '{actions}</div>';
        layoutTemplates.actions = '<div class="input-group-btn">{zoom} {delete}</div>';
        layoutTemplates.actionDelete = '<button type="button" class="kv-file-remove btn btn-sm btn-warning" title="{removeTitle}"{dataKey}>{removeIcon}</button>';

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

        var timer = null;
        dropZone.addEventListener('keyup', function(event){
            if (event.target instanceof HTMLInputElement && event.target.dataset.type === 'legend') {
                clearTimeout(timer);
                timer = setTimeout(function(){ onKeyup(event); }, 300);
            }
        });

        function onKeyup(event) {
            var preview = event.target.closest(`[data-fileindex][data-key]`);

            var value = event.target.value;
            if (isMultiple) {
                var names = legendInput.value ? JSON.parse(legendInput.value) : {};
                names[preview.dataset.key] = event.target.value;
                value = JSON.stringify(names);
            }

            legendInput.value = value;
        }

        function onFileImagesLoaded(event) {
            var files = $fileinput.fileinput('getPreview');
            files.config.forEach(function(data, idx) {
                var preview = dropZone.querySelector(`[data-fileindex="init_${idx}"]`);

                if (deletedFileIndexes.indexOf('init_' + idx) >= 0) {
                    preview.remove();
                    return;
                }

                preview.dataset.key = data.key;

                var legendInput = preview.querySelector('[data-type="legend"]');
                legendInput.value = data.name;

                var btn = preview.querySelector('button.kv-file-remove');
                if (btn) {
                    btn.addEventListener('click', function (event) {
                        onRemove(event, preview);
                    }, true);
                }
            });
        }

        if (!isMultiple) {
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
                preview.dataset.key = file.name;
                Array.prototype.forEach.call(preview.querySelectorAll('[data-name*="_dataKey_"]'), function(element) {
                    element.dataset.key = file.name;
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
            var positions = {};

            Array.prototype.forEach.call(dropZone.querySelectorAll(`[data-fileindex][data-key]`), function(element, idx) {
                positions[element.dataset.key] = idx;
            });

            positionInput.value = JSON.stringify(positions);
        }

        onFileImagesLoaded();

        if (isSortable) {
            onSortEnd();
        }
    }

    window.addEventListener('load', function (event) {
        handleFileinputs(document);
    });

    window.handleFileinput = handleFileinput;
    window.handleFileinputs = handleFileinputs;
})(jQuery, Sortable);