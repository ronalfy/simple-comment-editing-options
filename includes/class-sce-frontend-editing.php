<?php
/**
 * Register SCE Options for front-end editing.
 *
 * @package SCEOptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}

/**
 * Main class for front-end editing.
 */
class SCE_Frontend_Editing {

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_ajax_calls' ) );
		add_action( 'init', array( $this, 'setup_comment_filters' ) );
	}

	/**
	 * Set up front-end editing filters.
	 */
	public function setup_comment_filters() {
		/* Begin Filters */
		if ( ! is_feed() && ! defined( 'DOING_SCE' ) ) {
			if ( current_user_can( 'moderate_comments' ) ) {
				add_filter( 'comment_excerpt', array( $this, 'add_edit_interface' ), 1000, 2 );
				add_filter( 'comment_text', array( $this, 'add_edit_interface' ), 1000, 2 );
				add_filter( 'thesis_comment_text', array( $this, 'add_edit_interface' ), 1000, 2 );
				add_filter( 'sce_can_edit', '__return_false' );
				add_filter( 'edit_comment_link', array( $this, 'modify_edit_link' ), 10, 3 );
			}
		}
	}

	/**
	 * Add editing interface for front-end comment editing.
	 *
	 * @param string $comment_content The comment content.
	 * @param object $passed_comment  A passed comment object.
	 *
	 * @return string The comment wrapper.
	 */
	public function add_edit_interface( $comment_content, $passed_comment = false ) {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			return $comment_content;
		}

		// Get current comment.
		global $comment; // For Thesis.
		if ( ( ! $comment && ! $passed_comment ) || empty( $comment_content ) ) {
			return $comment_content;
		}
		if ( $passed_comment ) {
			$comment = (object) $passed_comment; // phpcs:ignore
		}

		$comment_id = absint( $comment->comment_ID );
		$edit_text  = apply_filters( 'sce_frontend_text_edit', __( 'Click to inline edit', 'simple-comment-editing-options' ) );

		// Build link.
		$link = add_query_arg(
			array(
				'comment_id' => $comment_id,
				'nonce'      => wp_create_nonce( 'sce-moderator-edit-' . $comment_id ),
			),
			admin_url( 'admin-ajax.php' )
		);

		// Return.
		$comment_wrapper = sprintf(
			'<div id="sce-front-end-comment-%d" class="sce-front-end-comment" data-cid="%d">%s</div>',
			$comment_id,
			$comment_id,
			$comment_content
		);
		return $comment_wrapper;
	}

	/**
	 * Sets up Ajax calls.
	 *
	 * Sets up Ajax calls.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function setup_ajax_calls() {
		add_action( 'wp_ajax_sce_get_moderation_comment', array( $this, 'ajax_get_comment' ) );
	}

	/**
	 * Gets a comment.
	 *
	 * Gets a comment.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function ajax_get_comment() {
		$nonce      = sanitize_text_field( $_POST['nonce'] ); // phpcs:ignore
		$comment_id = absint( $_POST['comment_id'] ); // phpcs:ignore

		// Do a permissions check.
		if ( ! current_user_can( 'moderate_comments' ) ) {
			$return = array(
				'errors' => true,
			);
			wp_send_json( $return );
			exit;
		}

		// Verify nonce.
		if ( ! wp_verify_nonce( $nonce, 'sce-moderator-edit-' . $comment_id ) ) {
			$return = array(
				'errors' => true,
			);
			wp_send_json( $return );
			exit;
		}

		$comment                  = get_comment( $comment_id );
		$comment->comment_content = $this->format_comment_text( $comment->comment_content );
		$comment->comment_author  = $this->format_comment_text( $comment->comment_author );
		wp_send_json( $comment );
		exit;
	}

	/**
	 * Returns formatted text for output.
	 *
	 * Returns formatted text for output.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param string $content The comment content.
	 *
	 * @return string formatted comment.
	 */
	private function format_comment_text( $content ) {
		// Format the comment for returning.
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$content = mb_convert_encoding( $content, '' . get_option( 'blog_charset' ) . '', mb_detect_encoding( $content, 'UTF-8, ISO-8859-1, ISO-8859-15', true ) );
		}
		return $content;
	}

	/**
	 * Modify the edit link HTML
	 *
	 * @param string $link The link HTML.
	 * @param int    $comment_id The comment ID.
	 * @param string $text       The edit text.
	 *
	 * @return string modified edit link.
	 */
	public function modify_edit_link( $link, $comment_id, $text ) {
		$nonce = wp_create_nonce( 'edit-comment-' . $comment_id );
		$url   = add_query_arg(
			array(
				'action' => 'sce_options_get_frontend_comment',
				'nonce'  => $nonce,
				'cid'    => $comment_id,
			),
			admin_url( 'admin-ajax.php' )
		);
		$html  = sprintf(
			'<a data-fancybox data-type="iframe" data-src="%s" href="javascript:;">%s</a>',
			esc_url( $url ),
			__( 'Edit', 'simple-comment-editing-options' )
		);
		return $html;
	}
}
