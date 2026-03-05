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


// ─── PACK REVENDEUR — SÉLECTEUR DE RÉFÉRENCES PARFUMS ────────────────────────
//
// COMMENT ÇA MARCHE :
//   1. Sur chaque produit pack (variable), tu remplis le champ admin
//      "Parfums disponibles" avec les noms séparés par des virgules.
//   2. Sur la page produit, le client sélectionne sa variation (ex: 20 pcs),
//      puis coche les parfums voulus et saisit la quantité par référence.
//   3. Le total doit correspondre à la quantité du pack (validé avant panier).
//   4. Le détail des références apparaît dans le panier, la commande et l'admin.
//
// ─────────────────────────────────────────────────────────────────────────────

// ── ADMIN : champs liste des parfums + quantité du pack ──────────────────────

add_action( 'woocommerce_product_options_general_product_data', 'manzili_pack_parfums_field' );
function manzili_pack_parfums_field() {
    woocommerce_wp_text_input( [
        'id'          => '_pack_quantity',
        'label'       => 'Nombre de parfums à choisir',
        'placeholder' => 'Ex: 10',
        'description' => 'Nombre exact de parfums que le client doit sélectionner (ex: 10 pour un Pack 10).',
        'desc_tip'    => true,
        'type'        => 'number',
    ] );
    woocommerce_wp_textarea_input( [
        'id'          => '_pack_parfums',
        'label'       => 'Parfums disponibles dans ce pack',
        'placeholder' => 'AÏSHA, BAKARA, BLACK OP, BLUE MAGIC, ...',
        'description' => 'Noms séparés par des virgules. Affichés sous forme de cases à cocher sur la page produit.',
        'desc_tip'    => true,
        'rows'        => 5,
    ] );
}

add_action( 'woocommerce_process_product_meta', 'manzili_save_pack_parfums' );
function manzili_save_pack_parfums( $post_id ) {
    $qty = isset( $_POST['_pack_quantity'] ) ? absint( $_POST['_pack_quantity'] ) : 0;
    update_post_meta( $post_id, '_pack_quantity', $qty );
    $val = isset( $_POST['_pack_parfums'] ) ? sanitize_textarea_field( $_POST['_pack_parfums'] ) : '';
    update_post_meta( $post_id, '_pack_parfums', $val );
}

// ── FRONTEND : affichage des cases à cocher ───────────────────────────────────

add_action( 'woocommerce_before_add_to_cart_button', 'manzili_display_parfum_selector' );
function manzili_display_parfum_selector() {
    global $product;

    $raw = get_post_meta( $product->get_id(), '_pack_parfums', true );
    if ( ! $raw ) return;

    $parfums   = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
    if ( empty( $parfums ) ) return;

    // Quantité fixe (produit simple) ou dynamique (produit variable via JS)
    $pack_qty  = (int) get_post_meta( $product->get_id(), '_pack_quantity', true );
    $is_variable = $product->is_type( 'variable' );
    $max_display = ( ! $is_variable && $pack_qty > 0 ) ? $pack_qty : '—';

    ?>
    <div class="manzili-pack-selector" data-pack-qty="<?php echo esc_attr( $pack_qty ); ?>" data-is-variable="<?php echo $is_variable ? '1' : '0'; ?>">
        <div class="manzili-pack-header">
            <strong>Choisissez vos références</strong>
            <span class="manzili-counter">
                <span class="manzili-count">0</span> / <span class="manzili-pack-max"><?php echo esc_html( $max_display ); ?></span> pcs sélectionnés
            </span>
        </div>

        <div class="manzili-parfums-grid">
            <?php foreach ( $parfums as $parfum ) :
                $uid = 'parfum_' . sanitize_title( $parfum );
            ?>
            <div class="manzili-parfum-item" data-parfum="<?php echo esc_attr( $parfum ); ?>">
                <label class="manzili-parfum-label">
                    <input type="checkbox"
                           class="manzili-parfum-check"
                           name="manzili_parfums[]"
                           value="<?php echo esc_attr( $parfum ); ?>"
                           id="<?php echo esc_attr( $uid ); ?>">
                    <span class="manzili-parfum-name"><?php echo esc_html( $parfum ); ?></span>
                </label>
                <input type="number"
                       class="manzili-parfum-qty"
                       name="manzili_qty[<?php echo esc_attr( $parfum ); ?>]"
                       value="1"
                       min="1"
                       max="999"
                       style="display:none;">
            </div>
            <?php endforeach; ?>
        </div>

        <p class="manzili-error" style="display:none;"></p>
    </div>
    <?php
}

