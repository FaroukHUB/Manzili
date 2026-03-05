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

// ── FRONTEND : sélecteur parfums ─────────────────────────────────────────────

add_action( 'woocommerce_before_add_to_cart_button', 'manzili_display_parfum_selector' );
function manzili_display_parfum_selector() {
    global $product;

    $raw = get_post_meta( $product->get_id(), '_pack_parfums', true );
    if ( ! $raw ) return;

    $parfums     = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
    if ( empty( $parfums ) ) return;

    $pack_qty    = (int) get_post_meta( $product->get_id(), '_pack_quantity', true );
    $is_variable = $product->is_type( 'variable' );
    $max_display = ( ! $is_variable && $pack_qty > 0 ) ? $pack_qty : '—';
    ?>
    <div class="mzl-selector" data-pack-qty="<?php echo esc_attr( $pack_qty ); ?>" data-is-variable="<?php echo $is_variable ? '1' : '0'; ?>">

        <div class="mzl-header">
            <span class="mzl-title">CHOISISSEZ VOS PARFUMS <em>*</em></span>
            <span class="mzl-pill"><strong class="mzl-count">0</strong> / <span class="mzl-pack-max"><?php echo esc_html( $max_display ); ?></span> pcs</span>
        </div>

        <div class="mzl-grid">
            <?php foreach ( $parfums as $parfum ) :
                $uid = 'mzl_' . sanitize_title( $parfum );
            ?>
            <div class="mzl-item" data-parfum="<?php echo esc_attr( $parfum ); ?>">
                <div class="mzl-left">
                    <input type="checkbox"
                           class="mzl-check"
                           name="manzili_parfums[]"
                           value="<?php echo esc_attr( $parfum ); ?>"
                           id="<?php echo esc_attr( $uid ); ?>">
                    <label for="<?php echo esc_attr( $uid ); ?>" class="mzl-name"><?php echo esc_html( $parfum ); ?></label>
                </div>
                <div class="mzl-stepper">
                    <button type="button" class="mzl-btn mzl-minus" aria-label="Moins">−</button>
                    <span class="mzl-qty-display">0</span>
                    <button type="button" class="mzl-btn mzl-plus" aria-label="Plus">+</button>
                    <input type="hidden" class="manzili-parfum-qty" name="manzili_qty[<?php echo esc_attr( $parfum ); ?>]" value="0">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="mzl-error" style="display:none;"></p>
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
        /* ── Conteneur ── */
        .mzl-selector {
            margin: 28px 0 20px;
            font-family: inherit;
        }

        /* ── En-tête ── */
        .mzl-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .mzl-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #111;
        }
        .mzl-title em {
            color: #c0392b;
            font-style: normal;
        }
        .mzl-pill {
            font-size: 12px;
            color: #555;
            border: 1px solid #ccc;
            border-radius: 20px;
            padding: 4px 14px;
            white-space: nowrap;
        }
        .mzl-pill strong {
            color: #111;
            font-weight: 700;
        }

        /* ── Grille 2 colonnes ── */
        .mzl-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-top: 1px solid #e5e5e5;
            border-left: 1px solid #e5e5e5;
        }
        @media (max-width: 520px) {
            .mzl-grid { grid-template-columns: 1fr; }
        }

        /* ── Ligne parfum ── */
        .mzl-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 14px;
            border-right: 1px solid #e5e5e5;
            border-bottom: 1px solid #e5e5e5;
            gap: 10px;
            transition: background 0.12s;
            min-height: 52px;
        }
        .mzl-item.is-active {
            background: #f7f7f7;
        }

        /* ── Partie gauche : checkbox + nom ── */
        .mzl-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }
        .mzl-check {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            cursor: pointer;
            accent-color: #111;
        }
        .mzl-name {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #222;
            cursor: pointer;
            line-height: 1.3;
            margin: 0;
        }

        /* ── Stepper ── */
        .mzl-stepper {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .mzl-btn {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 1.5px solid #bbb;
            background: transparent;
            color: #888;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: border-color 0.12s, color 0.12s, background 0.12s;
            flex-shrink: 0;
        }
        .mzl-btn:hover {
            border-color: #111;
            color: #111;
        }
        .mzl-btn.mzl-plus {
            border-color: #111;
            color: #111;
            font-weight: 700;
        }
        .mzl-btn.mzl-minus:disabled {
            opacity: 0.3;
            cursor: default;
        }
        .mzl-qty-display {
            font-size: 13px;
            font-weight: 600;
            color: #111;
            min-width: 18px;
            text-align: center;
        }

        /* ── Erreur ── */
        .mzl-error {
            margin-top: 14px;
            padding: 11px 16px;
            background: #fff5f5;
            border-left: 3px solid #c0392b;
            color: #c0392b;
            font-size: 13px;
            border-radius: 0 4px 4px 0;
        }
    </style>

    <script>
    (function () {
        'use strict';

        var selector   = document.querySelector('.mzl-selector');
        if (!selector) return;

        var isVariable = selector.getAttribute('data-is-variable') === '1';
        var fixedQty   = parseInt(selector.getAttribute('data-pack-qty'), 10) || 0;

        function getPackQty() {
            if (!isVariable) return fixedQty;
            var selects = document.querySelectorAll('.variations select');
            for (var i = 0; i < selects.length; i++) {
                var opt = selects[i].options[selects[i].selectedIndex];
                if (!opt || !opt.value) continue;
                var m = opt.text.match(/(\d+)/);
                if (m) return parseInt(m[1], 10);
            }
            return 0;
        }

        function getTotal() {
            var total = 0;
            selector.querySelectorAll('.manzili-parfum-qty').forEach(function (inp) {
                total += parseInt(inp.value, 10) || 0;
            });
            return total;
        }

        function refreshCounter() {
            var total  = getTotal();
            var countEl = selector.querySelector('.mzl-count');
            if (countEl) countEl.textContent = total;
        }

        function updatePackMax() {
            var qty   = getPackQty();
            var maxEl = selector.querySelector('.mzl-pack-max');
            if (maxEl) maxEl.textContent = qty > 0 ? qty : '—';
        }

        function setItemQty(item, newQty) {
            var hidden  = item.querySelector('.manzili-parfum-qty');
            var display = item.querySelector('.mzl-qty-display');
            var check   = item.querySelector('.mzl-check');
            var minus   = item.querySelector('.mzl-minus');

            newQty = Math.max(0, newQty);
            hidden.value        = newQty;
            display.textContent = newQty;
            check.checked       = newQty > 0;
            minus.disabled      = newQty === 0;
            item.classList.toggle('is-active', newQty > 0);
            refreshCounter();
        }

        /* ── Boutons + et − ── */
        selector.addEventListener('click', function (e) {
            var btn  = e.target.closest('.mzl-btn');
            if (!btn) return;
            var item = btn.closest('.mzl-item');
            var cur  = parseInt(item.querySelector('.manzili-parfum-qty').value, 10) || 0;
            if (btn.classList.contains('mzl-plus'))  setItemQty(item, cur + 1);
            if (btn.classList.contains('mzl-minus')) setItemQty(item, cur - 1);
        });

        /* ── Checkbox ── */
        selector.addEventListener('change', function (e) {
            var cb = e.target.closest('.mzl-check');
            if (!cb) return;
            var item = cb.closest('.mzl-item');
            var cur  = parseInt(item.querySelector('.manzili-parfum-qty').value, 10) || 0;
            if (!cb.checked) {
                setItemQty(item, 0);
            } else if (cur === 0) {
                setItemQty(item, 1);
            }
        });

        /* ── Variation change ── */
        var varForm = document.querySelector('form.variations_form');
        if (varForm) {
            varForm.addEventListener('found_variation', function () { updatePackMax(); refreshCounter(); });
            varForm.addEventListener('reset_data', function () {
                var maxEl = selector.querySelector('.mzl-pack-max');
                if (maxEl) maxEl.textContent = '—';
            });
        }

        /* ── Validation soumission ── */
        var cartForm = document.querySelector('form.cart');
        if (cartForm) {
            cartForm.addEventListener('submit', function (e) {
                var packQty = getPackQty();
                if (packQty <= 0) return;

                var total   = getTotal();
                var errorEl = selector.querySelector('.mzl-error');

                if (total !== packQty) {
                    e.preventDefault();
                    errorEl.textContent = 'Vous devez sélectionner exactement ' + packQty + ' pcs. Total actuel\u00a0: ' + total + '.';
                    errorEl.style.display = 'block';
                    errorEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    errorEl.style.display = 'none';
                }
            });
        }

        /* Init : désactiver tous les boutons − */
        selector.querySelectorAll('.mzl-minus').forEach(function (btn) { btn.disabled = true; });

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
