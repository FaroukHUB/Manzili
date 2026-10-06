<?php
/** Quiz Collection L'Original — à charger depuis le thème enfant. */
defined('ABSPATH') || exit;

function cloq_options() {
    return array(
        'famille' => array('floral'=>'Floral','boise'=>'Boisé','gourmand'=>'Gourmand / sucré','frais'=>'Frais','oriental'=>'Oriental'),
        'intensite' => array('discrete'=>'Discrète','equilibree'=>'Équilibrée','puissante'=>'Puissante'),
        'moment' => array('quotidien'=>'Au quotidien','soiree'=>'En soirée','occasion'=>'Occasion particulière'),
    );
}

function cloq_base() {
    // Familles tirées des descriptions courtes de l'export du 10/09/2026.
    // Une famille absente de cette liste n'est pas nécessairement incompatible.
    return array(
        139=>array('floral','gourmand'),163=>array('frais','boise'),
        165=>array('frais','boise'),167=>array('floral','gourmand'),
        173=>array('boise'),174=>array('gourmand'),175=>array('gourmand'),
        176=>array('frais'),177=>array('floral','boise'),178=>array('frais','boise'),
        179=>array('frais','boise'),180=>array('floral','oriental'),
        181=>array('gourmand'),183=>array('gourmand'),184=>array('floral'),186=>array('gourmand'),
    );
}

// Champs modifiables dans Produits > Modifier > Données produit > Général.
add_action('woocommerce_product_options_general_product_data', function() {
    global $product_object;
    if (!$product_object) return;
    $id=$product_object->get_id(); $base=cloq_base();
    $enabled=get_post_meta($id,'_cloq_enabled',true);
    if ($enabled==='') $enabled=isset($base[$id]) ? 'yes' : 'no';
    echo '<div class="options_group"><p class="form-field"><strong>Quiz parfum</strong></p>';
    woocommerce_wp_checkbox(array('id'=>'_cloq_enabled','label'=>'Inclure dans le quiz','value'=>$enabled));
    foreach (cloq_options() as $group=>$choices) {
        $saved=get_post_meta($id,'_cloq_'.$group,true);
        $selected=is_array($saved) ? $saved : ($group==='famille' && isset($base[$id]) ? $base[$id] : array());
        foreach ($choices as $key=>$label) {
            woocommerce_wp_checkbox(array('id'=>'_cloq_'.$group.'_'.$key,'label'=>ucfirst($group).' : '.$label,'value'=>in_array($key,$selected,true)?'yes':'no'));
        }
    }
    echo '<p class="form-field">Cochez uniquement les caractéristiques confirmées. Plusieurs choix sont possibles.</p></div>';
});

add_action('woocommerce_admin_process_product_object',function($product) {
    // WooCommerce contrôle les permissions et le nonce de la sauvegarde produit.
    $product->update_meta_data('_cloq_enabled',isset($_POST['_cloq_enabled'])?'yes':'no');
    foreach (cloq_options() as $group=>$choices) {
        $values=array();
        foreach ($choices as $key=>$label) if(isset($_POST['_cloq_'.$group.'_'.$key])) $values[]=$key;
        $product->update_meta_data('_cloq_'.$group,$values);
    }
});

