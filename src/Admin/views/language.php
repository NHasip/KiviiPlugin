<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$lang = $_GET['lang'] ?? 'nl';
$texts = get_option( "kivii_texts_{$lang}", [] );
$defaults = \Kivii\I18n\Translator::instance()->defaults();
$default_texts = $defaults[ $lang ] ?? $defaults['nl'];
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Taalinstellingen</h1>
    <div class="kivii-lang-tabs">
        <a href="?page=kivii-language&lang=nl" class="button <?php echo $lang === 'nl' ? 'button-primary' : ''; ?>">Nederlands</a>
        <a href="?page=kivii-language&lang=en" class="button <?php echo $lang === 'en' ? 'button-primary' : ''; ?>">English</a>
    </div>
    <p>Pas hieronder alle teksten aan voor <strong><?php echo $lang === 'nl' ? 'Nederlands' : 'English'; ?></strong>. Leeg laten = standaardtekst gebruiken.</p>
    <form method="post" action="options.php">
        <?php settings_fields( "kivii_texts_{$lang}_group" ); ?>
        <table class="form-table">
            <?php foreach ( $default_texts as $key => $default ) : ?>
            <tr>
                <th><label for="text_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $key ); ?></label></th>
                <td>
                    <input type="text" id="text_<?php echo esc_attr( $key ); ?>"
                           name="kivii_texts_<?php echo esc_attr( $lang ); ?>[<?php echo esc_attr( $key ); ?>]"
                           value="<?php echo esc_attr( $texts[ $key ] ?? '' ); ?>"
                           class="large-text"
                           placeholder="<?php echo esc_attr( $default ); ?>">
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php submit_button( 'Opslaan' ); ?>
    </form>
</div>
