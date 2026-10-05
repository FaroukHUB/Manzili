<?php
/**
 * CLO Product Meta Box
 *
 * Admin UI for the CLO product description template fields.
 * Adds a meta box to the WooCommerce product editor.
 *
 * @package Manzili
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'clo_product_description',
		'Description CLO — Collection L\'Original',
		'clo_meta_box_render',
		'product',
		'normal',
		'default'
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	global $post;
	if ( ! $post || 'product' !== $post->post_type ) {
		return;
	}
	wp_enqueue_media();
	$js_path = get_stylesheet_directory() . '/assets/js/clo-admin.js';
	wp_enqueue_script(
		'clo-admin',
		get_stylesheet_directory_uri() . '/assets/js/clo-admin.js',
		array( 'jquery' ),
		(string) filemtime( $js_path ),
		true
	);
	wp_add_inline_style( 'wp-admin', '
		.clo-meta-section { border:1px solid #ddd; border-radius:4px; margin-bottom:10px; }
		.clo-meta-section > summary { padding:8px 12px; cursor:pointer; font-weight:600; background:#f9f9f9; }
		.clo-meta-section > summary:hover { background:#f0f0f0; }
		.clo-meta-section .inside { padding:12px 14px; }
		.clo-meta-row { display:flex; gap:10px; margin-bottom:8px; flex-wrap:wrap; }
		.clo-meta-row label { min-width:160px; font-weight:500; padding-top:4px; }
		.clo-meta-row input[type=text], .clo-meta-row textarea { flex:1; min-width:200px; }
		.clo-meta-row textarea { height:60px; }
		.clo-meta-row .wide { width:100%; max-width:none; }
		.clo-img-picker { display:flex; align-items:flex-start; gap:10px; }
		.clo-img-preview { max-width:80px; max-height:80px; object-fit:cover; border:1px solid #ccc; display:none; }
		.clo-group-title { font-size:12px; font-weight:600; color:#666; text-transform:uppercase; letter-spacing:.5px; margin:10px 0 4px; border-bottom:1px solid #eee; padding-bottom:4px; }
	' );
} );

add_action( 'save_post_product', function ( $post_id ) {
	if ( ! isset( $_POST['clo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['clo_nonce'] ) ), 'clo_save_' . $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Checkbox: always set explicitly (absent when unchecked)
	update_post_meta( $post_id, '_clo_enabled', isset( $_POST['clo_enabled'] ) ? '1' : '' );

	$text_fields = array(
		'_clo_kicker', '_clo_intro_h2', '_clo_lead', '_clo_intro_p2',
		'_clo_fact_1_label', '_clo_fact_1_value', '_clo_fact_2_label', '_clo_fact_2_value',
		'_clo_fact_3_label', '_clo_fact_3_value', '_clo_fact_4_label', '_clo_fact_4_value',
		'_clo_image_1_alt', '_clo_image_1_caption',
		'_clo_notes_kicker', '_clo_notes_h2', '_clo_notes_intro',
		'_clo_note_1_index', '_clo_note_1_h3', '_clo_note_1_text',
		'_clo_note_2_index', '_clo_note_2_h3', '_clo_note_2_text',
		'_clo_note_3_index', '_clo_note_3_h3', '_clo_note_3_text',
		'_clo_apply_kicker', '_clo_apply_h2', '_clo_apply_intro',
		'_clo_step_1_strong', '_clo_step_1_span', '_clo_step_2_strong', '_clo_step_2_span',
		'_clo_step_3_strong', '_clo_step_3_span', '_clo_step_4_strong', '_clo_step_4_span',
		'_clo_step_5_strong', '_clo_step_5_span',
		'_clo_tip_label', '_clo_tip_text',
		'_clo_image_2_alt',
		'_clo_night_kicker', '_clo_night_h2', '_clo_night_p',
		'_clo_season_1_label', '_clo_season_1_text', '_clo_season_2_label', '_clo_season_2_text',
		'_clo_season_3_label', '_clo_season_3_text', '_clo_season_4_label', '_clo_season_4_text',
		'_clo_image_3_alt', '_clo_image_3_caption',
		'_clo_guide_kicker', '_clo_guide_h2', '_clo_guide_intro',
		'_clo_guide_1_icon', '_clo_guide_1_h3', '_clo_guide_1_text',
		'_clo_guide_2_icon', '_clo_guide_2_h3', '_clo_guide_2_text',
		'_clo_guide_3_icon', '_clo_guide_3_h3', '_clo_guide_3_text',
		'_clo_guide_4_icon', '_clo_guide_4_h3', '_clo_guide_4_text',
		'_clo_forwho_kicker', '_clo_forwho_h2', '_clo_forwho_p',
		'_clo_check_1', '_clo_check_2', '_clo_check_3',
		'_clo_check_4', '_clo_check_5', '_clo_check_6',
		'_clo_honest_label', '_clo_honest_text',
		'_clo_faq_kicker', '_clo_faq_h2', '_clo_faq_intro',
		'_clo_faq_1_q', '_clo_faq_1_a', '_clo_faq_2_q', '_clo_faq_2_a',
		'_clo_faq_3_q', '_clo_faq_3_a', '_clo_faq_4_q', '_clo_faq_4_a',
		'_clo_faq_5_q', '_clo_faq_5_a', '_clo_faq_6_q', '_clo_faq_6_a',
		'_clo_faq_7_q', '_clo_faq_7_a', '_clo_faq_8_q', '_clo_faq_8_a',
		'_clo_disc_h2', '_clo_disc_p',
		'_clo_link_1_text', '_clo_link_1_href', '_clo_link_2_text', '_clo_link_2_href',
		'_clo_disclaimer',
	);

	$int_fields = array( '_clo_image_1_id', '_clo_image_2_id', '_clo_image_3_id', '_clo_video_id' );

	foreach ( $text_fields as $field ) {
		$key = ltrim( $field, '_' );
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $field, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	foreach ( $int_fields as $field ) {
		$key = ltrim( $field, '_' );
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $field, (int) $_POST[ $key ] );
		}
	}
} );

function clo_meta_box_render( WP_Post $post ): void {
	$id = $post->ID;
	wp_nonce_field( 'clo_save_' . $id, 'clo_nonce' );

	$g = function ( string $key ) use ( $id ): string {
		return (string) get_post_meta( $id, $key, true );
	};
	$gi = function ( string $key ) use ( $id ): int {
		return (int) get_post_meta( $id, $key, true );
	};

	$enabled   = $g( '_clo_enabled' );
	$img1_id   = $gi( '_clo_image_1_id' );
	$img2_id   = $gi( '_clo_image_2_id' );
	$img3_id   = $gi( '_clo_image_3_id' );
	$video_id  = $gi( '_clo_video_id' );
	$img1_url  = $img1_id ? wp_get_attachment_image_url( $img1_id, 'thumbnail' ) : '';
	$img2_url  = $img2_id ? wp_get_attachment_image_url( $img2_id, 'thumbnail' ) : '';
	$img3_url  = $img3_id ? wp_get_attachment_image_url( $img3_id, 'thumbnail' ) : '';
	$video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';

	?>
	<p>
		<label style="font-weight:600;">
			<input type="checkbox" name="clo_enabled" value="1" <?php checked( '1', $enabled ); ?>>
			Activer le template CLO pour ce produit
		</label>
		<span style="color:#666;font-size:12px;display:block;margin-top:4px;">
			Quand cette case est cochée, la description du produit est rendue via le template CLO. Laissez décoché pour conserver la description WooCommerce actuelle.
		</span>
	</p>

	<details class="clo-meta-section" open>
		<summary>Introduction</summary>
		<div class="inside">
			<?php clo_field_row( 'Kicker', '_clo_kicker', $g( '_clo_kicker' ) ); ?>
			<?php clo_field_row( 'Titre H2', '_clo_intro_h2', $g( '_clo_intro_h2' ) ); ?>
			<?php clo_field_row( 'Accroche (lead)', '_clo_lead', $g( '_clo_lead' ), 'textarea' ); ?>
			<?php clo_field_row( 'Paragraphe 2', '_clo_intro_p2', $g( '_clo_intro_p2' ), 'textarea' ); ?>

			<p class="clo-group-title">Caractéristiques (4 max)</p>
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<div class="clo-meta-row">
					<label>Fait <?php echo esc_html( (string) $i ); ?></label>
					<input type="text" name="_clo_fact_<?php echo esc_attr( (string) $i ); ?>_label"
						value="<?php echo esc_attr( $g( "_clo_fact_{$i}_label" ) ); ?>"
						placeholder="Étiquette" style="flex:1;">
					<input type="text" name="_clo_fact_<?php echo esc_attr( (string) $i ); ?>_value"
						value="<?php echo esc_attr( $g( "_clo_fact_{$i}_value" ) ); ?>"
						placeholder="Valeur" style="flex:1;">
				</div>
			<?php endfor; ?>

			<p class="clo-group-title">Image 1 (ambiance intro)</p>
			<?php clo_image_picker_row( 1, $img1_id, $img1_url, $g( '_clo_image_1_alt' ), $g( '_clo_image_1_caption' ), true ); ?>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>Pyramide olfactive</summary>
		<div class="inside">
			<?php clo_field_row( 'Kicker', '_clo_notes_kicker', $g( '_clo_notes_kicker' ) ); ?>
			<?php clo_field_row( 'Titre H2', '_clo_notes_h2', $g( '_clo_notes_h2' ) ); ?>
			<?php clo_field_row( 'Intro', '_clo_notes_intro', $g( '_clo_notes_intro' ), 'textarea' ); ?>

			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<p class="clo-group-title">Note <?php echo esc_html( (string) $i ); ?></p>
				<?php clo_field_row( 'Index', "_clo_note_{$i}_index", $g( "_clo_note_{$i}_index" ) ); ?>
				<?php clo_field_row( 'H3', "_clo_note_{$i}_h3", $g( "_clo_note_{$i}_h3" ) ); ?>
				<?php clo_field_row( 'Texte', "_clo_note_{$i}_text", $g( "_clo_note_{$i}_text" ), 'textarea' ); ?>
			<?php endfor; ?>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>Application (section split)</summary>
		<div class="inside">
			<?php clo_field_row( 'Kicker', '_clo_apply_kicker', $g( '_clo_apply_kicker' ) ); ?>
			<?php clo_field_row( 'Titre H2', '_clo_apply_h2', $g( '_clo_apply_h2' ) ); ?>
			<?php clo_field_row( 'Intro', '_clo_apply_intro', $g( '_clo_apply_intro' ), 'textarea' ); ?>

			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<div class="clo-meta-row">
					<label>Étape <?php echo esc_html( (string) $i ); ?></label>
					<input type="text" name="_clo_step_<?php echo esc_attr( (string) $i ); ?>_strong"
						value="<?php echo esc_attr( $g( "_clo_step_{$i}_strong" ) ); ?>"
						placeholder="Titre étape" style="flex:1;">
					<input type="text" name="_clo_step_<?php echo esc_attr( (string) $i ); ?>_span"
						value="<?php echo esc_attr( $g( "_clo_step_{$i}_span" ) ); ?>"
						placeholder="Description étape" style="flex:2;">
				</div>
			<?php endfor; ?>

			<div class="clo-meta-row">
				<label>Astuce — Label</label>
				<input type="text" name="_clo_tip_label" value="<?php echo esc_attr( $g( '_clo_tip_label' ) ); ?>" style="flex:1;">
			</div>
			<div class="clo-meta-row">
				<label>Astuce — Texte</label>
				<textarea name="_clo_tip_text" style="flex:1;"><?php echo esc_textarea( $g( '_clo_tip_text' ) ); ?></textarea>
			</div>

			<p class="clo-group-title">Image 2 (geste application)</p>
			<?php clo_image_picker_row( 2, $img2_id, $img2_url, $g( '_clo_image_2_alt' ), '', false ); ?>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>Saisons &amp; occasions</summary>
		<div class="inside">
			<?php clo_field_row( 'Kicker', '_clo_night_kicker', $g( '_clo_night_kicker' ) ); ?>
			<?php clo_field_row( 'Titre H2', '_clo_night_h2', $g( '_clo_night_h2' ) ); ?>
			<?php clo_field_row( 'Paragraphe', '_clo_night_p', $g( '_clo_night_p' ), 'textarea' ); ?>

			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<div class="clo-meta-row">
					<label>Saison <?php echo esc_html( (string) $i ); ?></label>
					<input type="text" name="_clo_season_<?php echo esc_attr( (string) $i ); ?>_label"
						value="<?php echo esc_attr( $g( "_clo_season_{$i}_label" ) ); ?>"
						placeholder="Étiquette saison" style="flex:1;">
					<input type="text" name="_clo_season_<?php echo esc_attr( (string) $i ); ?>_text"
						value="<?php echo esc_attr( $g( "_clo_season_{$i}_text" ) ); ?>"
						placeholder="Texte saison" style="flex:2;">
				</div>
			<?php endfor; ?>

			<p class="clo-group-title">Image 3 (ambiance soirée)</p>
			<?php clo_image_picker_row( 3, $img3_id, $img3_url, $g( '_clo_image_3_alt' ), $g( '_clo_image_3_caption' ), true ); ?>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>Vidéo popup (popup à l'entrée de la fiche)</summary>
		<div class="inside">
			<p style="color:#666;font-size:12px;margin-bottom:10px;">Vidéo uploadée dans la médiathèque WordPress. Elle s'affiche automatiquement en popup quand le visiteur arrive sur la fiche (une seule fois par session).</p>
			<input type="hidden" id="clo_video_id" name="clo_video_id" value="<?php echo esc_attr( (string) $video_id ); ?>">
			<div class="clo-img-picker">
				<?php if ( $video_url ) : ?>
					<video src="<?php echo esc_url( $video_url ); ?>" style="max-width:120px;max-height:80px;border:1px solid #ccc;" controls muted></video>
				<?php endif; ?>
				<div>
					<button type="button" id="clo-video-pick" class="button" data-title="Choisir une vidéo">
						<?php echo $video_url ? 'Changer la vidéo' : 'Choisir une vidéo'; ?>
					</button>
					<button type="button" id="clo-video-remove" class="button" style="<?php echo $video_url ? '' : 'display:none;'; ?>">
						Supprimer
					</button>
				</div>
			</div>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>Guide de conservation</summary>
		<div class="inside">
			<?php clo_field_row( 'Kicker', '_clo_guide_kicker', $g( '_clo_guide_kicker' ) ); ?>
			<?php clo_field_row( 'Titre H2', '_clo_guide_h2', $g( '_clo_guide_h2' ) ); ?>
			<?php clo_field_row( 'Intro', '_clo_guide_intro', $g( '_clo_guide_intro' ), 'textarea' ); ?>

			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<p class="clo-group-title">Carte <?php echo esc_html( (string) $i ); ?></p>
				<?php clo_field_row( 'Icône', "_clo_guide_{$i}_icon", $g( "_clo_guide_{$i}_icon" ) ); ?>
				<?php clo_field_row( 'H3', "_clo_guide_{$i}_h3", $g( "_clo_guide_{$i}_h3" ) ); ?>
				<?php clo_field_row( 'Texte', "_clo_guide_{$i}_text", $g( "_clo_guide_{$i}_text" ), 'textarea' ); ?>
			<?php endfor; ?>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>Profil olfactif — Pour qui ?</summary>
		<div class="inside">
			<?php clo_field_row( 'Kicker', '_clo_forwho_kicker', $g( '_clo_forwho_kicker' ) ); ?>
			<?php clo_field_row( 'Titre H2', '_clo_forwho_h2', $g( '_clo_forwho_h2' ) ); ?>
			<?php clo_field_row( 'Paragraphe', '_clo_forwho_p', $g( '_clo_forwho_p' ), 'textarea' ); ?>

			<p class="clo-group-title">Checklist (6 items max)</p>
			<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
				<?php clo_field_row( "Item {$i}", "_clo_check_{$i}", $g( "_clo_check_{$i}" ) ); ?>
			<?php endfor; ?>

			<?php clo_field_row( 'À savoir — Label', '_clo_honest_label', $g( '_clo_honest_label' ) ); ?>
			<?php clo_field_row( 'À savoir — Texte', '_clo_honest_text', $g( '_clo_honest_text' ), 'textarea' ); ?>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>FAQ (8 questions max)</summary>
		<div class="inside">
			<?php clo_field_row( 'Kicker', '_clo_faq_kicker', $g( '_clo_faq_kicker' ) ); ?>
			<?php clo_field_row( 'Titre H2', '_clo_faq_h2', $g( '_clo_faq_h2' ) ); ?>
			<?php clo_field_row( 'Intro', '_clo_faq_intro', $g( '_clo_faq_intro' ), 'textarea' ); ?>

			<?php for ( $i = 1; $i <= 8; $i++ ) : ?>
				<p class="clo-group-title">Q<?php echo esc_html( (string) $i ); ?></p>
				<?php clo_field_row( 'Question', "_clo_faq_{$i}_q", $g( "_clo_faq_{$i}_q" ) ); ?>
				<?php clo_field_row( 'Réponse', "_clo_faq_{$i}_a", $g( "_clo_faq_{$i}_a" ), 'textarea' ); ?>
			<?php endfor; ?>
		</div>
	</details>

	<details class="clo-meta-section">
		<summary>Découvrir la collection</summary>
		<div class="inside">
			<?php clo_field_row( 'Titre H2', '_clo_disc_h2', $g( '_clo_disc_h2' ) ); ?>
			<?php clo_field_row( 'Paragraphe', '_clo_disc_p', $g( '_clo_disc_p' ), 'textarea' ); ?>

			<p class="clo-group-title">Liens (2 max)</p>
			<?php for ( $i = 1; $i <= 2; $i++ ) : ?>
				<div class="clo-meta-row">
					<label>Lien <?php echo esc_html( (string) $i ); ?></label>
					<input type="text" name="_clo_link_<?php echo esc_attr( (string) $i ); ?>_text"
						value="<?php echo esc_attr( $g( "_clo_link_{$i}_text" ) ); ?>"
						placeholder="Texte du lien" style="flex:1;">
					<input type="text" name="_clo_link_<?php echo esc_attr( (string) $i ); ?>_href"
						value="<?php echo esc_attr( $g( "_clo_link_{$i}_href" ) ); ?>"
						placeholder="URL" style="flex:2;">
				</div>
			<?php endfor; ?>

			<?php clo_field_row( 'Mention légale', '_clo_disclaimer', $g( '_clo_disclaimer' ), 'textarea' ); ?>
		</div>
	</details>
	<?php
}

function clo_field_row( string $label, string $name, string $value, string $type = 'text' ): void {
	$input_name = ltrim( $name, '_' );
	?>
	<div class="clo-meta-row">
		<label for="<?php echo esc_attr( $input_name ); ?>"><?php echo esc_html( $label ); ?></label>
		<?php if ( 'textarea' === $type ) : ?>
			<textarea id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<?php else : ?>
			<input type="text" id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php endif; ?>
	</div>
	<?php
}

function clo_image_picker_row( int $n, int $img_id, string $img_url, string $alt, string $caption, bool $has_caption ): void {
	$input_name = "clo_image_{$n}_id";
	?>
	<input type="hidden" id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( (string) $img_id ); ?>">
	<div class="clo-img-picker">
		<img id="clo-img<?php echo esc_attr( (string) $n ); ?>-preview"
			class="clo-img-preview"
			src="<?php echo esc_url( $img_url ); ?>"
			alt=""
			style="<?php echo $img_url ? 'display:block;' : 'display:none;'; ?>">
		<div>
			<button type="button"
				id="clo-img<?php echo esc_attr( (string) $n ); ?>-pick"
				class="button"
				data-title="Image <?php echo esc_attr( (string) $n ); ?> — Choisir dans la médiathèque">
				<?php echo $img_url ? 'Changer l\'image' : 'Choisir une image'; ?>
			</button>
			<button type="button"
				id="clo-img<?php echo esc_attr( (string) $n ); ?>-remove"
				class="button"
				style="<?php echo $img_url ? '' : 'display:none;'; ?>">
				Supprimer
			</button>
		</div>
	</div>
	<div class="clo-meta-row" style="margin-top:6px;">
		<label for="clo_image_<?php echo esc_attr( (string) $n ); ?>_alt">Texte alt</label>
		<input type="text" id="clo_image_<?php echo esc_attr( (string) $n ); ?>_alt"
			name="clo_image_<?php echo esc_attr( (string) $n ); ?>_alt"
			value="<?php echo esc_attr( $alt ); ?>">
	</div>
	<?php if ( $has_caption ) : ?>
	<div class="clo-meta-row">
		<label for="clo_image_<?php echo esc_attr( (string) $n ); ?>_caption">Légende</label>
		<input type="text" id="clo_image_<?php echo esc_attr( (string) $n ); ?>_caption"
			name="clo_image_<?php echo esc_attr( (string) $n ); ?>_caption"
			value="<?php echo esc_attr( $caption ); ?>">
	</div>
	<?php endif; ?>
	<?php
}