// ── FRONTEND : JS + CSS ───────────────────────────────────────────────────────

add_action( 'wp_footer', 'manzili_pack_selector_assets' );
function manzili_pack_selector_assets() {
    if ( ! is_product() ) return;
    global $product;
    if ( ! $product ) return;
    if ( ! get_post_meta( $product->get_id(), '_pack_parfums', true ) ) return;
    ?>
    <style>
        .manzili-pack-selector {
            margin: 24px 0 16px;
            padding: 16px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background: #fafafa;
        }
        .manzili-pack-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            flex-wrap: wrap;
            gap: 6px;
        }
        .manzili-counter {
            font-size: 13px;
            color: #555;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 3px 12px;
        }
        .manzili-counter .manzili-count {
            font-weight: 700;
            color: #1a1a1a;
        }
        .manzili-parfums-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 8px;
        }
        .manzili-parfum-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            transition: border-color 0.15s, background 0.15s;
        }
        .manzili-parfum-item.is-checked {
            border-color: #1a1a1a;
            background: #f0f0f0;
        }
        .manzili-parfum-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            flex: 1;
            margin: 0;
        }
        .manzili-parfum-name {
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .manzili-parfum-qty {
            width: 56px;
            padding: 4px 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-align: center;
            font-size: 13px;
            flex-shrink: 0;
        }
        .manzili-error {
            margin-top: 12px;
            padding: 10px 14px;
            background: #fff3f3;
            border: 1px solid #e88;
            border-radius: 4px;
            color: #c00;
            font-size: 13px;
        }
        @media (max-width: 480px) {
            .manzili-parfums-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
    (function () {
        'use strict';

        var selector = document.querySelector('.manzili-pack-selector');
        var isVariable = selector && selector.getAttribute('data-is-variable') === '1';
        var fixedQty   = selector ? parseInt(selector.getAttribute('data-pack-qty'), 10) : 0;

        // Pour produit simple : quantité fixe (data-pack-qty)
        // Pour produit variable : lit la variation sélectionnée ex "20 pcs" → 20
        function getPackQty() {
            if (!isVariable) return fixedQty > 0 ? fixedQty : 0;
            var selects = document.querySelectorAll('.variations select');
            for (var i = 0; i < selects.length; i++) {
                var opt = selects[i].options[selects[i].selectedIndex];
                if (!opt || !opt.value) continue;
                var match = opt.text.match(/(\d+)/);
                if (match) return parseInt(match[1], 10);
            }
            return 0;
        }

        function updateCounter() {
            var total = 0;
            document.querySelectorAll('.manzili-parfum-qty').forEach(function (inp) {
                if (inp.style.display !== 'none') {
                    total += parseInt(inp.value, 10) || 0;
                }
            });
            var countEl = document.querySelector('.manzili-count');
            if (countEl) countEl.textContent = total;
            return total;
        }

        function updatePackMax() {
            var qty = getPackQty();
            var maxEl = document.querySelector('.manzili-pack-max');
            if (maxEl) maxEl.textContent = qty > 0 ? qty : '—';
        }

        // Cases à cocher
        document.querySelectorAll('.manzili-parfum-check').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var item = this.closest('.manzili-parfum-item');
                var qtyInput = item.querySelector('.manzili-parfum-qty');
                if (this.checked) {
                    item.classList.add('is-checked');
                    qtyInput.style.display = 'inline-block';
                    qtyInput.value = 1;
                } else {
                    item.classList.remove('is-checked');
                    qtyInput.style.display = 'none';
                    qtyInput.value = 1;
                }
                updateCounter();
            });
        });

        // Saisie quantité
        document.querySelectorAll('.manzili-parfum-qty').forEach(function (inp) {
            inp.addEventListener('input', function () { updateCounter(); });
        });

        // Changement de variation → mettre à jour l'affichage max
        var form = document.querySelector('form.variations_form');
        if (form) {
            form.addEventListener('found_variation', function () {
                updatePackMax();
                updateCounter();
            });
            form.addEventListener('reset_data', function () {
                var maxEl = document.querySelector('.manzili-pack-max');
                if (maxEl) maxEl.textContent = '—';
            });
        }

        // Validation avant ajout au panier
        var cartForm = document.querySelector('form.cart');
        if (cartForm) {
            cartForm.addEventListener('submit', function (e) {
                var packQty  = getPackQty();
                var selected = document.querySelectorAll('.manzili-parfum-check:checked').length;
                var total    = updateCounter();
                var errorEl  = document.querySelector('.manzili-error');

                // Pas de parfums cochés → OK (produit sans liste de parfums)
                if (selected === 0) {
                    if (errorEl) errorEl.style.display = 'none';
                    return;
                }

                // Vérification du total
                if (packQty > 0 && total !== packQty) {
                    e.preventDefault();
                    if (errorEl) {
                        errorEl.textContent = 'Le total doit être exactement ' + packQty
                            + ' pcs. Vous en avez sélectionné ' + total + '.';
                        errorEl.style.display = 'block';
                        errorEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                } else {
                    if (errorEl) errorEl.style.display = 'none';
                }
            });
        }

    })();
    </script>
    <?php
}

