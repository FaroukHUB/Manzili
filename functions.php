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
add_action( 'woocommerce_shop_loop_item_title', 'manzili_display_badge', 1 );
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


/* =============================================
   MANZILI - SLIDER FRAGRANCES LUXURY
   Shortcode: [manzili_slider]
   ============================================= */
function manzili_fragrance_slider() {
    $slides = [
        /* 1 — Collection L'Original · Bois Intense */
        [
            'bg'          => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-bois.png',
            'bg_color'    => '#1a2010',
            'bottle'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-coloriginal-scaled.png',
            'bottle_blend'=> false,
            'label'       => "Collection Privée · L'Original",
            'title'       => "L'Original",
            'title_size'  => '',
            'watermark'   => 'Bois Intense',
            'desc'        => "Des fragrances d'exception inspirées des plus grands parfums du monde. Boisées, poudrées et sensuelles, chaque création allie noblesse olfactive et élégance intemporelle.",
            'notes'       => 'Boisé · Poudré · Sensuel · Intemporel',
            'link'        => 'https://www.collectionloriginal.com/product-category/collection-privee-loriginal/',
            'link_label'  => 'Voir la collection',
            'link2'       => '',
            'link2_label' => '',
            'has_bg_title'=> false,
            'deco_imgs'   => [
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/sandalwood-scaled-1.png',                          'alt'=>'Sandalwood',        'float'=>'slow', 'delay'=>'0.9s',  'style'=>'width:70px;left:41%;bottom:16%;'],
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/batons-bois-santal-isoles-fond-transparent-png-psd.png', 'alt'=>'Bâtons de santal', 'float'=>'med',  'delay'=>'1.15s', 'style'=>'width:72px;right:24%;top:22%;'],
            ],
        ],
        /* 2 — Coton Frais (produit) */
        [
            'bg'          => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-coton.png',
            'bg_color'    => '#d0dce8',
            'bottle'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-coton.png',
            'bottle_blend'=> false,
            'label'       => "Collection Privée · L'Original",
            'title'       => 'Coton Frais',
            'title_size'  => 'font-size:clamp(30px,4.5vw,74px);',
            'watermark'   => 'Coton Frais',
            'desc'        => '',
            'notes'       => 'Coton · Musc Blanc · Iris · Bois de Santal',
            'link'        => 'https://www.collectionloriginal.com/product/coton-frais-coton-frais-collection-privee-loriginal/',
            'link_label'  => 'Découvrir',
            'link2'       => 'https://www.collectionloriginal.com/product-category/collection-privee-loriginal/',
            'link2_label' => 'Voir la collection',
            'has_bg_title'=> false,
        ],
        /* 3 — Collection Privée Paris · Krypton */
        [
            'bg'          => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-krypton.png',
            'bg_color'    => '#0a1505',
            'bottle'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-colprivee.png',
            'bottle_blend'=> true,
            'label'       => 'Collection Privée · Paris',
            'title'       => 'Paris',
            'title_size'  => '',
            'watermark'   => 'Krypton',
            'desc'        => "L'élégance parisienne sublimée en fragrance. Des compositions florales, fraîches et raffinées, pour incarner l'art de vivre à la française avec une sophistication absolue.",
            'notes'       => 'Floral · Frais · Élégant · Parisien',
            'link'        => 'https://www.collectionloriginal.com/product-category/collection-privee-paris/',
            'link_label'  => 'Voir la collection',
            'link2'       => '',
            'link2_label' => '',
            'has_bg_title'=> false,
        ],
        /* 4 — Collection Privée Intense · Dima Morocco */
        [
            'bg'          => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-dima.png',
            'bg_color'    => '#7a4020',
            'bottle'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-dima.png',
            'bottle_blend'=> false,
            'label'       => 'Collection Privée · Intense',
            'title'       => 'Intense',
            'title_size'  => 'font-size:clamp(36px,5.5vw,88px);',
            'watermark'   => 'Dima Morocco',
            'desc'        => "Des sillages d'une intensité captivante, pour ceux qui osent s'affirmer. Une collection orientale et puissante, dont chaque fragrance révèle un caractère inoubliable.",
            'notes'       => 'Oriental · Intense · Captivant · Envoûtant',
            'link'        => 'https://www.collectionloriginal.com/product-category/collection-privee-intense/',
            'link_label'  => 'Voir la collection',
            'link2'       => 'https://www.collectionloriginal.com/product/dima-maghreb-althair-collection-privee-lintense/',
            'link2_label' => 'Dima Morocco',
            'has_bg_title'=> false,
        ],
        /* 5 — Pistachio (produit · titre flottant derrière la brume) */
        [
            'bg'          => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-pistachio.png',
            'bg_color'    => '#7aad6a',
            'bottle'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-pistachio.png',
            'bottle_blend'=> false,
            'label'       => "Collection Privée · L'Original",
            'title'       => 'Pistachio',
            'title_size'  => '',
            'watermark'   => 'Pistachio',
            'desc'        => '',
            'notes'       => 'Pistache · Caramel · Vanille · Musc Blanc',
            'link'        => 'https://www.collectionloriginal.com/product/pistachio-pistachio-collection-privee-loriginal/',
            'link_label'  => 'Découvrir',
            'link2'       => 'https://www.collectionloriginal.com/product-category/collection-privee-loriginal/',
            'link2_label' => 'Voir la collection',
            'has_bg_title'=> true,
            'deco_imgs'   => [
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/p9-1.png', 'alt'=>'Pistache', 'float'=>'slow', 'delay'=>'0.8s',  'style'=>'width:62px;left:42%;top:18%;'],
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/p7.png',   'alt'=>'Pistache', 'float'=>'med',  'delay'=>'1.0s',  'style'=>'width:58px;right:22%;top:26%;'],
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/p3.png',   'alt'=>'Pistache', 'float'=>'fast', 'delay'=>'1.2s',  'style'=>'width:66px;right:26%;bottom:22%;'],
            ],
        ],
        /* 6 — Rose Vanille (produit) */
        [
            'bg'          => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-rose.png',
            'bg_color'    => '#6b3040',
            'bottle'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-rose.png',
            'bottle_blend'=> false,
            'label'       => 'Collection Privée · Intense',
            'title'       => 'Rose Vanille',
            'title_size'  => 'font-size:clamp(30px,4.5vw,74px);',
            'watermark'   => 'Rose Vanille',
            'desc'        => '',
            'notes'       => 'Rose de Grasse · Jasmin · Vanille · Musc Blanc',
            'link'        => 'https://www.collectionloriginal.com/product/rose-vanille-roses-vanille-collection-privee-lintense/',
            'link_label'  => 'Découvrir',
            'link2'       => 'https://www.collectionloriginal.com/product-category/collection-privee-intense/',
            'link2_label' => 'Voir la collection',
            'has_bg_title'=> false,
            'deco_imgs'   => [
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/fleur-vanille-blanche-isolee-fond-transparent.png', 'alt'=>'Fleur de vanille', 'float'=>'slow', 'delay'=>'0.8s',  'style'=>'width:78px;left:39%;top:18%;'],
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/rose.png',    'alt'=>'Rose',    'float'=>'med',  'delay'=>'1.0s',  'style'=>'width:80px;right:20%;top:16%;'],
                ['url'=>'https://www.collectionloriginal.com/wp-content/uploads/2026/03/vanille.png', 'alt'=>'Vanille', 'float'=>'fast', 'delay'=>'1.2s',  'style'=>'width:64px;right:24%;bottom:20%;'],
            ],
        ],
    ];
    ob_start(); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;700;900&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <div class="lx-wrap">
      <div class="lx-slider" id="lxSlider">

        <?php foreach ( $slides as $i => $s ) : ?>
        <div class="lx-slide<?php echo $i === 0 ? ' is-active' : ''; ?>" data-index="<?php echo $i; ?>">
          <div class="lx-bg" style="background-image:url('<?php echo esc_url($s['bg']); ?>'); background-color:<?php echo esc_attr($s['bg_color']); ?>; transform:scale(1.03);"></div>
          <div class="lx-overlay"></div>
          <div class="lx-overlay-bottom"></div>
          <div class="lx-grain"></div>
          <div class="lx-watermark"><?php echo esc_html($s['watermark']); ?></div>

          <div class="lx-layout">
            <div class="lx-content">
              <div class="lx-label"><?php echo esc_html($s['label']); ?></div>
              <h2 class="lx-title" data-title="<?php echo esc_attr($s['title']); ?>"<?php if($s['title_size']) echo ' style="'.esc_attr($s['title_size']).'"'; ?>></h2>
              <div class="lx-divider"></div>
              <?php if ( ! empty($s['desc']) ) : ?>
              <p class="lx-desc"><?php echo esc_html($s['desc']); ?></p>
              <?php endif; ?>
              <div class="lx-notes"><?php echo esc_html($s['notes']); ?></div>
              <div class="lx-ctas">
                <a href="<?php echo esc_url($s['link']); ?>" class="lx-cta">
                  <span><?php echo esc_html($s['link_label']); ?></span>
                  <span class="lx-arrow"></span>
                </a>
                <?php if ( ! empty($s['link2']) ) : ?>
                <a href="<?php echo esc_url($s['link2']); ?>" class="lx-cta lx-cta--ghost">
                  <span><?php echo esc_html($s['link2_label']); ?></span>
                  <span class="lx-arrow"></span>
                </a>
                <?php endif; ?>
              </div>
            </div>
            <div class="lx-bottle-col">
              <?php if ( $s['has_bg_title'] ) : ?>
              <div class="lx-bg-title"><?php echo esc_html($s['title']); ?></div>
              <?php endif; ?>
              <?php if ( ! empty($s['deco_imgs']) ) : foreach ( $s['deco_imgs'] as $d ) : ?>
              <img class="lx-deco lx-deco--<?php echo esc_attr($d['float']); ?>"
                   src="<?php echo esc_url($d['url']); ?>"
                   alt="<?php echo esc_attr($d['alt']); ?>"
                   style="<?php echo esc_attr($d['style']); ?> transition-delay:<?php echo esc_attr($d['delay']); ?>;">
              <?php endforeach; endif; ?>
              <div class="lx-bottle-wrap">
              <img class="lx-bottle<?php echo $s['bottle_blend'] ? ' lx-bottle--light' : ''; ?>"
                   src="<?php echo esc_url($s['bottle']); ?>"
                   alt="<?php echo esc_attr($s['title'].' — '.$s['label']); ?>">
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <nav class="lx-dots" id="lxDots">
          <?php foreach ( $slides as $i => $s ) : ?>
          <button class="lx-dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-slide="<?php echo $i; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
          <?php endforeach; ?>
        </nav>

        <div class="lx-arrows">
          <button class="lx-arrow-btn" id="lxPrev" aria-label="Précédent">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <button class="lx-arrow-btn" id="lxNext" aria-label="Suivant">
            <svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"/></svg>
          </button>
        </div>

        <div class="lx-progress" id="lxProgress"></div>
      </div>
    </div>
    <style>
    /* ── LUXURY SLIDER ── */
    :root {
      --lx-gold:      #c9a96e;
      --lx-gold-lt:   #e8d5a3;
      --lx-white:     #ffffff;
      --lx-ease:      cubic-bezier(0.76, 0, 0.24, 1);
      --lx-ease-out:  cubic-bezier(0.16, 1, 0.3, 1);
    }
    .lx-wrap { width:100%; overflow:hidden; }
    .lx-slider {
      position:relative;
      width:100%;
      height:90vh;
      min-height:520px;
      background:#000;
    }

    /* ── SLIDE ── */
    .lx-slide {
      position:absolute; inset:0;
      visibility:hidden;
      z-index:1;
    }
    .lx-slide.is-active { visibility:visible; z-index:2; }

    /* Background */
    .lx-bg {
      position:absolute; inset:0;
      background-size:cover;
      background-position:center;
      will-change:transform;
      transition:transform 10s cubic-bezier(0.25,0.46,0.45,0.94);
    }
    .lx-slide.is-active .lx-bg { transform:scale(1) !important; }

    /* Overlays */
    .lx-overlay {
      position:absolute; inset:0;
      background:linear-gradient(to right,
        rgba(0,0,0,0.88) 0%,
        rgba(0,0,0,0.60) 30%,
        rgba(0,0,0,0.22) 55%,
        rgba(0,0,0,0.00) 72%
      );
    }
    .lx-overlay-bottom {
      position:absolute;
      bottom:0; left:0; right:0;
      height:30%;
      background:linear-gradient(to top, rgba(0,0,0,0.40) 0%, transparent 100%);
    }

    /* Grain */
    .lx-grain {
      position:absolute; inset:0;
      opacity:0.03;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
      background-size:300px;
      pointer-events:none;
    }

    /* ── WATERMARK ── */
    .lx-watermark {
      position:absolute;
      top:50%; left:50%;
      font-family:'Montserrat', sans-serif;
      font-size:clamp(28px,6vw,90px);
      font-weight:900;
      letter-spacing:0.28em;
      text-transform:uppercase;
      color:rgba(255,255,255,0.08);
      -webkit-text-stroke:1.5px rgba(255,255,255,0.35);
      white-space:nowrap;
      pointer-events:none;
      z-index:3;
      opacity:0;
      transform:translate(-50%,-50%);
      transition:opacity 1.2s 0.2s;
    }
    .lx-slide.is-active .lx-watermark {
      opacity:1;
      animation:lxWmFloat 14s ease-in-out infinite alternate;
    }
    @keyframes lxWmFloat {
      0%   { transform:translate(calc(-50% - 45px),-50%); }
      100% { transform:translate(calc(-50% + 45px),-50%); }
    }

    /* ── BG TITLE (Pistachio — titre flottant derrière la brume) ── */
    .lx-bg-title {
      position:absolute;
      top:50%; left:50%;
      transform:translate(-50%,-50%);
      font-family:'Montserrat', sans-serif;
      font-size:clamp(55px,10vw,155px);
      font-weight:900;
      letter-spacing:0.1em;
      text-transform:uppercase;
      color:transparent;
      -webkit-text-stroke:1.5px rgba(255,255,255,0.09);
      white-space:nowrap;
      pointer-events:none;
      opacity:0;
      transition:opacity 1.5s 1s;
      z-index:4;
    }
    .lx-slide.is-active .lx-bg-title {
      opacity:1;
      animation:lxBgFloat 10s ease-in-out infinite alternate;
    }
    @keyframes lxBgFloat {
      0%   { transform:translate(calc(-50% - 30px),-50%); }
      100% { transform:translate(calc(-50% + 30px),-50%); }
    }

    /* ── LAYOUT ── */
    .lx-layout {
      position:absolute; inset:0;
      z-index:5;
    }

    /* ── CONTENT (left — absolute) ── */
    .lx-content {
      position:absolute;
      left:0; top:0; bottom:0;
      width:46%;
      display:flex;
      flex-direction:column;
      justify-content:center;
      padding:8% 5% 6% 7%;
      z-index:3;
    }

    /* Label */
    .lx-label {
      font-family:'Montserrat', sans-serif;
      font-size:10px;
      letter-spacing:7px;
      text-transform:uppercase;
      color:#ffffff;
      margin-bottom:20px;
      opacity:0;
      transform:translateY(18px);
      transition:opacity 0.7s 0.35s var(--lx-ease-out), transform 0.7s 0.35s var(--lx-ease-out);
    }
    .lx-slide.is-active .lx-label { opacity:1; transform:translateY(0); }

    /* Title */
    .lx-title {
      font-family:'Montserrat', sans-serif;
      font-size:clamp(38px,6vw,96px);
      font-weight:100;
      letter-spacing:0.08em;
      text-transform:uppercase;
      color:#ffffff;
      line-height:0.92;
      margin-bottom:26px;
      overflow:visible;
      white-space:nowrap;
    }
    .lx-char-wrap {
      display:inline-block;
      overflow:hidden;
      line-height:1.05;
      vertical-align:top;
    }
    .lx-char {
      display:inline-block;
      transform:translateY(105%);
      transition:transform 0.75s var(--lx-ease-out);
    }
    .lx-slide.is-active .lx-char { transform:translateY(0); }

    /* Divider */
    .lx-divider {
      width:0; height:1px;
      background:linear-gradient(to right, var(--lx-gold), transparent);
      margin-bottom:20px;
      transition:width 1.1s 0.65s var(--lx-ease-out);
    }
    .lx-slide.is-active .lx-divider { width:80px; }

    /* Collection desc paragraph */
    .lx-desc {
      font-family:'Lato', sans-serif;
      font-size:13px;
      line-height:1.75;
      color:rgba(255,255,255,0.62);
      margin-bottom:22px;
      max-width:340px;
      opacity:0;
      transform:translateY(10px);
      transition:opacity 0.7s 0.78s var(--lx-ease-out), transform 0.7s 0.78s var(--lx-ease-out);
    }
    .lx-slide.is-active .lx-desc { opacity:1; transform:translateY(0); }

    /* Fragrance notes */
    .lx-notes {
      font-family:'Lato', sans-serif;
      font-size:10px;
      letter-spacing:4px;
      text-transform:uppercase;
      color:rgba(232,213,163,0.75);
      margin-bottom:26px;
      opacity:0;
      transition:opacity 0.7s 0.9s;
    }
    .lx-slide.is-active .lx-notes { opacity:1; }

    /* CTAs */
    .lx-ctas {
      display:flex;
      gap:14px;
      flex-wrap:wrap;
      opacity:0;
      transform:translateY(14px);
      transition:opacity 0.7s 1.15s var(--lx-ease-out), transform 0.7s 1.15s var(--lx-ease-out);
    }
    .lx-slide.is-active .lx-ctas { opacity:1; transform:translateY(0); }

    .lx-cta {
      position:relative;
      display:inline-flex;
      align-items:center;
      gap:16px;
      padding:13px 30px;
      border:1px solid rgba(201,169,110,0.85);
      color:#ffffff;
      text-decoration:none;
      font-family:'Lato', sans-serif;
      font-size:9px;
      letter-spacing:5px;
      text-transform:uppercase;
      overflow:hidden;
      transition:color 0.45s var(--lx-ease);
    }
    .lx-cta::before {
      content:'';
      position:absolute; inset:0;
      background:var(--lx-gold);
      transform:scaleX(0);
      transform-origin:left;
      transition:transform 0.5s var(--lx-ease);
    }
    .lx-cta:hover { color:#000; }
    .lx-cta:hover::before { transform:scaleX(1); }
    .lx-cta span { position:relative; z-index:1; }

    .lx-arrow {
      position:relative; z-index:1;
      display:flex; align-items:center;
    }
    .lx-arrow::before {
      content:'';
      display:block;
      width:20px; height:1px;
      background:currentColor;
      transition:width 0.35s var(--lx-ease);
    }
    .lx-arrow::after {
      content:'';
      display:block;
      width:5px; height:5px;
      border-right:1px solid currentColor;
      border-top:1px solid currentColor;
      transform:rotate(45deg);
      margin-left:-1px;
    }
    .lx-cta:hover .lx-arrow::before { width:30px; }

    .lx-cta--ghost {
      border-color:rgba(255,255,255,0.30);
      color:rgba(255,255,255,0.80);
    }
    .lx-cta--ghost::before { background:rgba(255,255,255,0.12); }
    .lx-cta--ghost:hover { color:#ffffff; }

    /* ── BOTTLE (posée au sol) ── */
    .lx-bottle-col {
      position:absolute;
      inset:0;
      display:flex;
      align-items:flex-end;
      justify-content:center;
      padding-bottom:10%;
      z-index:2;
      pointer-events:none;
    }
    /* Ombre au sol — s'anime avec le flottement */
    .lx-bottle-col::after {
      content:'';
      position:absolute;
      bottom:4%; left:50%;
      transform:translateX(-50%) scaleX(1);
      width:18%; height:14px;
      background:radial-gradient(ellipse at center, rgba(0,0,0,0.60) 0%, rgba(0,0,0,0) 70%);
      border-radius:50%;
      pointer-events:none;
      filter:blur(7px);
      transition:none;
    }
    .lx-slide.is-active .lx-bottle-col::after {
      animation:lxShadowFloat 4s ease-in-out 1.8s infinite;
    }
    @keyframes lxShadowFloat {
      0%,100% { transform:translateX(-50%) scaleX(1);   opacity:0.85; }
      50%      { transform:translateX(-50%) scaleX(0.6); opacity:0.40; }
    }

    /* Wrapper float — ne perturbe pas l'animation d'entrée de l'image */
    .lx-bottle-wrap {
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .lx-slide.is-active .lx-bottle-wrap {
      animation:lxBottleFloat 4s ease-in-out 1.8s infinite;
    }
    @keyframes lxBottleFloat {
      0%,100% { transform:translateY(0);    }
      50%      { transform:translateY(-14px); }
    }

    .lx-bottle {
      height:clamp(300px,65vh,600px);
      width:auto; max-width:90%;
      object-fit:contain;
      filter:
        drop-shadow(0 30px 45px rgba(0,0,0,0.65))
        drop-shadow(0  8px 18px rgba(0,0,0,0.45))
        brightness(1.05) contrast(1.02);
      opacity:0;
      transform:translateY(40px) scale(0.94);
      transition:opacity 1.3s 0.5s var(--lx-ease-out), transform 1.3s 0.5s var(--lx-ease-out);
      position:relative; z-index:5;
    }
    .lx-slide.is-active .lx-bottle { opacity:1; transform:translateY(0) scale(1); }
    .lx-bottle--light { mix-blend-mode:multiply; }

    /* ── DOTS ── */
    .lx-dots {
      position:absolute;
      right:3.5%; top:50%;
      transform:translateY(-50%);
      display:flex; flex-direction:column;
      gap:14px; z-index:100;
    }
    .lx-dot {
      position:relative;
      width:28px; height:28px;
      display:flex; align-items:center; justify-content:center;
      cursor:pointer; background:transparent; border:none; padding:0;
    }
    .lx-dot::before {
      content:'';
      width:4px; height:4px;
      border-radius:50%;
      background:rgba(255,255,255,0.30);
      transition:background 0.4s, transform 0.4s;
    }
    .lx-dot.is-active::before { background:var(--lx-gold); transform:scale(1.8); }
    .lx-dot.is-active::after {
      content:'';
      position:absolute; inset:4px;
      border-radius:50%;
      border:1px solid rgba(201,169,110,0.4);
      animation:lxRingPulse 2s ease-in-out infinite;
    }
    @keyframes lxRingPulse {
      0%,100% { transform:scale(1); opacity:1; }
      50%     { transform:scale(1.3); opacity:0.4; }
    }

    /* ── ARROWS ── */
    .lx-arrows {
      position:absolute;
      bottom:36px; right:5%;
      display:flex; gap:10px; z-index:100;
    }
    .lx-arrow-btn {
      width:48px; height:48px;
      border:1px solid rgba(255,255,255,0.18);
      display:flex; align-items:center; justify-content:center;
      cursor:pointer;
      transition:border-color 0.3s, background 0.3s;
      background:rgba(0,0,0,0.2);
      backdrop-filter:blur(6px);
    }
    .lx-arrow-btn:hover { border-color:var(--lx-gold); background:rgba(201,169,110,0.12); }
    .lx-arrow-btn svg { width:14px; height:14px; stroke:#fff; fill:none; stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; }

    /* ── PROGRESS ── */
    .lx-progress {
      position:absolute;
      bottom:0; left:0;
      height:2px;
      background:linear-gradient(to right, var(--lx-gold), var(--lx-gold-lt));
      z-index:100; width:0%;
      transition:width linear;
      box-shadow:0 0 8px rgba(201,169,110,0.5);
    }

    /* ── DECO INGREDIENT IMAGES ── */
    .lx-deco {
      position:absolute;
      pointer-events:none;
      opacity:0;
      transition:opacity 1.3s var(--lx-ease-out);
      z-index:3;
      object-fit:contain;
      filter:drop-shadow(0 8px 20px rgba(0,0,0,0.50));
    }
    .lx-slide.is-active .lx-deco { opacity:1; }
    @keyframes lxDecoSlow {
      from { transform:translateY(0)    rotate(-4deg); }
      to   { transform:translateY(-18px) rotate(4deg); }
    }
    @keyframes lxDecoMed {
      from { transform:translateY(0)    rotate(3deg); }
      to   { transform:translateY(-12px) rotate(-3deg); }
    }
    @keyframes lxDecoFast {
      from { transform:translateY(-6px) rotate(-2deg); }
      to   { transform:translateY(10px)  rotate(3deg); }
    }
    .lx-deco--slow { animation:lxDecoSlow 8s ease-in-out infinite alternate; }
    .lx-deco--med  { animation:lxDecoMed  6s ease-in-out infinite alternate; }
    .lx-deco--fast { animation:lxDecoFast 5s ease-in-out infinite alternate; }

    /* ── RESPONSIVE MOBILE ── */
    @media (max-width:768px) {
      .lx-slider { height:100svh; min-height:580px; }

      /* Overlay : sombre haut (texte lisible), transparent milieu (bouteille visible), sombre bas */
      .lx-overlay {
        background:linear-gradient(to bottom,
          rgba(0,0,0,0.78) 0%,
          rgba(0,0,0,0.38) 30%,
          rgba(0,0,0,0.12) 55%,
          rgba(0,0,0,0.72) 100%
        );
      }

      /* Content : pleine hauteur en flex column
         → les éléments s'empilent verticalement sans se chevaucher
         → .lx-notes avec flex:1 pousse les CTAs en bas naturellement */
      .lx-content {
        position:absolute;
        top:0; left:0; right:0; bottom:0;
        width:100%;
        padding:46px 6% 70px;
        justify-content:flex-start;
        z-index:10;
      }

      /* Description masquée */
      .lx-desc { display:none; }

      /* Textes compacts */
      .lx-label   { margin-bottom:8px;  font-size:8px;  letter-spacing:4px; }
      .lx-title   { font-size:clamp(22px,8vw,40px) !important; margin-bottom:10px; white-space:normal; line-height:1.0; }
      .lx-divider { margin-bottom:10px; }
      /* flex:1 = espace élastique qui pousse les CTAs tout en bas */
      .lx-notes   { font-size:7.5px; letter-spacing:2.5px; margin-bottom:0; flex:1; }

      /* CTAs : dans le flux flex (pas position:absolute), poussés en bas */
      .lx-ctas {
        position:static;
        flex-direction:row;
        flex-wrap:nowrap;
        gap:8px;
        transform:none !important;
      }
      .lx-cta {
        flex:1;
        justify-content:center;
        padding:10px 8px;
        font-size:7px;
        letter-spacing:2.5px;
        gap:0;
        text-align:center;
      }
      /* Flèche décorative masquée sur mobile */
      .lx-cta .lx-arrow { display:none; }

      /* Bouteille : visible, centrée */
      .lx-bottle-col {
        display:flex;
        align-items:flex-end;
        justify-content:center;
        padding-bottom:18%;
        z-index:4;
      }
      .lx-bottle-col::after { bottom:9%; width:26%; }
      .lx-bottle { height:clamp(180px,38vh,300px); }

      /* Watermark réduit */
      .lx-watermark { font-size:clamp(18px,5vw,42px); }

      /* Dots masqués, flèches centrées en bas */
      .lx-dots { display:none; }
      .lx-arrows { bottom:18px; right:50%; transform:translateX(50%); }
    }
    </style>
    <script>
    (function(){
      'use strict';
      var AUTO = 6000, DUR = 900;
      var wrap     = document.getElementById('lxSlider');
      if(!wrap) return;
      var slides   = Array.from(wrap.querySelectorAll('.lx-slide'));
      var dots     = Array.from(wrap.querySelectorAll('.lx-dot'));
      var bar      = document.getElementById('lxProgress');
      var cur      = 0, animating = false, timer;

      /* ── Char split ── */
      wrap.querySelectorAll('.lx-title[data-title]').forEach(function(el){
        var text = el.dataset.title;
        el.innerHTML = '';
        Array.from(text).forEach(function(ch, i){
          var w = document.createElement('div');
          w.className = 'lx-char-wrap';
          var s = document.createElement('span');
          s.className = 'lx-char';
          s.style.transitionDelay = (0.45 + i * 0.055) + 's';
          s.textContent = ch === ' ' ? '\u2009' : ch;
          w.appendChild(s);
          el.appendChild(w);
        });
      });

      /* ── Progress ── */
      function startBar(){
        bar.style.transition = 'none';
        bar.style.width = '0%';
        void bar.offsetWidth;
        bar.style.transition = 'width ' + AUTO + 'ms linear';
        bar.style.width = '100%';
      }
      function resetBar(){
        bar.style.transition = 'none';
        bar.style.width = '0%';
      }

      /* ── Go to slide ── */
      function goTo(next, dir){
        if(animating || next === cur) return;
        animating = true;
        clearTimeout(timer);
        resetBar();
        var prev = cur; cur = next;
        slides[next].style.zIndex = '1';
        slides[prev].style.zIndex = '2';
        slides[next].classList.add('is-active');
        slides[next].querySelector('.lx-bg').style.transform = 'scale(1.03)';
        setTimeout(function(){
          slides[prev].classList.remove('is-active');
          slides[next].style.zIndex = '2';
          slides[prev].style.zIndex = '1';
          dots.forEach(function(d, i){ d.classList.toggle('is-active', i === next); });
          animating = false;
          startBar();
          timer = setTimeout(function(){ goTo((cur+1)%slides.length,'next'); }, AUTO);
        }, DUR);
      }

      /* ── Init ── */
      slides.forEach(function(s, i){
        s.style.zIndex = i === 0 ? '2' : '1';
        s.querySelector('.lx-bg').style.transform = i === 0 ? 'scale(1)' : 'scale(1.03)';
      });
      startBar();
      timer = setTimeout(function(){ goTo(1,'next'); }, AUTO);

      /* ── Controls ── */
      document.getElementById('lxNext').addEventListener('click', function(){
        clearTimeout(timer); goTo((cur+1)%slides.length,'next');
      });
      document.getElementById('lxPrev').addEventListener('click', function(){
        clearTimeout(timer); goTo((cur-1+slides.length)%slides.length,'prev');
      });
      dots.forEach(function(d, i){
        d.addEventListener('click', function(){
          clearTimeout(timer); goTo(i, i > cur ? 'next' : 'prev');
        });
      });
      /* Swipe */
      var sx = 0;
      wrap.addEventListener('touchstart', function(e){ sx = e.touches[0].clientX; },{passive:true});
      wrap.addEventListener('touchend', function(e){
        var diff = sx - e.changedTouches[0].clientX;
        if(Math.abs(diff) > 50){ clearTimeout(timer); goTo(diff>0?(cur+1)%slides.length:(cur-1+slides.length)%slides.length, diff>0?'next':'prev'); }
      });
      /* Keyboard */
      document.addEventListener('keydown', function(e){
        if(e.key==='ArrowRight'){ clearTimeout(timer); goTo((cur+1)%slides.length,'next'); }
        if(e.key==='ArrowLeft') { clearTimeout(timer); goTo((cur-1+slides.length)%slides.length,'prev'); }
      });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'manzili_slider', 'manzili_fragrance_slider' );
