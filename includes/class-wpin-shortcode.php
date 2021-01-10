<?php

/**
 * Processes the [wpin] shortcode
 *
 * @since      1.0.0
 * @package    Wpin
 * @subpackage Wpin/includes
 */
class Wpin_Shortcode {

	const TAG = 'wpin';

	/**
	 * Process the shortcode for wpin
	 *
	 * @param array       $atts The attributes of the tag.
	 * @param null|string $content The enclosed tag content.
	 *
	 * @return string
	 */
	public function shortcode( $atts = array(), $content = null ) {
		$validation_message = '';
		if ( ! $this->validate_content( $content, $validation_message ) ) {
			return $validation_message;
		}

		$feed = $this->fetch_feed( $content );

		return $this->render_feed( $feed );
	}

	/**
	 * Check that the content enclosed in the tag is valid
	 *
	 * @param string $content The enclosed tag content.
	 * @param string $validation_message Will be populated with the validation message if invalid.
	 *
	 * @return bool
	 */
	private function validate_content( $content, &$validation_message ) {
		if ( null === $content ) {
			$validation_message = 'Should include a URL';
			return false;
		}

		// Ensure that the URL is actually of pinboard.in, don't want to fetch arbitrary sites.
		if (0 === preg_match( '|^https?://pinboard\.in/|', $content )) {
			$validation_message = 'Invalid URL, it should start with https://pinboard.in/';
			return false;
		}

		return true;
	}

	/**
	 * Fetch the feed for the given URL
	 *
	 * @param $url The URL the user wants to display bookmarks for
	 *
	 * @return boolean|array The feed content as an array, unless there's an issue in which case false
	 */
	private function fetch_feed( $url ) {
		$url = $this->build_url( $url );

		$cache_key = __CLASS__ . $url;

		$body = get_transient($cache_key);

		if ( false === $body ) {
			$response = wp_remote_get( $url );
			if ( '200' != wp_remote_retrieve_response_code( $response ) ) {
				return false;  // Failed to get an ok response, return nothing instead
			}

			// TODO: Check if receiving a 429 response and exponential back-off if so.
			// Successful calls are cached for an hour which is much higher than the every three second limit
			// pinboard.in specify and the feed is being used rather than the API, so perhaps this isn't strictly
			// required but it would be nice.

			$body = wp_remote_retrieve_body ( $response );
			set_transient( $cache_key, $body, HOUR_IN_SECONDS );
		}

		$feed = json_decode( $body, true );

		if ( ! is_array( $feed )) {
			return false;   // Expecting to decode this to an array, anything else is likely to be a bug
		}

		return $feed;
	}

	/**
	 * Convert the URL the user entered to the feeds URL
	 *
	 * @param string|null $url The URL the user wants to display bookmarks for
	 *
	 * @return string
	 */
	private function build_url( $url ) {
		// Always use https
		$url = str_replace( 'http://', 'https://', $url );
		// Swap out the user-friendly URL for the feed equivalent
		$url = str_replace( 'https://pinboard.in/', 'https://feeds.pinboard.in/json/', $url);

		return $url;
	}

	private function render_feed( $feed ) {
		ob_start();
		foreach ( $feed as $entry ) {
			$url = esc_url( $entry['u'] );
			$title = esc_html( $entry['d'] );
			$description = nl2br( esc_html( trim( $entry['n'] ) ) );
			$bookmarker = $entry['a'];
			$tags = array_map( 'esc_html', $entry['t'] );
			$bookmarked_at_utc = esc_attr( $entry['dt'] );
			$timestamp_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$bookmarked_at_human = esc_html( date_i18n( $timestamp_format, strtotime( $entry['dt'] )));

			$tag_prefix = 'https://pinboard.in';
			if ( $bookmarker ) {
				$tag_prefix .= "/u:{$bookmarker}";
			}
			$tag_prefix .= "/t:";

			?>
<li class="wpin-bookmark">
	<a class="wpin-title" href="<?=$url?>"><?=$title?></a>
	<?php if ( $description ): ?>
		<div class="wpin-desc"><?=$description?></div>
	<?php else: ?>
		<br>
	<?php endif; ?>
	<time class="wpin-date" datetime="<?=$bookmarked_at_utc?>"><?=$bookmarked_at_human?></time>
	<?php if ( $tags ): ?>
		<ul class="wpin-tags">
			<?php foreach( $tags as $tag ): ?>
				<li class="wpin-tag"><a href="<?=esc_url( $tag_prefix . $tag )?>"><?=$tag?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</li>

		<?php

		}
		return '<ul class="wpin-bookmarks">' . ob_get_clean() . '</ul>';
	}
}
