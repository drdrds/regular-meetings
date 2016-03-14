<?php
class DS_Meeting_Times {
 
 	public  $name;
	public  $day;
	public  $hour;
	public  $mins;
	public  $seriesId;
	static $maxId=0;

	public function __construct ( $id=null, $name='', $day=0, $hour=11, $mins='00') {

		if ($id<>null) {  DS_Meeting_Times::$maxId=max(DS_Meeting_Times::$maxId,$id);$this->seriesId=$id;}
		else { 	DS_Meeting_Times::$maxId++; $this->seriesId=DS_Meeting_Times::$maxId;}
		$this->name=$name;
		$this->day=$day;
		$this->hour=$hour;
		$this->mins=$mins;
	}
	
	public function Weekly_Meeting ($WeekCommencing, $earliest) {
	
            	$timestamp=strtotime(" + $this->day Days $this->hour hours $this->mins minutes", $WeekCommencing);
		if ($timestamp > $earliest ) { 
			$mtg= new DS_meeting( $timestamp, $this->seriesId, $this->name ); } 
		else {$mtg=FALSE;} 
		return $mtg;
	}
	

	public function admin_form_elements ($i, $text="Every week") {
		
		$returnHTML='<input type="hidden" name="DSC_'.$i.'" value="'.get_class($this).'">';
		$returnHTML.='<input type="hidden" name="DS_Id_'.$i.'" value="'.$this->seriesId.'">';
		$returnHTML.= 'Id='.$this->seriesId.': <input type="text" name="DS_meeting_name-'.$i.'" value="'.$this->name.'" placeholder="Please enter the name of your event/meeting" size="40" />';
		$returnHTML.='<select name="DS_day_'.$i.'">';
		$weekdays=ARRAY ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
		for ($j=0; $j<=6; $j++) { 
			$selected= ($j==$this->day) ?  "selected='selected'" : "";
			$returnHTML.="<option $selected value='$j' > $weekdays[$j] </option>"; 
		}
		$returnHTML.=" </select>";
		$returnHTML.="<input type='number' name='DS_hour_$i' placeholder='HH' min=0 max=23 value='".$this->hour."' style='width: 50px' />:";
		$returnHTML.="<input type='number' name='DS_mins_$i' placeholder='MM' min=0 max=59 value='".$this->mins."' style='width: 50px' />";
		$returnHTML.=$text;
		$returnHTML.="<span style='float: right'> ( Delete entry? <input type='checkbox' name='DS_delete_$i' />) </span>"; 
		
		return $returnHTML;
	}
	
	public function update_details ($i) {
	// updates the properties based on the form elements output above
	
		
		$this->name=$_POST["DS_meeting_name-$i"];
		$this->day=$_POST["DS_day_$i"];
		$this->hour=$_POST["DS_hour_$i"];
		$this->mins=$_POST["DS_mins_$i"];
		
	
	}
}

Class DS_MT_Some_Weeks extends DS_Meeting_Times {

	protected $weeks; 
	
	public function Weekly_Meeting ($WeekCommencing, $earliest) {
	
            	$timestamp=strtotime(" + $this->day Days $this->hour hours $this->mins minutes", $WeekCommencing);
            	$week=floor((date('j',$timestamp)-1) / 7 ); 
            	if ($timestamp>$earliest && $this->weeks[$week]==TRUE ) { 
				$mtg= new DS_meeting( $timestamp, $this->seriesId, $this->name );	
                	} else {$mtg=FALSE;}      
		return $mtg;
	}
	
	public	function admin_form_elements ($i, $text="Certain weeks") {
			$returnHTML=parent::admin_form_elements( $i, $text);
			$returnHTML.='</br> &nbsp &nbsp &nbsp Weeks of the month? ';
			$returnHTML.="1st: <input type='checkbox' name='MTW-$i-1'";
			$returnHTML.=($this->weeks[0]==0) ?  "/>" : " checked='checked' />"; 
			$returnHTML.="2nd: <input type='checkbox' name='MTW-$i-2'";
			$returnHTML.=($this->weeks[1]==0) ?  "/>" : " checked='checked' />"; 
			$returnHTML.="3rd: <input type='checkbox' name='MTW-$i-3'";
			$returnHTML.=($this->weeks[2]==0) ?  "/>" : " checked='checked' />"; 
			$returnHTML.="4th: <input type='checkbox' name='MTW-$i-4'";
			$returnHTML.=($this->weeks[3]==0) ?  "/>" : " checked='checked' />"; 
			$returnHTML.="5th: <input type='checkbox' name='MTW-$i-5'";
			$returnHTML.=($this->weeks[4]==0) ?  "/>" : " checked='checked' />"; 
			
			$returnHTML.="";
			return $returnHTML;
		}
		
	public function update_details ($i) {
		 parent::update_details($i);		
		 $weeks=ARRAY(isset($_POST["MTW-$i-1"]),isset($_POST["MTW-$i-2"]),isset($_POST["MTW-$i-3"]),isset($_POST["MTW-$i-4"]),isset($_POST["MTW-$i-5"]) );
		$this->weeks=$weeks;
	}

}

