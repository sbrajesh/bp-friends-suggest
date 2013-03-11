<?php
/*
Plugin Name: BP Friends Suggestions Widget
Plugin URI: http://buddydev.com/plugins/buddypress-friends-suggest/
Description: BuddyPress Friends Suggestion Widget  - displays friend suggestions for logged in users.
Version: 1.0.2
Author: gwu
Author URI: http://buddydev.com/members/gwu123/
 Last Updated: September 8, 2012
*/

add_action("wp_print_scripts","bp_friend_suggest_add_js");
function bp_friend_suggest_add_js(){
    if(!is_user_logged_in())
        return;
   $fsuggest_url=plugin_dir_url(__FILE__);//with a trailing slash
    wp_enqueue_script("friend-suggest-js",$fsuggest_url."friend-suggest.js",array("jquery"));
}

//load text domain
function friend_suggest_load_textdomain() {
        $locale = apply_filters( 'friend_suggest_load_textdomain_get_locale', get_locale() );
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );
		$mofile = apply_filters( 'friend_suggest_load_textdomain_mofile', $mofile_default );

                if ( file_exists( $mofile ) ) 
                    // make sure file exists, and load it
			load_textdomain( "bp-show-friends", $mofile );
                      
	}
}
add_action ( 'bp_init', 'friend_suggest_load_textdomain', 2 );

class BP_Friend_Suggestions_Widget extends WP_Widget {
	function __construct() {
            parent::__construct(false, $name = __( 'Friends Suggest Widget', 'bp-show-friends' ));
            }

	function widget($args, $instance) {
		global $bp;
		if(!is_user_logged_in())
                    return;//do not show to non logged in user
		extract( $args );
		echo $before_widget.
                        $before_title.
                            $instance['title'].
                        $after_title;
		bp_show_friend_suggestions_list($instance['max']);
		echo $after_widget; 				
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max'] = absint( $new_instance['max'] );

		return $instance;
	}
	function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title'=>__('Friend Suggestions','bp-show-friends'),'max' => 5 ) );
			$title = strip_tags( $instance['title'] );
			$max =absint( $instance['max'] );
			?>
			<p>
                            <label for='bp-show-friend-widget-suggest-title'><?php _e( 'Title' , 'bp-show-friends'); ?>
                                    <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat" value="<?php echo esc_attr( $title ); ?>" />
                            </label>
			</p>
			<p>
                            <label for='bp-show-friends-widget-per-page'><?php _e( 'Max Number of suggestions:', 'bp-show-friends' ); ?>
                                    <input class="widefat" id="<?php echo $this->get_field_id( 'max' ); ?>" name="<?php echo $this->get_field_name( 'max' ); ?>" type="text" value="<?php echo esc_attr( $max ); ?>" style="width: 30%" />
                            </label>
                        </p>
			
	<?php
	}	
}

//action takes place here
function  bp_show_friend_suggestions_list($limit=5){
	global $bp;
	$user_id = get_current_user_id();
       
        $my_friends=(array)friends_get_friend_user_ids($user_id);//get all friend ids
       
        $my_friend_req=(array)friend_suggest_get_friendship_requested_user_ids($user_id);//get all friend request by me
     
        $possible_friends=array();//we will store the possible friend ids here
        foreach($my_friends as $friend_id)
                $possible_friends=array_merge($possible_friends,(array)friends_get_friend_user_ids($friend_id));
     

        //we have the list of friends of friends, we will just remove
        //now get only udifferent friend ids(unique)
        $possible_friends=array_unique($possible_friends);

        //intersect my friends with this array
        $my_friends[]=get_current_user_id();//include me to
       
        $excluded_users=get_user_meta($user_id,"hidden_friend_suggestions",true);
     
        $excluded_users=$excluded_users;
        $excluded_users=array_merge($my_friends,(array)$excluded_users,(array)$my_friend_req);

        //we may check the preference of the user regarding , like not add
        
        $possible_friends=array_diff($possible_friends,$excluded_users);//get those user who are not my friend and also exclude me too
        if(!empty($possible_friends)){
           shuffle($possible_friends);//randomize
           $possible_friends=array_slice($possible_friends, 0,$limit);
        }
         
         
        if(!empty($possible_friends)):?>
                       <ul id="members-list" class="item-list suggested-friend-item-list">
                        <?php 	foreach ($possible_friends as $possible_friend):?>
                            <li>
                               <?php $member_link= bp_core_get_user_domain($possible_friend);
                                     $member_name=  bp_core_get_user_displayname($possible_friend);

                                ?>
                                <div class="item-avatar">
                                        <a href="<?php echo $member_link;?>"><?php echo bp_core_fetch_avatar(array('type'=>'thumb','width'=>25,'height'=>25,'item_id'=>$possible_friend)); ?></a>
                                </div>

                                    <div class="item">
                                            <div class="item-title">
                                                    <a href="<?php echo $member_link; ?>"><?php echo $member_name; ?></a>
                                             </div>
                                    </div>
                                     <div class="action">
                                            <?php  bp_friend_suggest_hide_link($possible_friend); ?>
                                            <?php bp_add_friend_button( $possible_friend ); ?>
                                    </div>
                                    <div class="clear"></div>
            
                            </li>
			
                        <?php endforeach;?>
                              </ul>
                     <?php else:?>
                      <div id="message" class="info">
                        <p><?php _e( "We don't have enough details to suggest a friend yet", 'buddypress' ) ?></p>
                    </div>

                            <?php endif;?>
                    
   <?php
}
function friend_suggest_register_widget(){
  add_action('widgets_init', create_function('', 'return register_widget("BP_Friend_Suggestions_Widget");') );
  
}
add_action("bp_loaded","friend_suggest_register_widget");

function bp_friend_suggest_hide_link($possible_friend){
    $url=bp_get_root_domain()."/remove-friend-suggestion/?suggest_id=".$possible_friend."&_wpnonce=".wp_create_nonce('friend-suggestion-remove-'.$possible_friend);
?>
<span class="remove-friend-suggestion"><a href="<?php echo $url;?>" title="Hide this suggestion">x</a></span>
<?php
}

//ajax hiding of suggestion
add_action("wp_ajax_friend_suggest_remove_suggestion","friend_suggest_remove_suggestion");

function friend_suggest_remove_suggestion(){
  $suggestion_id=$_POST['suggestion_id']  ;
 check_ajax_referer('friend-suggestion-remove-'.$suggestion_id);

 if(empty ($suggestion_id)||!is_user_logged_in())
     return;
 global $bp;
 $user_id=$bp->loggedin_user->id;
$excluded=get_user_meta($user_id,"hidden_friend_suggestions" ,true);
$excluded=(array)($excluded);
$excluded[]=$suggestion_id;
update_user_meta($user_id,"hidden_friend_suggestions",$excluded);
    exit(0);
}

function friend_suggest_get_friendship_requested_user_ids( $user_id ) {
		global $wpdb, $bp;

		return $wpdb->get_col( $wpdb->prepare( "SELECT friend_user_id FROM {$bp->friends->table_name} WHERE initiator_user_id = %d AND is_confirmed = 0", $user_id ) );
	}

  //fix bp bug of not showing the add friend button on profile pages

   add_filter('bp_get_add_friend_button', "friend_suggest_fix_bp_button_bug");

   function friend_suggest_fix_bp_button_bug($button){
       if($button['id']=='not_friends')
           $button['block_self']=false;
       return $button;
   }
?>