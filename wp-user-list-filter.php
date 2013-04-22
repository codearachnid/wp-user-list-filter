<?php

if ( !defined( 'ABSPATH' ) )
	die( '-1' );

if( !class_exists('WP_User_List_Filter') ){
	class WP_User_List_Filter {

		private static $_this;
		private $is_active = false;
		public $export = false; // Show export button? (Currently does nothing)

		function __construct() {
			add_action( 'admin_notices', array( $this, 'maybe_show_filters' ) );
			add_action( 'admin_footer_text', array( $this, 'footer' ) );
		}

		function is_active(){
			global $pagenow;
			$desired_screen = 'users.php';

			// Exit early on autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				$this->is_active = false;
			}
			
			// Inline save?
			if ( defined( 'DOING_AJAX') && DOING_AJAX && isset($_POST['screen']) && $desired_screen === $_POST['screen'] ) {
				$this->is_active = true;
			}
			
			if ( $desired_screen === $pagenow ) {
				$this->is_active = true;
			} else {
				$this->is_active = false;
			}

			return apply_filters( 'wp_user_list_filter_is_active', $this->is_active );
		}

		function footer( $pass_through ){
			if ( !$this->is_active() )
				return false;

			?>
			<script>
				// move the filter block below the title and alerts because we don't have a hook on users.php
				jQuery('#wp-user-list-filter').insertAfter( ".wrap > h2:first" );


/*
jQuery(document).ready(function($) {
	
	// filters & columns box. Move into place. Show/hide.
	var theFilters = $("#the-filters"),
	tribeFilters = $("#tribe-filters"),
	tribeFiltersHeader = tribeFilters.find("h3");

	tribeFilters
		.insertAfter(".wrap > h2:first")
		.removeClass("wrap");
	tribeFiltersHeader.click(function() {
		if ( theFilters.is(':visible') )
			$.cookie("hideFiltersAndColumns", "true"); // cookies only store strings
		else
			$.cookie("hideFiltersAndColumns", "false");
		
		theFilters.toggle();
		$("#filters-wrap").toggleClass("closed");
	});
	// hide it if it was hidden
	if ( $.cookie("hideFiltersAndColumns") === "true" )
		tribeFiltersHeader.click();
	
	// Also the arrow
	tribeFilters.find(".handlediv").click(function() { tribeFiltersHeader.click()	})
	
	// so we preserve our state when clicking on all/published/drafts

	$(".subsubsub a").click(function(event) {
		event.preventDefault();
		var url = $(this).attr("href"),
		form = $("#the-filters");
		form.attr("action", url).submit();
	});
	
	// un-fixed width columns
	$("#posts-filter .fixed").removeClass("fixed");

	// Save/Cancel Filters
	$("#the-filters .save.button-secondary").click(function(ev) {
		$(this).parent().hide().find("input").attr("disabled", "disabled");
		$("#the-filters .save-options").show();
		$("#filter_name").focus();
		ev.preventDefault();
	});
	$("#cancel-save").click(function(ev) {
		$(this).parent().hide();
		$("#the-filters .actions").show().find("input").removeAttr("disabled");
		ev.preventDefault();
	});
	
	// Save that Filter
	$("#filter_name").keypress(function(ev){
		if ( ev.keyCode == 13 ) {
			ev.preventDefault()
			$(this).next().click()
		}
	})
	
	// Maintain sorting
	$(".tribe-filters-active .wp-list-table .sortable a").click(function(ev) {
		theFilters.attr("action", this.href).submit()
		ev.preventDefault()
	})

});
*/
			</script>
			<style>
				#wp-user-list-filter form{
					padding: 10px;
				}
			</style>
			<?php
			return $pass_through;
		}

		function maybe_show_filters(){
			if ( !$this->is_active() )
				return false;

			$action_url = add_query_arg('post_type', $GLOBALS['typenow'], admin_url('users.php') );

			?>
			<div id="wp-user-list-filter" class="metabox-holder meta-box-sortables">
				<div id="filters-wrap" class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp-user-list-filter') ?>"></div>
					<h3 title="<?php _e('Click to toggle', 'wp-user-list-filter') ?>"><?php _e('Filters &amp; Columns', 'wp-user-list-filter' ); ?></h3>
					<form id="the-filters" action="<?php echo $action_url; ?>" method="post">
						<div class="alignleft filters">
							FILTERS
						</div>
						<div class="alignright filters">
							COLUMNS
						</div>
						<br class="clear" />
						<div class="alignleft actions">
							<input type="submit" name="tribe-apply" value="<?php _e('Apply', 'wp-user-list-filter') ?>" class="button-primary" />
							<input type="submit" name="tribe-clear" value="<?php _e('Clear', 'wp-user-list-filter') ?>" class="button-secondary" />
							<input type="submit" name="save" value="<?php _e('Save', 'wp-user-list-filter') ?>" class="button-secondary save" />
							<?php if ( $this->export ) : ?>
							<input type="submit" name="csv" value="Export" title="<?php _e('Export to CSV', 'wp-user-list-filter') ?>" class="button-secondary csv" />
							<?php endif; ?>

						</div>
						<div class="alignleft save-options">
							<label for="filter_name"><?php _e('Filter Name', 'wp-user-list-filter') ?> </label><input type="text" name="filter_name" value="" id="filter_name" />
							<input type="submit" name="tribe-save" value="<?php _e('Save', 'wp-user-list-filter') ?>" class="button-primary save" />
							<a href="#" id="cancel-save"><?php _e('Cancel', 'wp-user-list-filter') ?></a>
						</div>
						<br class="clear" />
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Static Singleton Factory Method
		 * 
		 * @return static $_this instance
		 * @readlink http://eamann.com/tech/the-case-for-singletons/
		 */
		public static function instance() {
			if ( !isset( self::$_this ) ) {
				$className = __CLASS__;
				self::$_this = new $className;
			}
			return self::$_this;
		}

	}
}