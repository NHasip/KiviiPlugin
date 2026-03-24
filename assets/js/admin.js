/**
 * Kivii Online Afspraak - Admin JavaScript
 * Handles services CRUD, filtering, API test, CSV export and utility actions.
 */
(function ($) {
    'use strict';

    const nonce = typeof kiviiNonce !== 'undefined' ? kiviiNonce : '';

    function openCategoryModal(data = {}) {
        $('#cat-id').val(data.id || 0);
        $('#cat-name-nl').val(data.nameNl || '');
        $('#cat-name-en').val(data.nameEn || '');
        $('#cat-desc-nl').val(data.descNl || '');
        $('#cat-desc-en').val(data.descEn || '');
        $('#cat-sort').val(data.sort || 0);
        $('#cat-active').prop('checked', data.active !== 0);
        $('#kivii-category-modal').show();
    }

    function openServiceModal(data = {}) {
        $('#svc-id').val(data.id || 0);
        if (data.category) {
            $('#svc-category').val(data.category);
        }
        $('#svc-title-nl').val(data.titleNl || '');
        $('#svc-title-en').val(data.titleEn || '');
        $('#svc-desc-nl').val(data.descNl || '');
        $('#svc-desc-en').val(data.descEn || '');
        $('#svc-long-nl').val(data.longNl || '');
        $('#svc-long-en').val(data.longEn || '');
        $('#svc-price').val(data.price || '');
        $('#svc-price-label-nl').val(data.priceLabelNl || '');
        $('#svc-price-label-en').val(data.priceLabelEn || '');
        $('#svc-duration').val(data.duration ?? 30);
        $('#svc-addon').prop('checked', data.addon == 1);
        $('#svc-active').prop('checked', data.active !== 0);
        $('#svc-sort').val(data.sort || 0);
        $('#kivii-service-modal').show();
    }

    function applyServiceFilters() {
        const search = ($('#kivii-service-search').val() || '').toLowerCase().trim();
        const category = $('#kivii-service-filter-category').val();
        const status = $('#kivii-service-filter-status').val();
        const type = $('#kivii-service-filter-type').val();

        $('.kivii-category-block').each(function () {
            const block = $(this);
            let visibleRows = 0;

            block.find('.kivii-service-row').each(function () {
                const row = $(this);
                const matchesSearch = !search || String(row.data('search') || '').includes(search);
                const matchesCategory = !category || String(row.data('category')) === String(category);
                const matchesStatus = !status || row.data('status') === status;
                const matchesType = !type || row.data('type') === type;
                const isVisible = matchesSearch && matchesCategory && matchesStatus && matchesType;

                row.toggle(isVisible);
                if (isVisible) {
                    visibleRows++;
                }
            });

            const categoryMatches = !search || String(block.data('category-name') || '').includes(search);
            block.toggle(categoryMatches || visibleRows > 0);
        });
    }

    // Services: Add/Edit Category
    $('#kivii-add-category').on('click', function () {
        openCategoryModal();
    });

    $(document).on('click', '.kivii-edit-category', function () {
        const btn = $(this);
        openCategoryModal({
            id: btn.data('id'),
            nameNl: btn.data('nl'),
            nameEn: btn.data('en'),
            descNl: btn.data('desc-nl'),
            descEn: btn.data('desc-en'),
            sort: btn.data('sort'),
            active: btn.data('active')
        });
    });

    $('#kivii-save-category').on('click', function () {
        $.post(ajaxurl, {
            action: 'kivii_save_category',
            nonce: nonce,
            category: {
                id: $('#cat-id').val(),
                name_nl: $('#cat-name-nl').val(),
                name_en: $('#cat-name-en').val(),
                description_nl: $('#cat-desc-nl').val(),
                description_en: $('#cat-desc-en').val(),
                sort_order: $('#cat-sort').val(),
                is_active: $('#cat-active').is(':checked') ? 1 : 0
            }
        }, function () {
            location.reload();
        });
    });

    $(document).on('click', '.kivii-delete-category', function () {
        if (!confirm('Categorie verwijderen? Alle bijbehorende werkzaamheden worden ook verwijderd.')) {
            return;
        }

        $.post(ajaxurl, {
            action: 'kivii_delete_category',
            nonce: nonce,
            id: $(this).data('id')
        }, function () {
            location.reload();
        });
    });

    // Services: Add/Edit Service
    $('#kivii-add-service').on('click', function () {
        openServiceModal({ active: 1, duration: 30 });
    });

    $(document).on('click', '.kivii-add-service-for-category', function () {
        openServiceModal({
            category: $(this).data('category'),
            active: 1,
            duration: 30
        });
    });

    $(document).on('click', '.kivii-edit-service', function () {
        const btn = $(this);
        openServiceModal({
            id: btn.data('id'),
            category: btn.data('category'),
            titleNl: btn.data('title-nl'),
            titleEn: btn.data('title-en'),
            descNl: btn.data('desc-nl'),
            descEn: btn.data('desc-en'),
            longNl: btn.data('long-nl'),
            longEn: btn.data('long-en'),
            price: btn.data('price'),
            priceLabelNl: btn.data('price-label-nl'),
            priceLabelEn: btn.data('price-label-en'),
            duration: btn.data('duration'),
            addon: btn.data('addon'),
            active: btn.data('active'),
            sort: btn.data('sort')
        });
    });

    $('#kivii-save-service').on('click', function () {
        $.post(ajaxurl, {
            action: 'kivii_save_service',
            nonce: nonce,
            service: {
                id: $('#svc-id').val(),
                category_id: $('#svc-category').val(),
                title_nl: $('#svc-title-nl').val(),
                title_en: $('#svc-title-en').val(),
                description_nl: $('#svc-desc-nl').val(),
                description_en: $('#svc-desc-en').val(),
                long_desc_nl: $('#svc-long-nl').val(),
                long_desc_en: $('#svc-long-en').val(),
                price: $('#svc-price').val(),
                price_label_nl: $('#svc-price-label-nl').val(),
                price_label_en: $('#svc-price-label-en').val(),
                duration_minutes: $('#svc-duration').val(),
                is_addon: $('#svc-addon').is(':checked') ? 1 : 0,
                is_active: $('#svc-active').is(':checked') ? 1 : 0,
                sort_order: $('#svc-sort').val()
            }
        }, function () {
            location.reload();
        });
    });

    $(document).on('click', '.kivii-delete-service', function () {
        if (!confirm('Werkzaamheid verwijderen?')) {
            return;
        }

        $.post(ajaxurl, {
            action: 'kivii_delete_service',
            nonce: nonce,
            id: $(this).data('id')
        }, function () {
            location.reload();
        });
    });

    // Filters
    $('#kivii-service-search, #kivii-service-filter-category, #kivii-service-filter-status, #kivii-service-filter-type').on('input change', applyServiceFilters);

    // Modal close
    $(document).on('click', '.kivii-modal-close', function () {
        $(this).closest('.kivii-modal').hide();
    });

    $(document).on('click', '.kivii-modal', function (event) {
        if ($(event.target).hasClass('kivii-modal')) {
            $(this).hide();
        }
    });

    // Import/Export
    $('#kivii-export-services').on('click', function () {
        $.post(ajaxurl, { action: 'kivii_export_services', nonce: nonce }, function (resp) {
            if (!resp.success) {
                return;
            }

            const blob = new Blob([JSON.stringify(resp.data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'kivii-services-export.json';
            link.click();
            URL.revokeObjectURL(url);
        });
    });

    $('#kivii-import-services').on('click', function () {
        $('#kivii-import-file').click();
    });

    $('#kivii-import-file').on('change', function () {
        const file = this.files[0];
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            if (!confirm('Weet u zeker dat u wilt importeren? Bestaande data wordt overschreven.')) {
                return;
            }

            $.post(ajaxurl, {
                action: 'kivii_import_services',
                nonce: nonce,
                json_data: event.target.result
            }, function () {
                location.reload();
            });
        };
        reader.readAsText(file);
    });

    // API Test
    $('#kivii-test-api').on('click', function () {
        const button = $(this);
        const result = $('#kivii-test-result');

        button.prop('disabled', true).text('Testen...');
        result.text('');

        $.ajax({
            url: (window.kiviiData?.restUrl || '/wp-json/kiviiweb/v1') + '/api-test',
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce }
        }).done(function (resp) {
            if (resp.success) {
                result.css('color', '#B0C426').text('Verbinding succesvol!');
            } else {
                result.css('color', '#DC2626').text(resp.message || 'Verbinding mislukt.');
            }
        }).fail(function () {
            result.css('color', '#DC2626').text('Netwerkfout.');
        }).always(function () {
            button.prop('disabled', false).text('Test API verbinding');
        });
    });

    // CSV Export
    $('#kivii-export-csv').on('click', function () {
        $.post(ajaxurl, { action: 'kivii_export_csv', nonce: nonce, filters: {} }, function (resp) {
            if (!resp.success || !resp.data || resp.data.length === 0) {
                alert('Geen data om te exporteren.');
                return;
            }

            const rows = resp.data;
            const headers = Object.keys(rows[0]);
            let csv = headers.join(';') + '\n';

            rows.forEach(row => {
                csv += headers.map(header => `"${String(row[header] || '').replace(/"/g, '""')}"`).join(';') + '\n';
            });

            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'kivii-boekingen.csv';
            link.click();
            URL.revokeObjectURL(url);
        });
    });

    // Resend actions
    $('#kivii-resend-api').on('click', function () {
        const id = $(this).data('id');
        if (!confirm('Boeking opnieuw versturen naar Kivii?')) {
            return;
        }

        $.post(ajaxurl, { action: 'kivii_resend_booking', nonce: nonce, booking_id: id }, function (resp) {
            alert(resp.success ? 'Opnieuw verstuurd.' : (resp.data || 'Fout'));
        });
    });

    $('#kivii-resend-email').on('click', function () {
        const id = $(this).data('id');
        $.post(ajaxurl, { action: 'kivii_resend_email', nonce: nonce, booking_id: id }, function (resp) {
            alert(resp.success ? 'E-mail verzonden.' : (resp.data || 'Fout'));
        });
    });

    // Clear Logs
    $('#kivii-clear-logs').on('click', function () {
        if (!confirm('Alle logs wissen?')) {
            return;
        }

        $.post(ajaxurl, { action: 'kivii_clear_logs', nonce: nonce }, function () {
            location.reload();
        });
    });

    // WP Color Picker
    if ($.fn.wpColorPicker) {
        $('.kivii-color-picker').wpColorPicker();
    }

})(jQuery);
