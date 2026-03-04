<?php
// Add custom Theme Functions here

/**
 * Création automatique de l'attribut global "Quantité" pour les Packs Revendeurs
 * S'exécute une seule fois via l'option 'manzili_quantite_attr_created'
 */
add_action( 'init', 'manzili_create_quantite_attribute' );
function manzili_create_quantite_attribute() {

    if ( get_option( 'manzili_quantite_attr_created' ) ) {
        return;
    }

    if ( ! function_exists( 'wc_create_attribute' ) ) {
        return;
    }

    // Créer l'attribut global "Quantité"
    $attribute_id = wc_create_attribute( array(
        'name'         => 'Quantité',
        'slug'         => 'quantite',
        'type'         => 'select',
        'order_by'     => 'menu_order',
        'has_archives' => false,
    ) );

    if ( is_wp_error( $attribute_id ) ) {
        return;
    }

    // Toutes les valeurs de quantité pour les 3 collections
    $terms = array(
        '10 pcs',
        '20 pcs',
        '35 pcs',
        '40 pcs',
        '48 pcs',
        '50 pcs',
        '60 pcs',
        '96 pcs',
        '100 pcs',
        '2 boîtes (56 pcs)',
        '4 boîtes (112 pcs)',
        '8 boîtes (224 pcs)',
        '12 pcs',
        '24 pcs',
    );

    foreach ( $terms as $term_name ) {
        wp_insert_term( $term_name, 'pa_quantite' );
    }

    update_option( 'manzili_quantite_attr_created', true );
}
