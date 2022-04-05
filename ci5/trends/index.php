<?php


    define('CI5_TABLE','ci5') ; 

	require '../../conn.php' ;
	require '../../functions.php' ; 
	require '../../common/classes/CanDataFormat.php' ;
	require '../classes/CanCI5.php' ; 

	header('Access-Control-Allow-Origin: *');

    // debug( $_GET ) ; 

	// main params
	$get_mode 		= ( isset($_GET['mode']) && !empty( $_GET['mode'] )) ? filter_input( INPUT_GET , 'mode' ) : 'populations' ; 
	$get_mode_population = ( isset($_GET['mode_population']) && !empty( $_GET['mode_population'] )) ? filter_input( INPUT_GET , 'mode_population' ) : 1 ; 

	// get settings 
	$get_sex 		= ( isset($_GET['sex']) && !empty( $_GET['sex'] )) ? filter_input( INPUT_GET , 'sex' ) : 0 ; 				// default male
	$get_type 		= ( isset($_GET['type']) && !empty( $_GET['type'] )) ? filter_input( INPUT_GET , 'type' ) : 0 ; 				// default is incidence
	$get_cancer		= ( isset($_GET['cancer']) && !empty( $_GET['cancer'] )) ? $_GET['cancer'] : 39 ;
	$get_year		= ( isset($_GET['year']) && !empty( $_GET['year'] )) ? $_GET['year'] : false ;
	$get_volume		= ( isset($_GET['volume']) && !empty( $_GET['volume'] )) ? $_GET['volume'] : 0 ;
	$get_registry	= ( isset($_GET['registry']) && !empty( $_GET['registry'] )) ? $_GET['registry'] : 1201 ;
    $get_continent   = ( isset($_GET['continent']) && !empty( $_GET['continent'] )) ? $_GET['continent'] : 0 ;    
    $get_ethnic_group   = ( isset($_GET['ethnic_group']) && !empty( $_GET['ethnic_group'] )) ? $_GET['ethnic_group'] : 1201 ;
    $get_trends_by   = ( isset($_GET['trends_by']) && !empty( $_GET['trends_by'] )) ? $_GET['trends_by'] : 'population' ;
    // 

    $settings = [
        'trends_by' => $get_trends_by , 
		'mode' => $get_mode , 
		'mode_population' => $get_mode_population , 
		'sex' => $get_sex , 
		'type' => $get_type , 
		'cancer' => $get_cancer , 
		'year' => $get_year , 
		'volume' => $get_volume , 
		'registry' => $get_registry , 
        'continent' => $get_continent , 
        'ethnic_group' => $get_ethnic_group , 
	] ; 
    
    // init data
    $total = 0 ; 
	$output = [] ; 
    $values = [] ; // extra values

	switch( $get_mode )
	{
        // ---------------------------------------------------------------------------------------------------------------------
        case 'cancer_site' : 
            
            if ( empty( $_GET['population'] ))
                $output = CanCI5::getCancers() ; 
            else
                $output = CanCI5::getCancerById( $get_population ) ; 
            
            break ; 

        case 'registry' : 
            
            if ( empty( $_GET['registry'] ))
                $output = CanCI5::getRegistries() ; 
            else
                $output = CanCI5::getRegistryById( $get_registry ) ; 
            
            $total  = count( $output ) ;
            break ; 

         case 'registry_ethnic' : 

            $output = CanCI5::getRegistriesEthnicGroups( $get_registry ) ; 

            break ; 

        case 'years' : 

            $output = CanCI5::getYears( $get_registry ) ; 

            break ; 

         case 'population' : 
            
            $output = CanCI5::getPopulation(  $settings ) ; 

            break ; 

        case 'cases-areas' : 

            $settings['full_year'] = true ; 
            $output = CanCI5::getTrendsCases( $settings ) ; 
            break ; 

        case 'cases' : 

            // special case when 0 (get both sex)
            if ( $settings['sex'] == 0 )
            {
                $settings['sex'] = 1 ; 
                $output_males = CanCI5::getTrendsCases( $settings ) ; 

                $settings['sex'] = 2 ; 
                $output_females = CanCI5::getTrendsCases( $settings ) ; 

                $output = [ 'males' => $output_males  , 'females' => $output_females ] ; 
            }
            else
            {
                $output = CanCI5::getTrendsCases( $settings ) ; 
            }

            $values = CanCI5::getRanges( $settings ); 

            break ; 

        case 'top_cases':

            $output = CanCI5::getTopCancersPerYear( $settings ) ; 
            
            break ; 

        case 'populations' : 

            $output = CanCI5::getPopulationByRegistry( $settings ) ; 
            $keys = array_keys($output['females']) ;

            $values = [ 'min' => $keys[0] , 'max' => $keys[ count($keys) -1 ] ] ;
            break ;  

    } // end switch 

    CanDataFormat::output([ 'dataset' => $output , 'values' => $values ]) ; 