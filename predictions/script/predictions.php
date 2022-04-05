<?php

	// years
	$years				= [ 2015 , 2020 , 2025 , 2030 , 2035 ] ;

	// populations males + females
	$Males 		= [ 'populations' => [] , 'predictions' => [] ,  'numbers' => [] , '0_65' => []  ] ; 
	$Females 	= [ 'populations' => [] , 'predictions' => [] ,  'numbers' => [] , '0_65' => [] ] ; 

	// MALES ===================================================================================================================================================
	$results_m = CanCases::getPop( ['country' => $get_country , 'sex' => 1 ] , true ) ; 
	for ($age = 0; $age < 10; $age++) $Males['populations'][$age] = (int)$results_m[$age+1] ; 
	$results = CanCases::getNumbers( [ 'country' =>  $get_country ,'sex' => 1 , 'type' => $get_type , 'cancer' => $get_cancers ] ) ; 
	for ( $age = 0 ; $age < 10 ; $age++ ) $Males['numbers'][ $age ] = (int)$results[ $age ]  ; 

	$Males['0_65'] = slice( $Males['numbers'] , 0 , 7) ; 
	$Males['65+'] = slice( $Males['numbers'] , 7 , count($Males['numbers']) - 1 ) ; 

	$Males[2012] = [ 
		'data' => ['0_65' => sum($Males['0_65']) , '65+' => sum($Males['65+']) , 'sum' => sum($Males['0_65']) + sum($Males['65+']) ] 
	] ;
	foreach( $years as $year )
	{
		$results_m_p = CanCases::getPopByYear( ['country' => $get_country , 'sex' => 1 ] , true , $year  ) ; 
		for ($age = 0; $age < 10; $age++) $Males['predictions'][$year][$age] = (int)$results_m_p[$age] ; 

		$predictions = ['0_65' => 0 , '65+' => 0 , 'sum' => 0 ] ; 
		for ( $age = 0 ; $age < 10 ; $age++ )
		{
			$value =  round( ( $Males['numbers'][$age] / $Males['populations'][$age] ) * $Males['predictions'][$year][$age] ) ;  
			if ( $age < 7 )
				$predictions['0_65'] += $value  ; 
			else
				$predictions['65+'] += $value ; 
		}
		$predictions['sum'] = sum( $predictions ) ; 

		$Males['estimations'][ $year ] = [ 
			'data' => $predictions , 
			'changes' => [ 
				'0_65' 	=> ($predictions['0_65'] - sum($Males['0_65'])) , 
				'65+' 	=> ($predictions['65+'] - sum($Males['65+'])) ,
				'sum'	=> ($predictions['0_65'] - sum($Males['0_65'])) + ($predictions['65+'] - sum($Males['65+']))
			] 
		] ;
	}
	// debug( $Males[ 2012 ] ); 
	// debug( $Males['estimations'] ); 
	// exit() ; 

	// FEMALES ===================================================================================================================================================
	$results_m = CanCases::getPop( ['country' => $get_country , 'sex' => 2 ] , true ) ; 
	for ($age = 0; $age < 10; $age++) $Females['populations'][$age] = (int)$results_m[$age+1] ; 
	$results = CanCases::getNumbers( [ 'country' =>  $get_country ,'sex' => 2 , 'type' => $get_type , 'cancer' => $get_cancers ] ) ; 
	for ( $age = 0 ; $age < 10 ; $age++ ) $Females['numbers'][ $age ] = (int)$results[ $age ]  ; 

	$Females['0_65'] = slice( $Females['numbers'] , 0 , 7) ; 
	$Females['65+'] = slice( $Females['numbers'] , 7 , count($Females['numbers']) - 1 ) ; 

	$Females[2012] = [ 
		'data' => ['0_65' => sum($Females['0_65']) , '65+' => sum($Females['65+']) , 'sum' => sum($Females['0_65']) + sum($Females['65+']) ] 
	] ;

	foreach( $years as $year )
	{
		$results_m_p = CanCases::getPopByYear( ['country' => $get_country , 'sex' => 2 ] , true , $year  ) ; 
		for ($age = 0; $age < 10; $age++) $Females['predictions'][$year][$age] = (int)$results_m_p[$age] ; 

		$predictions = ['0_65' => 0 , '65+' => 0 , 'sum' => 0 ] ; 
		for ( $age = 0 ; $age < 10 ; $age++ )
		{
			$value =  round( ( $Females['numbers'][$age] / $Females['populations'][$age] ) * $Females['predictions'][$year][$age] ) ;  
			if ( $age < 7 )
				$predictions['0_65'] += $value  ; 
			else
				$predictions['65+'] += $value ; 
		}
		$predictions['sum'] = sum( $predictions ) ; 

		$Females['estimations'][ $year ] = [ 
			'data' => $predictions , 
			'changes' => [ 
				'0_65' 	=> ($predictions['0_65'] - sum($Females['0_65'])) , 
				'65+' 	=> ($predictions['65+'] - sum($Females['65+'])) ,
				'sum'	=> ($predictions['0_65'] - sum($Females['0_65'])) + ($predictions['65+'] - sum($Females['65+']))
			] 
		] ;
	}


	// $FINAL_DATA
  	$FINAL_DATA = [
  		[ 
  			'key' => 'Males' , 
  			'values' => [ 
  				[ 
  					'label' => 2012 , 
  					'sum' => $Males[2012]['data']['sum'] , 
  					'0_65' => $Males[2012]['data']['0_65'] , 
  					'65+' => $Males[2012]['data']['65+'] 
  				] 
  			]
  		], 
  		[ 
  			'key' => 'Females' , 
  			'values' => [ 
  				[ 
  					'label' => 2012 , 
  					'sum' => $Females[2012]['data']['sum'] , 
  					'0_65' => $Females[2012]['data']['0_65'] , 
  					'65+' => $Females[2012]['data']['65+'] 
  				]
  			]  
  		],
  		[
  			'key' => 'Both sexes' , 
  			'values' => [ 
  				[ 
  					'label' => 2012 , 
  					'sum' => $Males[2012]['data']['sum'] + $Females[2012]['data']['sum'] , 
  					'0_65' => $Males[2012]['data']['0_65'] + $Females[2012]['data']['0_65'] , 
  					'65+' => $Males[2012]['data']['65+'] + $Females[2012]['data']['65+'] 
  				] 
  			]
  		]
  	] ;

  	foreach( $years as $year )
  	{
  		// Males
  		$FINAL_DATA[0]['values'][] = [ 'label' => (int)$year , 'sum' => $Males['estimations'][ $year ]['data']['sum'] , '0_65' => $Males['estimations'][ $year ]['data']['0_65'] , '65+' => $Males['estimations'][ $year ]['data']['65+'] ]  ; 
  		// Females
  		$FINAL_DATA[1]['values'][] = [ 'label' => (int)$year , 'sum' => $Females['estimations'][ $year ]['data']['sum'] , '0_65' => $Females['estimations'][ $year ]['data']['0_65'] , '65+' => $Females['estimations'][ $year ]['data']['65+'] ]  ; 
  		// Both sexes
  		$FINAL_DATA[2]['values'][] = [ 'label' => (int)$year , 'sum' => $Males['estimations'][ $year ]['data']['sum'] + $Females['estimations'][ $year ]['data']['sum'] , '0_65' => $Males['estimations'][ $year ]['data']['0_65'] + $Females['estimations'][ $year ]['data']['0_65'] , '65+' => $Males['estimations'][ $year ]['data']['65+'] + $Females['estimations'][ $year ]['data']['65+'] ]  ; 
  	}


  	// remove final
  	if ( $get_remove == 1 ) unset($FINAL_DATA[2]);

  	$country = CanGlobocan::getCountryById( $get_country );

  	$CANCERS = CanGlobocan::getCancers(true);
  	$TYPE = [ 0 => 'cases' , 1 => 'deaths' ] ; 
		
	CanDataFormat::output( ['title' => $title , 'data' => [ 'final' => $FINAL_DATA , 'males' => $Males , 'females' => $Females ] ] , $get_mode_download , $get_force_download ) ; 

	exit(); 	

?>