<?php

/* post a new event to meetup */

class WPGwinnett_Meetup_Post{

    public function __construct() {

// we are using updated_post_meta instead of save_post because save_post does not return the meta data.  

      add_action('updated_post_meta', array( $this,'change_meetup_posting'), 10, 4); 
	}



    function change_meetup_posting($meta_id, $post_id, $meta_key='', $meta_value=''){
        
        $post = get_post($post_id);
      
        //ensure the post is one of our events and ensure that the save meta data is finished doing its work

        if ( ($post->post_type == 'tribe_events') && ($meta_key=='_edit_lock')){

            $group = 'MEETUP_GROUP';
            // for testing 
            if (defined ( 'MEETUP_API_TESTING'))
                    $group = 'MEETUP_API_TESTING';
        
           
          // error_log(print_r($post,true));

            // get the event info from the post ID
            $eventInfo = tribe_get_event_meta($post_id);
           
           
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
           
            // check if its a new post or an update
            // we use the time stamp beause our hook doesn't have an 'update' boolean field like the save_post hook
       
              if ($post->post_date   === $post->post_modified ){
                   $url = 'https://api.meetup.com/2/events?key=' . MEETUP_API . '&group_urlname=' . $group . '/events'; 
                   $url .= '&description='. $post->post_content . '&event_hosts='.$organizerName . '&name=' .$post->post_title. '&how_to_find_us=' .$fullLocation. '&duration=' .$eventInfo['_EventDuration'][0] . '&time=' .  $time_in_ms .', ' . $endTime_in_ms ;
                   $response = wp_remote_post($url);
              }
                   else {


                    $url .= '&description='. $post->post_content . '&event_hosts='.$organizerName . '&name=' .$post->post_title. '&how_to_find_us=' .$fullLocation. '&duration=' .$eventInfo['_EventDuration'][0] . '&time=' .  $time_in_ms .', ' . $endTime_in_ms ;
                  // to be implemented
                     return;

                   }
   
           
            error_log('response is '. print_r($response, TRUE));


            
            
        }
    }

}

?>