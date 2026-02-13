<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$repo = new \Kivii\Database\ServiceRepository();
$categories = $repo->get_categories();
$nonce = wp_create_nonce( 'kivii_admin_nonce' );
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Werkzaamheden</h1>

    <div class="kivii-toolbar">
        <button class="button button-primary" id="kivii-add-category">+ Categorie toevoegen</button>
        <button class="button" id="kivii-add-service">+ Werkzaamheid toevoegen</button>
        <button class="button" id="kivii-export-services">Exporteer JSON</button>
        <button class="button" id="kivii-import-services">Importeer JSON</button>
        <input type="file" id="kivii-import-file" accept=".json" style="display:none;">
    </div>

    <!-- Category form modal -->
    <div id="kivii-category-modal" class="kivii-modal" style="display:none;">
        <div class="kivii-modal-content">
            <h2>Categorie</h2>
            <input type="hidden" id="cat-id" value="0">
            <table class="form-table">
                <tr><th>Naam (NL)</th><td><input type="text" id="cat-name-nl" class="regular-text"></td></tr>
                <tr><th>Naam (EN)</th><td><input type="text" id="cat-name-en" class="regular-text"></td></tr>
                <tr><th>Volgorde</th><td><input type="number" id="cat-sort" class="small-text" value="0"></td></tr>
                <tr><th>Actief</th><td><input type="checkbox" id="cat-active" checked></td></tr>
            </table>
            <button class="button button-primary" id="kivii-save-category">Opslaan</button>
            <button class="button kivii-modal-close">Annuleren</button>
        </div>
    </div>

    <!-- Service form modal -->
    <div id="kivii-service-modal" class="kivii-modal" style="display:none;">
        <div class="kivii-modal-content">
            <h2>Werkzaamheid</h2>
            <input type="hidden" id="svc-id" value="0">
            <table class="form-table">
                <tr><th>Categorie</th>
                    <td><select id="svc-category">
                        <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->id ); ?>"><?php echo esc_html( $cat->name_nl ); ?></option>
                        <?php endforeach; ?>
                    </select></td>
                </tr>
                <tr><th>Titel (NL)</th><td><input type="text" id="svc-title-nl" class="regular-text"></td></tr>
                <tr><th>Titel (EN)</th><td><input type="text" id="svc-title-en" class="regular-text"></td></tr>
                <tr><th>Korte omschrijving (NL)</th><td><textarea id="svc-desc-nl" class="large-text" rows="2"></textarea></td></tr>
                <tr><th>Korte omschrijving (EN)</th><td><textarea id="svc-desc-en" class="large-text" rows="2"></textarea></td></tr>
                <tr><th>Uitgebreide omschrijving (NL)</th><td><textarea id="svc-long-nl" class="large-text" rows="4"></textarea></td></tr>
                <tr><th>Uitgebreide omschrijving (EN)</th><td><textarea id="svc-long-en" class="large-text" rows="4"></textarea></td></tr>
                <tr><th>Prijs (€)</th><td><input type="number" id="svc-price" step="0.01" min="0" class="small-text"></td></tr>
                <tr><th>Duur (minuten)</th><td><input type="number" id="svc-duration" min="5" class="small-text" value="30"></td></tr>
                <tr><th>Is add-on</th><td><input type="checkbox" id="svc-addon"></td></tr>
                <tr><th>Actief</th><td><input type="checkbox" id="svc-active" checked></td></tr>
                <tr><th>Volgorde</th><td><input type="number" id="svc-sort" class="small-text" value="0"></td></tr>
            </table>
            <button class="button button-primary" id="kivii-save-service">Opslaan</button>
            <button class="button kivii-modal-close">Annuleren</button>
        </div>
    </div>

    <!-- Existing data -->
    <div id="kivii-services-list">
        <?php foreach ( $categories as $cat ) : ?>
        <div class="kivii-category-block" data-id="<?php echo esc_attr( $cat->id ); ?>">
            <div class="kivii-category-header">
                <h2><?php echo esc_html( $cat->name_nl ); ?>
                    <?php if ( $cat->name_en ) : ?> <small>(<?php echo esc_html( $cat->name_en ); ?>)</small><?php endif; ?>
                    <?php if ( ! $cat->is_active ) : ?> <span class="kivii-badge-inactive">Inactief</span><?php endif; ?>
                </h2>
                <div>
                    <button class="button button-small kivii-edit-category" data-id="<?php echo esc_attr( $cat->id ); ?>" data-nl="<?php echo esc_attr( $cat->name_nl ); ?>" data-en="<?php echo esc_attr( $cat->name_en ); ?>" data-sort="<?php echo esc_attr( $cat->sort_order ); ?>" data-active="<?php echo esc_attr( $cat->is_active ); ?>">Bewerken</button>
                    <button class="button button-small kivii-delete-category" data-id="<?php echo esc_attr( $cat->id ); ?>">Verwijderen</button>
                </div>
            </div>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Prijs</th>
                        <th>Duur</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $services = $repo->get_services( (int) $cat->id );
                foreach ( $services as $svc ) :
                ?>
                    <tr>
                        <td><strong><?php echo esc_html( $svc->title_nl ); ?></strong>
                            <?php if ( $svc->description_nl ) : ?><br><small><?php echo esc_html( $svc->description_nl ); ?></small><?php endif; ?>
                        </td>
                        <td>€ <?php echo number_format( (float) $svc->price, 2, ',', '.' ); ?></td>
                        <td><?php echo (int) $svc->duration_minutes; ?> min</td>
                        <td><?php echo $svc->is_addon ? 'Add-on' : 'Hoofddienst'; ?></td>
                        <td><?php echo $svc->is_active ? '<span class="kivii-badge-active">Actief</span>' : '<span class="kivii-badge-inactive">Inactief</span>'; ?></td>
                        <td>
                            <button class="button button-small kivii-edit-service"
                                data-id="<?php echo esc_attr( $svc->id ); ?>"
                                data-category="<?php echo esc_attr( $svc->category_id ); ?>"
                                data-title-nl="<?php echo esc_attr( $svc->title_nl ); ?>"
                                data-title-en="<?php echo esc_attr( $svc->title_en ); ?>"
                                data-desc-nl="<?php echo esc_attr( $svc->description_nl ); ?>"
                                data-desc-en="<?php echo esc_attr( $svc->description_en ); ?>"
                                data-long-nl="<?php echo esc_attr( $svc->long_desc_nl ); ?>"
                                data-long-en="<?php echo esc_attr( $svc->long_desc_en ); ?>"
                                data-price="<?php echo esc_attr( $svc->price ); ?>"
                                data-duration="<?php echo esc_attr( $svc->duration_minutes ); ?>"
                                data-addon="<?php echo esc_attr( $svc->is_addon ); ?>"
                                data-active="<?php echo esc_attr( $svc->is_active ); ?>"
                                data-sort="<?php echo esc_attr( $svc->sort_order ); ?>"
                            >Bewerken</button>
                            <button class="button button-small kivii-delete-service" data-id="<?php echo esc_attr( $svc->id ); ?>">Verwijderen</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>

        <?php if ( empty( $categories ) ) : ?>
        <div class="kivii-empty-state">
            <p>Nog geen werkzaamheden geconfigureerd. Voeg eerst een categorie toe.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>var kiviiNonce = '<?php echo esc_js( $nonce ); ?>';</script>