// ── PANIER : sauvegarder la sélection ────────────────────────────────────────

add_filter( 'woocommerce_add_cart_item_data', 'manzili_save_parfums_to_cart', 10, 2 );
function manzili_save_parfums_to_cart( $cart_item_data, $product_id ) {
    if ( empty( $_POST['manzili_parfums'] ) ) return $cart_item_data;

    $selection = [];
    foreach ( $_POST['manzili_parfums'] as $parfum ) {
        $parfum = sanitize_text_field( $parfum );
        $qty    = isset( $_POST['manzili_qty'][ $parfum ] ) ? absint( $_POST['manzili_qty'][ $parfum ] ) : 1;
        if ( $qty > 0 ) {
            $selection[ $parfum ] = $qty;
        }
    }

    if ( ! empty( $selection ) ) {
        $cart_item_data['manzili_parfums'] = $selection;
        // Clé unique pour éviter que WooCommerce fusionne des paniers différents
        $cart_item_data['manzili_unique_key'] = md5( serialize( $selection ) );
    }

    return $cart_item_data;
}

// ── PANIER : afficher le détail des références ────────────────────────────────

add_filter( 'woocommerce_get_item_data', 'manzili_display_parfums_in_cart', 10, 2 );
function manzili_display_parfums_in_cart( $item_data, $cart_item ) {
    if ( empty( $cart_item['manzili_parfums'] ) ) return $item_data;

    $lines = [];
    foreach ( $cart_item['manzili_parfums'] as $parfum => $qty ) {
        $lines[] = esc_html( $parfum ) . ' × ' . intval( $qty );
    }

    $item_data[] = [
        'name'  => 'Références choisies',
        'value' => implode( ', ', $lines ),
    ];

    return $item_data;
}

// ── COMMANDE : sauvegarder dans les méta de la ligne ─────────────────────────

add_action( 'woocommerce_checkout_create_order_line_item', 'manzili_save_parfums_to_order', 10, 4 );
function manzili_save_parfums_to_order( $item, $cart_item_key, $values, $order ) {
    if ( empty( $values['manzili_parfums'] ) ) return;

    $lines = [];
    foreach ( $values['manzili_parfums'] as $parfum => $qty ) {
        $lines[] = esc_html( $parfum ) . ' × ' . intval( $qty );
    }

    $item->add_meta_data( 'Références choisies', implode( ', ', $lines ), true );
}
