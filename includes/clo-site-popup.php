<?php
/**
 * CLO Site-wide Video Popup
 *
 * Affiche une vidéo en popup à l'arrivée sur le site.
 * Configurable via Réglages > Popup Vidéo dans l'admin WordPress.
 * Une seule fois par durée configurable (cookie).
 *
 * @package Manzili
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Page de réglages admin ────────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_options_page(
		'Popup Vidéo — Site',
		'Popup Vidéo',
		'manage_options',
		'clo-site-popup',
		'clo_site_popup_settings_page'
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_clo-site-popup' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'clo-site-popup-admin',
		get_stylesheet_directory_uri() . '/assets/js/clo-admin.js',
		array( 'jquery' ),
		(string) filemtime( get_stylesheet_directory() . '/assets/js/clo-admin.js' ),
		true
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'clo_site_popup_group', 'clo_popup_enabled',    array( 'sanitize_callback' => 'absint' ) );
	register_setting( 'clo_site_popup_group', 'clo_popup_video_id',   array( 'sanitize_callback' => 'absint' ) );
	register_setting( 'clo_site_popup_group', 'clo_popup_cookie_days',array( 'sanitize_callback' => 'absint' ) );
	register_setting( 'clo_site_popup_group', 'clo_popup_delay_ms',   array( 'sanitize_callback' => 'absint' ) );
} );

function clo_site_popup_settings_page(): void {
	$enabled     = (int) get_option( 'clo_popup_enabled', 0 );
	$video_id    = (int) get_option( 'clo_popup_video_id', 0 );
	$cookie_days = (int) get_option( 'clo_popup_cookie_days', 7 );
	$delay_ms    = (int) get_option( 'clo_popup_delay_ms', 1000 );
	$video_url   = $video_id ? wp_get_attachment_url( $video_id ) : '';
	?>
	<div class="wrap">
		<h1>Popup Vidéo — Site</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'clo_site_popup_group' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Activer le popup</th>
					<td>
						<label>
							<input type="checkbox" name="clo_popup_enabled" value="1" <?php checked( 1, $enabled ); ?>>
							Afficher la vidéo en popup à l'arrivée sur le site
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">Vidéo</th>
					<td>
						<input type="hidden" id="clo_site_video_id" name="clo_popup_video_id" value="<?php echo esc_attr( (string) $video_id ); ?>">
						<div style="display:flex;align-items:flex-start;gap:12px;">
							<?php if ( $video_url ) : ?>
								<video id="clo-site-video-preview" src="<?php echo esc_url( $video_url ); ?>" style="max-width:200px;max-height:120px;border:1px solid #ddd;" controls muted></video>
							<?php else : ?>
								<video id="clo-site-video-preview" style="display:none;max-width:200px;max-height:120px;border:1px solid #ddd;" controls muted></video>
							<?php endif; ?>
							<div>
								<button type="button" id="clo-site-video-pick" class="button button-primary" data-title="Choisir la vidéo du popup">
									<?php echo $video_url ? 'Changer la vidéo' : 'Choisir une vidéo'; ?>
								</button>
								<button type="button" id="clo-site-video-remove" class="button" style="<?php echo $video_url ? '' : 'display:none;'; ?>margin-left:6px;">
									Supprimer
								</button>
								<p class="description">Uploadez ou sélectionnez une vidéo depuis la médiathèque WordPress (MP4 recommandé).</p>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">Délai avant affichage</th>
					<td>
						<input type="number" name="clo_popup_delay_ms" value="<?php echo esc_attr( (string) $delay_ms ); ?>" min="0" max="10000" step="100" style="width:100px;"> ms
						<p class="description">Délai en millisecondes après le chargement de la page (1000 = 1 seconde).</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Fréquence d'affichage</th>
					<td>
						<input type="number" name="clo_popup_cookie_days" value="<?php echo esc_attr( (string) $cookie_days ); ?>" min="0" max="365" style="width:80px;"> jours
						<p class="description">Le popup ne réapparaît pas pendant cette durée. 0 = une seule fois par session navigateur.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Enregistrer' ); ?>
		</form>
	</div>
	<script>
	(function(){
		var frame;
		var btn    = document.getElementById('clo-site-video-pick');
		var remove = document.getElementById('clo-site-video-remove');
		var input  = document.getElementById('clo_site_video_id');
		var preview = document.getElementById('clo-site-video-preview');
		if (!btn) return;
		btn.addEventListener('click', function(e){
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title:'Choisir la vidéo', button:{text:'Utiliser cette vidéo'}, multiple:false, library:{type:'video'} });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				input.value = att.id;
				preview.src = att.url;
				preview.style.display = 'block';
				remove.style.display  = 'inline-block';
				btn.textContent = 'Changer la vidéo';
			});
			frame.open();
		});
		remove.addEventListener('click', function(e){
			e.preventDefault();
			input.value = '';
			preview.src = ''; preview.style.display = 'none';
			remove.style.display = 'none';
			btn.textContent = 'Choisir une vidéo';
		});
	}());
	</script>
	<?php
}

// ── Front-end : sortie du popup ───────────────────────────────────────────────

add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	if ( ! (int) get_option( 'clo_popup_enabled', 0 ) ) {
		return;
	}
	$video_id = (int) get_option( 'clo_popup_video_id', 0 );
	if ( ! $video_id ) {
		return;
	}
	$video_url   = (string) wp_get_attachment_url( $video_id );
	if ( ! $video_url ) {
		return;
	}
	$delay_ms    = max( 0, (int) get_option( 'clo_popup_delay_ms', 1000 ) );
	$cookie_days = max( 0, (int) get_option( 'clo_popup_cookie_days', 7 ) );
	$cookie_key  = 'clo_site_popup_seen';
	?>
	<div id="clo-site-popup" role="dialog" aria-modal="true" aria-label="Bienvenue sur notre site" style="position:fixed;inset:0;background:rgba(0,0,0,.85);display:flex;align-items:center;justify-content:center;z-index:999999;opacity:0;pointer-events:none;transition:opacity .4s ease;">
		<div style="position:relative;width:min(900px,92vw);max-height:92vh;">
			<button id="clo-site-popup-close" aria-label="Fermer la vidéo" style="position:absolute;top:-2.6rem;right:0;background:none;border:none;color:#fff;font-size:1.8rem;line-height:1;cursor:pointer;padding:.25rem .5rem;opacity:.85;">&#x2715;</button>
			<video id="clo-site-popup-video" src="<?php echo esc_url( $video_url ); ?>" controls playsinline preload="metadata" style="width:100%;display:block;max-height:88vh;object-fit:contain;background:#000;"></video>
		</div>
	</div>
	<script>
	(function(){
		var COOKIE  = <?php echo json_encode( $cookie_key ); ?>;
		var DAYS    = <?php echo (int) $cookie_days; ?>;
		var DELAY   = <?php echo (int) $delay_ms; ?>;
		var popup   = document.getElementById('clo-site-popup');
		var video   = document.getElementById('clo-site-popup-video');
		var closeBtn= document.getElementById('clo-site-popup-close');

		function getCookie(n){ var m=document.cookie.match('(^|;)\\s*'+n+'=([^;]+)'); return m?m[2]:null; }
		function setCookie(n,v,d){ var e=new Date(); e.setDate(e.getDate()+(d||1)); document.cookie=n+'='+v+';expires='+e.toUTCString()+';path=/;SameSite=Lax'; }

		if (getCookie(COOKIE)) return;

		function closePopup(){
			popup.style.opacity='0';
			popup.style.pointerEvents='none';
			if(video) video.pause();
			if(DAYS>0){ setCookie(COOKIE,'1',DAYS); }
			else { sessionStorage.setItem(COOKIE,'1'); }
		}

		if(DAYS===0 && sessionStorage.getItem(COOKIE)) return;

		setTimeout(function(){
			popup.style.opacity='1';
			popup.style.pointerEvents='all';
		}, DELAY);

		closeBtn.addEventListener('click', closePopup);
		popup.addEventListener('click', function(e){ if(e.target===popup) closePopup(); });
		document.addEventListener('keydown', function(e){ if(e.key==='Escape') closePopup(); });
	}());
	</script>
	<?php
} );
