<?php

	
	$conf = [
        'minify' => true , 
        'cache' => [
            'folder' => 'cache/',
            'cancers' => true 
        ], 
        'y' => [
            2012 => [
                'prevalence' => true , 
                'all_cancers_id' => [29] , 
                'forbidden_cancers' => [13],
                'ages' => [ 'N0_14','N15_39','N40_44','N45_49','N50_54','N55_59','N60_64','N65_69','N70_74','N75'],  
                'fields' => [ 'P0_14','P15_39','P40_44','P45_49','P50_54','P55_59','P60_64','P65_69','P70_74','P75'],  
                'segi' => [ 31000,37000,6000,6000,5000,4000,4000,3000,2000,2000 ] , 
                'age_threshold' => [ 15,25,5,5,5,5,5,5,5,5 ]
            ],
            2018 => [
                'prevalence' => false , 
                'all_cancers_id' => [39,40] , 
                'forbidden_cancers' => [/*37,38,17*/],
                'nmsc_cancers' => 17 , // exception specific to globocan
                'hidden_cancers' => [37,38] ,
                'ages' => [ 'N00_04','N05_09','N10_14','N15_19','N20_24','N25_29','N30_34','N35_39','N40_44','N45_49','N50_54','N55_59','N60_64','N65_69','N70_74','N75_79','N80_84','N85'] , 
                'segi' => [ 12000,10000,9000,9000,8000,8000,6000,6000,6000,6000,5000,4000,4000,3000,2000,1000,500,500 ] , 
                'age_threshold' => [ 5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5 ] , 
                'year_predictions' => [2020,2025,2030,2035,2040] ,
                'grouped_cancers' => [ 
                    [
                        'cancer' => 41 , 
                        'label' =>  'Colorectum' , 
                        'ids' => [8,9,10] , 
                        'ICD'   => 'C18-21',
                        'color' => '#FFD803' , 
                        'title' => 'Colorectal cancer' 
                    ]
                ] 
            ],
            '2019_gi' => [
                'prevalence' => false , 
                'all_cancers_id' => [39] , 
                'forbidden_cancers' => [/*37,38,17*/],
                'nmsc_cancers' => 17 , // exception specific to globocan
                'hidden_cancers' => [99] ,
                'ages' => [ 'N00_04','N05_09','N10_14','N15_19','N20_24','N25_29','N30_34','N35_39','N40_44','N45_49','N50_54','N55_59','N60_64','N65_69','N70_74','N75_79','N80_84','N85'] , 
                'segi' => [ 12000,10000,9000,9000,8000,8000,6000,6000,6000,6000,5000,4000,4000,3000,2000,1000,500,500 ] , 
                'age_threshold' => [ 5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5 ] , 
                'year_predictions' => [2020,2025,2030,2035,2040] ,
                'grouped_cancers' => [] 
            ],
            2020 => [
                'prevalence' => false , 
                'all_cancers_id' => [39,40] , 
                'forbidden_cancers' => [/*37,38,17*/],
                'nmsc_cancers' => 17 , // exception specific to globocan
                'hidden_cancers' => [37,38] ,
                'ages' => [ 'N00_04','N05_09','N10_14','N15_19','N20_24','N25_29','N30_34','N35_39','N40_44','N45_49','N50_54','N55_59','N60_64','N65_69','N70_74','N75_79','N80_84','N85'] , 
                'segi' => [ 12000,10000,9000,9000,8000,8000,6000,6000,6000,6000,5000,4000,4000,3000,2000,1000,500,500 ] , 
                'age_threshold' => [ 5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5 ] , 
                'year_predictions' => [2020,2025,2030,2035,2040] ,
                'grouped_cancers' => [ 
                    [
                        'cancer' => 41 , 
                        'label' =>  'Colorectum' , 
                        'ids' => [8,9,10] , 
                        'ICD'   => 'C18-21',
                        'color' => '#FFD803' , 
                        'title' => 'Colorectal cancer' 
                    ]
                ] 
            ]
        ]
    ] ; 


    /*$methods_txt    = [
        'inc' => [

            '1'=>'National (or local with coverage greater than 50%) rates projected to 2018',
            '2a'=>'Most recent rates from a single registry applied to 2018 population',
            '2b'=>'Weighted/simple average of the most recent local rates applied to 2018 population',
            '3a'=>'Estimated from national mortality estimates by modelling, using mortality:incidence ratios derived from country-specific cancer registry data',
            '3b'=>'Estimated from national mortality estimates by modelling, using mortality:incidence ratios derived from cancer registry data in neighbouring countries',
            '4'=>'"All sites" estimates from neighbouring countries partitioned using frequency data',
            '9'=>'No data: the rates are those of neighbouring countries or registries in the same area'
        ], 
        'mort' => [
            '1'=>'National rates projected to 2018',
            '2a'=>'Most recent rates from one source applied to 2018 population',
            '2b'=>'Weighted/simple average of the most recent local rates applied to 2018 population',
            '3'=>'Estimated from national incidence estimates by modelling, using incidence:mortality ratios derived from cancer registry data in neighbouring countries',
            '9'=>' No data: the rates are those of neighbouring countries in the same area'
        ]
    ] ;*/

    $methods_txt    = [
        'inc' => [

            '1'=>'National (or local with coverage greater than 50%) rates projected to 2020',
            '2a'=>'Most recent rates from a single registry applied to 2020 population',
            '2b'=>'Weighted/simple average of the most recent local rates applied to 2020 population',
            '3a'=>'Estimated from national mortality estimates by modelling, using mortality:incidence ratios derived from country-specific cancer registry data',
            '3b'=>'Estimated from national mortality estimates by modelling, using mortality:incidence ratios derived from cancer registry data in neighbouring countries',
            '4'=>'"All sites" estimates from neighbouring countries partitioned using frequency data',
            '9'=>'No data: the rates are those of neighbouring countries or registries in the same area'
        ], 
        'mort' => [
            '1'=>'National rates projected to 2020',
            '2a'=>'Most recent rates from one source applied to 2020 population',
            '2b'=>'Weighted/simple average of the most recent local rates applied to 2020 population',
            '3'=>'Estimated from national incidence estimates by modelling, using incidence:mortality ratios derived from cancer registry data in neighbouring countries',
            '9'=>'No data: the rates are those of neighbouring countries in the same area'
        ]
    ] ;


	// main params
    $get_v      = ( isset($_GET['v']) && !empty( $_GET['v'] )) ? filter_input( INPUT_GET , 'v' ) : 'v1' ; 
    $get_year   = ( isset($_GET['y']) && !empty( $_GET['y'] )) ? filter_input( INPUT_GET , 'y' ) : 2018 ; 
    $get_mode 	= ( isset($_GET['mode']) && !empty( $_GET['mode'] )) ? filter_input( INPUT_GET , 'mode' ) : 'populations' ; 
    $get_mode_population = ( isset($_GET['mode_population']) && !empty( $_GET['mode_population'] )) ? filter_input( INPUT_GET , 'mode_population' ) : 'countries' ; 
    $get_sub_mode = ( isset($_GET['sub_mode']) && !empty( $_GET['sub_mode'] )) ? filter_input( INPUT_GET , 'sub_mode' ) : 'population' ; 

	// get settings 
	$get_sex 		= ( isset($_GET['sex']) && !empty( $_GET['sex'] )) ? filter_input( INPUT_GET , 'sex' ) : 0 ; 			// default male
	$get_type 		= ( isset($_GET['type']) && !empty( $_GET['type'] )) ? filter_input( INPUT_GET , 'type' ) : 0 ; 	
	$get_cancer		= ( isset($_GET['cancer']) && !empty( $_GET['cancer'] )) ? $_GET['cancer'] : 29 ;
    $get_population	= ( isset($_GET['population']) && !empty( $_GET['population'] )) ? $_GET['population'] : 900 ;
    
    $get_prevalence = ( isset($_GET['prevalence']) && !empty( $_GET['prevalence'] )) ? $_GET['prevalence'] : false ;
    $get_statistic  = ( isset($_GET['statistic']) && !empty( $_GET['statistic'] )) ? $_GET['statistic'] : 0 ;

    $get_sort       = ( isset($_GET['sort']) && !empty( $_GET['sort'] )) ? $_GET['sort'] : 'total' ;
    $get_sort_dir   = ( isset($_GET['sort_dir']) && !empty( $_GET['sort_dir'] )) ? $_GET['sort_dir'] : 'desc' ;
    $get_grouped    = ( isset($_GET['grouped'])) ? (bool)$_GET['grouped'] : true ;
    $get_ages_group = ( isset($_GET['ages_group']) && $_GET['ages_group'] != '0_17' && $_GET['ages_group'] != '0_85' && $_GET['ages_group'] != 'all' ) ? explode('_',$_GET['ages_group']) : 'all' ;

    $get_show_ages      = ( isset($_GET['show_ages']) && filter_input( INPUT_GET , 'show_ages' ) == 1 ) ? true : false ; 
    $get_show_details      = ( isset($_GET['show_details']) && filter_input( INPUT_GET , 'show_details' ) == 1 ) ? true : false ; 

    $get_predictions_year       = ( isset($_GET['predictions_year']) && !empty( $_GET['predictions_year'] )) ? filter_input( INPUT_GET , 'predictions_year' ) : 2020 ; 
    $get_apc       = ( isset($_GET['apc']) && !empty( $_GET['apc'] )) ? filter_input( INPUT_GET , 'apc' ) : 0 ; 
    $get_grouping_cancer = ( isset($_GET['grouping_cancer']) && !empty( $_GET['grouping_cancer'] ) && $_GET['grouping_cancer'] == 1 ) ? true : false ; 

    $get_exclude_nmsc = ( isset($_GET['exclude_nmsc']) && (int)$_GET['exclude_nmsc'] == 0 ) ? false : true ;
    $get_include_nmsc = ( isset($_GET['include_nmsc']) && (int)$_GET['include_nmsc'] == 0 ) ? false : true ;
    $get_include_nmsc_other = ( isset($_GET['include_nmsc_other']) && (int)$_GET['include_nmsc_other'] == 0 ) ? false : true ;

    $get_field_key   = ( isset($_GET['field_key']) && !empty( $_GET['field_key'] )) ? $_GET['field_key'] : 'asr' ;

    $get_recalculate  = ( isset($_GET['recalculate']) && !empty( $_GET['recalculate'] )) ? true : false ;
    $get_extra_pop  = ( isset($_GET['extra_pop']) && !empty( $_GET['extra_pop'] )) ? $_GET['extra_pop'] : '' ;

    $settings = [
        
        'v' => $get_v , 
        'year'=> $get_year ,
		'mode' => $get_mode , // mode numbers
        'sub_mode' => $get_sub_mode , // mode population | cancer

		'mode_population' => $get_mode_population , // grouping mode
		'sex' => (int)$get_sex , 
		'type' => (int)$get_type , 
		'cancers' => $get_cancer , 
		'prevalence' => $get_prevalence , 
		'statistic' => $get_statistic , 
		'population' => $get_population , 

        // new version 
        'sexes' => explode('_',$get_sex) , 
        'types' => explode('_',$get_type) , 
        'cancers' => explode('_',$get_cancer) , 
        'populations' => explode('_',$get_population) , 

        // extra fields, not inside nice url
        'sort' => $get_sort , 
        'sort_dir' => $get_sort_dir , 
        'grouped' => $get_grouped , 
        'ages_group' => $get_ages_group , 
        'show_ages' => $get_show_ages , 
        'show_details' => $get_show_details , 
        'grouping_cancer' => $get_grouping_cancer , 
        'exclude_nmsc' => $get_exclude_nmsc , 
        'include_nmsc' => $get_include_nmsc , 
        'include_nmsc_other' => $get_include_nmsc_other , 
        'allow_search_group' => true , 
        'field_key' => $get_field_key , 

        // for prediction
        'predictions_year' => explode('_',$get_predictions_year) , 
        'apc' => explode('_',$get_apc) , 

        'recalculate' => $get_recalculate , 
        'extra_pop' => $get_extra_pop
	] ; 

    if( $get_include_nmsc == false )
    {
        array_push( $conf['y'][ $settings['year'] ]['forbidden_cancers'] , $conf['y'][ $settings['year'] ]['nmsc_cancers'] ) ; 

    }

?>