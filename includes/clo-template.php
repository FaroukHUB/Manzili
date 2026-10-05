<?php
/**
 * CLO Product Description Template Renderer
 *
 * Renders the full .clo-product HTML for a WooCommerce product from post meta.
 * Called when _clo_enabled = '1' for the product.
 *
 * @package Manzili
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function clo_render_product_description( int $post_id ): string {

	$m = function ( string $key ) use ( $post_id ): string {
		return (string) get_post_meta( $post_id, $key, true );
	};

	$slug         = sanitize_title( get_the_title( $post_id ) );
	$product_name = get_the_title( $post_id );

	$kicker   = $m( '_clo_kicker' );
	$intro_h2 = $m( '_clo_intro_h2' );
	$lead     = $m( '_clo_lead' );
	$intro_p2 = $m( '_clo_intro_p2' );

	$facts = [];
	for ( $i = 1; $i <= 4; $i++ ) {
		$f_label = $m( "_clo_fact_{$i}_label" );
		$f_value = $m( "_clo_fact_{$i}_value" );
		if ( '' !== $f_label || '' !== $f_value ) {
			$facts[] = [ 'label' => $f_label, 'value' => $f_value ];
		}
	}

	$img1_id      = (int) $m( '_clo_image_1_id' );
	$img1_alt     = $m( '_clo_image_1_alt' );
	$img1_caption = $m( '_clo_image_1_caption' );
	$img1_url     = $img1_id ? (string) wp_get_attachment_image_url( $img1_id, 'large' ) : '';

	$notes_kicker = $m( '_clo_notes_kicker' );
	$notes_h2     = $m( '_clo_notes_h2' );
	$notes_intro  = $m( '_clo_notes_intro' );

	$notes = [];
	for ( $i = 1; $i <= 3; $i++ ) {
		$n_index = $m( "_clo_note_{$i}_index" );
		$n_h3    = $m( "_clo_note_{$i}_h3" );
		$n_text  = $m( "_clo_note_{$i}_text" );
		if ( '' !== $n_index || '' !== $n_h3 || '' !== $n_text ) {
			$notes[] = [ 'index' => $n_index, 'h3' => $n_h3, 'text' => $n_text ];
		}
	}

	$apply_kicker = $m( '_clo_apply_kicker' );
	$apply_h2     = $m( '_clo_apply_h2' );
	$apply_intro  = $m( '_clo_apply_intro' );

	$steps = [];
	for ( $i = 1; $i <= 5; $i++ ) {
		$s_strong = $m( "_clo_step_{$i}_strong" );
		$s_span   = $m( "_clo_step_{$i}_span" );
		if ( '' !== $s_strong || '' !== $s_span ) {
			$steps[] = [ 'strong' => $s_strong, 'span' => $s_span ];
		}
	}

	$tip_label = $m( '_clo_tip_label' );
	$tip_text  = $m( '_clo_tip_text' );

	$img2_id  = (int) $m( '_clo_image_2_id' );
	$img2_alt = $m( '_clo_image_2_alt' );
	$img2_url = $img2_id ? (string) wp_get_attachment_image_url( $img2_id, 'large' ) : '';

	$night_kicker = $m( '_clo_night_kicker' );
	$night_h2     = $m( '_clo_night_h2' );
	$night_p      = $m( '_clo_night_p' );

	$seasons = [];
	for ( $i = 1; $i <= 4; $i++ ) {
		$se_label = $m( "_clo_season_{$i}_label" );
		$se_text  = $m( "_clo_season_{$i}_text" );
		if ( '' !== $se_label ) {
			$seasons[] = [ 'label' => $se_label, 'text' => $se_text ];
		}
	}

	$img3_id      = (int) $m( '_clo_image_3_id' );
	$img3_alt     = $m( '_clo_image_3_alt' );
	$img3_caption = $m( '_clo_image_3_caption' );
	$img3_url     = $img3_id ? (string) wp_get_attachment_image_url( $img3_id, 'large' ) : '';

	$guide_kicker = $m( '_clo_guide_kicker' );
	$guide_h2     = $m( '_clo_guide_h2' );
	$guide_intro  = $m( '_clo_guide_intro' );

	$guides = [];
	for ( $i = 1; $i <= 4; $i++ ) {
		$g_icon = $m( "_clo_guide_{$i}_icon" );
		$g_h3   = $m( "_clo_guide_{$i}_h3" );
		$g_text = $m( "_clo_guide_{$i}_text" );
		if ( '' !== $g_h3 ) {
			$guides[] = [ 'icon' => $g_icon, 'h3' => $g_h3, 'text' => $g_text ];
		}
	}

	$forwho_kicker = $m( '_clo_forwho_kicker' );
	$forwho_h2     = $m( '_clo_forwho_h2' );
	$forwho_p      = $m( '_clo_forwho_p' );

	$checks = [];
	for ( $i = 1; $i <= 6; $i++ ) {
		$c = $m( "_clo_check_{$i}" );
		if ( '' !== $c ) {
			$checks[] = $c;
		}
	}

	$honest_label = $m( '_clo_honest_label' );
	$honest_text  = $m( '_clo_honest_text' );

	$faq_kicker = $m( '_clo_faq_kicker' );
	$faq_h2     = $m( '_clo_faq_h2' );
	$faq_intro  = $m( '_clo_faq_intro' );

	$faqs = [];
	for ( $i = 1; $i <= 8; $i++ ) {
		$fq = $m( "_clo_faq_{$i}_q" );
		$fa = $m( "_clo_faq_{$i}_a" );
		if ( '' !== $fq ) {
			$faqs[] = [ 'q' => $fq, 'a' => $fa ];
		}
	}

	$disc_h2    = $m( '_clo_disc_h2' );
	$disc_p     = $m( '_clo_disc_p' );
	$disclaimer = $m( '_clo_disclaimer' );

	$disc_links = [];
	for ( $i = 1; $i <= 2; $i++ ) {
		$l_text = $m( "_clo_link_{$i}_text" );
		$l_href = $m( "_clo_link_{$i}_href" );
		if ( '' !== $l_href ) {
			$disc_links[] = [ 'text' => $l_text, 'href' => $l_href ];
		}
	}

	ob_start();
	?>
	<article class="clo-product clo-product--original" aria-label="<?php echo esc_attr( 'Présentation détaillée de ' . $product_name ); ?>">

		<section class="clo-section clo-intro">
			<div>
				<?php if ( '' !== $kicker ) : ?>
					<p class="clo-kicker"><?php echo esc_html( $kicker ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $intro_h2 ) : ?>
					<h2><?php echo esc_html( $intro_h2 ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $lead ) : ?>
					<p class="clo-lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $intro_p2 ) : ?>
					<p><?php echo esc_html( $intro_p2 ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $facts ) ) : ?>
					<div class="clo-facts">
						<?php foreach ( $facts as $fact ) : ?>
							<div class="clo-fact">
								<span><?php echo esc_html( $fact['label'] ); ?></span>
								<strong><?php echo esc_html( $fact['value'] ); ?></strong>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $img1_url ) : ?>
				<figure class="clo-media">
					<img src="<?php echo esc_url( $img1_url ); ?>" alt="<?php echo esc_attr( $img1_alt ); ?>" loading="lazy" decoding="async">
					<?php if ( '' !== $img1_caption ) : ?>
						<figcaption class="clo-media-label"><?php echo esc_html( $img1_caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
		</section>

		<?php if ( ! empty( $notes ) ) : ?>
			<section class="clo-section clo-notes" aria-labelledby="<?php echo esc_attr( $slug ); ?>-notes-title">
				<?php if ( '' !== $notes_kicker ) : ?>
					<p class="clo-kicker"><?php echo esc_html( $notes_kicker ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $notes_h2 ) : ?>
					<h2 id="<?php echo esc_attr( $slug ); ?>-notes-title"><?php echo esc_html( $notes_h2 ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $notes_intro ) : ?>
					<p class="clo-notes-intro"><?php echo esc_html( $notes_intro ); ?></p>
				<?php endif; ?>
				<div class="clo-notes-grid">
					<?php foreach ( $notes as $note ) : ?>
						<div class="clo-note-card">
							<?php if ( '' !== $note['index'] ) : ?>
								<span class="clo-note-index"><?php echo esc_html( $note['index'] ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $note['h3'] ) : ?>
								<h3><?php echo esc_html( $note['h3'] ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== $note['text'] ) : ?>
								<p><?php echo esc_html( $note['text'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $steps ) || '' !== $apply_h2 ) : ?>
			<section class="clo-split" aria-labelledby="<?php echo esc_attr( $slug ); ?>-application-title">
				<?php if ( '' !== $img2_url ) : ?>
					<div class="clo-split-media">
						<img src="<?php echo esc_url( $img2_url ); ?>" alt="<?php echo esc_attr( $img2_alt ); ?>" loading="lazy" decoding="async">
					</div>
				<?php endif; ?>
				<div class="clo-split-copy">
					<?php if ( '' !== $apply_kicker ) : ?>
						<p class="clo-kicker"><?php echo esc_html( $apply_kicker ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $apply_h2 ) : ?>
						<h2 id="<?php echo esc_attr( $slug ); ?>-application-title"><?php echo esc_html( $apply_h2 ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $apply_intro ) : ?>
						<p><?php echo esc_html( $apply_intro ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $steps ) ) : ?>
						<ol class="clo-steps">
							<?php foreach ( $steps as $step ) : ?>
								<li>
									<?php if ( '' !== $step['strong'] ) : ?>
										<strong><?php echo esc_html( $step['strong'] ); ?></strong>
									<?php endif; ?>
									<?php if ( '' !== $step['span'] ) : ?>
										<span><?php echo esc_html( $step['span'] ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
					<?php if ( '' !== $tip_label || '' !== $tip_text ) : ?>
						<div class="clo-tip">
							<?php if ( '' !== $tip_label ) : ?>
								<strong><?php echo esc_html( $tip_label ); ?></strong>
							<?php endif; ?>
							<?php echo esc_html( $tip_text ); ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( '' !== $night_h2 ) : ?>
			<section class="clo-section clo-night" aria-labelledby="<?php echo esc_attr( $slug ); ?>-moments-title">
				<div>
					<?php if ( '' !== $night_kicker ) : ?>
						<p class="clo-kicker"><?php echo esc_html( $night_kicker ); ?></p>
					<?php endif; ?>
					<h2 id="<?php echo esc_attr( $slug ); ?>-moments-title"><?php echo esc_html( $night_h2 ); ?></h2>
					<?php if ( '' !== $night_p ) : ?>
						<p><?php echo esc_html( $night_p ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $seasons ) ) : ?>
						<div class="clo-seasons">
							<?php foreach ( $seasons as $season ) : ?>
								<div class="clo-season">
									<span><?php echo esc_html( $season['label'] ); ?></span>
									<p><?php echo esc_html( $season['text'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $img3_url ) : ?>
					<figure class="clo-media">
						<img src="<?php echo esc_url( $img3_url ); ?>" alt="<?php echo esc_attr( $img3_alt ); ?>" loading="lazy" decoding="async">
						<?php if ( '' !== $img3_caption ) : ?>
							<figcaption class="clo-media-label"><?php echo esc_html( $img3_caption ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $guides ) ) : ?>
			<section class="clo-section clo-guide" aria-labelledby="<?php echo esc_attr( $slug ); ?>-tenue-title">
				<div class="clo-guide-head">
					<div>
						<?php if ( '' !== $guide_kicker ) : ?>
							<p class="clo-kicker"><?php echo esc_html( $guide_kicker ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $guide_h2 ) : ?>
							<h2 id="<?php echo esc_attr( $slug ); ?>-tenue-title"><?php echo esc_html( $guide_h2 ); ?></h2>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $guide_intro ) : ?>
						<p><?php echo esc_html( $guide_intro ); ?></p>
					<?php endif; ?>
				</div>
				<div class="clo-guide-grid">
					<?php foreach ( $guides as $guide ) : ?>
						<div class="clo-guide-card">
							<?php if ( '' !== $guide['icon'] ) : ?>
								<span><?php echo esc_html( $guide['icon'] ); ?></span>
							<?php endif; ?>
							<h3><?php echo esc_html( $guide['h3'] ); ?></h3>
							<?php if ( '' !== $guide['text'] ) : ?>
								<p><?php echo esc_html( $guide['text'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $checks ) || '' !== $forwho_h2 ) : ?>
			<section class="clo-section clo-forwho" aria-labelledby="<?php echo esc_attr( $slug ); ?>-profile-title">
				<div>
					<?php if ( '' !== $forwho_kicker ) : ?>
						<p class="clo-kicker"><?php echo esc_html( $forwho_kicker ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $forwho_h2 ) : ?>
						<h2 id="<?php echo esc_attr( $slug ); ?>-profile-title"><?php echo esc_html( $forwho_h2 ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $forwho_p ) : ?>
						<p><?php echo esc_html( $forwho_p ); ?></p>
					<?php endif; ?>
				</div>
				<div>
					<?php if ( ! empty( $checks ) ) : ?>
						<ul class="clo-checklist">
							<?php foreach ( $checks as $check ) : ?>
								<li><?php echo esc_html( $check ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( '' !== $honest_label || '' !== $honest_text ) : ?>
						<p class="clo-honest">
							<?php if ( '' !== $honest_label ) : ?>
								<strong><?php echo esc_html( $honest_label ); ?></strong>
							<?php endif; ?>
							<?php echo esc_html( $honest_text ); ?>
						</p>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $faqs ) ) : ?>
			<section class="clo-section clo-faq" aria-labelledby="<?php echo esc_attr( $slug ); ?>-faq-title">
				<div class="clo-faq-head">
					<?php if ( '' !== $faq_kicker ) : ?>
						<p class="clo-kicker"><?php echo esc_html( $faq_kicker ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $faq_h2 ) : ?>
						<h2 id="<?php echo esc_attr( $slug ); ?>-faq-title"><?php echo esc_html( $faq_h2 ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $faq_intro ) : ?>
						<p><?php echo esc_html( $faq_intro ); ?></p>
					<?php endif; ?>
				</div>
				<div class="clo-faq-list">
					<?php foreach ( $faqs as $faq ) : ?>
						<details>
							<summary><?php echo esc_html( $faq['q'] ); ?></summary>
							<div class="clo-answer"><p><?php echo esc_html( $faq['a'] ); ?></p></div>
						</details>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $disc_links ) ) : ?>
			<aside class="clo-discover" aria-label="<?php echo esc_attr( 'Découvrir ' . $product_name ); ?>">
				<?php if ( '' !== $disc_h2 ) : ?>
					<h2><?php echo esc_html( $disc_h2 ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $disc_p ) : ?>
					<p><?php echo esc_html( $disc_p ); ?></p>
				<?php endif; ?>
				<div class="clo-links">
					<?php foreach ( $disc_links as $link ) : ?>
						<a class="clo-link" href="<?php echo esc_url( $link['href'] ); ?>"><?php echo esc_html( $link['text'] ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php if ( '' !== $disclaimer ) : ?>
					<p class="clo-disclaimer"><?php echo esc_html( $disclaimer ); ?></p>
				<?php endif; ?>
			</aside>
		<?php endif; ?>

	</article>
	<?php
	return ob_get_clean();
}
