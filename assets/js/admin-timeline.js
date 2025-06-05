// assets/js/admin-timeline.js
(function($) {
    $(document).ready(function() {
        // Inicializa o WordPress Color Picker
        $('.wtp-color-picker').wpColorPicker();

        // Inicializa o Select2 para seleção de fontes
        if (typeof $.fn.select2 === 'function') {
            $('.wtp-select2-font').select2({
                width: '100%',
                templateResult: function(font) {
                    if (!font.id || !font.element || !$(font.element).data('font-name')) {
                        return font.text;
                    }
                    var fontName = $(font.element).data('font-name');
                    return $('<span style="font-family: ' + font.id.replace(/"/g, "'") + ';">' + fontName + '</span>');
                },
                templateSelection: function(font) {
                     if (!font.id || !font.element || !$(font.element).data('font-name')) {
                        return font.text;
                    }
                    var fontName = $(font.element).data('font-name');
                    return fontName;
                }
            });
        } else {
            console.warn('WP Timeline Pro: Select2 não está carregado.');
        }

        // Mostrar/Esconder condicionalmente a cor de fundo do card
        var $noCardCheckbox = $('#wtp_no_card_format');
        var $cardBgSettingRow = $('tr.wtp-card-bg-setting'); // O <tr> que contém o campo de cor de fundo

        function toggleCardBgSetting() {
            if ($noCardCheckbox.is(':checked')) {
                $cardBgSettingRow.hide();
            } else {
                $cardBgSettingRow.show();
            }
        }
        if ($noCardCheckbox.length) {
            toggleCardBgSetting(); // Executa na carga da página
            $noCardCheckbox.on('change', toggleCardBgSetting); // Executa na mudança
        }


        // Manipulador para adicionar novo item via AJAX
        $('#wtp-add-new-item-button').on('click', function() {
            var $button = $(this);
            var $spinner = $button.siblings('.spinner');
            var timelineId = $button.data('timeline-id');
            var year = $('#wtp_new_item_year').val();
            // O campo de título foi removido do formulário AJAX, então o enviaremos como vazio.
            // O título real será definido ao editar o item.
            var title = ""; // Título é agora opcional e não coletado deste formulário
            var description = $('#wtp_new_item_description').val();
            var nonce = $('#wtp_timeline_items_manager_nonce').val();

            $spinner.addClass('is-active');
            $button.prop('disabled', true);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wtp_add_timeline_item',
                    nonce: nonce,
                    timeline_id: timelineId,
                    year: year,
                    title: title, // Envia vazio; o título será gerenciado na edição do item CPT
                    description: description
                },
                success: function(response) {
                    if (response.success) {
                        $('#wtp-timeline-items-list-container').html(response.data.items_html);
                        $('#wtp_new_item_year').val('');
                        $('#wtp_new_item_description').val('');
                    } else {
                        var errorMessage = wtp_admin_vars.i18n.error_adding_item;
                        if (response.data && response.data.message) {
                            errorMessage += ': ' + response.data.message;
                        }
                        alert(errorMessage);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(wtp_admin_vars.i18n.ajax_error + ': ' + textStatus + ' - ' + errorThrown);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                }
            });
        });

        // Manipulador para excluir item via AJAX
        $('#wtp-timeline-items-list-container').on('click', '.wtp-delete-item-button', function() {
            var $button = $(this);
            var itemId = $button.data('item-id');
            var itemNonce = $button.data('nonce');
            var $listItem = $button.closest('li');

            if (!confirm(wtp_admin_vars.i18n.confirm_delete_item)) {
                return;
            }
            $button.prop('disabled', true);
            $listItem.css('opacity', '0.5');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wtp_delete_timeline_item',
                    nonce: itemNonce,
                    item_id: itemId
                },
                success: function(response) {
                    if (response.success) {
                        $('#wtp-timeline-items-list-container').html(response.data.items_html);
                    } else {
                        var errorMessage = wtp_admin_vars.i18n.error_deleting_item;
                         if (response.data && response.data.message) {
                            errorMessage += ': ' + response.data.message;
                        }
                        alert(errorMessage);
                        $listItem.css('opacity', '1');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(wtp_admin_vars.i18n.ajax_error + ': ' + textStatus + ' - ' + errorThrown);
                    $listItem.css('opacity', '1');
                }
            });
        });

        // Lógica para pré-selecionar a timeline pai (se aplicável ao editar item CPT diretamente)
        const urlParams = new URLSearchParams(window.location.search);
        const parentTimelineIdFromUrl = urlParams.get('wtp_parent_id');
        const parentTimelineSelect = $('#wtp_parent_timeline_id'); // No CPT item edit screen

        if (parentTimelineIdFromUrl && parentTimelineSelect.length) {
            if (!parentTimelineSelect.val()) {
                 parentTimelineSelect.val(parentTimelineIdFromUrl);
            }
        }
    });
})(jQuery);
// Fim do arquivo assets/js/admin-timeline.js