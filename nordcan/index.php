<?php
    
    // require __DIR__ . '/../vendor/autoload.php';

	require '../conn.php' ;
	require '../functions.php' ; 

    require '../common/classes/CanDataFormat.php' ;   
    require '../common/classes/CanCache.php' ;   

	require 'classes/CanNordcan.php' ;
    require '../globocan/classes/CanGlobocan.php' ;

	require 'classes/CanCases.php' ;

    require '../common/geography.php' ; 
	
	header('Access-Control-Allow-Origin: *');

    // include common variables from urls
    require 'settings.php' ; 
    
    // init data
    $total = 0 ; 
	$output = [] ; 

	switch( $get_mode )
	{
        case "sandbox" : 
            $settings['year'] = 2018 ; 
            $results = CanNordcan::getCountriesPerGroup('hdi_group_2015',1); 
            foreach($results as $c ) 
            {
                var_dump($c);
                //echo "{$c[2]}<br>";
            }
            exit() ; 
            break ; 

        // ---------------------------------------------------------------------------------------------------------------------
        case 'data_sources' : 
            
            $output = CanNordcan::getDataSources() ; 
            $total  = count( $output ) ;
            break ; 
        
        // ---------------------------------------------------------------------------------------------------------------------
        case 'data_methods' : 
            
            $output = CanNordcan::getDataMethods() ; 
            $total  = count( $output ) ;
            break ; 
        
        // ---------------------------------------------------------------------------------------------------------------------
        case 'cancer_site' : 
            
            if ( empty( $_GET['population'] ) )
                $output = CanNordcan::getCancers(true,false,$settings) ; 
            else
                $output = CanNordcan::getCancerById( $get_population ) ; 
            
            $output = array_values($output);
            $total  = count( $output ) ;
            break ; 
            
        // ---------------------------------------------------------------------------------------------------------------------    
		case 'populations' : // numbers of populations
            
            if ( empty( $_GET['population'] ))
            {
                $output = CanNordcan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                
            }
            else
            {
                $output = CanNordcan::getPopulationById( $get_population ) ; 

                $output['demography'] = CanCases::getPop( [ 'country' => $get_population ] ); 

                $output['source_methods'] = CanNordcan::getDataSourceMethod( $get_population ); 
            }
                
            $total  = count( $output ) ;
            
			break ; 

        // groupings populations
        case "hubs" : 
        case "continents": 
        case "areas":
        case "regions":
        case "hdi":
        case "who": 
        case "income": 

            if ( $get_mode == 'continents' || $get_mode == 'hdi' || $get_mode == 'areas' || $get_mode == 'regions' || $get_mode == 'who' || $get_mode == 'income' )
            {
                if( $get_sub_mode == 'population' )
                {
                    // /api/globocan/v1/2018/hubs/population/1/0/all/39/
                    // /api/globocan/v1/2018/hdi/population/1/0/all/39/
                    // /api/globocan/v1/2018/who/population/1/0/all/39/
                    $settings['allow_search_group'] = false ; 
                    $settings['items'] = CanNordcan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                    $output = CanNordcan::getRealGroupingNumbers( $settings ) ; 

                    // grab color
                    // exit() ; 

                }

                break ; 

            }
            else
            {
                // v1: cumulated pop data
                if( $get_sub_mode == 'population')
                {
                    $settings['items'] = CanNordcan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                    $settings['grouped'] = true ; // always true
                    $settings['sort'] = 'total' ; 
                    // /api/globocan/v1/2018/hubs/population/1/0/all/39/
                    // /api/globocan/v1/2018/hdi/population/1/0/all/39/
                    // /api/globocan/v1/2018/who/population/1/0/all/39/
                    $output = CanNordcan::getGroupingNumbers( $settings ) ; 

                }
                else
                {
                    // /api/globocan/v1/2018/hdi/cancer/0/2/4/all/
                    // get the group of populations 
                    $data = CanNordcan::getGroupingPopulations( $settings ); 
                    // build list of countries from grouping (population)
                    $countries_ids = CanNordcan::getCountriesPerGroup( $data['key'] , $settings['population'] );
                    // build all items (list of cancer)
                    $settings['items'] = CanNordcan::getCancers(true,false,$settings) ;  
                    // force list of countries
                    $settings['populations'] = $countries_ids ; 
                    // get optimize numbers 
                    $output = CanNordcan::getNumbers( $settings ) ;
                    $output = CanNordcan::optimizeNumbers( $output , $settings ); 
                }
            }

            break ; 

        case 'data' : 
        case 'numbers' : 

            // http://www.gco.local/api/globocan/v1/2018/numbers/population/1/0/all/39/?mode_population=hubs
            // @deprecated
            if ( $get_mode_population != 'countries' && $get_sub_mode == 'population')
            {
                 $output = CanNordcan::getGroupingNumbers( $settings ) ; 
            }
            else
            {

                // get all cancers or all populations: items is the list of element group by (if sub_mode == cancer, then items are cancers site)
                if ( $settings['sub_mode'] == 'cancer')
                    $settings['items'] = CanNordcan::getCancers(true,false,$settings) ;  
                else
                    $settings['items'] = CanNordcan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                
                // if (isset($_GET['fred']) && $_GET['fred'] == 5){ var_dump($settings);exit() ; }

                // var_dump($settings); //exit();

                $output = CanNordcan::getNumbers( $settings ) ; 
                $output = CanNordcan::optimizeNumbers( $output );

                if ( $settings['cohort'] != "" && $settings['cohort'] == true )
                {
                    $output = CanNordcan::runCohort( $output );
                } 

                if ( count( $settings['types'] ) == 3 && $settings['types'][2] == 2 )
                {
                    // specific, combine inc/mort and prev
                    $prev_settings = $settings ; 

                    $prev_settings['type'] = 2 ; 
                    $prevalence = CanNordcan::getNumbers( $prev_settings ) ; 

                    foreach( $prevalence as $row ) 
                    {
                        $row['type'] = 2 ; 
                        $output[] = $row ;
                    }
                }
            }
            break ; 

        // top 

        // get predictions
        case 'predictions' : 

            // $output = CanNordcan::getPredictions( $settings ) ; 
            break ; 

        // special url to get all numbers (type,sex,cancer) for all countries
        case 'full_numbers' : 

            $output = CanNordcan::getFullNumbers() ; 
            break ; 

    
        
	   } // end switch 

    CanDataFormat::output( $output  ) ; 
