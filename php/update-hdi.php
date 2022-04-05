<?php

	require '../conn.php' ;
	require '../functions.php' ; 

	// 
	require '../globocan/classes/CanGlobocan.php' ; 

	$tmp = CanGlobocan::getPopulations() ; 
	$populations = [] ; 
	$populations_label = [] ; 

	foreach( $tmp as $population )
	{
		if ( (int)$population['AREA'] == 99 || (int)$population['CONTINENT'] == 0 ) continue ; 

		$populations[ $population['COUNTRY'] ] = $population ; 
		$populations_label[ $population['LABEL'] ] = $population ; 
	}

	// HDI
	if (($handle = fopen("data/hdi2012.csv", "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	        $num = count($data);
	        if ($data[0]=='COUNTRY')continue; 
	        $query =  "UPDATE globocan2012_country SET hdi_2012 = {$data[1]} WHERE COUNTRY = {$data[0]} LIMIT 1 ";
	        // $sql .'<br>';
	        // $result = $o_bdd->query( $query ) ; 

	        // echo "$result: sql<br>";

	        $row++;
	    }
	    fclose($handle);
	}

	if (($handle = fopen("data/countries_un.csv", "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	        $num = count($data);
	        if ($data[0]=='country')continue; 

	        $query =  "UPDATE globocan2012_country SET LABEL = '{$data[10]}' WHERE COUNTRY = {$data[9]} LIMIT 1 ";
	        // echo $query .'<br>';
	        // $result = $o_bdd->query( $query ) ; 

	        // echo "$result: sql<br>";

	        $row++;
	    }
	    fclose($handle);
	}

	// exit(); 

	if (($handle = fopen("data/hdi.csv", "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	        $num = count($data);

	        if ($data[1]=='HDI Rank')continue; 

	      	$state = ($data[7] == "Member state") ? 1 : 0 ; 
	         
	        if ( !isset( $populations_label[ $data[2] ] )){
	        	// echo " Error with => {$data[2]} not found <br> ";
	        }
	        else{
	        	$query =  "UPDATE globocan2012_country SET hdi_2012 = '{$data[25]}', hdi_2013 = '{$data[26]}', hdi_2014 = '{$data[27]}' , hdi_2015 = '{$data[28]}' WHERE COUNTRY = {$populations_label[ $data[2] ]['COUNTRY']} LIMIT 1 ";
	       
	        	// echo "$query <br>";
	        	$result = $o_bdd->query( $query ) ; 
	        }
	        

	        $row++;
	    }
	    fclose($handle);
	}




