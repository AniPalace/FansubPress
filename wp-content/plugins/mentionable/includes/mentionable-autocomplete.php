<?php
/**
 * Handle the mention autocomplete ajax call
 *
 * @author X-Team <x-team.com>
 * @author Jonathan Bardo <jonathan.bardo@x-team.com>
 */

class Mentionable_Autocomplete {

	/**
	 * Class constructor
	 *
	 * @return \Mentionable_Autocomplete
	 */
	public function __construct(){
		add_action( 'wp_ajax_get_mentionable', array( $this, 'handle_ajax' ) );
	}

	/**
	 * Handle the autocomplete ajax request
	 *
	 * @return json
	 */
	public function handle_ajax() {
		check_ajax_referer( 'mentionable_nonce', 'mentionable_nonce' );

		if ( ! isset( $_REQUEST['mentionable_word'] ) ) {
			wp_send_json_error( 'Mention Word is not specified.' );
		}

		// This helps us search by post title
		add_filter( 'posts_where', array( $this, 'posts_where_like_title' ), 10, 2 );

		$query_args = array( 
			'post_type'       => Mentionable_Settings::$options['autocomplete_post_types'],
			'post_title_like' => $_REQUEST['mentionable_word'],
			'fields'          => 'ids',
			'posts_per_page'  => 5,
		);

		$query = new WP_Query( apply_filters( 'mentionable/autocomplete/args', $query_args ) );

		$results = array();
		foreach ( $query->posts as $id ) {
			$results[ get_the_title( $id ) ] = array(
				'id'   => $id,
				'url'  => get_permalink( $id ),
				'type' => get_post_type( $id ),
			);
		}

		if ( empty( $results ) ) {
			wp_send_json_error( 'No results' );
		} else {
			wp_send_json_success( $results );
		}

	}

	/**
	 * Aaa a where clause to filter by post name
	 *
	 * @param string $where
	 * @param array $wp_query
	 *
	 * @return string
	 */
	function posts_where_like_title( $where, &$wp_query ) {
		global $wpdb;

		if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\'';
		}

		return $where;
	}

}
