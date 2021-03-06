<?php // phpcs:ignore

get_header();

$frontpage_id = get_option( 'page_on_front' );
?>

<div class="wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); // phpcs:ignore ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					<?php twentyseventeen_edit_link( get_the_ID() ); ?>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<?php the_content(); ?>
				</div><!-- .entry-content -->

				<div class="current-talk">
				</div><!-- .current-talk -->

				<?php
				$args = array(
					'post_type'      => array( 'wptd_talk' ),
					'post_status'    => array( 'publish' ),
					'nopaging'       => true,
					'posts_per_page' => -1,
					'orderby'        => 'meta_value_num',
					'meta_key'       => 'utc_start_time',
					'order'          => 'ASC',
				);

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) : //phpcs:ignore ?>
					<div class="entry-content">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();

							$speakers      = get_field( 'speaker' );
							$speaker_names = array();
							foreach ( $speakers as $speaker ) {
								array_push( $speaker_names, get_the_title( $speaker ) );
							}
							?>

							<div class="talk">
								<div class="left">
									<div class="time"
										data-duration="<?php echo esc_attr( str_replace( ' minutes', '', get_field( 'duration' ) ) ); ?>"
										data-when="now"
										data-time="<?php echo esc_attr( get_field( 'event_date', $frontpage_id ) ) . ' ' . esc_attr( get_field( 'utc_start_time' ) ) . ':00'; ?>">
										<?php echo esc_attr( get_field( 'utc_start_time' ) ); ?> <br>
									</div>
									<div class="local-time">
										<?php echo esc_attr( get_field( 'utc_start_time' ) ); ?> <br>
									</div>
								</div>
								<div class="right">
									<div class="title">
										<?php the_title(); ?>
									</div>
									<div class="speaker">
										<?php echo '<a href="' . esc_url( site_url( '/the-speakers/', 'https' ) ) . '">' . esc_attr( implode( ', ', $speaker_names ) ) . '</a>'; ?>
									</div>
									<div class="description">
										<?php echo wp_kses_post( get_field( 'description' ) ); ?>
									</div>
									<div class="info">
										<?php echo esc_attr( get_field( 'live_or_prerecorded' ) ) . ' | ' . esc_attr( get_field( 'duration' ) ) . ' | ' . esc_attr( get_field( 'target_language' ) ) .  ' | ' . esc_attr( get_field( 'target_audience' ) ); ?>
									</div>
								</div>
							</div>

						<?php endwhile; ?>
					</div><!-- .entry-content -->
				<?php endif; ?>

				<?php wp_reset_postdata(); ?>

			</article><!-- #post-## -->

			<?php endwhile; // End of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<script>
	( function( $ ) {
		function fixTalkList() {
			$( '.time' ).each( function () {
				var talkTimeUTC = $( this ).attr( 'data-time' ),
					timeLocal = moment.utc( $( this ).attr( 'data-time' ) ).toDate(),
					currTimeUTC = moment().utc().format( 'YYYY-MM-DD HH:mm:ss' ),
					durTimeUTC = $( this ).attr( 'data-duration' ),
					durTime = moment( talkTimeUTC ).add( durTimeUTC, 'm' ),
					endTime = durTime.format( 'YYYY-MM-DD HH:mm:ss' );

				$( '.current-talk' ).html('');

				if ( currTimeUTC > talkTimeUTC ) {
					$( this ).attr( 'data-when', 'past' );
				} else {
					$( this ).attr( 'data-when', 'future' );
				}

				if ( currTimeUTC < endTime && currTimeUTC > talkTimeUTC ) {
					$( this ).data( 'when', 'now' );
				}

				timeLocal = moment( timeLocal ).format( 'HH:mm' );
				$( this ).siblings( '.local-time' ).html( timeLocal );
			} );

			$( 'div[data-when="past"]' ).each( function () {
				$( this ).css( 'opacity', '.4' );
			} );

			var currTalk = $( 'div[data-when="now"]' ).clone();
			$( '.current-talk .talk-holder' ).html( currTalk );
		}

		$( 'document' ).ready( function() {
			var theLiveDay = '<?php echo esc_attr( get_field( 'event_date', $frontpage_id ) ); ?>',
				currDay = moment().utc().format( 'YYYY-MM-DD' );

			if ( theLiveDay != currDay ) {
				$('.current-talk').css('display', 'none');
			}

			fixTalkList();

			setInterval( function () {
				fixTalkList();
			}, 60000 );
		})
	})( jQuery );
</script>

<?php
get_footer();
