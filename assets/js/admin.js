/**
 * Kivii Online Afspraak – Admin JavaScript
 * Handles AJAX operations for services CRUD, API test, CSV export, etc.
 */
(function ($) {
    'use strict';

    const nonce = typeof kiviiNonce !== 'undefined' ? kiviiNonce : '';

    // ── Services: Add/Edit Category ────────
    $('#kivii-add-category').on('click', function () {
        $('#cat-id').val(0);
        $('#cat-name-nl, #cat-name-en').val('');
        $('#cat-sort').val(0);
        $('#cat-active').prop('checked', true);
        $('#kivii-category-modal').show();
    });

    $(document).on('click', '.kivii-edit-category', function () {
        const btn = $(this);
        $('#cat-id').val(btn.data('id'));
        $('#cat-name-nl').val(btn.data('nl'));
        $('#cat-name-en').val(btn.data('en'));
        $('#cat-sort').val(btn.data('sort'));
        $('#cat-active').prop('checked', btn.data('active') == 1);
        $('#kivii-category-modal').show();
    });

    $('#kivii-save-category').on('click', function () {
        $.post(ajaxurl, {
            action: 'kivii_save_category',
            nonce: nonce,
            category: {
                id: $('#cat-id').val(),
                name_nl: $('#cat-name-nl').val(),
                name_en: $('#cat-name-en').val(),
                sort_order: $('#cat-sort').val(),
                is_active: $('#cat-active').is(':checked') ? 1 : 0,
            }
        }, function () { location.reload(); });
    });

    $(document).on('click', '.kivii-delete-category', function () {
        if (!confirm('Categorie verwijderen? Alle bijbehorende werkzaamheden worden ook verwijderd.')) return;
        $.post(ajaxurl, { action: 'kivii_delete_category', nonce: nonce, id: $(this).data('id') }, function () { location.reload(); });
    });

    // ── Services: Add/Edit Service ─────────
    $('#kivii-add-service').on('click', function () {
        $('#svc-id').val(0);
        $('#svc-title-nl, #svc-title-en, #svc-desc-nl, #svc-desc-en, #svc-long-nl, #svc-long-en').val('');
        $('#svc-price').val('');
        $('#svc-duration').val(30);
        $('#svc-addon').prop('checked', false);
        $('#svc-active').prop('checked', true);
        $('#svc-sort').val(0);
        $('#kivii-service-modal').show();
    });

    $(document).on('click', '.kivii-edit-service', function () {
        const btn = $(this);
        $('#svc-id').val(btn.data('id'));
        $('#svc-category').val(btn.data('category'));
        $('#svc-title-nl').val(btn.data('title-nl'));
        $('#svc-title-en').val(btn.data('title-en'));
        $('#svc-desc-nl').val(btn.data('desc-nl'));
        $('#svc-desc-en').val(btn.data('desc-en'));
        $('#svc-long-nl').val(btn.data('long-nl'));
        $('#svc-long-en').val(btn.data('long-en'));
        $('#svc-price').val(btn.data('price'));
        $('#svc-duration').val(btn.data('duration'));
        $('#svc-addon').prop('checked', btn.data('addon') == 1);
        $('#svc-active').prop('checked', btn.data('active') == 1);
        $('#svc-sort').val(btn.data('sort'));
        $('#kivii-service-modal').show();
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
                duration_minutes: $('#svc-duration').val(),
                is_addon: $('#svc-addon').is(':checked') ? 1 : 0,
                is_active: $('#svc-active').is(':checked') ? 1 : 0,
                sort_order: $('#svc-sort').val(),
            }
        }, function () { location.reload(); });
    });

    $(document).on('click', '.kivii-delete-service', function () {
        if (!confirm('Werkzaamheid verwijderen?')) return;
        $.post(ajaxurl, { action: 'kivii_delete_service', nonce: nonce, id: $(this).data('id') }, function () { location.reload(); });
    });

    // ── Modal close ────────────────────────
    $(document).on('click', '.kivii-modal-close', function () {
        $(this).closest('.kivii-modal').hide();
    });

    $(document).on('click', '.kivii-modal', function (e) {
        if ($(e.target).hasClass('kivii-modal')) $(this).hide();
    });

    // ── Import/Export ──────────────────────
    $('#kivii-export-services').on('click', function () {
        $.post(ajaxurl, { action: 'kivii_export_services', nonce: nonce }, function (resp) {
            if (resp.success) {
                const blob = new Blob([JSON.stringify(resp.data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'kivii-services-export.json';
                a.click();
                URL.revokeObjectURL(url);
            }
        });
    });

    $('#kivii-import-services').on('click', function () {
        $('#kivii-import-file').click();
    });

    $('#kivii-import-file').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            if (confirm('Weet u zeker dat u wilt importeren? Bestaande data wordt overschreven.')) {
                $.post(ajaxurl, {
                    action: 'kivii_import_services',
                    nonce: nonce,
                    json_data: e.target.result,
                }, function () { location.reload(); });
            }
        };
        reader.readAsText(file);
    });

    // ── API Test ───────────────────────────
    $('#kivii-test-api').on('click', function () {
        const btn = $(this);
        const result = $('#kivii-test-result');
        btn.prop('disabled', true).text('Testen...');
        result.text('');

        $.ajax({
            url: (window.kiviiData?.restUrl || '/wp-json/kiviiweb/v1') + '/api-test',
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce },
        }).done(function (resp) {
            if (resp.success) {
                result.css('color', '#059669').text('✅ Verbinding succesvol!');
            } else {
                result.css('color', '#DC2626').text('❌ ' + (resp.message || 'Verbinding mislukt.'));
            }
        }).fail(function () {
            result.css('color', '#DC2626').text('❌ Netwerkfout.');
        }).always(function () {
            btn.prop('disabled', false).text('Test API verbinding');
        });
    });

    // ── CSV Export ──────────────────────────
    $('#kivii-export-csv').on('click', function () {
        $.post(ajaxurl, { action: 'kivii_export_csv', nonce: nonce, filters: {} }, function (resp) {
            if (resp.success && resp.data) {
                const rows = resp.data;
                if (rows.length === 0) { alert('Geen data om te exporteren.'); return; }
                const headers = Object.keys(rows[0]);
                let csv = headers.join(';') + '\n';
                rows.forEach(row => {
                    csv += headers.map(h => '"' + String(row[h] || '').replace(/"/g, '""') + '"').join(';') + '\n';
                });
                const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'kivii-boekingen.csv';
                a.click();
            }
        });
    });

    // ── Resend actions ─────────────────────
    $('#kivii-resend-api').on('click', function () {
        const id = $(this).data('id');
        if (!confirm('Boeking opnieuw versturen naar Kivii?')) return;
        $.post(ajaxurl, { action: 'kivii_resend_booking', nonce: nonce, booking_id: id }, function (resp) {
            alert(resp.success ? '✅ Opnieuw verstuurd!' : '❌ ' + (resp.data || 'Fout'));
        });
    });

    $('#kivii-resend-email').on('click', function () {
        const id = $(this).data('id');
        $.post(ajaxurl, { action: 'kivii_resend_email', nonce: nonce, booking_id: id }, function (resp) {
            alert(resp.success ? '✅ E-mail verzonden!' : '❌ ' + (resp.data || 'Fout'));
        });
    });

    // ── Clear Logs ─────────────────────────
    $('#kivii-clear-logs').on('click', function () {
        if (!confirm('Alle logs wissen?')) return;
        $.post(ajaxurl, { action: 'kivii_clear_logs', nonce: nonce }, function () { location.reload(); });
    });

    // ── WP Color Picker ────────────────────
    if ($.fn.wpColorPicker) {
        $('.kivii-color-picker').wpColorPicker();
    }

})(jQuery);
