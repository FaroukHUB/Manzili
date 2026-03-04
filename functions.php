<?php
// Add custom Theme Functions here

// ─── BADGE PERSONNALISÉ PAR PRODUIT ──────────────────────────────────────────

// 1. Ajouter le champ dans l'admin produit (onglet Général)
add_action( 'woocommerce_product_options_general_product_data', 'manzili_add_badge_field' );
function manzili_add_badge_field() {
    woocommerce_wp_text_input( [
        'id'          => '_custom_badge',
        'label'       => 'Badge personnalisé',
        'placeholder' => 'Ex: Nouveau, Exclusif, Édition limitée...',
        'desc_tip'    => true,
        'description' => 'Texte affiché en badge sur l\'image du produit. Laisser vide pour ne pas afficher.',
    ] );
}

// 2. Sauvegarder le champ
add_action( 'woocommerce_process_product_meta', 'manzili_save_badge_field' );
function manzili_save_badge_field( $post_id ) {
    $badge = isset( $_POST['_custom_badge'] ) ? sanitize_text_field( $_POST['_custom_badge'] ) : '';
    update_post_meta( $post_id, '_custom_badge', $badge );
}

// 3. Afficher le badge sur la boutique (liste de produits)
add_action( 'woocommerce_before_shop_loop_item_title', 'manzili_display_badge', 5 );
function manzili_display_badge() {
    global $product;
    $badge = get_post_meta( $product->get_id(), '_custom_badge', true );
    if ( $badge ) {
        echo '<span class="manzili-badge">' . esc_html( $badge ) . '</span>';
    }
}

// 4. Afficher le badge sur la page produit individuelle
add_action( 'woocommerce_before_single_product_summary', 'manzili_display_badge_single', 5 );
function manzili_display_badge_single() {
    global $product;
    $badge = get_post_meta( $product->get_id(), '_custom_badge', true );
    if ( $badge ) {
        echo '<span class="manzili-badge manzili-badge--single">' . esc_html( $badge ) . '</span>';
    }
}

// 5. CSS du badge (injecté dans le <head>)
add_action( 'wp_head', 'manzili_badge_styles' );
function manzili_badge_styles() {
    ?>
    <style>
        /* Badge sur la liste de produits */
        .woocommerce ul.products li.product {
            position: relative;
        }
        .manzili-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #1a1a1a;
            color: #fff;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-radius: 3px;
            z-index: 10;
            pointer-events: none;
        }

        /* Badge sur la page produit individuelle */
        .woocommerce div.product {
            position: relative;
        }
        .manzili-badge--single {
            top: 15px;
            left: 15px;
            font-size: 13px;
            padding: 6px 14px;
        }
    </style>
    <?php
}