add_shortcode('clo_quiz_parfum',function() {
    if (!function_exists('wc_get_product')) return '<p>Le quiz nécessite WooCommerce.</p>';
    $options=cloq_options(); $answers=array();
    foreach ($options as $group=>$choices) {
        $raw=isset($_GET['cloq_'.$group]) && is_string($_GET['cloq_'.$group]) ? sanitize_key(wp_unslash($_GET['cloq_'.$group])) : '';
        $answers[$group]=isset($choices[$raw])?$raw:'';
    }
    $submitted=isset($_GET['cloq_search']);
    $valid=!in_array('',$answers,true);
    $uid=wp_unique_id('cloq-');
    ob_start();
    ?>
    <style>
    .cloq{background:#fbfaf7;color:#171717;padding:38px 24px;box-sizing:border-box}.cloq *{box-sizing:border-box}
    .cloq .cloq-inner{max-width:1200px;margin:auto}.cloq h2{text-align:center;font:400 clamp(27px,3vw,42px)/1.2 Georgia,serif;text-transform:uppercase;color:#171717;margin:0 0 10px}
    .cloq .cloq-intro{text-align:center;color:#77736c;font-size:13px;margin:0 0 26px}
    .cloq .cloq-form{display:grid;grid-template-columns:repeat(3,minmax(0,1fr)) auto;gap:14px;align-items:end;margin:0}
    .cloq label{display:block;color:#514633;font-size:12px;margin-bottom:7px}.cloq select{width:100%;height:54px;margin:0;padding:0 12px;border:1px solid #e8e1d6;background:#fff;color:#27231e;font-size:16px}
    .cloq button,.cloq .cloq-link{display:inline-flex;align-items:center;justify-content:center;min-height:48px;margin:0;padding:12px 18px;border:1px solid #c9a04a;background:#d2aa5c;color:#17130c;text-decoration:none;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;cursor:pointer}
    .cloq button{height:54px}.cloq button:hover,.cloq .cloq-link:hover{background:#17130c;color:#e1c37e}.cloq :focus-visible{outline:2px solid #987329;outline-offset:3px}
    .cloq .cloq-results{margin-top:30px;scroll-margin-top:100px}.cloq .cloq-results>h3{font:400 25px Georgia,serif;color:#171717}.cloq .cloq-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
    .cloq article{display:flex;flex-direction:column;padding:20px;background:white;border:1px solid #e8e3dc}.cloq article img{display:block;width:100%;height:210px;object-fit:contain;margin:0 0 16px}.cloq article h4{font:400 20px/1.35 Georgia,serif;color:#171717;margin:0 0 12px}
    .cloq article p{font-size:13px;line-height:1.6;color:#655e52}.cloq .cloq-price{font-weight:700;color:#171717;margin-top:auto}.cloq .cloq-note{font-size:12px;color:#77736c}
    @media(max-width:850px){.cloq .cloq-form{grid-template-columns:1fr;max-width:480px;margin:auto}.cloq button{width:100%}.cloq .cloq-grid{grid-template-columns:1fr}.cloq{padding:30px 16px}}
    </style>
    <section class="cloq" aria-labelledby="<?php echo esc_attr($uid); ?>">
    <div class="cloq-inner">
    <h2 id="<?php echo esc_attr($uid); ?>">Trouvez votre signature</h2>
    <p class="cloq-intro">Choisissez vos préférences et découvrez notre sélection de parfums.</p>
    <form class="cloq-form" method="get" action="<?php echo esc_url(get_permalink()); ?>#cloq-resultats">
    <input type="hidden" name="cloq_search" value="1">
    <?php
    // Préserve le routage des sites utilisant des permaliens simples.
    $route=array(); parse_str((string)wp_parse_url(get_permalink(),PHP_URL_QUERY),$route);
    foreach($route as $key=>$value) if(is_scalar($value)) echo '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
    $labels=array('famille'=>'1 · Votre univers','intensite'=>'2 · Votre intensité','moment'=>'3 · Votre moment');
    foreach($options as $group=>$choices): ?>
    <div><label for="<?php echo esc_attr($uid.$group); ?>"><?php echo esc_html($labels[$group]); ?></label>
    <select required id="<?php echo esc_attr($uid.$group); ?>" name="cloq_<?php echo esc_attr($group); ?>">
    <option value="">Choisir…</option>
    <?php foreach($choices as $key=>$label): ?>
    <option value="<?php echo esc_attr($key); ?>" <?php selected($answers[$group],$key); ?>><?php echo esc_html($label); ?></option>
    <?php endforeach; ?></select></div>
    <?php endforeach; ?>
    <button type="submit">Trouver mon parfum →</button></form>
    <?php if($submitted): ?>
    <div class="cloq-results" id="cloq-resultats">
    <?php if(!$valid): ?><p>Choisissez une réponse dans chacun des trois menus.</p>
    <?php else:
        $base=cloq_base();
        $extra=get_posts(array('post_type'=>'product','post_status'=>'publish','fields'=>'ids','posts_per_page'=>-1,'no_found_rows'=>true,'meta_key'=>'_cloq_enabled','meta_value'=>'yes'));
        $ids=array_unique(array_merge(array_keys($base),$extra)); $results=array();
        foreach($ids as $id) {
            if(get_post_meta($id,'_cloq_enabled',true)==='no') continue;
            $p=wc_get_product($id);
            if(!$p || $p->get_status()!=='publish' || !$p->is_visible() || !$p->is_in_stock() || !$p->is_purchasable() || post_password_required($id)) continue;
            $profile=array();
            foreach($options as $group=>$choices) {
                $stored=get_post_meta($id,'_cloq_'.$group,true);
                $profile[$group]=is_array($stored)?$stored:($group==='famille' && isset($base[$id])?$base[$id]:array());
            }
            if(!in_array($answers['famille'],$profile['famille'],true)) continue;
            $score=0; $matched=array(); $missing=array(); $different=array();
            foreach($answers as $group=>$answer) {
                if(in_array($answer,$profile[$group],true)) { $score++; $matched[]=$options[$group][$answer]; }
                elseif(empty($profile[$group])) $missing[]=$group==='intensite'?'intensité':'occasion';
                else $different[]=$group==='intensite'?'intensité':'occasion';
            }
            $results[]=array('p'=>$p,'score'=>$score,'matched'=>$matched,'missing'=>$missing,'different'=>$different);
        }
        usort($results,function($a,$b){return $b['score']<=>$a['score'] ?: $a['p']->get_id()<=>$b['p']->get_id();});
        ?>
        <h3>Votre sélection</h3>
        <?php if(empty($results)): ?><p>Aucun parfum disponible ne correspond actuellement à cet univers dans notre sélection. Essayez un autre univers.</p>
        <?php else: ?><div class="cloq-grid">
        <?php foreach(array_slice($results,0,3) as $result): $p=$result['p']; ?>
        <article>
        <?php echo wp_kses_post($p->get_image('woocommerce_thumbnail')); ?>
        <h4><?php echo esc_html($p->get_name()); ?></h4>
        <p><?php echo esc_html(($result['score']===3?'Vos trois critères correspondent : ':'Correspondance partielle : ').implode(', ',$result['matched']).'.'); ?></p>
        <?php if($result['missing']): ?><p class="cloq-note"><?php echo esc_html('Non renseigné : '.implode(', ',$result['missing']).'.'); ?></p><?php endif; ?>
        <?php if($result['different']): ?><p class="cloq-note"><?php echo esc_html('Diffère de votre choix : '.implode(', ',$result['different']).'.'); ?></p><?php endif; ?>
        <p class="cloq-price"><?php echo wp_kses_post($p->get_price_html()); ?></p>
        <a class="cloq-link" href="<?php echo esc_url($p->get_permalink()); ?>">Découvrir ce parfum →</a>
        </article><?php endforeach; ?></div><?php endif; ?>
    <?php endif; ?></div><?php endif; ?>
    </div></section>
    <?php return ob_get_clean();
});
