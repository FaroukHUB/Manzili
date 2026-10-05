<?php
/**
 * CLO Pilot Import — WP-CLI script
 *
 * Usage (on the server, from the WordPress root):
 *   wp eval-file wp-content/themes/flatsome-child/assets/data/clo-pilot-import.php
 *
 * For each pilot product:
 * - Backs up the current post_content to _clo_desc_backup
 * - Writes all _clo_* meta fields
 * - Sets _clo_enabled = '1'
 * - NEVER modifies post_content
 *
 * Products: AÏSHA (139), Cherry (174), Vie (191)
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "Run with: wp eval-file path/to/this-file.php\n";
	exit( 1 );
}

$pilots = [];

// ─────────────────────────────────────────────────────────────────────────────
// AÏSHA — ID 139
// Content extracted from the existing .clo-product HTML in the product description
// ─────────────────────────────────────────────────────────────────────────────
$pilots[139] = [
	'_clo_enabled'        => '1',
	'_clo_kicker'         => "Collection Privée L'Original · Eau de parfum 50 ml",
	'_clo_intro_h2'       => 'Aisha, une rose gourmande traversée par un souffle salin',
	'_clo_lead'           => "Inspirée de Magic Al-Jazeera, Aisha Collection Privée L'Original joue sur un contraste captivant : une fraîcheur ozonique légèrement salée, la sensualité de la rose turque et une douceur de praline, puis un fond chaud de vanille, d'accord ambré boisé et de patchouli.",
	'_clo_intro_p2'       => 'Son évolution progressive lui donne une personnalité à la fois lumineuse, enveloppante et reconnaissable. Une fragrance mixte pensée pour celles et ceux qui aiment les parfums orientaux floraux, sans renoncer à une ouverture fraîche.',

	'_clo_fact_1_label'   => 'Style olfactif',
	'_clo_fact_1_value'   => 'Oriental · Floral · Gourmand',
	'_clo_fact_2_label'   => 'Inspiration',
	'_clo_fact_2_value'   => 'Magic Al-Jazeera',
	'_clo_fact_3_label'   => 'Profil',
	'_clo_fact_3_value'   => 'Fragrance mixte',
	'_clo_fact_4_label'   => 'Format',
	'_clo_fact_4_value'   => 'Eau de parfum · 50 ml',

	'_clo_image_1_id'      => 0,
	'_clo_image_1_alt'     => "Univers olfactif d'Aisha Collection Privée L'Original aux accents de rose, de vanille et d'ambre",
	'_clo_image_1_caption' => 'Fraîcheur saline · Rose turque · Praline · Vanille',

	'_clo_notes_kicker'   => 'Composition',
	'_clo_notes_h2'       => 'La pyramide olfactive d\'Aisha',
	'_clo_notes_intro'    => "Aisha se révèle en trois temps : une entrée aérienne, un cœur floral gourmand, puis une base plus chaude qui structure le sillage.",

	'_clo_note_1_index'   => "01 · L'envol",
	'_clo_note_1_h3'      => 'Notes de tête',
	'_clo_note_1_text'    => "Accords ozoniques et sel. Une ouverture fraîche, minérale et lumineuse qui allège immédiatement la composition.",
	'_clo_note_2_index'   => '02 · Le cœur',
	'_clo_note_2_h3'      => 'Notes de cœur',
	'_clo_note_2_text'    => "Rose turque et praline. La fleur gagne en rondeur grâce à une gourmandise douce, élégante et réconfortante.",
	'_clo_note_3_index'   => '03 · Le sillage',
	'_clo_note_3_h3'      => 'Notes de fond',
	'_clo_note_3_text'    => "Vanille, accord ambré boisé et patchouli. Un fond enveloppant qui apporte chaleur, profondeur et caractère.",

	'_clo_apply_kicker'   => 'Le rituel',
	'_clo_apply_h2'       => 'Comment porter Aisha ?',
	'_clo_apply_intro'    => "L'intensité idéale dépend de votre peau, de la température et de la présence recherchée. Commencez avec peu de vaporisations, puis ajustez après avoir laissé la fragrance évoluer quelques minutes.",

	'_clo_step_1_strong'  => 'Pour la journée',
	'_clo_step_1_span'    => "Commencez par 2 vaporisations légères, par exemple sur le cou et un poignet.",
	'_clo_step_2_strong'  => 'Pour une soirée',
	'_clo_step_2_span'    => "Selon vos préférences, 3 à 4 vaporisations créent une présence plus enveloppante.",
	'_clo_step_3_strong'  => 'Sur les points de pulsation',
	'_clo_step_3_span'    => "Cou, derrière les oreilles, poignets ou pli des coudes favorisent une diffusion progressive.",
	'_clo_step_4_strong'  => 'Sans frotter',
	'_clo_step_4_span'    => "Laissez sécher naturellement afin de préserver l'évolution de l'ouverture jusqu'aux notes de fond.",
	'_clo_step_5_strong'  => '',
	'_clo_step_5_span'    => '',

	'_clo_tip_label'      => 'Astuce Collection Privée :',
	'_clo_tip_text'       => "appliquez Aisha sur une peau propre et hydratée avec un soin sans parfum. Une peau moins sèche retient généralement mieux la fragrance.",

	'_clo_image_2_id'     => 0,
	'_clo_image_2_alt'    => "Conseils pour appliquer l'eau de parfum Aisha Collection Privée L'Original",

	'_clo_night_kicker'   => 'Saisons & occasions',
	'_clo_night_h2'       => 'Quand porter Aisha ?',
	'_clo_night_p'        => "Sa rose gourmande et son fond vanillé ambré s'expriment particulièrement bien lorsque l'air se rafraîchit. Son départ salin et ozonique lui permet toutefois de rester plus lumineux qu'un oriental exclusivement sucré.",

	'_clo_season_1_label' => 'Automne · Hiver',
	'_clo_season_1_text'  => "La vanille, le patchouli et l'accord ambré boisé gagnent en profondeur.",
	'_clo_season_2_label' => 'Printemps',
	'_clo_season_2_text'  => "La fraîcheur initiale et la rose ressortent avec une application maîtrisée.",
	'_clo_season_3_label' => 'Été',
	'_clo_season_3_text'  => "Privilégiez une vaporisation légère et les soirées moins chaudes.",
	'_clo_season_4_label' => 'Jour · Soir',
	'_clo_season_4_text'  => "Discret en journée avec peu de sprays, plus enveloppant pour un dîner ou une sortie.",

	'_clo_image_3_id'      => 0,
	'_clo_image_3_alt'     => "Aisha Collection Privée L'Original, parfum oriental floral à porter notamment en soirée",
	'_clo_image_3_caption' => 'Une signature contrastée du jour jusqu\'au soir',

	'_clo_guide_kicker'   => 'Conseils de conservation',
	'_clo_guide_h2'       => 'Comment prolonger son sillage ?',
	'_clo_guide_intro'    => "La tenue d'un parfum varie selon la peau, la température et la quantité appliquée. Ces gestes simples permettent de profiter plus harmonieusement de l'évolution d'Aisha, sans surcharger le sillage.",
	'_clo_guide_1_icon'   => '01',
	'_clo_guide_1_h3'     => 'Hydrater la peau',
	'_clo_guide_1_text'   => "Utilisez un soin neutre avant la vaporisation pour limiter l'évaporation trop rapide sur peau sèche.",
	'_clo_guide_2_icon'   => '02',
	'_clo_guide_2_h3'     => 'Cibler les zones chaudes',
	'_clo_guide_2_text'   => "Cou, nuque, poignets et plis des coudes diffusent la fragrance au fil des mouvements.",
	'_clo_guide_3_icon'   => '03',
	'_clo_guide_3_h3'     => 'Parfumer un vêtement',
	'_clo_guide_3_text'   => "Vaporisez à 20–30 cm après un test sur une zone cachée, surtout sur les textiles clairs ou délicats.",
	'_clo_guide_4_icon'   => '04',
	'_clo_guide_4_h3'     => 'Protéger le flacon',
	'_clo_guide_4_text'   => "Conservez-le debout, loin du soleil, de la chaleur et de l'humidité. Évitez la voiture et la salle de bain.",

	'_clo_forwho_kicker'  => 'Votre profil olfactif',
	'_clo_forwho_h2'      => 'Aisha est-il fait pour vous ?',
	'_clo_forwho_p'       => "Aisha s'adresse aux amateurs de contrastes : le frais et le chaleureux, le floral et le gourmand, la lumière du sel et la profondeur du patchouli.",
	'_clo_check_1'        => "Vous appréciez la rose lorsqu'elle est adoucie par la vanille et la praline.",
	'_clo_check_2'        => "Vous recherchez un parfum oriental floral avec une ouverture fraîche et singulière.",
	'_clo_check_3'        => "Vous aimez les fragrances mixtes, enveloppantes et faciles à reconnaître.",
	'_clo_check_4'        => "Vous voulez pouvoir moduler l'intensité entre le quotidien et la soirée.",
	'_clo_check_5'        => '',
	'_clo_check_6'        => '',
	'_clo_honest_label'   => 'À savoir avant de choisir :',
	'_clo_honest_text'    => "si vous recherchez uniquement une eau très légère, hespéridée et discrète du début à la fin, le fond vanillé, ambré et gourmand d'Aisha pourra paraître plus présent.",

	'_clo_faq_kicker'     => 'Questions fréquentes',
	'_clo_faq_h2'         => "Tout savoir sur Aisha Collection Privée L'Original",
	'_clo_faq_intro'      => "Des réponses précises pour choisir, appliquer et conserver votre eau de parfum Aisha 50 ml.",
	'_clo_faq_1_q'        => "À quoi sent Aisha Collection Privée L'Original ?",
	'_clo_faq_1_a'        => "Aisha débute sur des accords ozoniques et salés, puis dévoile un cœur de rose turque et de praline. La vanille, l'accord ambré boisé et le patchouli composent ensuite un fond plus chaud, gourmand et enveloppant.",
	'_clo_faq_2_q'        => "Aisha est-il un parfum pour femme ou pour homme ?",
	'_clo_faq_2_a'        => "Aisha est présenté comme une fragrance mixte. Sa rose gourmande peut séduire les amateurs de floraux, tandis que ses facettes salines, boisées et ambrées lui donnent un caractère qui ne dépend pas d'un genre.",
	'_clo_faq_3_q'        => "Aisha est-il identique à Magic Al-Jazeera ?",
	'_clo_faq_3_a'        => "Non. Aisha est une fragrance indépendante inspirée de l'univers olfactif de Magic Al-Jazeera. Une inspiration reprend un esprit et certains accords, sans prétendre être le parfum original ni lui être affiliée.",
	'_clo_faq_4_q'        => "Quelle est la meilleure saison pour porter Aisha ?",
	'_clo_faq_4_a'        => "Son fond vanillé, ambré et boisé convient particulièrement à l'automne et à l'hiver. Au printemps, son ouverture fraîche et son cœur floral restent agréables. En été, préférez une application légère et les moments moins chauds.",
	'_clo_faq_5_q'        => "Combien de vaporisations faut-il appliquer ?",
	'_clo_faq_5_a'        => "Commencez par 2 vaporisations en journée. Pour une soirée ou une présence plus affirmée, 3 à 4 peuvent convenir selon votre peau, la température et votre sensibilité. Il vaut mieux ajuster progressivement.",
	'_clo_faq_6_q'        => "Comment faire tenir Aisha plus longtemps ?",
	'_clo_faq_6_a'        => "Appliquez-le sur une peau propre et hydratée, ciblez les points de pulsation et évitez de frotter les poignets. Vous pouvez aussi parfumer légèrement un vêtement après un test sur une zone non visible.",
	'_clo_faq_7_q'        => "Peut-on vaporiser Aisha sur les vêtements ?",
	'_clo_faq_7_a'        => "Oui, avec précaution. Vaporisez à environ 20–30 cm et testez d'abord sur une partie cachée. Évitez une application directe sur la soie, les textiles délicats ou les matières claires susceptibles de marquer.",
	'_clo_faq_8_q'        => "Aisha convient-il comme idée cadeau ?",
	'_clo_faq_8_a'        => "Oui, surtout pour une personne qui aime la rose, la vanille, les parfums orientaux floraux et les compositions gourmandes. Son format 50 ml et son profil mixte en font une option polyvalente.",

	'_clo_disc_h2'        => "Prolongez l'expérience Collection Privée L'Original",
	'_clo_disc_p'         => "Découvrez d'autres signatures de la collection ou composez une sélection de plusieurs fragrances.",
	'_clo_link_1_text'    => "Voir la Collection Privée L'Original",
	'_clo_link_1_href'    => 'https://www.collectionloriginal.com/product-category/collection-privee-loriginal/',
	'_clo_link_2_text'    => 'Découvrir le lot 3 Best Seller',
	'_clo_link_2_href'    => 'https://www.collectionloriginal.com/product/lot-3-best-seller/',
	'_clo_disclaimer'     => "La référence à Magic Al-Jazeera décrit une inspiration olfactive. Aisha Collection Privée L'Original est une création indépendante, sans affiliation avec la marque citée.",
];

// ─────────────────────────────────────────────────────────────────────────────
// CHERRY — ID 174
// Content adapted from the existing product description (no invented facts)
// ─────────────────────────────────────────────────────────────────────────────
$pilots[174] = [
	'_clo_enabled'        => '1',
	'_clo_kicker'         => "Collection Privée L'Original · Eau de parfum 50 ml",
	'_clo_intro_h2'       => 'Cherry, une cerise lumineuse sur un fond doux de vanille et de musc',
	'_clo_lead'           => "Cherry Collection Privée L'Original associe une ouverture juteuse et légèrement acidulée à un cœur floral délicatement amandé, avant de se prolonger sur un fond doux de vanille et de musc.",
	'_clo_intro_p2'       => "Une fragrance gourmande, tendre et facile à porter, pensée pour accompagner aussi bien le quotidien que les moments plus habillés.",

	'_clo_fact_1_label'   => 'Style olfactif',
	'_clo_fact_1_value'   => 'Fruité · Floral · Gourmand',
	'_clo_fact_2_label'   => 'Profil',
	'_clo_fact_2_value'   => 'Fragrance féminine',
	'_clo_fact_3_label'   => 'Format',
	'_clo_fact_3_value'   => 'Eau de parfum · 50 ml',
	'_clo_fact_4_label'   => '',
	'_clo_fact_4_value'   => '',

	'_clo_image_1_id'      => 0,
	'_clo_image_1_alt'     => "Cherry Collection Privée L'Original, eau de parfum fruité floral 50 ml",
	'_clo_image_1_caption' => 'Cerise · Floral · Amande · Vanille · Musc',

	'_clo_notes_kicker'   => 'Composition',
	'_clo_notes_h2'       => 'La pyramide olfactive de Cherry',
	'_clo_notes_intro'    => "Cherry se révèle en trois temps : une ouverture fruitée et lumineuse, un cœur floral amandé, puis une base douce et enveloppante.",

	'_clo_note_1_index'   => "01 · L'envol",
	'_clo_note_1_h3'      => 'Notes de tête',
	'_clo_note_1_text'    => "Cerise juteuse et légèrement acidulée. Une ouverture fruitée, lumineuse et immédiatement joyeuse.",
	'_clo_note_2_index'   => '02 · Le cœur',
	'_clo_note_2_h3'      => 'Notes de cœur',
	'_clo_note_2_text'    => "Accords floraux subtils et nuances amandées. Une facette douce, élégante et légèrement crémeuse.",
	'_clo_note_3_index'   => '03 · Le sillage',
	'_clo_note_3_h3'      => 'Notes de fond',
	'_clo_note_3_text'    => "Vanille et musc. Un fond soyeux, tendre et délicatement sucré qui prolonge naturellement la composition.",

	'_clo_apply_kicker'   => 'Le rituel',
	'_clo_apply_h2'       => 'Comment porter Cherry ?',
	'_clo_apply_intro'    => "Quelques vaporisations sur les points de pulsation suffisent pour profiter pleinement de son sillage tendre et gourmand.",

	'_clo_step_1_strong'  => 'Sur les points de pulsation',
	'_clo_step_1_span'    => "Cou, poignets, derrière les oreilles ou pli des coudes pour une diffusion progressive.",
	'_clo_step_2_strong'  => 'Sur une peau hydratée',
	'_clo_step_2_span'    => "Appliquez de préférence après un soin neutre pour améliorer la tenue de la fragrance.",
	'_clo_step_3_strong'  => 'Sans frotter',
	'_clo_step_3_span'    => "Laissez sécher naturellement pour préserver le développement des notes.",
	'_clo_step_4_strong'  => '',
	'_clo_step_4_span'    => '',
	'_clo_step_5_strong'  => '',
	'_clo_step_5_span'    => '',

	'_clo_tip_label'      => 'Conseil :',
	'_clo_tip_text'       => "l'application peut être renouvelée selon l'intensité recherchée et le moment de la journée.",

	'_clo_image_2_id'     => 0,
	'_clo_image_2_alt'    => "Application de Cherry Collection Privée L'Original sur les points de pulsation",

	'_clo_night_kicker'   => 'Saisons & occasions',
	'_clo_night_h2'       => 'Quand porter Cherry ?',
	'_clo_night_p'        => "Grâce à sa composition équilibrée, Cherry s'adapte à toutes les saisons et à de nombreuses occasions.",

	'_clo_season_1_label' => 'Printemps · Été',
	'_clo_season_1_text'  => "La cerise juteuse et légèrement acidulée apporte fraîcheur et luminosité.",
	'_clo_season_2_label' => 'Automne · Hiver',
	'_clo_season_2_text'  => "Les nuances amandées, vanillées et musquées révèlent une facette plus douce et réconfortante.",
	'_clo_season_3_label' => 'Quotidien',
	'_clo_season_3_text'  => "La légèreté de Cherry en fait une eau de parfum agréable pour une utilisation régulière.",
	'_clo_season_4_label' => 'Soirée',
	'_clo_season_4_text'  => "Un sillage délicatement sucré, tendre et durable qui convient aussi aux sorties.",

	'_clo_image_3_id'      => 0,
	'_clo_image_3_alt'     => "Cherry Collection Privée L'Original, parfum fruité à porter toutes saisons",
	'_clo_image_3_caption' => 'Une signature fruitée, douce et addictive',

	'_clo_guide_kicker'   => 'Conseils de conservation',
	'_clo_guide_h2'       => 'Comment prolonger son sillage ?',
	'_clo_guide_intro'    => "Ces gestes simples permettent de profiter pleinement de l'évolution de Cherry.",
	'_clo_guide_1_icon'   => '01',
	'_clo_guide_1_h3'     => 'Hydrater la peau',
	'_clo_guide_1_text'   => "Appliquez un soin neutre avant la vaporisation pour limiter l'évaporation sur peau sèche.",
	'_clo_guide_2_icon'   => '02',
	'_clo_guide_2_h3'     => 'Cibler les zones chaudes',
	'_clo_guide_2_text'   => "Cou, poignets et plis des coudes diffusent la fragrance au fil des mouvements.",
	'_clo_guide_3_icon'   => '03',
	'_clo_guide_3_h3'     => 'Parfumer un vêtement',
	'_clo_guide_3_text'   => "Vaporisez à 20–30 cm après un test sur une zone cachée, surtout sur les textiles délicats.",
	'_clo_guide_4_icon'   => '04',
	'_clo_guide_4_h3'     => 'Protéger le flacon',
	'_clo_guide_4_text'   => "Conservez-le debout, à l'abri du soleil, de la chaleur et de l'humidité.",

	'_clo_forwho_kicker'  => 'Votre profil olfactif',
	'_clo_forwho_h2'      => 'Cherry est-il fait pour vous ?',
	'_clo_forwho_p'       => "Cherry séduira particulièrement les personnes qui apprécient les parfums fruités, gourmands et faciles à porter.",
	'_clo_check_1'        => "Vous aimez la cerise juteuse et lumineuse.",
	'_clo_check_2'        => "Vous appréciez les notes sucrées légèrement acidulées.",
	'_clo_check_3'        => "Vous recherchez des accords floraux délicats.",
	'_clo_check_4'        => "Vous aimez les nuances amandées et les fonds vanillés et musqués.",
	'_clo_check_5'        => "Vous souhaitez un sillage doux et élégant, polyvalent et facile à porter.",
	'_clo_check_6'        => '',
	'_clo_honest_label'   => 'À savoir avant de choisir :',
	'_clo_honest_text'    => "si vous recherchez un parfum très boisé, épicé ou exclusivement oriental, la légèreté fruitée et la douceur de Cherry pourront sembler moins affirmées.",

	'_clo_faq_kicker'     => 'Questions fréquentes',
	'_clo_faq_h2'         => "Tout savoir sur Cherry Collection Privée L'Original",
	'_clo_faq_intro'      => "Des réponses précises pour choisir, appliquer et conserver votre eau de parfum Cherry 50 ml.",
	'_clo_faq_1_q'        => "À quoi sent Cherry Collection Privée L'Original ?",
	'_clo_faq_1_a'        => "Cherry s'ouvre sur une cerise juteuse et légèrement acidulée, puis dévoile un cœur floral aux nuances amandées. La vanille et le musc composent un fond soyeux, tendre et délicatement sucré.",
	'_clo_faq_2_q'        => "Cherry est-il adapté à toutes les saisons ?",
	'_clo_faq_2_a'        => "Oui. Au printemps et en été, la cerise apporte fraîcheur et luminosité. En automne et en hiver, les accords amandés, vanillés et musqués révèlent une facette plus douce et réconfortante.",
	'_clo_faq_3_q'        => "Combien de vaporisations faut-il appliquer ?",
	'_clo_faq_3_a'        => "Quelques vaporisations suffisent. L'application peut être renouvelée selon l'intensité souhaitée et le moment de la journée.",
	'_clo_faq_4_q'        => "Comment faire tenir Cherry plus longtemps ?",
	'_clo_faq_4_a'        => "Appliquez-le sur une peau propre et hydratée, ciblez les points de pulsation et évitez de frotter les poignets après la vaporisation.",
	'_clo_faq_5_q'        => "Cherry convient-il comme idée cadeau ?",
	'_clo_faq_5_a'        => "Oui. Sa signature douce et gourmande peut séduire les amateurs de parfums fruités comme les personnes qui recherchent une fragrance tendre et facile à porter. Son format 50 ml convient pour découvrir la composition ou l'utiliser régulièrement.",
	'_clo_faq_6_q'        => '',
	'_clo_faq_6_a'        => '',
	'_clo_faq_7_q'        => '',
	'_clo_faq_7_a'        => '',
	'_clo_faq_8_q'        => '',
	'_clo_faq_8_a'        => '',

	'_clo_disc_h2'        => "Prolongez l'expérience Collection Privée L'Original",
	'_clo_disc_p'         => "Découvrez d'autres signatures de la collection ou composez une sélection de plusieurs fragrances.",
	'_clo_link_1_text'    => "Voir la Collection Privée L'Original",
	'_clo_link_1_href'    => 'https://www.collectionloriginal.com/product-category/collection-privee-loriginal/',
	'_clo_link_2_text'    => 'Découvrir le lot 3 Best Seller',
	'_clo_link_2_href'    => 'https://www.collectionloriginal.com/product/lot-3-best-seller/',
	'_clo_disclaimer'     => '',
];

// ─────────────────────────────────────────────────────────────────────────────
// VIE — ID 191
// Content adapted from the existing product description (no invented facts)
// ─────────────────────────────────────────────────────────────────────────────

// Read Vie's existing description to populate fields
$vie_desc_raw = get_post_field( 'post_content', 191 );

$pilots[191] = [
	'_clo_enabled'        => '1',
	'_clo_kicker'         => "Collection Privée L'Original · Eau de parfum 50 ml",
	'_clo_intro_h2'       => 'Vie, une lumière fruitée sur un fond chaleureux de vanille',
	'_clo_lead'           => "Vie Collection Privée L'Original célèbre la joie et la douceur au quotidien. Sa fraîcheur fruitée rencontre un cœur floral délicat, avant de se prolonger sur un fond chaleureux de vanille et de notes sucrées.",
	'_clo_intro_p2'       => "Une eau de parfum lumineuse et gourmande, pensée pour celles qui recherchent un parfum facile à vivre et naturellement séduisant.",

	'_clo_fact_1_label'   => 'Style olfactif',
	'_clo_fact_1_value'   => 'Fruité · Floral · Gourmand',
	'_clo_fact_2_label'   => 'Profil',
	'_clo_fact_2_value'   => 'Fragrance féminine',
	'_clo_fact_3_label'   => 'Format',
	'_clo_fact_3_value'   => 'Eau de parfum · 50 ml',
	'_clo_fact_4_label'   => '',
	'_clo_fact_4_value'   => '',

	'_clo_image_1_id'      => 0,
	'_clo_image_1_alt'     => "Vie Collection Privée L'Original, eau de parfum féminin fruité floral 50 ml",
	'_clo_image_1_caption' => 'Fruité · Floral · Vanille · Notes sucrées',

	'_clo_notes_kicker'   => 'Composition',
	'_clo_notes_h2'       => 'La pyramide olfactive de Vie',
	'_clo_notes_intro'    => "Vie se révèle en trois temps : une ouverture fruitée et lumineuse, un cœur floral délicat, puis une base chaude et gourmande.",

	'_clo_note_1_index'   => "01 · L'envol",
	'_clo_note_1_h3'      => 'Notes de tête',
	'_clo_note_1_text'    => "Fraîcheur fruitée et lumineuse. Une ouverture vive qui apporte joie et légèreté dès les premiers instants.",
	'_clo_note_2_index'   => '02 · Le cœur',
	'_clo_note_2_h3'      => 'Notes de cœur',
	'_clo_note_2_text'    => "Cœur floral délicat. La composition gagne en élégance et en féminité avec une floralité douce et raffinée.",
	'_clo_note_3_index'   => '03 · Le sillage',
	'_clo_note_3_h3'      => 'Notes de fond',
	'_clo_note_3_text'    => "Vanille et notes sucrées. Un fond chaleureux et enveloppant qui prolonge la composition avec douceur.",

	'_clo_apply_kicker'   => 'Le rituel',
	'_clo_apply_h2'       => 'Comment porter Vie ?',
	'_clo_apply_intro'    => "Quelques vaporisations sur les points de pulsation suffisent pour révéler toute la lumière de Vie.",

	'_clo_step_1_strong'  => 'Sur les points de pulsation',
	'_clo_step_1_span'    => "Cou, poignets, derrière les oreilles ou pli des coudes pour une diffusion progressive.",
	'_clo_step_2_strong'  => 'Sur une peau hydratée',
	'_clo_step_2_span'    => "Appliquez de préférence après un soin neutre pour une meilleure tenue.",
	'_clo_step_3_strong'  => 'Sans frotter',
	'_clo_step_3_span'    => "Laissez sécher naturellement pour préserver l'évolution des notes.",
	'_clo_step_4_strong'  => '',
	'_clo_step_4_span'    => '',
	'_clo_step_5_strong'  => '',
	'_clo_step_5_span'    => '',

	'_clo_tip_label'      => 'Conseil :',
	'_clo_tip_text'       => "renouvelez l'application selon l'intensité recherchée et le moment de la journée.",

	'_clo_image_2_id'     => 0,
	'_clo_image_2_alt'    => "Application de l'eau de parfum Vie Collection Privée L'Original",

	'_clo_night_kicker'   => 'Saisons & occasions',
	'_clo_night_h2'       => 'Quand porter Vie ?',
	'_clo_night_p'        => "Sa composition équilibrée entre fraîcheur fruitée et douceur vanillée lui permet de s'adapter à toutes les saisons et de nombreuses occasions.",

	'_clo_season_1_label' => 'Printemps · Été',
	'_clo_season_1_text'  => "La fraîcheur fruitée et le cœur floral s'expriment avec légèreté et luminosité.",
	'_clo_season_2_label' => 'Automne · Hiver',
	'_clo_season_2_text'  => "La vanille et les notes sucrées révèlent une facette plus douce et réconfortante.",
	'_clo_season_3_label' => 'Quotidien',
	'_clo_season_3_text'  => "Légère et accessible, Vie accompagne agréablement le quotidien.",
	'_clo_season_4_label' => 'Soirée',
	'_clo_season_4_text'  => "Son sillage chaleureux et gourmand convient aussi aux sorties et moments habillés.",

	'_clo_image_3_id'      => 0,
	'_clo_image_3_alt'     => "Vie Collection Privée L'Original, parfum féminin lumineux et gourmand",
	'_clo_image_3_caption' => 'Une fragrance lumineuse, douce et féminine',

	'_clo_guide_kicker'   => 'Conseils de conservation',
	'_clo_guide_h2'       => 'Comment prolonger son sillage ?',
	'_clo_guide_intro'    => "Ces gestes simples permettent de profiter pleinement de l'évolution de Vie.",
	'_clo_guide_1_icon'   => '01',
	'_clo_guide_1_h3'     => 'Hydrater la peau',
	'_clo_guide_1_text'   => "Appliquez un soin neutre avant la vaporisation pour limiter l'évaporation sur peau sèche.",
	'_clo_guide_2_icon'   => '02',
	'_clo_guide_2_h3'     => 'Cibler les zones chaudes',
	'_clo_guide_2_text'   => "Cou, poignets et plis des coudes diffusent la fragrance au fil des mouvements.",
	'_clo_guide_3_icon'   => '03',
	'_clo_guide_3_h3'     => 'Parfumer un vêtement',
	'_clo_guide_3_text'   => "Vaporisez à 20–30 cm après un test sur une zone cachée, surtout sur les textiles délicats.",
	'_clo_guide_4_icon'   => '04',
	'_clo_guide_4_h3'     => 'Protéger le flacon',
	'_clo_guide_4_text'   => "Conservez-le debout, à l'abri du soleil, de la chaleur et de l'humidité.",

	'_clo_forwho_kicker'  => 'Votre profil olfactif',
	'_clo_forwho_h2'      => 'Vie est-il fait pour vous ?',
	'_clo_forwho_p'       => "Vie séduira les personnes qui aiment les parfums lumineux, gourmands et naturellement féminins.",
	'_clo_check_1'        => "Vous aimez les parfums fruités, légers et lumineux.",
	'_clo_check_2'        => "Vous appréciez les cœurs floraux délicats et raffinés.",
	'_clo_check_3'        => "Vous aimez les fonds de vanille et les notes sucrées douces.",
	'_clo_check_4'        => "Vous cherchez un parfum facile à porter au quotidien comme en soirée.",
	'_clo_check_5'        => '',
	'_clo_check_6'        => '',
	'_clo_honest_label'   => 'À savoir avant de choisir :',
	'_clo_honest_text'    => "si vous préférez les parfums très épicés, boisés ou intensément orientaux, la douceur lumineuse de Vie pourra sembler moins affirmée.",

	'_clo_faq_kicker'     => 'Questions fréquentes',
	'_clo_faq_h2'         => "Tout savoir sur Vie Collection Privée L'Original",
	'_clo_faq_intro'      => "Des réponses précises pour choisir, appliquer et conserver votre eau de parfum Vie 50 ml.",
	'_clo_faq_1_q'        => "À quoi sent Vie Collection Privée L'Original ?",
	'_clo_faq_1_a'        => "Vie s'ouvre sur une fraîcheur fruitée et lumineuse, puis dévoile un cœur floral délicat et raffiné. La vanille et les notes sucrées composent un fond chaleureux et enveloppant.",
	'_clo_faq_2_q'        => "Vie est-il adapté à toutes les saisons ?",
	'_clo_faq_2_a'        => "Oui. Au printemps et en été, sa fraîcheur fruitée apporte légèreté et luminosité. En automne et en hiver, la vanille et les notes sucrées révèlent une facette plus douce et réconfortante.",
	'_clo_faq_3_q'        => "Combien de vaporisations faut-il appliquer ?",
	'_clo_faq_3_a'        => "Quelques vaporisations sur les points de pulsation suffisent. L'application peut être renouvelée selon l'intensité souhaitée.",
	'_clo_faq_4_q'        => "Comment faire tenir Vie plus longtemps ?",
	'_clo_faq_4_a'        => "Appliquez sur une peau propre et hydratée, ciblez les points de pulsation et évitez de frotter les poignets après la vaporisation.",
	'_clo_faq_5_q'        => "Vie convient-il comme idée cadeau ?",
	'_clo_faq_5_a'        => "Oui. Sa signature lumineuse, douce et féminine en fait un choix idéal pour une personne qui aime les parfums fruités et floraux gourmands. Son format 50 ml convient pour découvrir la composition ou l'offrir.",
	'_clo_faq_6_q'        => '',
	'_clo_faq_6_a'        => '',
	'_clo_faq_7_q'        => '',
	'_clo_faq_7_a'        => '',
	'_clo_faq_8_q'        => '',
	'_clo_faq_8_a'        => '',

	'_clo_disc_h2'        => "Prolongez l'expérience Collection Privée L'Original",
	'_clo_disc_p'         => "Découvrez d'autres signatures de la collection ou composez une sélection de plusieurs fragrances.",
	'_clo_link_1_text'    => "Voir la Collection Privée L'Original",
	'_clo_link_1_href'    => 'https://www.collectionloriginal.com/product-category/collection-privee-loriginal/',
	'_clo_link_2_text'    => 'Découvrir le lot 3 Best Seller',
	'_clo_link_2_href'    => 'https://www.collectionloriginal.com/product/lot-3-best-seller/',
	'_clo_disclaimer'     => '',
];

// ─────────────────────────────────────────────────────────────────────────────
// Import loop
// ─────────────────────────────────────────────────────────────────────────────
foreach ( $pilots as $product_id => $fields ) {
	$post = get_post( $product_id );
	if ( ! $post ) {
		WP_CLI::warning( "Product ID {$product_id} not found — skipping." );
		continue;
	}

	// Back up the existing description only once
	$existing_backup = get_post_meta( $product_id, '_clo_desc_backup', true );
	if ( ! $existing_backup ) {
		update_post_meta( $product_id, '_clo_desc_backup', $post->post_content );
		WP_CLI::log( "[{$product_id}] Description backed up to _clo_desc_backup." );
	} else {
		WP_CLI::log( "[{$product_id}] _clo_desc_backup already exists, not overwriting." );
	}

	// Write all CLO meta fields
	foreach ( $fields as $meta_key => $meta_value ) {
		update_post_meta( $product_id, $meta_key, $meta_value );
	}

	WP_CLI::success( "[{$product_id}] {$post->post_title} — " . count( $fields ) . " meta fields written, _clo_enabled = '{$fields['_clo_enabled']}'." );
}

WP_CLI::success( "Import complete. " . count( $pilots ) . " pilot products processed." );
WP_CLI::log( "NOTE: Images have ID=0 — assign them via the admin meta box (product editor > Description CLO)." );
