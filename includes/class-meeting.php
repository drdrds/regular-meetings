<?php

class DS_meeting {

	protected $timestamp;
	public $seriesId;  // Id of the series this meeting is part of. ? "0" for one off meeting?
	public $name;
	public $description;
	 #public $Nbr;   // number of meeting in series? used for tracking updates????
	public $link;
	
	
	public function __construct ( $timestamp,  $seriesId,$name, $description="", $link="") {
	
		$this->timestamp = $timestamp;
		$this->seriesId = $seriesId;
		$this->name = $name;
		$this->description = $description;
		$this->link = $link;
	
	}
	
	public function get_timestamp() {return $this->timestamp; }
	
	public function outputHTML ( $displayOptions= ARRAY()  ) {
	$display_name = TRUE;
	$display_description = FALSE;
	$date_format = '  jS F  g:ia';
	if (is_array($displayOptions) ) {extract( $displayOptions ); }
	
		$returnHTML= '<div class="DS_meeting_details, Meeting_Series_$this->seriesId">';
		if ($display_name==TRUE) $returnHTML.='<span class="service-name">'.$this->name.': </span>';
		$returnHTML.='<span class="DS_datetime">'.date( $date_format, $this->timestamp).'</span>'; 
		if ($display_description==TRUE) $returnHTML.='<br/><span class="service-description">'.$this->description.'</span>';
		$returnHTML.='<br/><hr/>';
		$returnHTML.='</div>';
		
		return $returnHTML;
	
	}
	
}