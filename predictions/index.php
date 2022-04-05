<?php

	include '../conn.php' ; 
	include '../functions.php' ; 
	require 'classes/CanPredictions.php' ; 
	require '../globocan/classes/CanGlobocan.php' ; 
	require '../globocan/classes/CanCases.php' ;
	require '../common/classes/CanDataFormat.php' ;  	

	require '../common/geography.php' ; 
	
	header('Access-Control-Allow-Origin: *');

	// include common variables from urls
	require '../common/settings.php' ; 

	switch( $get_mode )
	{
		case 'cancer' : 
			$cancers = CanGlobocan::getCancers( true ,false,$settings) ; 
			$output = array_values( $cancers ) ; 
			sksort( $output , 'LABEL' , true ) ; 
			CanDataFormat::output([ 'dataset' => $output , 'max' => $max ]) ; 
			break ; 

		case 'population' : 

			$output = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $settings );
			CanDataFormat::output([ 'dataset' => $output , 'max' => $max ]) ; 
			break ; 

		case 'predictions' : 

			$output = CanPredictions::getPredictions( $settings ) ; 
			CanDataFormat::output($output) ; 

			break ;

		case 'predictions_cases' : 
		case 'predictions_deaths' : 
			
			$CANCERS = CanGlobocan::getCancers(true,false,$settings) ;
			
			if ( $parameters['cancers'] == 'all' )
			{
				foreach( $CANCERS as $cancer )
				{
					if ( (int)$cancer['id'] == 13 || (int)$cancer['id'] == 29 ) continue ; 
					$parameters['cancers'] = (int)$cancer['id'] ; 
					$tmp = CanCases::getPredictions( $settings );
					$output[] = $tmp['data'] ; 
				}
			}
			else
			{
				// $parameters['cumulate'] = true ; 
				$tmp = CanCases::getPredictions( $settings );
				$output = $tmp['data'] ; 
				$max =  $tmp['max'] ; 
			}
			CanDataFormat::output([ 'dataset' => $output , 'max' => $max ]) ; 
			break ; 

		case 'predictions_cases_all' : 
		case 'predictions_deaths_all' :

			$output = CanPredictions::getPerPopulations( $settings ) ; 
			CanDataFormat::output([ 'dataset' => $output , 'max' => $max ]) ; 
			break ;  
	}

	