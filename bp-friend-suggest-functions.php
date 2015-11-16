<?php
function friend_suggest_get_friendship_requested_user_ids( $user_id ) {
		global $wpdb;
		
		$bp = buddypress();

		return $wpdb->get_col( $wpdb->prepare( "SELECT friend_user_id FROM {$bp->friends->table_name} WHERE initiator_user_id = %d AND is_confirmed = 0", $user_id ) );
}
	
function bp_friend_suggest_hide_link( $possible_friend ) {
	
    $url = bp_get_root_domain() . "/remove-friend-suggestion/?suggest_id=" . $possible_friend . "&_wpnonce=" . wp_create_nonce( 'friend-suggestion-remove-' . $possible_friend );
?>
<span class="remove-friend-suggestion"><a href="<?php echo $url; ?>" title="<?php _e( 'Hide this suggestion', 'bp-friend-suggest' );?>">x</a></span>
<?php
}

function friend_suggest_fix_bp_button_bug($button){
	if( $button['id'] == 'not_friends' ) {
		$button['block_self'] = false;
	}
	
	return $button;
}
add_filter( 'bp_get_add_friend_button', 'friend_suggest_fix_bp_button_bug' );
