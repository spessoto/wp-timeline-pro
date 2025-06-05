// assets/js/elementor-preview.js
(function($) {
    var WtpElementorPreview = {

        /**
         * Função Debounce para limitar a frequência com que uma função pode ser executada.
         * @param {function} func - A função a ser "debounced".
         * @param {number} wait - O tempo de espera em milissegundos.
         * @param {boolean} immediate - Se true, executa a função no início do período de espera.
         * @returns {function} A nova função "debounced".
         */
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        /**
         * Busca e renderiza a pré-visualização da timeline.
         * @param {object} widgetView - A view do widget Elementor.
         */
        fetchPreview: function(widgetView) {
            // Obtém o ID da timeline selecionada e o ID do elemento do widget
            var timelineId = widgetView.model.get('settings').get('selected_timeline_id');
            var elementId = widgetView.getID();
            var $previewContainer = widgetView.$el.find('.wtp-elementor-timeline-preview[data-element-id="' + elementId + '"]');

            // Se nenhuma timeline estiver selecionada, exibe um aviso
            if (!timelineId) {
                $previewContainer.html('<div class="elementor-alert elementor-alert-warning">' + wtp_elementor_preview_vars.i18n.select_timeline + '</div>');
                return;
            }

            // Mostra o loader enquanto a pré-visualização é carregada
            $previewContainer.html('<div class="elementor-loader-wrapper"><div class="elementor-loader"><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div></div></div><p style="text-align:center;">' + wtp_elementor_preview_vars.i18n.loading_preview + '</p>');

            // Coleta todas as configurações e extrai apenas as relevantes para substituição de estilo
            var allSettings = widgetView.model.get('settings').toJSON();
            var relevantSettings = {};
            for (var key in allSettings) {
                if (key.startsWith('override_')) {
                    relevantSettings[key] = allSettings[key];
                }
            }

            // Requisição AJAX para buscar o HTML da timeline renderizado
            $.ajax({
                url: wtp_elementor_preview_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wtp_get_elementor_preview',
                    nonce: wtp_elementor_preview_vars.nonce,
                    timeline_id: timelineId,
                    elementor_settings: JSON.stringify(relevantSettings) // Envia apenas as configurações relevantes
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        $previewContainer.html(response.data.html);
                        // Dispara um evento para que outros scripts (ex: animações) possam re-inicializar
                        $(document).trigger('wtpElementorPreviewRendered', [$previewContainer]);

                        // Tenta forçar o Elementor a recalcular o layout
                        if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.elements !== 'undefined' && typeof elementorFrontend.elements.$window !== 'undefined') {
                            elementorFrontend.elements.$window.trigger('resize');
                        }
                    } else {
                        // Exibe mensagem de erro se a resposta AJAX não for bem-sucedida
                        var errorMessage = response.data && response.data.message ? response.data.message : wtp_elementor_preview_vars.i18n.error_loading_preview;
                        $previewContainer.html('<div class="elementor-alert elementor-alert-danger">' + errorMessage + '</div>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Exibe mensagem de erro em caso de falha na requisição AJAX
                    $previewContainer.html('<div class="elementor-alert elementor-alert-danger">' + wtp_elementor_preview_vars.i18n.ajax_error + ': ' + errorThrown + '</div>');
                }
            });
        }
    };

    // Inicializa a lógica de pré-visualização quando o frontend do Elementor estiver pronto
    $(window).on('elementor/frontend/init', function() {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            console.warn('WP Timeline Pro: Hooks do frontend do Elementor não disponíveis para pré-visualização.');
            return;
        }

        // Adiciona uma ação para quando o widget 'wtp_timeline' estiver pronto no editor
        elementorFrontend.hooks.addAction('frontend/element_ready/wtp_timeline.default', function($scope, $) {
            var widgetView = $scope.data('elementor-elements-handler'); // Método primário para obter a view

            // Fallback para obter a view do widget, se o método primário falhar
            if (!widgetView) {
                 if (typeof elementorFrontend.elements !== 'undefined' && typeof elementorFrontend.elements.views !== 'undefined' && $scope.data('model-cid')) {
                    widgetView = elementorFrontend.elements.views.findByModelCID($scope.data('model-cid'));
                }
            }
            
            // Se a view do widget não puder ser obtida, registra um aviso e tenta uma carga inicial com mock
            if (!widgetView) {
                console.warn('WP Timeline Pro: Não foi possível obter a view do widget para pré-visualização AJAX.', $scope);
                var initialTimelineId = $scope.find('.wtp-elementor-timeline-preview').data('timeline-id');
                if (initialTimelineId) {
                    // Simula uma view básica para a chamada inicial
                    var mockView = {
                        model: { get: function() { return { get: function(key) { return key === 'selected_timeline_id' ? initialTimelineId : ''; }, toJSON: function() { return { selected_timeline_id: initialTimelineId }; } }; } },
                        getID: function() { return $scope.find('.wtp-elementor-timeline-preview').data('element-id'); },
                        $el: $scope
                    };
                    WtpElementorPreview.fetchPreview(mockView);
                }
                return;
            }

            // Cria uma função "debounced" para buscar a pré-visualização, limitando chamadas excessivas
            var debouncedFetchPreview = WtpElementorPreview.debounce(function() {
                WtpElementorPreview.fetchPreview(widgetView);
            }, 500); // Atraso de 500ms

            // Renderiza a pré-visualização na carga inicial do widget
            debouncedFetchPreview();

            // Escuta por mudanças nas configurações do modelo do widget
            widgetView.model.on('change', function(model) {
                var changedAttributes = model.changedAttributes();
                var relevantChange = false;
                // Verifica se alguma das configurações relevantes (ID da timeline ou substituições de estilo) mudou
                for (var key in changedAttributes) {
                    if (key === 'selected_timeline_id' || key.startsWith('override_')) {
                        relevantChange = true;
                        break;
                    }
                }
                // Se uma configuração relevante mudou, atualiza a pré-visualização
                if (relevantChange) {
                    debouncedFetchPreview();
                }
            });
        });
    });

})(jQuery);
