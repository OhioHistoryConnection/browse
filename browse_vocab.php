<?php
	
	/**
	* Code snippet for a CONTENTdm browse
	* Phil Sager <psager@ohiohistory.org>.
	* 
	* This snippet is free to use, modify, or distribute.
	* However, the author accepts no responsibility for 
	* what happens or doesn't happen with your site should  
	* you decide to implement this snippet or any variation of it.
	* However, if you make substantial improvements, myself and 
	* everyone in the CDM community will thank you for posting
	* an update to the CONTENTdm forum.
	*
	* Note: this code provides an easy, but not the best, way  
	* of listing out linked vocabulary terms in CONTENTdm in that a 
	* dmGetCollectionFieldVocabulary or cache call is made EVERY 
	* time a letter or number is clicked on.
	*/
	 
	// name of this browse script
	$THIS_FILE = "[this_file.php]"; // e.g. "index.php"
	// path to browse directory containing browse script
	$BROWSE_PATH = "[/path/to/browse]"; // e.g. "/cdm/browse"
	// Browse page URL
	$THIS_PAGE = "http://".$_SERVER["HTTP_HOST"] . $BROWSE_PATH . "/". $THIS_FILE;
	// [host] = your CDM host or local domain
	$CDM_HOST = "http://[host]"; // e.g. "http://cdmNNNNN.contentdm.oclc.org" or "http://digitallibrary.someplace.edu", etc.
	// [web services] domain of your CDM web services
	$CDM_WEBSERVICES = "https://[web services]"; // e.g. "https://serverNNNNN.contentdm.oclc.org"
	$COLL_ALIAS = "[alias]"; // e.g. "pNNNNNcollNN" or "postcards"
	// alphanumeric array for navigation purposes
	$LETTER_VALUES = array_merge(range(1,9), range('A','Z')); 
	
	// set the default browse field with a valid CDM field nickname
	$browse_field = "subjec";
	// get a user-selected browse value if one exists. This might come from another page.
	// e.g. http://cdmNNNNN.contentdm.oclc.org/cdm/browse/index.php?field=creato
	if (isset($_GET['field'])) { $browse_field = strip_tags(trim($_GET['field'])); } 
	// set default browse letter ("1" for subjects; "A" for places or contributor names)
	$browse_letter = ($browse_field == "subjec") ? "1" : "A";
	// get the user-selected letter or number
	if (isset($_GET['letter'])) { $browse_letter = $_GET['letter']; }
	// display field name here is "place", but is actually mapped to "coveraa" for purposes of retrieving place vocabulary
	$dc_field = ($browse_field == "place") ? "coveraa" : $browse_field;
	
	// get the vocubulary in an array
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $CDM_WEBSERVICES."/dmwebservices/index.php?q=dmGetCollectionFieldVocabulary/".$COLL_ALIAS."/".$browse_field."/0/0/json");
	curl_setopt($ch, CURLOPT_HEADER, 0);  // do not return http headers
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the contents of the call
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$cdm_data_json = curl_exec($ch);	
	$browse_array = json_decode($cdm_data_json, true);

?> 

<!-- start page html, CSS, etc. here ... format and style the page however you want... -->

<?php
  
	// output navigation for listing out linked controlled vocabulary terms
	for ($i=0; $i < count($LETTER_VALUES); $i++) {
		if (is_numeric($LETTER_VALUES[$i]) && $browse_field != "subjec") { continue; }
		echo '<a href="'.$THIS_PAGE.'?letter='.$LETTER_VALUES[$i].'&field='.$browse_field.'">'.$LETTER_VALUES[$i].'</a> | ';
	}

?>

<!-- possibly additional html before the actual list begins ... -->

<?php
  
	// list out linked vocabulary terms
	echo("<b>".$browse_letter."</b>:<br>");
	for ($i = 0; $i < count($browse_array); $i++) {
		if (preg_match('/^[^0-9A-Za-z].+/', $browse_array[$i])) { continue; }
		$browse_upper = ucfirst($browse_array[$i]);
		$current_letter = substr($browse_upper, 0, 1);
		if ($current_letter == $browse_letter) {
			echo('<p><a href="'.$CDM_HOST .'/cdm/search/searchterm/'.urlencode($browse_upper).'/field/'.$dc_field.'/mode/exact/conn/and/order/nosort">'.$browse_upper.'</a></p>');
		}
	}

?>

<!-- finish out the page ... -->

