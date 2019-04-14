<?php

/* post a new event to meetup */

class WPGwinnett_Meetup_Post{

    public function __construct() {
      add_action('save_post', array( $this,'change_meetup_posting'), 99, 3); 
	}



    function change_meetup_posting($post_id, $post, $update ) {
    
          //check to see if we have the correct post type, status, permissions and we 
          // are not doing an auto save, a bulk edit or a cron

          if ( $post->post_type != 'tribe_events')
              return;
      
          if ($post->post_status != "publish")
          return;

          if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
            return;
          }
          if ( isset( $post->post_type ) && 'revision' == $post->post_type ) 
              return;
        
      
          if ( defined( 'DOING_CRON' ) && DOING_CRON )
            return;

          if ( !current_user_can( 'publish_tribe_events', $post_id ) ) 
            return;
        

    
          $group = 'MEETUP_GROUP';
          // for testing 
          if (defined ( 'MEETUP_API_TESTING'))
                  $group = 'MEETUP_API_TESTING';
      
           
          // error_log(print_r($post,true));

           // get the event info from the post ID
			      $eventStreet=  tribe_get_address( $post_id );
            $eventCity =  tribe_get_city($post_id);
            $eventState = tribe_get_state($post_id);
            $eventZip = tribe_get_zip($post_id);
           
			
		
           
		 
            // get the event info from the post ID
            $eventInfo = tribe_get_event_meta($post_id);
		      //	error_log('event info ' . print_r($eventInfo, true));
           // get the organizers names
            $organizerName = '';
            $numItems = count($eventInfo['_EventOrganizerID']);
            $i = 0; 

            foreach($eventInfo['_EventOrganizerID'] as $e)
            {
              $organizerName .=  tribe_get_organizer($e);
              if(++$i != $numItems) {
                $organizerName .= ', ';

            }
            


          }
          
          
          // get the address
            
            $eventStreet=  tribe_get_address( $post_id );
            $eventCity =  tribe_get_city($post_id);
            $eventState = tribe_get_state($post_id);
            $eventZip = tribe_get_zip($post_id);
            
             // Meetup requires start and end time in milliseconds -  this was found via the $response field 
             // documentation doesn't state this 

            $eventDate = strtotime($eventInfo['_EventStartDate'][0] ); // get unix timestamp
            $time_in_ms = $eventDate * 1000;
           
            $eventEndDate = strtotime($eventInfo['_EventEndDate'][0]);
            $endTime_in_ms = $eventEndDate * 1000;
           
           
           
            $fullLocation = $eventStreet . ' ' . $eventCity . ' ' . $eventState . ' ' . $eventZip;
           
                   
       
              
            $url = 'https://api.meetup.com/2/events?key=' . MEETUP_API . '&group_urlname=' . $group . '/events'; 
            $url .= '&description='. $post->post_content . '&event_hosts='.$organizerName . '&name=' .$post->post_title. '&how_to_find_us=' .$fullLocation. '&duration=' .$eventInfo['_EventDuration'][0] . '&time=' .  $time_in_ms .', ' . $endTime_in_ms ;
          // error_log('url is ' . $url);

				     $response = wp_remote_post($url);
             
           
            error_log('response is '. print_r($response, TRUE));


            
            
         
    }

}

?>