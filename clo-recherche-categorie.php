<?php
defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_before_shop_loop', 'clo_category_search_form', 5 );
add_action( 'woocommerce_no_products_found', 'clo_category_search_form', 5 );

function clo_category_search_form() {
    static $shown = false;
    if ( $shown || is_admin() || ! function_exists( 'is_product_category' ) ) {
        return;
    }

    $term = is_product_category() ? get_queried_object() : null;
    if ( ! ( $term instanceof WP_Term ) ) {
        $slug = get_query_var( 'product_cat' );
        if ( ! is_search() || ! is_string( $slug ) || '' === $slug ) {
            return;
        }
        $term = get_term_by( 'slug', $slug, 'product_cat' );
    }
    if ( ! ( $term instanceof WP_Term ) || 'product_cat' !== $term->taxonomy ) {
        return;
    }
    $reset_url = get_term_link( $term );
    if ( is_wp_error( $reset_url ) ) {
        return;
    }
    $shown = true;
    $search = get_search_query( false );
    ?>
    <style>
    #clo-category-search{clear:both;flex:0 0 100%;width:100%;margin:0 0 26px;padding:22px;background:#fbfaf7;border:1px solid #e8e0d3;box-sizing:border-box}
    #clo-category-search *{box-sizing:border-box}
    #clo-category-search label{display:block;margin:0 0 12px;color:#30291f;font-family:Georgia,serif;font-size:20px;font-weight:400;line-height:1.4}
    #clo-category-search form{display:flex;gap:10px;margin:0}
    #clo-category-search input[type="search"]{flex:1;min-width:0;height:48px;margin:0;padding:0 14px;border:1px solid #d9d0c1;border-radius:0;background:#fff;color:#222;font-size:16px;box-shadow:none}
    #clo-category-search button{flex:none;height:48px;min-height:48px;margin:0;padding:0 22px;border:1px solid #c9a04a;border-radius:0;background:#c9a04a;color:#17130c;font-size:11px;letter-spacing:.08em;font-weight:700;line-height:1.2;cursor:pointer}
    #clo-category-search button:hover{background:#dfbd73}
    #clo-category-search a{display:inline-block;margin-top:12px;color:#70521c;font-size:13px;text-decoration:underline}
    #clo-category-search :is(input,button,a):focus-visible{outline:2px solid #967029;outline-offset:3px}
    @media(max-width:549px){#clo-category-search{padding:17px}#clo-category-search form{flex-direction:column}#clo-category-searchinput[type="search"]{flex:auto;width:100%}#clo-category-search button{width:100%}#clo-category-search label{font-size:18px}}
    </style>

    <div id="clo-category-search">
        <label for="clo-category-search-input">
            Rechercher dans <?php echo esc_html( $term->name ); ?>
        </label>
        <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Recherche dans la catégorie">
            <input type="search" id="clo-category-search-input" name="s"
                   value="<?php echo esc_attr( $search ); ?>"
                   placeholder="Nom du parfum, mot-clé…">
            <input type="hidden" name="post_type" value="product">
            <input type="hidden" name="product_cat" value="<?php echo esc_attr( $term->slug ); ?>">
            <button type="submit">Rechercher →</button>
        </form>
        <?php if ( '' !== $search ) : ?>
            <a href="<?php echo esc_url( $reset_url ); ?>">Effacer la recherche et voir toute la catégorie</a>
        <?php endif; ?>
    </div>
    <?php
}
