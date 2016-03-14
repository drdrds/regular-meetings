<?php

Class DS_regular_meetings {

	private static $instance;  //implementing singleton design pattern

	protected $mt_classes;    // Array of Meeting Time Classes;
	protected $meeting_times; // Array of Meeting Time Objects specifying the weekly meeting times;
	protected $meetings;      // Array of meeting objects containting the actual meetings;
	protected $nbr_mtgs_series; 
	
	public function __construct() {

		if (!self::$instance) {
			self::$instance = $this;
		
			$this->mt_classes = ARRAY ( 'DS_Meeting_Times' => 'Every week', 
		        	                    'DS_MT_Some_Weeks' => 'certain weeks every month',
		                	            'DS_MT_Some_Weeks_Some_Months' => 'certain weeks & certain months');
		       if (class_exists( 'DS_CS_Sundays')) { $this->mt_classes['DS_CS_Sundays'] = 'CS Sunday Services'; }
		                	            
		}  
		return self::$instance;
		
	}
	
	protected function load_meeting_times () { 
		#delete_option('DS_meeting_times' ) ;
		$this->meeting_times = get_option ( 'DS_meeting_times', Array ( new DS_Meeting_Times( ) ));
		}	
		
	protected function save_meeting_times () {	
		usort( $this->meeting_times, array( $this, 'compare_meeting_times') );
		update_option( 'DS_meeting_times' , $this->meeting_times );
	}	
	
	protected function load_meetings () { 
		$this->meetings = get_option ( 'DS_upcoming_meetings', Array() );
		$ts_now=strtotime('-5 minutes'); 
		if ($this->meetings<>NULL) 
			while ( (count($this->meetings) > 0 ) && ($this->meetings[0]->get_timestamp() < $ts_now)) {
				$discard=array_shift($this->meetings); 
			}
		
		}	
		
	protected function save_meetings ($meetings=null) {
		if ($meetings<>null) {$this->meetings=$meetings;}
		if ($this->meetings==null) $this->meetings=ARRAY();
		if (count($this->meetings) > 1) {usort( $this->meetings, array( $this, 'compare_meetings') );}
		update_option( 'DS_upcoming_meetings' , $this->meetings );
	}	
	
	protected function get_meetings_to_display ( $nbr, $ids=null) {
		if (null==$this->meetings) {$this->load_meetings();}
		$meetings_to_display=$this->filtered_meetings($ids);
		if (count($meetings_to_display)<$nbr) { // add meetings
			$WeekCommencing=strtotime( 'last sunday');
			$wk=0;			
			if (count($meetings_to_display)>0) {
				while ($WeekCommencing < $this->meetings[count($this->meetings)-1]->get_timestamp())
					{ $wk++; $WeekCommencing=strtotime('+ 7days',$WeekCommencing); }
			}
			$earliest = strtotime('today');
			$this->load_meeting_times(); 
			while (count($meetings_to_display) < $nbr && ($wk<52*5) ) {  // add events week by week until we have enough; but only search 5 years!
				foreach( $this->meeting_times as $MTO ) {  // loop through the Meeting Time Objects
					$mtg=$MTO->Weekly_Meeting($WeekCommencing,$earliest);
					if ($mtg<>FALSE ) {
						$this->meetings[]=$mtg;  //if there is a meeting this week add it to the list of meetings;
						if ((null==$ids) OR in_array($mtg->seriesId,$ids)) { $meetings_to_display[]=$mtg;}
					}
				}		
				$wk++;
				$WeekCommencing=strtotime('+ 7days',$WeekCommencing);
			}
			$this->save_meetings();
		 }
		$meetings_to_display = array_slice( $meetings_to_display, 0, $nbr);
		
				
		return $meetings_to_display;

	}
	
	
	public function add_upcoming_meetings ( $min=9 ) {
		
		$meetings=ARRAY();
		$WeekCommencing=strtotime( 'last sunday');
		$wk=0;
		$this->load_meeting_times(); 
		$earliest = strtotime('today');
		while (count($meetings) < $min && ($wk<52*5) ) {  // add events week by week until we have enough; but only search 5 years!
			foreach( $this->meeting_times as $MTO ) {  // loop through the Meeting Time Objects
				$mtg=$MTO->Weekly_Meeting($WeekCommencing,$earliest);
				if ($mtg<>FALSE ) {$meetings[]=$mtg;}   //if there is a meeting this week add it to the list of meetings;
			}
		$wk++;
		$WeekCommencing=strtotime('+ 7days',$WeekCommencing);
		}
		$this->save_meetings( $meetings );
		
 	}
 	
 	public function filtered_meetings ( $seriesIds=null ) {
 		
 		if ($seriesIds==null) {
 			return $this->meetings;
 		}
 		$filtered_mtgs=ARRAY();
 		foreach ($this->meetings as $mtg ) {
 			if ( in_array($mtg->seriesId, $seriesIds)) {
 				$filtered_mtgs[]=$mtg;	
 			}
 		}
 		return $filtered_mtgs;
 	}
 	
 	
 	public function display_upcoming_meetings ( $nbr=9,  $displayOptions, $seriesIds="") {
 		
 		$returnHTML="";
		$meetings_to_display=$this->get_meetings_to_display($nbr, $seriesIds);
		
		foreach ($meetings_to_display AS $mtg) {
			$returnHTML.= $mtg->outputHTML( $displayOptions );
		}
		return $returnHTML;
	} 	

	public function display_admin_settings_page () {

			
		if(!empty($_POST['meeting_submit'])) {$this->handle_admin_settings_form();} // handle the form if submitted
		$returnHTML='<div id="icon-options-general" class="icon32"> <br/></div>';
	        $returnHTML.= '<div class ="wrap">';
	        $returnHTML.= '<h2> Regular Meeting settings </h2>';
	        $returnHTML.= 'Please enter your regular weekly meeting times<br/><br/>';
		$returnHTML.= '<form method="post" action="'.$_SERVER['REQUEST_URI'].'">';
		
		$this->load_meeting_times(); 
		
		$i=1;
		foreach ($this->meeting_times as $meeting) {
			$returnHTML.= '<div>'.$meeting->admin_form_elements($i).'</br></br></div>';
			$i++;
		}
		$returnHTML.= '<br/><br/> Add another? <input type="checkbox" name="add-another" /> occurring ';
		$returnHTML.='<select name="DSNC">';
		foreach ( $this->mt_classes AS $class => $description ){
			$returnHTML.="<option value='$class' > $description </option>"; 
		}
		$returnHTML.=" </select>";
		
		
		$returnHTML.= '<p class="submit"><input type="submit" name="meeting_submit" class="button-primary" value="Save Changes" /></p>';
		$returnHTML.= '</form>';	
		$returnHTML.= 'See this <A href="http://plugins.drdscott.com/plugins/regularmeetings" target="new">post </A>on drdscott.com for usage instructions.'; 
		$returnHTML.= '</div>';
	
		
		echo $returnHTML;
		
		echo $this->display_upcoming_meetings ( 10 , Array( 'display_name' => TRUE, 'display_description' => TRUE, 'date_format' => '  jS F  g:ia') ); // just a test
}
 	
 	public function handle_admin_settings_form () {
	
		$i=1; 
		$meeting_times=ARRAY();
		While (isset($_POST["DSC_$i"])) {
			if (( FALSE===(isset($_POST["DS_delete_$i"]))) && class_exists( $_POST["DSC_$i"])) {
				$meeting_times[$i] = new $_POST["DSC_$i"]($_POST["DS_Id_$i"]);
				$meeting_times[$i]->update_details($i);
			}
		$i++;
		}	
		if (isset($_POST['add-another']) && class_exists($_POST['DSNC'] )) $meeting_times[$i]= new $_POST['DSNC'];
		$this->meeting_times=$meeting_times;
		$this->save_meeting_times();	
		$this->add_upcoming_meetings(20);  # should clear and recalculate meetings - later add option to add only new or changed meetings...
	}	
 
	
 	protected function compare_meeting_times ( $a, $b ) {
	// Compares Two meeting times objects this function is used by usort to sort the Meetings Times array
	 	if ($a->day == $b->day) {  // Compare days then hours then minutes then...
       			if ($a->hour==$b->hour) {
         			if  ($a->mins==$b->mins) {
              				return 0;
              			} else return ($a->mins < $b->mins) ? -1 : 1;
        		} else return ($a->hour < $b->hour) ? -1 : 1;
    		}
    		return ($a->day < $b->day) ? -1 : 1;
	}
 
 	protected function compare_meetings ( $a, $b ) {
	// Compares Two meeting objects this function is used by usort to sort the Meetings array before saving it
    		return ( $a->get_timestamp() < $b->get_timestamp() ) ? -1 : 1;
	}
 	
}