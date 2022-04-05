<?php
    
    /**
    * Script that explort data in json to merge all countries + all data per cancers
    *
    */
	require '../conn.php' ;
	require '../functions.php' ; 

    require '../common/classes/CanDataFormat.php' ;   

	require '../globocan/classes/CanGlobocan.php' ; 
	require '../globocan/classes/CanCases.php' ;
	
	header('Access-Control-Allow-Origin: *');

    // init data
    $total = 0 ; 
	$output = [] ; 

    // get all countries. 
    $s_populations  = file_get_contents( "http://www.gco.local:8080/api/globocan/" ) ; 
    $populations    = json_decode($s_populations);

    var_dump($populations) ; 

	CanDataFormat::output([ 'dataset' => $output  ]) ; 