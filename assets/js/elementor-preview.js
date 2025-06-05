// assets/js/elementor-preview.js
(function($) {
    var WtpElementorPreview = {

        /**
         * Debounce function to limit the frequency with which a function can be executed.
         * @param {function} func - The function to be debounced.
         * @param {number} wait - The wait time in milliseconds.
         * @param {boolean} immediate - If true, executes the function at the beginning of the wait period.
         * @returns {function} The new debounced function.
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
         * Fetches and renders the timeline preview.
         * @param {object} widgetView - The Elementor widget view.
         */
        fetchPreview: function(widgetView) {
            // Get the selected timeline ID and the widget element ID
            var timelineId = widgetView.model.get('settings').get('selected_timeline_id');
            var elementId = widgetView.getID();
            var $previewContainer = widgetView.$el.find('.wtp-elementor-timeline-preview[data-element-id="' + elementId + '"]');

            // If no timeline is selected, display a warning
            if (!timelineId) {
                $previewContainer.html('<div class="elementor-alert elementor-alert-warning">' + wtp_elementor_preview_vars.i18n.select_timeline + '</div>');
                return;
            }

            // Show loader while the preview is loading
            $previewContainer.html('<div class="elementor-loader-wrapper"><div class="elementor-loader"><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div></div></div><p style="text-align:center;">' + wtp_elementor_preview_vars.i18n.loading_preview + '</p>');

            // Collect all settings and extract only those relevant for style override
            var allSettings = widgetView.model.get('settings').toJSON();
            var relevantSettings = {};
            for (var key in allSettings) {
                if (key.startsWith('override_')) {
                    relevantSettings[key] = allSettings[key];
                }
            }

            // AJAX request to fetch the rendered timeline HTML
            $.ajax({
                url: wtp_elementor_preview_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wtp_get_elementor_preview',
                    nonce: wtp_elementor_preview_vars.nonce,
                    timeline_id: timelineId,
                    elementor_settings: JSON.stringify(relevantSettings) // Send only relevant settings
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        $previewContainer.html(response.data.html);
                        // Trigger an event so other scripts (e.g., animations) can re-initialize
                        $(document).trigger('wtpElementorPreviewRendered', [$previewContainer]);

                        // Try to force Elementor to recalculate the layout
                        if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.elements !== 'undefined' && typeof elementorFrontend.elements.$window !== 'undefined') {
                            elementorFrontend.elements.$window.trigger('resize');
                        }
                    } else {
                        // Display error message if AJAX response is not successful
                        var errorMessage = response.data && response.data.message ? response.data.message : wtp_elementor_preview_vars.i18n.error_loading_preview;
                        $previewContainer.html('<div class="elementor-alert elementor-alert-danger">' + errorMessage + '</div>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Display error message in case of AJAX request failure
                    $previewContainer.html('<div class="elementor-alert elementor-alert-danger">' + wtp_elementor_preview_vars.i18n.ajax_error + ': ' + errorThrown + '</div>');
                }
            });
        }
    };

    // Initialize preview logic when Elementor frontend is ready
    $(window).on('elementor/frontend/init', function() {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            console.warn('WP Timeline Pro: Elementor frontend hooks not available for preview.');
            return;
        }

        // Add an action for when the 'wtp_timeline' widget is ready in the editor
        elementorFrontend.hooks.addAction('frontend/element_ready/wtp_timeline.default', function($scope, $) {
            var widgetView = $scope.data('elementor-elements-handler'); // Primary method to get the view

            // Fallback to get the widget view, if the primary method fails
            if (!widgetView) {
                 if (typeof elementorFrontend.elements !== 'undefined' && typeof elementorFrontend.elements.views !== 'undefined' && $scope.data('model-cid')) {
                    widgetView = elementorFrontend.elements.views.findByModelCID($scope.data('model-cid'));
                }
            }

            // If the widget view cannot be obtained, log a warning and try an initial load with a mock
            if (!widgetView) {
                console.warn('WP Timeline Pro: Could not get widget view for AJAX preview.', $scope);
                var initialTimelineId = $scope.find('.wtp-elementor-timeline-preview').data('timeline-id');
                if (initialTimelineId) {
                    // Simulate a basic view for the initial call
                    var mockView = {
                        model: { get: function() { return { get: function(key) { return key === 'selected_timeline_id' ? initialTimelineId : ''; }, toJSON: function() { return { selected_timeline_id: initialTimelineId }; } }; } },
                        getID: function() { return $scope.find('.wtp-elementor-timeline-preview').data('element-id'); },
                        $el: $scope
                    };
                    WtpElementorPreview.fetchPreview(mockView);
                }
                // Even if widgetView is not found for preview, the edit button listener should still be attached if possible.
                // However, getting selected_timeline_id will be an issue without the model.
                // The event listener for 'wtp:editTimeline' relies on the control view, not the widget view.
            }

            // Create a debounced function to fetch the preview, limiting excessive calls
            // This part is for the live preview rendering
            if (widgetView) { // Only set up preview rendering if we have a valid widgetView
                var debouncedFetchPreview = WtpElementorPreview.debounce(function() {
                    WtpElementorPreview.fetchPreview(widgetView);
                }, 500); // 500ms delay

                // Render the preview on initial widget load
                debouncedFetchPreview();

                // Listen for changes in the widget model's settings
                widgetView.model.on('change', function(model) {
                    var changedAttributes = model.changedAttributes();
                    var relevantChange = false;
                    // Check if any relevant settings (timeline ID or style overrides) changed
                    for (var key in changedAttributes) {
                        if (key === 'selected_timeline_id' || key.startsWith('override_')) {
                            relevantChange = true;
                            break;
                        }
                    }
                    // If a relevant setting changed, update the preview
                    if (relevantChange) {
                        debouncedFetchPreview();
                    }
                });
            }

            // Add listener for the custom event from the panel button 'wtp:editTimeline'
            // This listener is independent of the widgetView for preview, it's for the panel button.
            if (window.elementor && window.elementor.channels && window.elementor.channels.editor) {
                elementor.channels.editor.on('wtp:editTimeline', function(controlView, widgetViewContext) {
                    // The widgetViewContext is the actual widget view instance passed from the button click handler
                    // (assuming the event is triggered with context: elementor.channels.editor.trigger('wtp:editTimeline', this, this.elementEditorView); )
                    // Or, more reliably, the controlView itself can give us access to the model.
                    var currentWidgetModel;
                    if (controlView && controlView.model) { // If controlView has model (newer Elementor versions)
                        currentWidgetModel = controlView.model;
                    } else if (widgetView) { // Fallback to the widgetView obtained for preview if controlView doesn't have model
                        currentWidgetModel = widgetView.model;
                    }

                    if (currentWidgetModel && typeof currentWidgetModel.get === 'function' && currentWidgetModel.get('settings') && typeof currentWidgetModel.get('settings').get === 'function') {
                        var selectedTimelineId = currentWidgetModel.get('settings').get('selected_timeline_id');

                        if (selectedTimelineId) {
                            var adminBaseUrl = wtp_elementor_preview_vars.ajax_url.replace('admin-ajax.php', '');
                            var editUrl = adminBaseUrl + 'post.php?post=' + selectedTimelineId + '&action=edit';
                            window.open(editUrl, '_blank'); // Open in a new tab
                        } else {
                            alert(wtp_elementor_preview_vars.i18n.select_timeline || 'Please select a timeline first.');
                        }
                    } else {
                         console.warn('WP Timeline Pro: Could not determine widget model for edit button.');
                         // Attempt to get selected_timeline_id directly from the $scope if model is unavailable
                         var timelineIdFromScope = $scope.find('select[data-setting="selected_timeline_id"]').val();
                         if(timelineIdFromScope){
                            var adminBaseUrl = wtp_elementor_preview_vars.ajax_url.replace('admin-ajax.php', '');
                            var editUrl = adminBaseUrl + 'post.php?post=' + timelineIdFromScope + '&action=edit';
                            window.open(editUrl, '_blank');
                         } else {
                            alert(wtp_elementor_preview_vars.i18n.select_timeline || 'Please select a timeline first.');
                         }
                    }
                });
            }
        });
    });

})(jQuery);
