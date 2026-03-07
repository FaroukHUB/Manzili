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
        .manzili-badge {
            display: block;
            width: 100%;
            background: #f5f0e8;
            color: #1a1a1a;
            padding: 8px 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: center;
            box-sizing: border-box;
            pointer-events: none;
        }
        .manzili-badge--single {
            font-size: 13px;
            padding: 10px 0;
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
            if (btn.classList.contains('mzl-plus')) {
                var packQty = getPackQty();
                if (packQty > 0 && getTotal() >= packQty) return;
                setItemQty(item, cur + 1);
            }
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


// ─── SLIDER LUXURY — SHORTCODE [manzili_slider] ───────────────────────────────
//
// USAGE : colle [manzili_slider] dans n'importe quelle page/widget Elementor.
// IMAGES : place tes fichiers dans /wp-content/themes/TON-THEME/images/
//   • bg-bois-intense.jpg     bottle-bois-intense.png
//   • bg-krypton.jpg          bottle-krypton.png
//   • bg-rose-vanille.jpg     bottle-rose-vanille.png
//   • bg-coton-frais.jpg      bottle-coton-frais.png
//   • bg-pistachio.jpg        bottle-pistachio.png
//   • bg-dima-morocco.jpg     bottle-dima-morocco.png
//
// ─────────────────────────────────────────────────────────────────────────────

add_shortcode( 'manzili_slider', 'manzili_slider_shortcode' );
function manzili_slider_shortcode() {

    $img = get_template_directory_uri() . '/images/';

    $slides = [
        [
            'key'        => 'bois-intense',
            'name'       => 'Bois Intense',
            'collection' => 'Collection Privée · L\'Original',
            'notes'      => 'Iris · Vétiver · Musc · Bois de Santal',
            'color'      => '#1a2010',
            'anchor'     => 'collection-privee-original',
            'bottle_light' => false,
            'title_size' => '',
        ],
        [
            'key'        => 'krypton',
            'name'       => 'Krypton',
            'collection' => 'Collection Privée · Paris',
            'notes'      => 'Bergamote · Cèdre · Vétiver · Ambre Vert',
            'color'      => '#0a1505',
            'anchor'     => 'collection-privee-paris',
            'bottle_light' => true,
            'title_size' => '',
        ],
        [
            'key'        => 'rose-vanille',
            'name'       => 'Rose Vanille',
            'collection' => 'Collection Privée · Intense',
            'notes'      => 'Rose · Vanille · Jasmin · Musc Blanc',
            'color'      => '#6b3040',
            'anchor'     => 'collection-privee-intense',
            'bottle_light' => false,
            'title_size' => 'font-size:clamp(34px,5vw,80px);',
            'overlay_strong' => true,
        ],
        [
            'key'        => 'coton-frais',
            'name'       => 'Coton Frais',
            'collection' => 'Collection Privée · L\'Original',
            'notes'      => 'Coton · Musc Blanc · Iris · Bois de Santal',
            'color'      => '#d0dce8',
            'anchor'     => 'collection-privee-original',
            'bottle_light' => false,
            'title_size' => 'font-size:clamp(34px,5vw,80px);',
            'overlay_strong' => true,
        ],
        [
            'key'        => 'pistachio',
            'name'       => 'Pistachio',
            'collection' => 'Collection Privée · L\'Original',
            'notes'      => 'Pistache · Amande · Musc Blanc · Vanille',
            'color'      => '#7aad6a',
            'anchor'     => 'collection-privee-original',
            'bottle_light' => false,
            'title_size' => '',
            'overlay_strong' => true,
        ],
        [
            'key'        => 'dima-morocco',
            'name'       => 'Dima Morocco',
            'collection' => 'Collection Privée · Intense',
            'notes'      => 'Rose · Oud · Ambre · Épices Orientales',
            'color'      => '#7a4020',
            'anchor'     => 'collection-privee-intense',
            'bottle_light' => false,
            'title_size' => 'font-size:clamp(28px,4.5vw,72px);',
            'overlay_strong' => true,
        ],
    ];

    $total = count( $slides );

    ob_start();
    ?>
    <style>
    /* ─── RESET SLIDER ─── */
    #mzl-luxury-slider *, #mzl-luxury-slider *::before, #mzl-luxury-slider *::after {
        margin: 0; padding: 0; box-sizing: border-box;
    }
    :root {
        --mzl-gold:       #c9a96e;
        --mzl-gold-light: #e8d5a3;
        --mzl-white:      #ffffff;
        --mzl-ease:       cubic-bezier(0.76, 0, 0.24, 1);
        --mzl-ease-out:   cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* ─── CUSTOM CURSOR ─── */
    .mzl-cursor {
        position: fixed; width: 7px; height: 7px;
        background: var(--mzl-gold); border-radius: 50%;
        pointer-events: none; z-index: 10000;
        transform: translate(-50%, -50%);
        transition: transform 0.05s;
        mix-blend-mode: difference;
    }
    .mzl-cursor-ring {
        position: fixed; width: 40px; height: 40px;
        border: 1px solid rgba(201,169,110,0.6); border-radius: 50%;
        pointer-events: none; z-index: 9999;
        transform: translate(-50%, -50%);
        transition: all 0.35s var(--mzl-ease);
    }
    .mzl-cursor-ring.expanded { width: 70px; height: 70px; border-color: rgba(201,169,110,0.3); }

    /* ─── SLIDER ─── */
    #mzl-luxury-slider {
        position: relative; width: 100%; height: 100vh;
        overflow: hidden; cursor: none; background: #000;
    }

    /* ─── SLIDE ─── */
    .mzl-slide {
        position: absolute; inset: 0;
        visibility: hidden; z-index: 1;
    }
    .mzl-slide.is-active { visibility: visible; z-index: 2; }

    /* Background */
    .mzl-slide__bg {
        position: absolute; inset: 0;
        background-size: cover; background-position: center;
        will-change: transform;
        transition: transform 10s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .mzl-slide.is-active .mzl-slide__bg { transform: scale(1) !important; }

    /* Overlays — assombrit UNIQUEMENT la gauche, laisse la droite (bouteille) nette */
    .mzl-slide__overlay {
        position: absolute; inset: 0;
        background: linear-gradient(
            to right,
            rgba(0,0,0,0.88) 0%,
            rgba(0,0,0,0.60) 30%,
            rgba(0,0,0,0.20) 55%,
            rgba(0,0,0,0.00) 72%
        );
    }
    .mzl-slide__overlay--strong {
        background: linear-gradient(
            to right,
            rgba(0,0,0,0.92) 0%,
            rgba(0,0,0,0.70) 32%,
            rgba(0,0,0,0.25) 58%,
            rgba(0,0,0,0.00) 75%
        );
    }
    .mzl-slide__overlay-bottom {
        position: absolute; bottom: 0; left: 0; right: 0; height: 30%;
        background: linear-gradient(to top, rgba(0,0,0,0.40) 0%, transparent 100%);
    }

    /* Grain */
    .mzl-slide__grain {
        position: absolute; inset: 0; opacity: 0.03;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
        background-size: 300px; pointer-events: none;
    }

    /* Watermark */
    .mzl-slide__watermark {
        position: absolute; top: 5%; left: 50%; transform: translateX(-50%);
        font-size: clamp(30px, 6vw, 90px); font-weight: 900;
        letter-spacing: 0.28em; text-transform: uppercase;
        color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,0.06);
        white-space: nowrap; pointer-events: none; z-index: 3;
        opacity: 0; transition: opacity 1.2s 0.2s;
    }
    .mzl-slide.is-active .mzl-slide__watermark { opacity: 1; }

    /* ─── LAYOUT ─── */
    .mzl-slide__layout {
        position: absolute; inset: 0; z-index: 5;
        display: grid; grid-template-columns: 46% 54%;
    }

    /* ─── TEXTE (gauche) ─── */
    .mzl-slide__content {
        display: flex; flex-direction: column; justify-content: center;
        padding: 0 5% 0 7%; padding-top: 10%;
    }
    .mzl-slide__label {
        font-size: 10px; letter-spacing: 7px; text-transform: uppercase;
        color: var(--mzl-gold); margin-bottom: 20px;
        opacity: 0; transform: translateY(18px);
        transition: opacity 0.7s 0.35s var(--mzl-ease-out), transform 0.7s 0.35s var(--mzl-ease-out);
    }
    .mzl-slide.is-active .mzl-slide__label { opacity: 1; transform: translateY(0); }

    .mzl-slide__title {
        font-size: clamp(40px, 6vw, 96px); font-weight: 100;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: var(--mzl-white); line-height: 0.92;
        margin-bottom: 26px; overflow: visible; white-space: nowrap;
    }
    .mzl-char-wrap { display: inline-block; overflow: hidden; line-height: 1.05; vertical-align: top; }
    .mzl-char { display: inline-block; transform: translateY(105%); transition: transform 0.75s var(--mzl-ease-out); }
    .mzl-slide.is-active .mzl-char { transform: translateY(0); }

    .mzl-slide__divider {
        width: 0; height: 1px;
        background: linear-gradient(to right, var(--mzl-gold), transparent);
        margin-bottom: 20px; transition: width 1.1s 0.65s var(--mzl-ease-out);
    }
    .mzl-slide.is-active .mzl-slide__divider { width: 80px; }

    .mzl-slide__notes {
        font-size: 10px; letter-spacing: 4px; text-transform: uppercase;
        color: rgba(232,213,163,0.75); margin-bottom: 26px;
        opacity: 0; transition: opacity 0.7s 0.9s;
    }
    .mzl-slide.is-active .mzl-slide__notes { opacity: 1; }

    .mzl-slide__ctas {
        display: flex; gap: 14px; flex-wrap: wrap;
        opacity: 0; transform: translateY(14px);
        transition: opacity 0.7s 1.15s var(--mzl-ease-out), transform 0.7s 1.15s var(--mzl-ease-out);
    }
    .mzl-slide.is-active .mzl-slide__ctas { opacity: 1; transform: translateY(0); }

    .mzl-cta {
        position: relative; display: inline-flex; align-items: center; gap: 16px;
        padding: 13px 30px; border: 1px solid rgba(201,169,110,0.85);
        color: var(--mzl-white); text-decoration: none;
        font-size: 9px; letter-spacing: 5px; text-transform: uppercase;
        overflow: hidden; cursor: none; transition: color 0.45s var(--mzl-ease);
    }
    .mzl-cta::before {
        content: ''; position: absolute; inset: 0;
        background: var(--mzl-gold); transform: scaleX(0);
        transform-origin: left; transition: transform 0.5s var(--mzl-ease);
    }
    .mzl-cta:hover { color: #000; }
    .mzl-cta:hover::before { transform: scaleX(1); }
    .mzl-cta span { position: relative; z-index: 1; }
    .mzl-cta-arrow { position: relative; z-index: 1; display: flex; align-items: center; }
    .mzl-cta-arrow::before {
        content: ''; display: block; width: 20px; height: 1px;
        background: currentColor; transition: width 0.35s var(--mzl-ease);
    }
    .mzl-cta-arrow::after {
        content: ''; display: block; width: 5px; height: 5px;
        border-right: 1px solid currentColor; border-top: 1px solid currentColor;
        transform: rotate(45deg); margin-left: -1px;
    }
    .mzl-cta:hover .mzl-cta-arrow::before { width: 30px; }
    .mzl-cta--ghost { border-color: rgba(255,255,255,0.30); color: rgba(255,255,255,0.80); }
    .mzl-cta--ghost::before { background: rgba(255,255,255,0.12); }
    .mzl-cta--ghost:hover { color: var(--mzl-white); }

    /* ─── BOUTEILLE (droite) ─── */
    .mzl-slide__bottle-col {
        position: relative; display: flex;
        align-items: center; justify-content: center;
        padding-bottom: 4%; padding-right: 3%;
    }
    /* Ombre au sol — ellipse floue sous la bouteille */
    .mzl-slide__bottle-col::after {
        content: ''; position: absolute;
        bottom: 5%; left: 50%; transform: translateX(-50%);
        width: 38%; height: 14px;
        background: radial-gradient(ellipse at center, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%; pointer-events: none; filter: blur(6px);
    }
    .mzl-slide__bottle {
        height: clamp(320px, 68vh, 620px); width: auto; max-width: 90%;
        object-fit: contain;
        filter:
            drop-shadow(0 30px 40px rgba(0,0,0,0.60))
            drop-shadow(0  8px 16px rgba(0,0,0,0.45))
            brightness(1.04) contrast(1.02);
        opacity: 0; transform: translateY(40px) scale(0.94);
        transition: opacity 1.3s 0.5s var(--mzl-ease-out), transform 1.3s 0.5s var(--mzl-ease-out);
    }
    .mzl-slide.is-active .mzl-slide__bottle { opacity: 1; transform: translateY(0) scale(1); }
    /* Bouteille fond blanc → multiply pour supprimer le blanc */
    .mzl-slide__bottle--light { mix-blend-mode: multiply; }

    /* ─── RIDEAU DE TRANSITION ─── */
    .mzl-curtain {
        position: absolute; inset: 0; z-index: 50; pointer-events: none;
        background: #0a0a0a;
        clip-path: polygon(105% 0, 105% 0, 105% 100%, 105% 100%);
        will-change: clip-path;
    }

    /* ─── DOTS ─── */
    .mzl-nav-dots {
        position: absolute; right: 3.5%; top: 50%; transform: translateY(-50%);
        display: flex; flex-direction: column; gap: 14px; z-index: 100;
    }
    .mzl-nav-dot {
        position: relative; width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        background: none; border: none; cursor: none;
    }
    .mzl-nav-dot::before {
        content: ''; width: 4px; height: 4px; border-radius: 50%;
        background: rgba(255,255,255,0.30);
        transition: background 0.4s, transform 0.4s;
    }
    .mzl-nav-dot.is-active::before { background: var(--mzl-gold); transform: scale(1.8); }
    .mzl-nav-dot.is-active::after {
        content: ''; position: absolute; inset: 4px; border-radius: 50%;
        border: 1px solid rgba(201,169,110,0.4);
        animation: mzlRingPulse 2s ease-in-out infinite;
    }
    @keyframes mzlRingPulse {
        0%,100% { transform: scale(1); opacity: 1; }
        50%      { transform: scale(1.3); opacity: 0.4; }
    }

    /* ─── FLÈCHES ─── */
    .mzl-nav-arrows {
        position: absolute; bottom: 36px; right: 5%;
        display: flex; gap: 10px; z-index: 100;
    }
    .mzl-nav-arrow {
        width: 48px; height: 48px; border: 1px solid rgba(255,255,255,0.18);
        display: flex; align-items: center; justify-content: center;
        cursor: none; background: rgba(0,0,0,0.2); backdrop-filter: blur(6px);
        transition: border-color 0.3s, background 0.3s;
    }
    .mzl-nav-arrow:hover { border-color: var(--mzl-gold); background: rgba(201,169,110,0.12); }
    .mzl-nav-arrow svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }

    /* ─── COMPTEUR ─── */
    .mzl-counter {
        position: absolute; bottom: 46px; left: 50%; transform: translateX(-50%);
        z-index: 100; font-size: 10px; letter-spacing: 5px;
        color: rgba(255,255,255,0.35); display: flex; align-items: baseline; gap: 4px;
    }
    .mzl-counter__current { font-size: 20px; font-weight: 200; color: var(--mzl-gold); line-height: 1; }

    /* ─── BARRE DE PROGRESSION ─── */
    .mzl-progress {
        position: absolute; bottom: 0; left: 0; height: 2px;
        background: linear-gradient(to right, var(--mzl-gold), var(--mzl-gold-light));
        z-index: 100; width: 0%;
        box-shadow: 0 0 8px rgba(201,169,110,0.5);
    }

    /* ─── DÉCO ─── */
    .mzl-vline {
        position: absolute; top: 0; bottom: 0; width: 1px; z-index: 2; pointer-events: none;
        background: linear-gradient(to bottom, transparent 0%, rgba(201,169,110,0.07) 30%, rgba(201,169,110,0.10) 60%, transparent 100%);
    }
    .mzl-corner {
        position: absolute; width: 26px; height: 26px; z-index: 6; pointer-events: none;
    }
    .mzl-corner::before, .mzl-corner::after { content: ''; position: absolute; background: var(--mzl-gold); opacity: 0.45; }
    .mzl-corner::before { width: 1px; height: 100%; }
    .mzl-corner::after  { width: 100%; height: 1px; }
    .mzl-corner--tl { top: 26px; left: 26px; }
    .mzl-corner--tl::before { top: 0; left: 0; }
    .mzl-corner--tl::after  { top: 0; left: 0; }
    .mzl-corner--tr { top: 26px; right: 26px; }
    .mzl-corner--tr::before { top: 0; right: 0; }
    .mzl-corner--tr::after  { top: 0; right: 0; }
    .mzl-corner--br { bottom: 48px; right: 26px; }
    .mzl-corner--br::before { bottom: 0; right: 0; }
    .mzl-corner--br::after  { bottom: 0; right: 0; }
    .mzl-corner--bl { bottom: 48px; left: 26px; }
    .mzl-corner--bl::before { bottom: 0; left: 0; }
    .mzl-corner--bl::after  { bottom: 0; left: 0; }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 768px) {
        #mzl-luxury-slider { cursor: auto; }
        .mzl-cursor, .mzl-cursor-ring { display: none; }
        .mzl-slide__layout { grid-template-columns: 1fr; }
        .mzl-slide__bottle-col { display: none; }
        .mzl-slide__content { padding: 0 6% 5%; justify-content: flex-end; padding-top: 0; }
        .mzl-nav-dots { display: none; }
        .mzl-slide__ctas { flex-direction: column; }
    }
    </style>

    <div class="mzl-cursor" id="mzlCursor"></div>
    <div class="mzl-cursor-ring" id="mzlCursorRing"></div>

    <div id="mzl-luxury-slider">

        <div class="mzl-curtain" id="mzlCurtain"></div>

        <div class="mzl-vline" style="left:12%"></div>
        <div class="mzl-vline" style="right:12%"></div>
        <div class="mzl-corner mzl-corner--tl"></div>
        <div class="mzl-corner mzl-corner--tr"></div>
        <div class="mzl-corner mzl-corner--br"></div>
        <div class="mzl-corner mzl-corner--bl"></div>

        <?php foreach ( $slides as $i => $s ) :
            $is_first     = $i === 0;
            $overlay_cls  = 'mzl-slide__overlay' . ( ! empty( $s['overlay_strong'] ) ? ' mzl-slide__overlay--strong' : '' );
            $bottle_cls   = 'mzl-slide__bottle' . ( $s['bottle_light'] ? ' mzl-slide__bottle--light' : '' );
            $anchor_col   = esc_attr( $s['anchor'] );
            $anchor_prod  = esc_attr( $s['key'] );
        ?>
        <div class="mzl-slide<?php echo $is_first ? ' is-active' : ''; ?>" data-index="<?php echo $i; ?>">
            <div class="mzl-slide__bg" style="background-image:url('<?php echo esc_url( $img . 'bg-' . $s['key'] . '.jpg' ); ?>'); background-color:<?php echo esc_attr( $s['color'] ); ?>; transform:scale(1.03);"></div>
            <div class="<?php echo esc_attr( $overlay_cls ); ?>"></div>
            <div class="mzl-slide__overlay-bottom"></div>
            <div class="mzl-slide__grain"></div>
            <div class="mzl-slide__watermark"><?php echo esc_html( $s['name'] ); ?></div>

            <div class="mzl-slide__layout">
                <div class="mzl-slide__content">
                    <div class="mzl-slide__label"><?php echo esc_html( $s['collection'] ); ?></div>
                    <h2 class="mzl-slide__title" data-title="<?php echo esc_attr( $s['name'] ); ?>"<?php echo $s['title_size'] ? ' style="' . esc_attr( $s['title_size'] ) . '"' : ''; ?>></h2>
                    <div class="mzl-slide__divider"></div>
                    <div class="mzl-slide__notes"><?php echo esc_html( $s['notes'] ); ?></div>
                    <div class="mzl-slide__ctas">
                        <a href="#<?php echo $anchor_col; ?>" class="mzl-cta">
                            <span>Voir la collection</span>
                            <span class="mzl-cta-arrow"></span>
                        </a>
                        <a href="#<?php echo $anchor_prod; ?>" class="mzl-cta mzl-cta--ghost">
                            <span>Acheter — 9,99 €</span>
                            <span class="mzl-cta-arrow"></span>
                        </a>
                    </div>
                </div>
                <div class="mzl-slide__bottle-col">
                    <img class="<?php echo esc_attr( $bottle_cls ); ?>"
                         src="<?php echo esc_url( $img . 'bottle-' . $s['key'] . '.png' ); ?>"
                         alt="<?php echo esc_attr( $s['name'] . ' — ' . $s['collection'] ); ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <nav class="mzl-nav-dots" id="mzlNavDots" aria-label="Navigation slides">
            <?php for ( $i = 0; $i < $total; $i++ ) : ?>
            <button class="mzl-nav-dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-slide="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </nav>

        <div class="mzl-nav-arrows">
            <button class="mzl-nav-arrow" id="mzlBtnPrev" aria-label="Précédent">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button class="mzl-nav-arrow" id="mzlBtnNext" aria-label="Suivant">
                <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg>
            </button>
        </div>

        <div class="mzl-counter" aria-live="polite">
            <span class="mzl-counter__current" id="mzlCounterCurrent">01</span>
            <span>/</span>
            <span><?php echo str_pad( $total, 2, '0', STR_PAD_LEFT ); ?></span>
        </div>

        <div class="mzl-progress" id="mzlProgress"></div>

    </div>

    <script>
    (function () {
        'use strict';

        var AUTO_DELAY = 6000;
        var TRANS_DUR  = 1100;

        var slider     = document.getElementById('mzl-luxury-slider');
        var slides     = Array.from(slider.querySelectorAll('.mzl-slide'));
        var dots       = Array.from(slider.querySelectorAll('.mzl-nav-dot'));
        var progressEl = document.getElementById('mzlProgress');
        var counterEl  = document.getElementById('mzlCounterCurrent');
        var curtain    = document.getElementById('mzlCurtain');
        var cursor     = document.getElementById('mzlCursor');
        var cursorRing = document.getElementById('mzlCursorRing');

        var current = 0, isAnimating = false, timer;
        var mouseX = 0, mouseY = 0, ringX = 0, ringY = 0;

        /* ── Char split ── */
        slider.querySelectorAll('.mzl-slide__title[data-title]').forEach(function (el) {
            var text = el.dataset.title;
            el.innerHTML = '';
            Array.from(text).forEach(function (char, i) {
                var wrap = document.createElement('div');
                wrap.className = 'mzl-char-wrap';
                var span = document.createElement('span');
                span.className = 'mzl-char';
                span.style.transitionDelay = (0.45 + i * 0.05) + 's';
                span.textContent = char === ' ' ? '\u2009' : char;
                wrap.appendChild(span);
                el.appendChild(wrap);
            });
        });

        /* ── Cursor ── */
        document.addEventListener('mousemove', function (e) {
            mouseX = e.clientX; mouseY = e.clientY;
            cursor.style.left = mouseX + 'px';
            cursor.style.top  = mouseY + 'px';
        });
        (function animateCursor() {
            ringX += (mouseX - ringX) * 0.1;
            ringY += (mouseY - ringY) * 0.1;
            cursorRing.style.left = ringX + 'px';
            cursorRing.style.top  = ringY + 'px';
            requestAnimationFrame(animateCursor);
        })();
        slider.querySelectorAll('.mzl-cta, .mzl-nav-arrow, .mzl-nav-dot').forEach(function (el) {
            el.addEventListener('mouseenter', function () { cursorRing.classList.add('expanded'); });
            el.addEventListener('mouseleave', function () { cursorRing.classList.remove('expanded'); });
        });

        /* ── Progress ── */
        function startProgress() {
            progressEl.style.transition = 'none';
            progressEl.style.width = '0%';
            void progressEl.offsetWidth;
            progressEl.style.transition = 'width ' + AUTO_DELAY + 'ms linear';
            progressEl.style.width = '100%';
        }
        function resetProgress() {
            progressEl.style.transition = 'none';
            progressEl.style.width = '0%';
        }

        /* ── Transition ── */
        function transitionTo(next, direction) {
            if (isAnimating || next === current) return;
            isAnimating = true;
            resetProgress();
            clearTimeout(timer);

            var prev = current;
            current = next;

            slides[next].style.zIndex = '1';
            slides[prev].style.zIndex = '2';
            slides[next].classList.add('is-active');
            slides[next].querySelector('.mzl-slide__bg').style.transform = 'scale(1.03)';

            var fromX = direction === 'next' ? '105% 0, 105% 0, 105% 100%, 105% 100%' : '-5% 0, -5% 0, -5% 100%, -5% 100%';
            var midX  = direction === 'next' ? '0% 0, 105% 0, 105% 100%, 0% 100%'     : '-5% 0, 100% 0, 100% 100%, -5% 100%';
            var toX   = direction === 'next' ? '-5% 0, -5% 0, -5% 100%, -5% 100%'     : '105% 0, 105% 0, 105% 100%, 105% 100%';

            curtain.style.transition = 'none';
            curtain.style.clipPath = 'polygon(' + fromX + ')';
            void curtain.offsetWidth;
            curtain.style.transition = 'clip-path ' + (TRANS_DUR * 0.5) + 'ms var(--mzl-ease)';
            curtain.style.clipPath = 'polygon(' + midX + ')';

            setTimeout(function () {
                slides[prev].classList.remove('is-active');
                slides[next].style.zIndex = '2';
                slides[prev].style.zIndex = '1';
                curtain.style.transition = 'clip-path ' + (TRANS_DUR * 0.5) + 'ms var(--mzl-ease)';
                curtain.style.clipPath = 'polygon(' + toX + ')';
                dots.forEach(function (d, i) { d.classList.toggle('is-active', i === next); });
                counterEl.textContent = String(next + 1).padStart(2, '0');
                setTimeout(function () {
                    isAnimating = false;
                    startProgress();
                    timer = setTimeout(function () { transitionTo((current + 1) % slides.length, 'next'); }, AUTO_DELAY);
                }, TRANS_DUR * 0.5 + 100);
            }, TRANS_DUR * 0.5 + 50);
        }

        /* ── Init ── */
        slides[0].style.zIndex = '2';
        slides.forEach(function (s, i) {
            if (i !== 0) s.style.zIndex = '1';
            s.querySelector('.mzl-slide__bg').style.transform = i === 0 ? 'scale(1)' : 'scale(1.03)';
        });
        startProgress();
        timer = setTimeout(function () { transitionTo(1, 'next'); }, AUTO_DELAY);

        /* ── Events ── */
        document.getElementById('mzlBtnNext').addEventListener('click', function () {
            clearTimeout(timer); transitionTo((current + 1) % slides.length, 'next');
        });
        document.getElementById('mzlBtnPrev').addEventListener('click', function () {
            clearTimeout(timer); transitionTo((current - 1 + slides.length) % slides.length, 'prev');
        });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var t = parseInt(dot.dataset.slide, 10);
                clearTimeout(timer);
                transitionTo(t, t > current ? 'next' : 'prev');
            });
        });
        slider.addEventListener('mouseenter', function () { clearTimeout(timer); resetProgress(); });
        slider.addEventListener('mouseleave', function () {
            startProgress();
            timer = setTimeout(function () { transitionTo((current + 1) % slides.length, 'next'); }, AUTO_DELAY);
        });
        var touchStart = 0;
        slider.addEventListener('touchstart', function (e) { touchStart = e.touches[0].clientX; }, { passive: true });
        slider.addEventListener('touchend', function (e) {
            var diff = touchStart - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                clearTimeout(timer);
                transitionTo(
                    diff > 0 ? (current + 1) % slides.length : (current - 1 + slides.length) % slides.length,
                    diff > 0 ? 'next' : 'prev'
                );
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight') { clearTimeout(timer); transitionTo((current + 1) % slides.length, 'next'); }
            if (e.key === 'ArrowLeft')  { clearTimeout(timer); transitionTo((current - 1 + slides.length) % slides.length, 'prev'); }
        });

    })();
    </script>
    <?php

    return ob_get_clean();
}
