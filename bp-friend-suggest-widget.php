<?php

/**
 * Widget
 */
class BP_Friend_Suggestions_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( false, $name = __( 'Friends Suggest Widget', 'bp-friends-suggest' ) );
	}

	public function widget( $args, $instance ) {
	
		
		if ( ! is_user_logged_in() ) {
			return; //do not show to non logged in user
		}
		
		extract( $args );
		
		echo $before_widget ;
		
		echo $before_title . $instance['title'] . $after_title ;
		
			bp_show_friend_suggestions_list( $instance['max'] );
			
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max'] = absint( $new_instance['max'] );

		return $instance;
	}

	public function form( $instance ) {
		
		$instance = wp_parse_args( (array) $instance, array( 
					'title' => __( 'Friend Suggestions', 'bp-friends-suggest' ), 
					'max' => 5
					) 
		);
		
		
		$title = strip_tags( $instance['title'] );
		$max = absint( $instance['max'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'bp-friends-suggest' ); ?>
				<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'max' ); ?>"><?php _e( 'Max Number of suggestions:', 'bp-friends-suggest' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'max' ); ?>" name="<?php echo $this->get_field_name( 'max' ); ?>" type="text" value="<?php echo esc_attr( $max ); ?>" style="width: 30%" />
			</label>
		</p>

		<?php
	}

}//end of widget class

//register widget
function friend_suggest_register_widget() {
	
	register_widget( 'BP_Friend_Suggestions_Widget' );
	
}

add_action( 'bp_widgets_init', 'friend_suggest_register_widget' );

//action takes place here
function bp_show_friend_suggestions_list( $limit = 5 ) {
	
	
	$user_id = get_current_user_id();

	$my_friends = (array) friends_get_friend_user_ids( $user_id ); //get all friend ids

	$my_friend_req = (array) friend_suggest_get_friendship_requested_user_ids( $user_id ); //get all friend request by me

	$possible_friends = array(); //we will store the possible friend ids here
	
	foreach ( $my_friends as $friend_id ) {
		$possible_friends = array_merge( $possible_friends, (array) friends_get_friend_user_ids( $friend_id ) );
	}
	

	//we have the list of friends of friends, we will just remove
	//now get only udifferent friend ids(unique)
	$possible_friends = array_unique( $possible_friends );

	//intersect my friends with this array
	$my_friends[] = get_current_user_id(); //include me to

	$excluded_users = get_user_meta( $user_id, 'hidden_friend_suggestions', true );

	$excluded_users = array_merge( $my_friends, (array) $excluded_users, (array) $my_friend_req );

	//we may check the preference of the user regarding , like not add

	$possible_friends = array_diff( $possible_friends, $excluded_users ); //get those user who are not my friend and also exclude me too
	
	if ( ! empty( $possible_friends ) ) {
		
		shuffle( $possible_friends ); //randomize
		$possible_friends = array_slice( $possible_friends, 0, $limit );
	}


	if ( ! empty( $possible_friends ) ):
		?>
		<ul id="members-suggestion-list" class="item-list suggested-friend-item-list">
				<?php foreach ( $possible_friends as $possible_friend ): ?>
				<li>
				<?php
					$member_link = bp_core_get_user_domain( $possible_friend );
					$member_name = bp_core_get_user_displayname( $possible_friend );
				?>
					<div class="item-avatar">
						<a href="<?php echo $member_link; ?>"><?php echo bp_core_fetch_avatar( array( 'type' => 'thumb', 'width' => 50, 'height' => 50, 'item_id' => $possible_friend ) ); ?></a>
					</div>

					<div class="item">
						<div class="item-title">
							<a href="<?php echo $member_link; ?>"><?php echo $member_name; ?></a>
						</div>
					</div>
					<div class="action">
					<?php bp_friend_suggest_hide_link( $possible_friend ); ?>
					<?php bp_add_friend_button( $possible_friend ); ?>
					</div>
					<div class="clear"></div>

				</li>

			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<div id="message" class="info">
			<p><?php _e( "We don't have enough details to suggest a friend yet", 'bp-friends-suggest' ) ?></p>
		</div>

	<?php endif; ?>

	<?php
}