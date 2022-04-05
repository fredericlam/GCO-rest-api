<?php

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
    
    // 

    $settings = [
		'mode' => $get_mode , 
		'mode_population' => $get_mode_population , 
		'sex' => $get_sex , 
		'type' => $get_type , 
		'cancer' => $get_cancer , 
		'year' => $get_year , 
		'volume' => $get_volume , 
		'registry' => $get_registry , 
        'continent' => $get_continent
	] ; 
    
    // init data
    $total = 0 ; 
	$output = [] ; 

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

        case 'registry_continent' : 

            $output = CanCI5::getRegistriesByContinent( $get_continent ) ; 

            break ; 

        case 'registry_ethnic' : 

            $output = CanCI5::getRegistriesEthnicGroups( $get_registry ) ; 

            break ; 

        case 'volume' : 
            
            if ( empty( $_GET['volume'] ))
                $output = CanCI5::getVolumes() ; 
            else
                $output = CanCI5::getVolumeById( $get_volume ) ; 
            break ; 

        case 'volume_registry' : 

            $output = CanCI5::getVolumeByRegistry( $get_registry ) ; 

            break ; 

        case 'population' : 
            
            $output = CanCI5::getPopulation(  $settings ) ; 

            break ; 
                            
        case 'cases' : 

            if ( empty( $_GET['by_registry'] ) && empty( $_GET['by_continent'] ) && empty( $_GET['by_cancer']) && empty( $_GET['by_volume']) )
                $output = CanCI5::getCasesByFilters( $settings ) ; 
            else if ( !empty( $_GET['by_registry'] ))
                $output = CanCI5::getCasesByRegistry( $settings ) ; 
            else if ( !empty( $_GET['by_continent'] ))
                $output = CanCI5::getCasesByRegistry( $settings ) ; 
            else if ( !empty( $_GET['by_cancer'] ))
                $output = CanCI5::getCasesByCancer( $settings ) ; 
            else if ( !empty( $_GET['by_volume'] ))
                $output = CanCI5::getCasesByVolumes( $settings ) ; 

            break ; 

    } // end switch 

    CanDataFormat::output([ 'dataset' => $output ]) ; 