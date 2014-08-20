<?php
	
	/* This script */
	// $THIS_PAGE = "http://".$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; 
	/* CDM host domain */
	// $CDM_HOST = "http://www.somedomain.org"; 
	/* CDM Web Services URL */
	// $CDM_WEB_SERVICES = 'https://www.somedomain.org'; 
	// Collection alias from which you want to retrieve controlled vocabulary
	// $COLL_ALIAS = "p267401coll32";
	/* Letters and numbers for navigation */
	// $LETTER_VALUES = array_merge(range(1,9), range('A','Z'));
	/* array of parameter names and corresponding DC fields */
	// $field = array("subjec"=>"subjec","title"=>"title","place"=>"coveraa","contri"=>"contri");
	/* Only relevant if you want to browse specific field values */
	// $CUSTOM_BROWSE_FIELD = $field['contri'];
	/* custom browse values */
	// $custom_values = file('custom.txt');
	/* set the default browse field */
	// $browse_field = "subjec";
	
	// Above values could be put in a config file
	include('conf/config.php');
	
	// get the user-selected browse value if one exists
	if (isset($_GET['field'])) { $browse_field = strip_tags(trim($_GET['field'])); }
	// set default browse letter ("1" for subjects; "A" for places or particpant names)
	$browse_letter = ($browse_field == "subjec") ? "1" : "A";
	// get the user-selected letter or number
	if ( isset($_GET['letter']) ) { $browse_letter = strip_tags(trim($_GET['letter'])); }

	if ($browse_field != $CUSTOM_BROWSE_FIELD) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $CDM_WEB_SERVICES . "/dmwebservices/index.php?q=dmGetCollectionFieldVocabulary/" . $COLL_ALIAS . "/" . $browse_field . "/0/0/json");
		curl_setopt($ch, CURLOPT_HEADER, 0);  // do not return http headers
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the contents of the call
		curl_setopt($ch, CURLOPT_TIMEOUT, 90); // same as php.ini max_execution_time
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$cdm_data_json = curl_exec($ch);
		$browse_array = json_decode($cdm_data_json, true);
	} else {
		$browse_array = $CUSTOM_BROWSE_ARRAY;
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>Ohio Memory Browse</title>
	<link rel="stylesheet" href="css/style.css" />
</head>
<body>

<div id="navcontainer">
	<ul id="navlist">
		<?php
			for ($i=0; $i < count($LETTER_VALUES); $i++) {
				if (is_numeric($LETTER_VALUES[$i]) && $browse_field != "subjec") { continue; }
				echo '<li><a href="'.$THIS_PAGE.'?letter='.$LETTER_VALUES[$i].'&field='.$browse_field.'">'.$LETTER_VALUES[$i].'</a></li>';
			}
		?>
	</ul>
</div>
<div id="browseContainer">

	<div id="tabs">
		<?php
			echo("<b>".$browse_letter."</b>:<br>");
			for ($i = 0; $i < count($browse_array); $i++) {
				if (preg_match('/^[^0-9A-Za-z].+/', $browse_array[$i])) { continue; }
				$browse_upper = ucfirst($browse_array[$i]);
				$current_letter = substr($browse_upper, 0, 1);
				if ($current_letter == $browse_letter) {
					echo('<p><a href="'.$CDM_HOST .'/cdm/search/searchterm/'.preg_replace('/\\//','%252F',$browse_upper).'/field/'.$field[$browse_field].'/mode/exact/conn/and/order/nosort">'.$browse_upper.'</a></p>');
				}
			}
		?>
	</div>
  
  	<!-- anything else to add -->
	<?php include('custom.inc'); ?>

</body>
</html>