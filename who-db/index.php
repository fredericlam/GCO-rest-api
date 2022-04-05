<?php

	include '../conn.php' ; 
	include '../functions.php' ; 
	require 'classes/CanWhoDB.php' ; 
	require '../common/classes/CanDataFormat.php' ;  	

	// get parameters
	$get_mode 			= isset($_GET['mode']) ? filter_input( INPUT_GET , 'mode' ) : 'cancer' ; 
	$get_sex 			= isset($_GET['sex']) ? filter_input( INPUT_GET , 'sex' ) : 1 ; 				// default male
	$get_country 		= isset($_GET['population']) ? filter_input( INPUT_GET , 'population' ) : 4080 ; // default is france (local country)
	$get_multiple_population = isset($_GET['multiple_population']) ? explode('-',$_GET['multiple_population']) : [4080] ; // multiple population
	$get_type 			= isset($_GET['type']) ? filter_input( INPUT_GET , 'type' ) : 0 ; 				// default is incidence
	$get_statistic		= isset($_GET['statistic']) ? $_GET['statistic'] : 0 ;
	$get_cancers		= isset($_GET['cancers']) ? $_GET['cancers'] : 29 ;
	$get_year			= isset($_GET['year']) ? $_GET['year'] : 3 ; 

	$output 			= [] ; 
	$max 				= 0 ; 

	$parameters			= [
		'type' 					=> $get_type , 
		'cancers' 				=> $get_cancers , 
		'population' 			=> $get_country , 
		'sex' 					=> $get_sex , 
		'multiple_population' 	=> $get_multiple_population
	] ; 

	switch( $get_mode )
	{
		case 'ages' : 
			switch( $get_type )
			{
				case 1 : 
					$output = CanWhoDB::getWStdAges() ; 
					break ; 

				case 2 : 
					$output = CanWhoDB::getWStdPosAges() ; 
					break ; 
			}
			
			break ; 

		case 'cancer' : 
			$cancers = CanWhoDB::getCancers( $parameters ) ; 
			$output = array_values( $cancers ) ; 
			sksort( $output , 'LABEL' , true ) ; 
			break ; 

		case 'countries' : 

			$countries = CanWhoDB::getCountries( $parameters );
			$output = array_values( $countries ) ; 
			sksort( $output , 'LABEL' , true ) ; 
			break ; 

		case 'populations' : 

			$output = CanWhoDB::getPopulations( $parameters );
			$max = CanWhoDB::getMaxPerPopulations( $get_multiple_population ) ; 

			break ; 
	}

	CanDataFormat::output([ 'dataset' => $output , 'max' => $max ]) ; 