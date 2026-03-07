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


/* =============================================
   MANZILI - SLIDER FRAGRANCES
   Shortcode: [manzili_slider]
   ============================================= */
function manzili_fragrance_slider() {
    $slides = [
        [
            'bg'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-bois.png',
            'bottle'  => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-coloriginal-scaled.png',
            'name'    => "Collection L'Original",
            'desc'    => "Un sillage boisé, chaleureux et profond. L'authenticité à l'état pur.",
            'link'    => '#',
        ],
        [
            'bg'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-coton.png',
            'bottle'  => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-coton.png',
            'name'    => 'Coton',
            'desc'    => "Une douceur enveloppante, légère comme un souffle. La pureté incarnée.",
            'link'    => '#',
        ],
        [
            'bg'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-krypton.png',
            'bottle'  => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-colprivee.png',
            'name'    => 'Collection Privée',
            'desc'    => "Réservée aux esprits d'exception. Une exclusivité rare et magnétique.",
            'link'    => '#',
        ],
        [
            'bg'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-dima.png',
            'bottle'  => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-dima.png',
            'name'    => 'Dima',
            'desc'    => "Une signature intemporelle. Intense, mystérieuse, inoubliable.",
            'link'    => '#',
        ],
        [
            'bg'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-pistachio.png',
            'bottle'  => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-pistachio.png',
            'name'    => 'Pistachio',
            'desc'    => "La fraîcheur d'un jardin d'Orient. Gourmande, vivante, envoûtante.",
            'link'    => '#',
        ],
        [
            'bg'      => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bg-rose.png',
            'bottle'  => 'https://www.collectionloriginal.com/wp-content/uploads/2026/03/bot-rose.png',
            'name'    => 'Rose',
            'desc'    => "L'élégance en fleur. Délicate et lumineuse, elle laisse une trace éternelle.",
            'link'    => '#',
        ],
    ];
    ob_start(); ?>
    <div class="mz-slider-wrap">
        <div class="mz-slider" id="mzSlider">
            <?php foreach ( $slides as $i => $s ) : ?>
            <div class="mz-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                <div class="mz-bg" style="background-image:url('<?php echo esc_url($s['bg']); ?>')"></div>
                <div class="mz-overlay"></div>
                <div class="mz-content">
                    <div class="mz-text">
                        <span class="mz-num"><?php echo str_pad($i+1,2,'0',STR_PAD_LEFT); ?></span>
                        <h2 class="mz-name"><?php echo esc_html($s['name']); ?></h2>
                        <p class="mz-desc"><?php echo esc_html($s['desc']); ?></p>
                        <a href="<?php echo esc_url($s['link']); ?>" class="mz-btn">Découvrir</a>
                    </div>
                    <div class="mz-bottle">
                        <img src="<?php echo esc_url($s['bottle']); ?>" alt="<?php echo esc_attr($s['name']); ?>">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <button class="mz-arrow mz-prev" aria-label="Précédent">&#8249;</button>
            <button class="mz-arrow mz-next" aria-label="Suivant">&#8250;</button>
            <div class="mz-dots">
                <?php foreach ( $slides as $i => $s ) : ?>
                <button class="mz-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <style>
    .mz-slider-wrap { width:100%; overflow:hidden; }
    .mz-slider {
        position:relative;
        width:100%;
        height:90vh;
        min-height:520px;
    }
    .mz-slide {
        position:absolute; inset:0;
        opacity:0;
        pointer-events:none;
        transition:opacity 0.9s ease;
    }
    .mz-slide.active {
        opacity:1;
        pointer-events:all;
    }
    .mz-bg {
        position:absolute; inset:0;
        background-size:cover;
        background-position:center;
        transform:scale(1.04);
        transition:transform 6s ease;
    }
    .mz-slide.active .mz-bg { transform:scale(1); }
    .mz-overlay {
        position:absolute; inset:0;
        background:linear-gradient(to right, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.05) 60%, transparent 100%);
    }
    .mz-content {
        position:relative; z-index:2;
        display:flex;
        align-items:center;
        justify-content:space-between;
        height:100%;
        padding:0 7%;
        max-width:1400px;
        margin:0 auto;
    }
    .mz-text {
        flex:1;
        max-width:420px;
        color:#fff;
    }
    .mz-num {
        display:block;
        font-size:0.75rem;
        letter-spacing:0.3em;
        opacity:0.6;
        margin-bottom:1rem;
        font-weight:400;
    }
    .mz-name {
        font-size:clamp(1.8rem, 3.5vw, 3.2rem);
        font-weight:300;
        letter-spacing:0.08em;
        text-transform:uppercase;
        line-height:1.15;
        margin:0 0 1rem 0;
    }
    .mz-desc {
        font-size:clamp(0.9rem, 1.3vw, 1.05rem);
        font-weight:300;
        line-height:1.7;
        opacity:0.88;
        margin:0 0 2rem 0;
        letter-spacing:0.02em;
    }
    .mz-btn {
        display:inline-block;
        padding:0.75rem 2.2rem;
        border:1px solid rgba(255,255,255,0.8);
        color:#fff;
        text-decoration:none;
        letter-spacing:0.18em;
        text-transform:uppercase;
        font-size:0.78rem;
        font-weight:400;
        transition:background 0.3s, color 0.3s;
    }
    .mz-btn:hover { background:#fff; color:#222; }

    /* ─── BOUTEILLE — posée sur le sol de la scène ─── */
    .mz-bottle {
        flex:1;
        display:flex;
        justify-content:center;
        align-items:flex-end;    /* bouteille ancrée en bas */
        align-self:stretch;      /* force la colonne à occuper toute la hauteur malgré align-items:center du parent */
        padding-bottom:8vh;      /* niveau du sol visible (~8% depuis le bas en vh) */
        position:relative;
    }
    /* Ombre au sol — ellipse floue juste sous la bouteille */
    .mz-bottle::after {
        content:'';
        position:absolute;
        bottom:8vh;              /* aligné avec la base de la bouteille */
        left:50%;
        transform:translateX(-50%);
        width:48%;
        height:22px;
        background:radial-gradient(ellipse at center, rgba(0,0,0,0.60) 0%, transparent 70%);
        border-radius:50%;
        filter:blur(10px);
        pointer-events:none;
    }
    @keyframes mz-float {
        0%   { transform:translateY(0px); }
        50%  { transform:translateY(-14px); }
        100% { transform:translateY(0px); }
    }
    .mz-bottle img {
        max-height:64vh;
        max-width:100%;
        object-fit:contain;
        filter:
            drop-shadow(0 35px 45px rgba(0,0,0,0.55))
            drop-shadow(0 8px 18px rgba(0,0,0,0.35))
            brightness(1.05) contrast(1.02);
        opacity:0;
        transform:translateY(30px);
        transition:opacity 0.8s ease 0.3s, transform 0.8s ease 0.3s;
    }
    .mz-slide.active .mz-bottle img {
        opacity:1;
        transform:translateY(0);
        animation:mz-float 4s ease-in-out infinite;
        animation-delay:1.2s;
    }
    /* Arrows */
    .mz-arrow {
        position:absolute;
        top:50%; transform:translateY(-50%);
        z-index:10;
        width:48px; height:48px;
        border-radius:50%;
        border:1px solid rgba(255,255,255,0.5);
        background:rgba(255,255,255,0.12);
        color:#fff;
        font-size:1.6rem;
        cursor:pointer;
        display:flex; align-items:center; justify-content:center;
        backdrop-filter:blur(6px);
        transition:background 0.3s, border 0.3s;
        padding:0;
        line-height:1;
    }
    .mz-arrow:hover { background:rgba(255,255,255,0.28); border-color:#fff; }
    .mz-prev { left:2%; }
    .mz-next { right:2%; }
    /* Dots */
    .mz-dots {
        position:absolute;
        bottom:28px; left:50%;
        transform:translateX(-50%);
        z-index:10;
        display:flex; gap:10px;
    }
    .mz-dot {
        width:8px; height:8px;
        border-radius:50%;
        border:1px solid rgba(255,255,255,0.7);
        background:transparent;
        cursor:pointer;
        padding:0;
        transition:all 0.35s ease;
    }
    .mz-dot.active {
        background:#fff;
        width:26px;
        border-radius:4px;
    }
    /* Mobile */
    @media (max-width:768px) {
        .mz-slider { height:100svh; min-height:600px; }
        .mz-overlay {
            background:linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.55) 100%);
        }
        .mz-content {
            flex-direction:column;
            justify-content:flex-start;
            align-items:center;
            padding:80px 6% 0;
            text-align:center;
            gap:0;
        }
        .mz-text { max-width:100%; flex:none; }
        /* Bouteille posée en bas sur mobile — positionnement absolu */
        .mz-bottle {
            position:absolute;
            bottom:0; left:0; right:0;
            height:52vh;
            display:flex;
            align-items:flex-end;
            justify-content:center;
            padding-bottom:6vh;
            flex:none;
            align-self:auto;
        }
        .mz-bottle::after {
            display:block;
            bottom:6vh;
        }
        .mz-bottle img { max-height:46vh; }
        .mz-prev { left:4%; }
        .mz-next { right:4%; }
    }
    </style>
    <script>
    (function(){
        var slider = document.getElementById('mzSlider');
        if(!slider) return;
        var slides = slider.querySelectorAll('.mz-slide');
        var dots   = slider.querySelectorAll('.mz-dot');
        var prev   = slider.querySelector('.mz-prev');
        var next   = slider.querySelector('.mz-next');
        var cur    = 0;
        var timer;
        var total  = slides.length;
        function goTo(n) {
            slides[cur].classList.remove('active');
            dots[cur].classList.remove('active');
            cur = (n + total) % total;
            slides[cur].classList.add('active');
            dots[cur].classList.add('active');
        }
        function autoStart() { timer = setInterval(function(){ goTo(cur+1); }, 5500); }
        function autoStop()  { clearInterval(timer); }
        function resetAuto() { autoStop(); autoStart(); }
        prev.addEventListener('click', function(){ goTo(cur-1); resetAuto(); });
        next.addEventListener('click', function(){ goTo(cur+1); resetAuto(); });
        dots.forEach(function(d, i){
            d.addEventListener('click', function(){ goTo(i); resetAuto(); });
        });
        /* Swipe mobile */
        var sx = 0;
        slider.addEventListener('touchstart', function(e){ sx = e.touches[0].clientX; }, {passive:true});
        slider.addEventListener('touchend', function(e){
            var diff = sx - e.changedTouches[0].clientX;
            if(Math.abs(diff) > 50){ goTo(diff > 0 ? cur+1 : cur-1); resetAuto(); }
        });
        autoStart();
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'manzili_slider', 'manzili_fragrance_slider' );
