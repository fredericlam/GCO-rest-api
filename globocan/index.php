<?php
    
    // require __DIR__ . '/../vendor/autoload.php';

	require '../conn.php' ;
	require '../functions.php' ; 

    require '../common/classes/CanDataFormat.php' ;   
    require '../common/classes/CanCache.php' ;   

	require 'classes/CanGlobocan.php' ; 
	require 'classes/CanCases.php' ;

    require '../common/geography.php' ; 
	
	header('Access-Control-Allow-Origin: *');

    // include common variables from urls
    require '../common/settings.php' ; 
    
    // init data
    $total = 0 ; 
	$output = [] ; 

	switch( $get_mode )
	{
        case "sandbox" : 
            $settings['year'] = 2018 ; 
            $results = CanGlobocan::getCountriesPerGroup('hdi_group_2015',1); 
            foreach($results as $c ) 
            {
                var_dump($c);
                //echo "{$c[2]}<br>";
            }
            exit() ; 
            break ; 

        // ---------------------------------------------------------------------------------------------------------------------
        case 'data_sources' : 
            
            $output = CanGlobocan::getDataSources() ; 
            $total  = count( $output ) ;
            break ; 
        
        // ---------------------------------------------------------------------------------------------------------------------
        case 'data_methods' : 
            
            $output = CanGlobocan::getDataMethods() ; 
            $total  = count( $output ) ;
            break ; 
        
        // ---------------------------------------------------------------------------------------------------------------------
        case 'cancer_site' : 
            
            if ( empty( $_GET['population'] ) )
                $output = CanGlobocan::getCancers(true,false,$settings) ; 
            else
                $output = CanGlobocan::getCancerById( $get_population ) ; 
            
            $output = array_values($output);
            $total  = count( $output ) ;
            break ; 
            
        // ---------------------------------------------------------------------------------------------------------------------    
		case 'populations' : // numbers of populations
            
            if ( empty( $_GET['population'] ))
            {
                $output = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                
            }
            else
            {
                $output = CanGlobocan::getPopulationById( $get_population ) ; 

                $output['demography'] = CanCases::getPop( [ 'country' => $get_population ] ); 

                $output['source_methods'] = CanGlobocan::getDataSourceMethod( $get_population ); 
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

            if ( $get_mode == 'continents' || $get_mode == 'hdi' || $get_mode == 'areas' || $get_mode == 'regions' || $get_mode == 'who' || $get_mode == 'income' || $get_mode == 'hubs' )
            {
                if( $get_sub_mode == 'population' )
                {
                    // /api/globocan/v1/2018/hubs/population/1/0/all/39/
                    // /api/globocan/v1/2018/hdi/population/1/0/all/39/
                    // /api/globocan/v1/2018/who/population/1/0/all/39/
                    $settings['allow_search_group'] = false ; 
                    $settings['items'] = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                    $output = CanGlobocan::getRealGroupingNumbers( $settings ) ; 

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
                    $settings['items'] = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                    $settings['grouped'] = true ; // always true
                    $settings['sort'] = 'total' ; 
                    // /api/globocan/v1/2018/hubs/population/1/0/all/39/
                    // /api/globocan/v1/2018/hdi/population/1/0/all/39/
                    // /api/globocan/v1/2018/who/population/1/0/all/39/
                    $output = CanGlobocan::getGroupingNumbers( $settings ) ; 

                }
                else
                {
                    // /api/globocan/v1/2018/hdi/cancer/0/2/4/all/
                    // get the group of populations 
                    $data = CanGlobocan::getGroupingPopulations( $settings ); 
                    // build list of countries from grouping (population)
                    $countries_ids = CanGlobocan::getCountriesPerGroup( $data['key'] , $settings['population'] );
                    // build all items (list of cancer)
                    $settings['items'] = CanGlobocan::getCancers(true,false,$settings) ;  
                    // force list of countries
                    $settings['populations'] = $countries_ids ; 
                    // get optimize numbers 
                    $output = CanGlobocan::getNumbers( $settings ) ;
                    $output = CanGlobocan::optimizeNumbers( $output , $settings ); 
                }
            }

            break ; 

        case 'data' : 
        case 'numbers' : 

            // http://www.gco.local/api/globocan/v1/2018/numbers/population/1/0/all/39/?mode_population=hubs
            // @deprecated
            if ( $get_mode_population != 'countries' && $get_sub_mode == 'population')
            {
                 $output = CanGlobocan::getGroupingNumbers( $settings ) ; 
            }
            else
            {

                // get all cancers or all populations: items is the list of element group by (if sub_mode == cancer, then items are cancers site)
                if ( $settings['sub_mode'] == 'cancer')
                    $settings['items'] = CanGlobocan::getCancers(true,false,$settings) ;  
                else
                    $settings['items'] = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
                
                // if (isset($_GET['fred']) && $_GET['fred'] == 5){ var_dump($settings);exit() ; }

                // var_dump($settings); //exit();

                $output = CanGlobocan::getNumbers( $settings ) ; 
                $output = CanGlobocan::optimizeNumbers( $output );

                if ( count( $settings['types'] ) == 3 && $settings['types'][2] == 2 )
                {
                    // specific, combine inc/mort and prev
                    $prev_settings = $settings ; 

                    $prev_settings['type'] = 2 ; 
                    $prevalence = CanGlobocan::getNumbers( $prev_settings ) ; 

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

            // $output = CanGlobocan::getPredictions( $settings ) ; 
            break ; 

        // special url to get all numbers (type,sex,cancer) for all countries
        case 'full_numbers' : 

            $output = CanGlobocan::getFullNumbers() ; 
            break ; 

        case "top_cancers" : 

            // if mode population is enable, then we list all countries from these groupings
            /*if ( $settings['mode_population'] != 'countries' ) 
            {
                $settings['populations'] = [] ; 
                $grouping = CanGlobocan::getGroupingPopulations( $settings ) ;

                foreach( $grouping['groups'] as $group )
                {
                    $countries = CanGlobocan::getCountriesPerGroup( $grouping['key'] , $group['id'] ); 
                    $settings['populations'] = array_merge ( $settings['populations'] , $countries ) ; 
                }
            }

            $output = CanGlobocan::getTopCancerPerCountries( $settings ) ; */

            // var_dump($settings);exit();
            $key = $settings['field_key'] ; 
            $include_nmsc = (int)$settings['include_nmsc'] ; 
            $grouping_cancer = (int)$settings['grouping_cancer'] ; 
            $file_path = ROOT . "api/globocan" . CACHE_PATH . "top-cancers/{$settings['type']}-{$settings['sex']}-{$include_nmsc}-{$grouping_cancer}-{$key}.json";
            
            // exit( $file_path ) ; 
            if ( file_exists( $file_path ))
            {
                $contents   = file_get_contents( $file_path );
                $output     = json_decode($contents); 
            }
            else
            {
                $output = CanGlobocan::getMaxPerCountries( $settings ) ; 
            }
            break ; 
        
	   } // end switch 

    CanDataFormat::output( $output  ) ; 
