<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$repo       = new \Kivii\Database\ServiceRepository();
$categories = $repo->get_categories();
$services   = $repo->get_services();
$nonce      = wp_create_nonce( 'kivii_admin_nonce' );

$services_by_category = [];
$active_services      = 0;
$addon_services       = 0;

foreach ( $services as $service ) {
    $services_by_category[ $service->category_id ][] = $service;

    if ( (int) $service->is_active === 1 ) {
        $active_services++;
    }

    if ( (int) $service->is_addon === 1 ) {
        $addon_services++;
    }
}
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Werkzaamheden</h1>
    <p>Beheer hier alle categorieën en werkzaamheden die in stap 2 van het boekingsformulier zichtbaar zijn.</p>

    <div class="kivii-admin-stats">
        <div class="kivii-admin-stat">
            <span class="kivii-admin-stat__label">Categorieën</span>
            <strong class="kivii-admin-stat__value"><?php echo esc_html( count( $categories ) ); ?></strong>
        </div>
        <div class="kivii-admin-stat">
            <span class="kivii-admin-stat__label">Werkzaamheden</span>
            <strong class="kivii-admin-stat__value"><?php echo esc_html( count( $services ) ); ?></strong>
        </div>
        <div class="kivii-admin-stat">
            <span class="kivii-admin-stat__label">Actief</span>
            <strong class="kivii-admin-stat__value"><?php echo esc_html( $active_services ); ?></strong>
        </div>
        <div class="kivii-admin-stat">
            <span class="kivii-admin-stat__label">Add-ons</span>
            <strong class="kivii-admin-stat__value"><?php echo esc_html( $addon_services ); ?></strong>
        </div>
    </div>

    <div class="kivii-toolbar">
        <button class="button button-primary" id="kivii-add-category">+ Categorie toevoegen</button>
        <button class="button" id="kivii-add-service">+ Werkzaamheid toevoegen</button>
        <button class="button" id="kivii-export-services">Exporteer JSON</button>
        <button class="button" id="kivii-import-services">Importeer JSON</button>
        <input type="file" id="kivii-import-file" accept=".json" style="display:none;">
    </div>

    <div class="kivii-services-filters">
        <input type="search" id="kivii-service-search" class="regular-text" placeholder="Zoek op titel, omschrijving of categorie">
        <select id="kivii-service-filter-category">
            <option value="">Alle categorieën</option>
            <?php foreach ( $categories as $category ) : ?>
                <option value="<?php echo esc_attr( $category->id ); ?>"><?php echo esc_html( $category->name_nl ); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="kivii-service-filter-status">
            <option value="">Alle statussen</option>
            <option value="active">Alleen actief</option>
            <option value="inactive">Alleen inactief</option>
        </select>
        <select id="kivii-service-filter-type">
            <option value="">Alle types</option>
            <option value="service">Hoofddiensten</option>
            <option value="addon">Add-ons</option>
        </select>
    </div>

    <div id="kivii-category-modal" class="kivii-modal" style="display:none;">
        <div class="kivii-modal-content">
            <h2>Categorie</h2>
            <input type="hidden" id="cat-id" value="0">
            <table class="form-table">
                <tr><th>Naam (NL)</th><td><input type="text" id="cat-name-nl" class="regular-text"></td></tr>
                <tr><th>Naam (EN)</th><td><input type="text" id="cat-name-en" class="regular-text"></td></tr>
                <tr><th>Omschrijving (NL)</th><td><textarea id="cat-desc-nl" class="large-text" rows="3"></textarea></td></tr>
                <tr><th>Omschrijving (EN)</th><td><textarea id="cat-desc-en" class="large-text" rows="3"></textarea></td></tr>
                <tr><th>Volgorde</th><td><input type="number" id="cat-sort" class="small-text" value="0"></td></tr>
                <tr><th>Actief</th><td><input type="checkbox" id="cat-active" checked></td></tr>
            </table>
            <button class="button button-primary" id="kivii-save-category">Opslaan</button>
            <button class="button kivii-modal-close">Annuleren</button>
        </div>
    </div>

    <div id="kivii-service-modal" class="kivii-modal" style="display:none;">
        <div class="kivii-modal-content">
            <h2>Werkzaamheid</h2>
            <input type="hidden" id="svc-id" value="0">
            <table class="form-table">
                <tr>
                    <th>Categorie</th>
                    <td>
                        <select id="svc-category">
                            <?php foreach ( $categories as $category ) : ?>
                                <option value="<?php echo esc_attr( $category->id ); ?>"><?php echo esc_html( $category->name_nl ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr><th>Titel (NL)</th><td><input type="text" id="svc-title-nl" class="regular-text"></td></tr>
                <tr><th>Titel (EN)</th><td><input type="text" id="svc-title-en" class="regular-text"></td></tr>
                <tr><th>Korte omschrijving (NL)</th><td><textarea id="svc-desc-nl" class="large-text" rows="2"></textarea></td></tr>
                <tr><th>Korte omschrijving (EN)</th><td><textarea id="svc-desc-en" class="large-text" rows="2"></textarea></td></tr>
                <tr><th>Uitgebreide omschrijving (NL)</th><td><textarea id="svc-long-nl" class="large-text" rows="4"></textarea></td></tr>
                <tr><th>Uitgebreide omschrijving (EN)</th><td><textarea id="svc-long-en" class="large-text" rows="4"></textarea></td></tr>
                <tr><th>Prijs basis (€)</th><td><input type="number" id="svc-price" step="0.01" min="0" class="small-text"></td></tr>
                <tr><th>Prijslabel (NL)</th><td><input type="text" id="svc-price-label-nl" class="regular-text" placeholder="Bijv. Vanaf € 89,00"></td></tr>
                <tr><th>Prijslabel (EN)</th><td><input type="text" id="svc-price-label-en" class="regular-text" placeholder="For example: From € 89.00"></td></tr>
                <tr><th>Duur (minuten)</th><td><input type="number" id="svc-duration" min="0" class="small-text" value="30"></td></tr>
                <tr><th>Is add-on</th><td><input type="checkbox" id="svc-addon"></td></tr>
                <tr><th>Actief</th><td><input type="checkbox" id="svc-active" checked></td></tr>
                <tr><th>Volgorde</th><td><input type="number" id="svc-sort" class="small-text" value="0"></td></tr>
            </table>
            <button class="button button-primary" id="kivii-save-service">Opslaan</button>
            <button class="button kivii-modal-close">Annuleren</button>
        </div>
    </div>

    <div id="kivii-services-list">
        <?php foreach ( $categories as $category ) : ?>
            <?php
            $category_services = $services_by_category[ $category->id ] ?? [];
            $service_count     = count( $category_services );
            ?>
            <div
                class="kivii-category-block"
                data-category-id="<?php echo esc_attr( $category->id ); ?>"
                data-category-name="<?php echo esc_attr( strtolower( $category->name_nl ) ); ?>"
            >
                <div class="kivii-category-header">
                    <div class="kivii-category-header__main">
                        <h2>
                            <?php echo esc_html( $category->name_nl ); ?>
                            <?php if ( $category->name_en ) : ?>
                                <small>(<?php echo esc_html( $category->name_en ); ?>)</small>
                            <?php endif; ?>
                            <span class="kivii-category-count"><?php echo esc_html( $service_count ); ?> werkzaamheden</span>
                            <?php if ( ! $category->is_active ) : ?>
                                <span class="kivii-badge-inactive">Inactief</span>
                            <?php endif; ?>
                        </h2>
                        <?php if ( $category->description_nl ) : ?>
                            <p class="kivii-category-description"><?php echo esc_html( $category->description_nl ); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="kivii-category-actions">
                        <button
                            class="button button-small kivii-add-service-for-category"
                            data-category="<?php echo esc_attr( $category->id ); ?>"
                        >+ Werkzaamheid</button>
                        <button
                            class="button button-small kivii-edit-category"
                            data-id="<?php echo esc_attr( $category->id ); ?>"
                            data-nl="<?php echo esc_attr( $category->name_nl ); ?>"
                            data-en="<?php echo esc_attr( $category->name_en ); ?>"
                            data-desc-nl="<?php echo esc_attr( $category->description_nl ); ?>"
                            data-desc-en="<?php echo esc_attr( $category->description_en ); ?>"
                            data-sort="<?php echo esc_attr( $category->sort_order ); ?>"
                            data-active="<?php echo esc_attr( $category->is_active ); ?>"
                        >Bewerken</button>
                        <button class="button button-small kivii-delete-category" data-id="<?php echo esc_attr( $category->id ); ?>">Verwijderen</button>
                    </div>
                </div>

                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th>Werkzaamheid</th>
                            <th>Prijs</th>
                            <th>Duur</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $category_services ) ) : ?>
                            <?php foreach ( $category_services as $service ) : ?>
                                <?php
                                $display_price = $service->price_label_nl;
                                if ( $display_price === '' ) {
                                    $display_price = (float) $service->price > 0
                                        ? '€ ' . number_format( (float) $service->price, 2, ',', '.' )
                                        : 'Op aanvraag';
                                }
                                ?>
                                <tr
                                    class="kivii-service-row"
                                    data-category="<?php echo esc_attr( $service->category_id ); ?>"
                                    data-status="<?php echo (int) $service->is_active === 1 ? 'active' : 'inactive'; ?>"
                                    data-type="<?php echo (int) $service->is_addon === 1 ? 'addon' : 'service'; ?>"
                                    data-search="<?php echo esc_attr( strtolower( implode( ' ', [ $category->name_nl, $service->title_nl, $service->description_nl, $service->long_desc_nl ] ) ) ); ?>"
                                >
                                    <td>
                                        <strong><?php echo esc_html( $service->title_nl ); ?></strong>
                                        <?php if ( $service->title_en ) : ?>
                                            <br><small><?php echo esc_html( $service->title_en ); ?></small>
                                        <?php endif; ?>
                                        <?php if ( $service->description_nl ) : ?>
                                            <div class="kivii-service-summary"><?php echo esc_html( $service->description_nl ); ?></div>
                                        <?php endif; ?>
                                        <?php if ( $service->long_desc_nl ) : ?>
                                            <div class="kivii-service-long"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $service->long_desc_nl ), 20 ) ); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html( $display_price ); ?></td>
                                    <td><?php echo (int) $service->duration_minutes > 0 ? esc_html( $service->duration_minutes . ' min' ) : 'In overleg'; ?></td>
                                    <td><?php echo (int) $service->is_addon === 1 ? 'Add-on' : 'Hoofddienst'; ?></td>
                                    <td>
                                        <?php
                                        echo (int) $service->is_active === 1
                                            ? '<span class="kivii-badge-active">Actief</span>'
                                            : '<span class="kivii-badge-inactive">Inactief</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <button
                                            class="button button-small kivii-edit-service"
                                            data-id="<?php echo esc_attr( $service->id ); ?>"
                                            data-category="<?php echo esc_attr( $service->category_id ); ?>"
                                            data-title-nl="<?php echo esc_attr( $service->title_nl ); ?>"
                                            data-title-en="<?php echo esc_attr( $service->title_en ); ?>"
                                            data-desc-nl="<?php echo esc_attr( $service->description_nl ); ?>"
                                            data-desc-en="<?php echo esc_attr( $service->description_en ); ?>"
                                            data-long-nl="<?php echo esc_attr( $service->long_desc_nl ); ?>"
                                            data-long-en="<?php echo esc_attr( $service->long_desc_en ); ?>"
                                            data-price="<?php echo esc_attr( $service->price ); ?>"
                                            data-price-label-nl="<?php echo esc_attr( $service->price_label_nl ); ?>"
                                            data-price-label-en="<?php echo esc_attr( $service->price_label_en ); ?>"
                                            data-duration="<?php echo esc_attr( $service->duration_minutes ); ?>"
                                            data-addon="<?php echo esc_attr( $service->is_addon ); ?>"
                                            data-active="<?php echo esc_attr( $service->is_active ); ?>"
                                            data-sort="<?php echo esc_attr( $service->sort_order ); ?>"
                                        >Bewerken</button>
                                        <button class="button button-small kivii-delete-service" data-id="<?php echo esc_attr( $service->id ); ?>">Verwijderen</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="kivii-empty-state">Nog geen werkzaamheden in deze categorie.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <?php if ( empty( $categories ) ) : ?>
            <div class="kivii-empty-state">
                <p>Nog geen werkzaamheden geconfigureerd. De standaardcatalogus wordt automatisch geladen zodra de plugin actief is in WordPress.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>var kiviiNonce = '<?php echo esc_js( $nonce ); ?>';</script>