Class DS_MT_Some_Weeks_Some_Months extends DS_MT_Some_Weeks {

	protected $months; 
	
	public function Weekly_Meeting ($WeekCommencing, $earliest) {
	
            	$timestamp=strtotime(" + $this->day Days $this->hour hours $this->mins minutes", $WeekCommencing);
            	$week=floor((date('j',$timestamp)-1) / 7 );   
            	$month=date('n',$timestamp)-1;
            	if ($timestamp>$earliest && $this->weeks[$week]==TRUE && $this->months[$month]==TRUE ) { 
				$mtg= new DS_meeting( $timestamp, $this->seriesId, $this->name );	
                	} else {$mtg=FALSE;}     
		return $mtg;
	
	}
	
	public	function admin_form_elements ($i, $text="Certain weeks & months") {
			$returnHTML=parent::admin_form_elements( $i, $text);
			$returnHTML.='<br/> &nbsp &nbsp &nbsp';
			$monthnames=ARRAY('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
			for ($j=0;$j<12; $j++) {
				$returnHTML.= $monthnames[$j].": <input type='checkbox' name='MT-$i-month-$j' "; 
				$returnHTML.=($this->months[$j]==0) ? $monthnames[$j]."/>" : "checked='checked' />  "; 
			} 
			
			return $returnHTML;
		}
		
	public function update_details ($i) {
		 parent::update_details($i);		
		 $months=ARRAY(isset($_POST["MT-$i-month-0"]), isset($_POST["MT-$i-month-1"]),isset($_POST["MT-$i-month-2"]),isset($_POST["MT-$i-month-3"]),isset($_POST["MT-$i-month-4"]),isset($_POST["MT-$i-month-5"]),isset($_POST["MT-$i-month-6"]), isset($_POST["MT-$i-month-7"]),isset($_POST["MT-$i-month-8"]),isset($_POST["MT-$i-month-9"]),isset($_POST["MT-$i-month-10"]),isset($_POST["MT-$i-month-11"]) );
		$this->months=$months;
	}

}

Class DS_CS_Sundays extends DS_Meeting_Times {

public function __construct ( $id=null, $name='Sunday Service', $day=0, $hour=11, $mins='00') {

		if ($id<>null) {  DS_Meeting_Times::$maxId=max(DS_Meeting_Times::$maxId,$id);$this->seriesId=$id;}
		else { 	DS_Meeting_Times::$maxId++; $this->seriesId=DS_Meeting_Times::$maxId;}
		$this->name=$name;
		$this->day=0;
		$this->hour=$hour;
		$this->mins=$mins;
	}
	
	public function Weekly_Meeting ($WeekCommencing, $earliest) {
	
	$service_subjects = array (
  				"God", "Sacrament", "Life", "Truth", "Love", 
  				"Spirit", "Soul", "Mind", "Christ Jesus", "Man", 
 				 "Substance", "Matter", "Reality",
 				 "Unreality", "Are Sin, Disease, and Death Real?", "Doctrine of Atonement", "Probation After Death",
 				 "Everlasting Punishment", "Adam and Fallen Man", "Mortals and Immortals", "Soul and Body",
 				 "Ancient and Modern Necromancy, alias Mesmerism and Hypnotism, Denounced",
 				 "God the Only Cause and Creator",
 				 "God the Preserver of Man",
 				 "Is the Universe, Including Man, Evolved by Atomic Force?",
 				 "Christian Science"
);
	
            	$timestamp=strtotime(" + $this->day Days $this->hour hours $this->mins minutes", $WeekCommencing);
            	$sun_week_of_year = floor(date("z",$timestamp)/7);  // z is day from 0 to 365.
  		$week_of_26 = fmod($sun_week_of_year, 26);
  		$subject = $service_subjects[$week_of_26];
 		$description="Subject  : <span class='lesson-subject'> $subject </span>";  
 
            	
            	
		if ($timestamp > $earliest ) { 
			$mtg= new DS_meeting( $timestamp, $this->seriesId, $this->name, $description ); } 
		else {$mtg=FALSE;} 
		return $mtg;
	}
	

	public function admin_form_elements ($i, $text="Every week") {
		
		$returnHTML='<input type="hidden" name="DSC_'.$i.'" value="'.get_class($this).'">';
		$returnHTML.='<input type="hidden" name="DS_Id_'.$i.'" value="'.$this->seriesId.'">';
		$returnHTML.= 'Id='.$this->seriesId.': CS <input type="text" name="DS_meeting_name-'.$i.'" value="'.$this->name.'" size="40" />';
		$returnHTML.='<select name="DS_day_'.$i.'">';
		$weekdays=ARRAY ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
		for ($j=0; $j<1; $j++) { 
			$selected= ($j==$this->day) ?  "selected='selected'" : "";
			$returnHTML.="<option $selected value='$j' > $weekdays[$j] </option>"; 
		}
		$returnHTML.=" </select>";
		$returnHTML.="<input type='text' name='DS_hour_$i' value='".$this->hour."' size='2' />:";
		$returnHTML.="<input type='text' name='DS_mins_$i' value='".$this->mins."' size='2' />";
		$returnHTML.=$text;
		$returnHTML.="<span style='float: right'> ( Delete entry Id=$this->seriesId? <input type='checkbox' name='DS_delete_$i' />) </span>"; 
		
		return $returnHTML;
	}
	
}

?>