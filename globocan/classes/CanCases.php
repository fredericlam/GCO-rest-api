<?php

	class CanCases
	{
		
		/**
		* Get numbers for pie chart
		* @param (array) list of query filters
		* @param (bool) if prevalence, change table name to "prevalence"
		* @param (string) type of data : all = all ages , adult = exclude field N0_14
		*/ 
		public static function getNumbers( $params , $prevalence = false , $type = 'all' )
		{
			global $o_bdd ; 

			// if we mentionned a table, this is prevalence
			if ( $prevalence == true ) 
			{
				$table 			= 'globocan2012_prevalence' ; 
				$query 			= " SELECT TOTAL "	;
				// SURVIVAL 0 = 1 year prevalence
				// SURVIVAL 1 = 3 years prevalence
				// SURVIVAL 2 = 5 years prevalence
				$field_type 	= " SURVIVAL " ; 
			}
			else
			{
				// default is numbers table
				$table 			= 'globocan2012_numbers' ; 
				$query 	= " SELECT N0_14,N15_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75 " ; 

				if ( $type == 'adult') $query = " SELECT N15_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75 " ; 
				$field_type 	= " TYPE " ; 
			}

			$query 		.= " FROM $table WHERE " ;
			$query 		.= " COUNTRY = ".$params['country']. " AND SEX= ".$params['sex']." AND $field_type = ".$params['type']." " ; 


			if ($params['cancer'] != 'all') $query .= "AND CANCER = " .$params['cancer'] ;
		 	
		 	// echo $query .'<br>' ; // exit() ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= $execute->fetch(PDO::FETCH_NUM) ; 

			return $result ; 
		}

		/**
		* Get pop (for population by cancer incidence by cancer > bar chart)
		*
		*/ 
		public static function getPop( $params , $one = false , $tableName = 'pop' )
		{
			global $o_bdd , $conf , $settings ; 

			$year = $settings['year'] ; 
			$table = "globocan{$year}_pop" ; 

			$array_fields = ( isset( $conf['y'][ $settings['year'] ]['fields'] ) ) ? $conf['y'][ $settings['year'] ]['fields'] : $conf['y'][ $settings['year'] ]['ages'] ; 

			$str_field = implode(',' , $array_fields ) ; 

			$query 		 = " SELECT $str_field" ; 

			$query 		.= " FROM $table WHERE " ;
			$query 		.= " COUNTRY = ".$params['country'] ; 

			if ( isset( $params['sex'] ) && !empty( $params['sex'] )) $query .= " AND SEX = ".$params['sex'] ;
			
			// echo $query .'<br>' ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$results 	= ( $one == false ) ? $execute->fetchAll(PDO::FETCH_NUM) : $execute->fetch(PDO::FETCH_NUM) ; 

			return $results ; 
		}

		/**
		* Get pop (for population by cancer incidence by cancer > bar chart)
		*
		*/ 
		public static function getGlobalNumbers( $params , $one = false  )
		{
			global $o_bdd ; 

			$query 		 = " SELECT TOTAL, ASR, CUM0_74 FROM globocan2012_numbers " ;
			$query 		.= " WHERE CANCER = ".$params['cancer']." AND COUNTRY = ".$params['country']. " AND SEX= ".$params['sex'] . " AND TYPE = ".$params['type'] ;
			// echo $query .'<br>' ; exit() ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= $execute->fetch() ; 

			return $result ; 
		}

		/**
		* Get pop (for population by cancer incidence by cancer > bar chart)
		*
		*/ 
		public static function getPopByYear( $params , $one = false , $year = '' )
		{
			global $o_bdd ; 

			$query 		 = " SELECT P0_14,P15_39,P40_44,P45_49,P50_54,P55_59,P60_64,P65_69,P70_74,P75" ; 
			$query 		.= " FROM globocan2012_ppop WHERE " ;
			$query 		.= " COUNTRY = ".$params['country']. " AND YEAR = $year AND SEX= ".$params['sex'] ;
			// echo $query .'<br>' ; exit() ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$results 	= ( $one == false ) ? $execute->fetchAll(PDO::FETCH_NUM) : $execute->fetch(PDO::FETCH_NUM) ; 

			return $results ; 
		}

		public static function getPredictions( $params )
		{
			$years = [ 2015 , 2020 , 2025 , 2030 , 2035 ] ;

			$start_year = 2012 ; 
			
			$get_type = $params['type'] ; 
			$get_cancers = $params['cancers'] ;  
			$get_remove = $params['remove'] ; 

			$dataset = [] ; 

			$max_value = 0 ; 

			$CANCERS = CanGlobocan::getCancers(true,false,$params);

			foreach( $params['populations'] as $pop => $population )
			{
				$get_country = $population ; 

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

				$Males[$start_year] = [ 
					'data' => ['0_65' => sum($Males['0_65']) , '65+' => sum($Males['65+']) , 'sum' => sum($Males['0_65']) + sum($Males['65+']) ] 
				];

				foreach( $years as $iYear => $year )
				{
					$results_m_p = CanCases::getPopByYear( ['country' => $get_country , 'sex' => 1 ] , true , $year  ) ; 
					for ($age = 0; $age < 10; $age++) $Males['predictions'][$year][$age] = (int)$results_m_p[$age] ; 

					$predictions = ['0_65' => 0 , '65+' => 0 , 'sum' => 0 , 'diff' => 0  ] ; 
					for ( $age = 0 ; $age < 10 ; $age++ )
					{
						if ( $Males['populations'][$age] == 0 )
							$value = 0 ; 
						else
							$value =  round( ( $Males['numbers'][$age] / $Males['populations'][$age] ) * $Males['predictions'][$year][$age] ) ;  
						
						if ( $age < 7 )
							$predictions['0_65'] += $value  ; 
						else
							$predictions['65+'] += $value ; 
						
						// echo "[$year - $age] = {$Males['numbers'][$age]} / {$Males['populations'][$age]} * {$Males['predictions'][$year][$age]} <br> " ; 
					}
					
					$predictions['sum'] = sum( $predictions ) ; 

					// echo " ==> SUM = {$predictions['sum']} <br><br>" ; 

					if ( $iYear == 0 ) // if first year of table 
					{
						$predictions['diff'] =  $predictions['sum'] - $Males[$start_year]['data']['sum'] ; 
					}
					else
					{
						//echo " {$predictions['sum']} = {$Males['estimations'][ $years[$iYear-1] ]['changes']['sum']} <br/>" ; 
						$predictions['diff'] = $predictions['sum'] - $Males['estimations'][ $years[$iYear-1] ]['data']['sum'] ; 
					}

					$young = ($predictions['0_65'] - sum($Males['0_65'])) ; 
					$old = ($predictions['65+'] - sum($Males['65+'])) ; 
					$sum = ($predictions['0_65'] - sum($Males['0_65'])) + ($predictions['65+'] - sum($Males['65+'])) ; 


					$young_percent 	= ( $predictions['0_65'] == 0 ) ? 0 : ( $young * 100 ) / $predictions['0_65'] ; 
					$old_percent 	= ( $predictions['65+'] == 0 ) ? 0 : ( $old * 100 ) / $predictions['65+'] ; 
					$sum_percent 	= ( $predictions['sum'] == 0 ) ? 0 : ( $sum * 100 ) / $predictions['sum'] ; 


					$Males['estimations'][ $year ] = [ 
						'data' => $predictions , 
						'changes' => [ 
							'0_65' 	=> $young , 
							'65+' 	=> $old ,
							'sum'	=> $sum
						] , 
						'percent' => [
							'0_65' 	=> $young_percent , 
							'65+' 	=> $old_percent ,
							'sum'	=> $sum_percent
						] , 
						'increment' => [
							'0_65' 	=> ( $iYear == 0 ) ? $young : $young - $Males['estimations'][ $years[$iYear-1] ]['changes']['0_65']  , 
							'65+' 	=> ( $iYear == 0 ) ? $old : $old - $Males['estimations'][ $years[$iYear-1] ]['changes']['65+'] ,
							'sum'	=> ( $iYear == 0 ) ? $sum : $sum - ( $Males['estimations'][ $years[$iYear-1] ]['changes']['0_65'] + $Males['estimations'][ $years[$iYear-1] ]['changes']['65+'] )  ,
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

				$Females[$start_year] = [ 
					'data' => ['0_65' => sum($Females['0_65']) , '65+' => sum($Females['65+']) , 'sum' => sum($Females['0_65']) + sum($Females['65+']) ] 
				] ;

				foreach( $years as $iYear => $year )
				{
					$results_m_p = CanCases::getPopByYear( ['country' => $get_country , 'sex' => 2 ] , true , $year  ) ; 
					for ($age = 0; $age < 10; $age++) $Females['predictions'][$year][$age] = (int)$results_m_p[$age] ; 

					$predictions = ['0_65' => 0 , '65+' => 0 , 'sum' => 0 , 'diff' => 0 ] ; 
					for ( $age = 0 ; $age < 10 ; $age++ )
					{
						if ( $Females['populations'][$age] == 0 )
							$value = 0 ; 
						else
							$value = round( ( $Females['numbers'][$age] / $Females['populations'][$age] ) * $Females['predictions'][$year][$age] ) ; 

						if ( $age < 7 )
							$predictions['0_65'] += $value  ; 
						else
							$predictions['65+'] += $value ; 
					}
					$predictions['sum'] = sum( $predictions ) ; 

					if ( $iYear == 0 )
					{
						$predictions['diff'] =  $predictions['sum'] - $Females[$start_year]['data']['sum'] ; 
					}
					else
					{
						$predictions['diff'] = $predictions['sum'] - $Females['estimations'][ $years[$iYear-1] ]['data']['sum'] ; 
					}


					$young = ($predictions['0_65'] - sum($Females['0_65'])) ; 
					$old = ($predictions['65+'] - sum($Females['65+'])) ; 
					$sum = ($predictions['0_65'] - sum($Females['0_65'])) + ($predictions['65+'] - sum($Females['65+'])) ; 

					$young_percent 	= ( $predictions['0_65'] == 0 ) ? 0 : ( $young * 100 ) / $predictions['0_65'] ; 
					$old_percent 	= ( $predictions['65+'] == 0 ) ? 0 :( $old * 100 ) / $predictions['65+'] ; 
					$sum_percent 	= ( $predictions['sum'] == 0 ) ? 0 :( $sum * 100 ) / $predictions['sum'] ; 

					$Females['estimations'][ $year ] = [ 
						'data' => $predictions , 
						'changes' => [ 
							'0_65' 	=> $young , 
							'65+' 	=> $old ,
							'sum'	=> $sum
						] , 
						'percent' => [
							'0_65' 	=> $young_percent , 
							'65+' 	=> $old_percent ,
							'sum'	=> $sum_percent
						] , 
						'increment' => [
							'0_65' 	=> ( $iYear == 0 ) ? $young : $young - $Females['estimations'][ $years[$iYear-1] ]['changes']['0_65']  , 
							'65+' 	=> ( $iYear == 0 ) ? $old : $old - $Females['estimations'][ $years[$iYear-1] ]['changes']['65+'] ,
							'sum'	=> ( $iYear == 0 ) ? $sum : $sum - ( $Females['estimations'][ $years[$iYear-1] ]['changes']['0_65'] + $Females['estimations'][ $years[$iYear-1] ]['changes']['65+'] )  ,
						]
					] ;
				}


				// $FINAL_DATA
			  	$FINAL_DATA = [
			  		[
			  			'key' => 'Both sexes' , 
			  			'values' => [ 
			  				[ 
			  					'label' => $start_year , 
			  					'sum' => $Males[$start_year]['data']['sum'] + $Females[$start_year]['data']['sum'] , 
			  					// 'diff' => $Males[$start_year]['data']['diff'] + $Females[$start_year]['data']['diff'] , 
			  					'0_65' => $Males[$start_year]['data']['0_65'] + $Females[$start_year]['data']['0_65'] , 
			  					'65+' => $Males[$start_year]['data']['65+'] + $Females[$start_year]['data']['65+'] 
			  				] 
			  			]
			  		],
			  		[ 
			  			'key' => 'Males' , 
			  			'values' => [ 
			  				[ 
			  					'label' => $start_year , 
			  					'sum' => $Males[$start_year]['data']['sum'] , 
			  					//'diff' => $Males[$start_year]['data']['diff'] , 
			  					'0_65' => $Males[$start_year]['data']['0_65'] , 
			  					'65+' => $Males[$start_year]['data']['65+'] 
			  				] 
			  			]
			  		], 
			  		[ 
			  			'key' => 'Females' , 
			  			'values' => [ 
			  				[ 
			  					'label' => $start_year , 
			  					'sum' => $Females[$start_year]['data']['sum'] , 
			  					//'diff' => $Females[$start_year]['data']['diff'] , 
			  					'0_65' => $Females[$start_year]['data']['0_65'] , 
			  					'65+' => $Females[$start_year]['data']['65+'] 
			  				]
			  			]  
			  		]
			  	] ;

			  	foreach( $years as $year )
			  	{
			  		// Males
			  		$FINAL_DATA[1]['values'][] = [ 
			  			'label' => (int)$year , 
			  			'sum' => $Males['estimations'][ $year ]['data']['sum'] , 
			  			'diff' => $Males['estimations'][ $year ]['data']['diff'] , 
			  			'0_65' => $Males['estimations'][ $year ]['data']['0_65'] , 
			  			'65+' => $Males['estimations'][ $year ]['data']['65+'] 
			  		]  ; 
			  		// Females
			  		$FINAL_DATA[2]['values'][] = [ 
			  			'label' => (int)$year , 
			  			'sum' => $Females['estimations'][ $year ]['data']['sum'] , 
			  			'diff' => $Females['estimations'][ $year ]['data']['diff'] , 
			  			'0_65' => $Females['estimations'][ $year ]['data']['0_65'] , 
			  			'65+' => $Females['estimations'][ $year ]['data']['65+'] 
			  		]  ; 

			  		// recalculate max
			  		if ( $Males['estimations'][ $year ]['data']['sum'] > $max_value )
			  			$max_value = $Males['estimations'][ $year ]['data']['sum'] ; 

			  		if ( $Females['estimations'][ $year ]['data']['sum'] > $max_value )
			  			$max_value = $Females['estimations'][ $year ]['data']['sum'] ; 


			  		// Both sexes
			  		$FINAL_DATA[0]['values'][] = [ 
			  			'label' => (int)$year , 
			  			'sum' => $Males['estimations'][ $year ]['data']['sum'] + $Females['estimations'][ $year ]['data']['sum'] , 
			  			'diff' => $Males['estimations'][ $year ]['data']['diff'] + $Females['estimations'][ $year ]['data']['diff'] , 
			  			'0_65' => $Males['estimations'][ $year ]['data']['0_65'] + $Females['estimations'][ $year ]['data']['0_65'] , 
			  			'65+' => $Males['estimations'][ $year ]['data']['65+'] + $Females['estimations'][ $year ]['data']['65+'] 
			  		]  ; 
			  	}

			  	// remove final
			  	if ( $get_remove == 1 ) unset($FINAL_DATA[2]);

			  	$country = CanGlobocan::getCountryById( $get_country );

			  	$TYPE = [ 0 => 'cases' , 1 => 'deaths' ] ; 

			  	$dataset[] = [ 
			  		'country_id' => $get_country , 
			  		'country' => $country['label'] , 
			  		'cancer_id' => $get_cancers , 
			  		'cancer' => $CANCERS[$get_cancers]['label'] , 
			  		'final' => $FINAL_DATA , 
			  		'males' => $Males , 
			  		'females' => $Females 
			  	] ; 
			}


			// if we cumulate, then combine values
			if ( isset( $params['cumulate'] ) && $params['cumulate'] == true )
			{
				$tmp_dataset = $dataset ; 
				$dataset = [] ; 

				// estimations 
				$estimations_females = [] ; $estimations_males = [] ; $estimations_both = [] ; $final_estimates = [] ; 

				// 2012 years
				for ( $s = 0 ; $s < 3 ; $s++ )
				{
					$final_estimates[ $s ][] = [
						'label' => 2012 , 
						'0_65' => 0 ,
						'65+' => 0 , 
						'sum' => 0
					] ; 
				}

				foreach ( $tmp_dataset as $row )
				{
					// 2012 years
					for ( $s = 0 ; $s < 3 ; $s++ )
					{
						$final_estimates[ $s ][ 0 ]['0_65'] += $row['final'][$s]['values'][0]['0_65'] ; 
						$final_estimates[ $s ][ 0 ]['65+'] += $row['final'][$s]['values'][0]['65+'] ; 
						$final_estimates[ $s ][ 0 ]['sum'] += $row['final'][$s]['values'][0]['sum'] ;
					}
				}

				// debug( $final_estimates ) ; exit() ; 

				$years = [ 2015 , 2020 , 2025 , 2030 , 2035 ] ;
				foreach( $years as $year )
				{
					$clear_data_diff = [ '0_65' => 0 , '65+' => 0 , 'sum' => 0 , 'diff' => 0 ]  ;
					$clear_data = [ '0_65' => 0 , '65+' => 0 , 'sum' => 0 ]  ;

					$estimations_males[ $year ] = [ 
						'data' => $clear_data_diff , 
						'changes' => $clear_data 
					] ; 

					$estimations_females[ $year ] = [ 
						'data' => $clear_data_diff , 
						'changes' => $clear_data 
					] ; 

					$estimations_both[ $year ] = [ 
						'data' => $clear_data_diff , 
						'changes' => $clear_data 
					] ; 

					for ( $s = 0 ; $s < 3 ; $s++ )
					{
						$tmp_data = $clear_data_diff ; 
						$tmp_data['label'] = $year ; 
						$final_estimates[$s][] = $tmp_data ; 
					}
				}

				// debug( $final_estimates ) ; exit() ;

				$clear_data = [ '0_65' => 0 , '65+' => 0 , 'sum' => 0 ]  ;

				$object = [
					'country_id' => [] , 
					'country' 	=> [] , 
					'cancer_id' => [] , 
					'cancer' => [] , 
					'final' => [
						[ 'key' => 'Both sexes' , 'values' => $final_estimates[0] ] ,
						[ 'key' => 'Males' , 'values' => $final_estimates[1] ] , 
						[ 'key' => 'Females' , 'values' => $final_estimates[2] ]
					] , 	
					'males' => [
						'2012' => [
							'data' => $clear_data
						] , 
						// 'populations' => [] , 
						// 'predictions' => [] , 
						// 'numbers' => [] , 
						'0_65' => [] , 
						'65+' => [] , 
						'estimations' => $estimations_females
					] , 
					'females' => [
						'2012' => [
							'data' => $clear_data
						] , 
						'populations' => [] , 
						'predictions' => [] , 
						'numbers' => [] , 
						'0_65' => [] , 
						'65+' => [] , 
						'estimations' => $estimations_females
 					]
				] ; 

				// debug( $tmp_dataset ) ; exit() ; 

				foreach ( $tmp_dataset as $row )
				{
					if ( !in_array( (int) $row['country_id'] , $object['country_id'] ) )
						$object['country_id'][] = (int) $row['country_id'] ;

					$object['country'][] =  $row['country'] ;

					if ( !in_array( (int) $row['cancer_id'] , $object['cancer_id'] ) )
					{
						$object['cancer_id'][] = (int) $row['cancer_id'] ;
						$object['cancer'][] = $row['cancer'] ;  
					}

					// init 2012
					
					$object['males']['2012']['data']['0_65'] += $row['males']['2012']['data']['0_65'] ;
					$object['males']['2012']['data']['65+'] += $row['males']['2012']['data']['65+'] ;
					$object['males']['2012']['data']['sum'] += $row['males']['2012']['data']['sum'] ;

					$object['females']['2012']['data']['0_65'] += $row['females']['2012']['data']['0_65'] ;
					$object['females']['2012']['data']['65+'] += $row['females']['2012']['data']['65+'] ;
					$object['females']['2012']['data']['sum'] += $row['females']['2012']['data']['sum'] ;


					foreach( $years as $iKey => $year )
					{
						// final - both sexes + males + females
						for ( $s = 0 ; $s < 3 ; $s++ )
						{
							// debug( $object['final'][ $s ]['values'] ) ; exit() ; 

							$object['final'][ $s ]['values'][ $iKey + 1 ]['0_65'] += $row['final'][ $s ]['values'][ $iKey + 1 ]['0_65'] ;
							$object['final'][ $s ]['values'][ $iKey + 1 ]['65+'] += $row['final'][ $s ]['values'][ $iKey + 1 ]['65+'] ;
							$object['final'][ $s ]['values'][ $iKey + 1 ]['sum'] += $row['final'][ $s ]['values'][ $iKey + 1 ]['sum'] ;
							$object['final'][ $s ]['values'][ $iKey + 1 ]['diff'] += (isset($row['final'][ $s ]['values'][ $iKey + 1 ]['diff'])) ?  $row['final'][ $s ]['values'][ $iKey + 1 ]['diff'] : 0 ;
						}

						// males ------------------------------------------------------------------------------------------------------------
						$object['males']['estimations'][$year]['data']['0_65'] += $row['males']['estimations'][$year]['data']['0_65'] ;
						$object['males']['estimations'][$year]['data']['65+'] += $row['males']['estimations'][$year]['data']['65+'] ;
						$object['males']['estimations'][$year]['data']['sum'] += $row['males']['estimations'][$year]['data']['sum'] ;
						$object['males']['estimations'][$year]['data']['diff'] += $row['males']['estimations'][$year]['data']['diff'] ;

						$object['males']['estimations'][$year]['changes']['0_65'] += $row['males']['estimations'][$year]['changes']['0_65'] ;
						$object['males']['estimations'][$year]['changes']['65+'] += $row['males']['estimations'][$year]['changes']['65+'] ;
						$object['males']['estimations'][$year]['changes']['sum'] += $row['males']['estimations'][$year]['changes']['sum'] ;

						// females ------------------------------------------------------------------------------------------------------------
						$object['females']['estimations'][$year]['data']['0_65'] += $row['females']['estimations'][$year]['data']['0_65'] ;
						$object['females']['estimations'][$year]['data']['65+'] += $row['females']['estimations'][$year]['data']['65+'] ;
						$object['females']['estimations'][$year]['data']['sum'] += $row['females']['estimations'][$year]['data']['sum'] ;
						$object['females']['estimations'][$year]['data']['diff'] += $row['females']['estimations'][$year]['data']['diff'] ;

						$object['females']['estimations'][$year]['changes']['0_65'] += $row['females']['estimations'][$year]['changes']['0_65'] ;
						$object['females']['estimations'][$year]['changes']['65+'] += $row['females']['estimations'][$year]['changes']['65+'] ;
						$object['females']['estimations'][$year]['changes']['sum'] += $row['females']['estimations'][$year]['changes']['sum'] ;
					}

					// females
				}

				$object['country'] = implode( ', ', $object['country'] ) ; 
				$object['cancer'] = implode( ', ', $object['cancer'] ) ; 

				$dataset = [ $object ] ; 

			} // 

			return [ 'data' => $dataset , 'max' => $max_value ] ; 
		}


		public static function getMaxPredictions( $params )
		{
			global $o_bdd ; 

			$query = " SELECT MAX(TOTAL) FROM globocan2012_ppop WHERE SEX <> 0 AND COUNTRY IN (".implode(',',$params['populations']).") LIMIT 1" ;

			$execute = $o_bdd->query( $query ) ; 
			$total 	= $execute->fetch(PDO::FETCH_NUM) ;  

			return $total ; 
		}

		public static function getCumulatedPopulations ( $params )
		{
			global $o_bdd , $conf , $settings ; 

			$year = $settings['year'] ; 
			$table = "globocan{$year}_pop" ; 
			$sum_query = 'SUM(TOTAL)' ; 

			if ( $params['ages'] != 'all')
			{
				$ages = [] ; 
				for( $i = $params['ages'][0] ; $i <= $params['ages'][1] ; $i++ )
				{
					$ages[] = $conf['y'][$year]['ages'][$i]  ;  
				}

				$sum_query = '('.implode('+',$ages).')' ; 
			}

			if ( count($params['countries']) == 0 ) 
				return [] ; 

			$query 		 = " SELECT $sum_query" ; 
			$query 		.= " FROM $table WHERE" ;
			$query 		.= " COUNTRY IN (".implode(',',$params['countries']). ") AND SEX= ".$params['sex'] ;
			
			// var_dump($params['countries']);
			// echo $query .'<br>' ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$results 	= $execute->fetchAll(PDO::FETCH_COLUMN) ; 

			return ( count( $results ) == 1 ) ? $results[0] : array_sum( $results ) ; 
		}

		/**
		* Get total pop (for population by cancer incidence by cancer > bar chart) and not each field
		*
		*/ 
		public static function getTotalPop( $params , $one = false )
		{
			global $o_bdd ; 

			$query 		 = " SELECT TOTAL" ; 
			$query 		.= " FROM globocan2012_pop WHERE " ;
			$query 		.= " COUNTRY = ".$params['country']. " AND SEX= ".$params['sex'] ;
			// echo $query .'<br>' ; exit() ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$results 	= ( $one == false ) ? $execute->fetchAll(PDO::FETCH_NUM) : $execute->fetch(PDO::FETCH_NUM) ; 

			return ( count( $results ) == 1 ) ? $results[0] : $results ; 
		}

		public static function filterCancerPopulations( $id_cancer , $pops_males , $pops_females , $pops_both )
		{
			// depending on cancer, return only males or females
			if ( in_array( $id_cancer, [15,16,17,18] )) return $pops_females ; 
			if ( in_array( $id_cancer, [19,20] )) return $pops_males ; 
			return $pops_both ; 
		}


		/**
		* Retrieve all results
		*
		*/ 
		public static function retrieveResults( $params , $prevalence = false , $type = 'all' )
		{
			$age_def 		= 10 ;
			$age_from 		= 0 ;
			$age_to 		= 9 ;
			$nPopulation	= 0 ; 
			$data 			= [] ; 
			$tab_n 			= [] ;	// real values / 1000
			$tab_real_values= [] ; 
			$tab_i 			= [] ;  // indexes
			$tab_p			= [] ;  // percent 
			$tab_cum		= [] ;
			$tab_asr		= [] ; 
			$tab_cr			= [] ; 
			$Total			= 0 ;
			$nCancer		= count( $params['cancers']) ;  
			$nPopulation	= count( $params['type_population']) ; 
			$KEYS 			= [] ; 
			$populations 	= [] ; 
			$total_population=[] ; 

			foreach( $params['type_population'] as $id => $label ) $KEYS[] = $id ; 
			
			$all_countries = CanGlobocan::getCountries( 'COUNTRY' , true , ['type' => $params['type'],'sex' => $params['sex']] ) ; 

			for ( $pop = 0 ; $pop < $nPopulation ; $pop++ )
			{
				for ( $age = 0 ; $age < 10 ; $age++ ) $Cases[ $age ] = 0 ; 
				$numbers = 0 ; 

				// find population for each "country" 
				$results_pop = CanCases::getPop( ['country' => $KEYS[$pop] , 'sex' => $params['sex'] ] , true ) ; 
				for ($age = 0; $age <= 10; $age++) $populations[$age] = $results_pop[$age] ; 

				$total_population[$pop] = CanCases::getTotalPop( ['country' => $KEYS[$pop] , 'sex' => $params['sex'] ] , true ) ; 

				for ( $can = 0 ; $can < $nCancer ; $can++ )
				{	
					// echo " [ $table ] " ; 
					$result 	= CanCases::getNumbers( [ 'country' => $KEYS[$pop] , 'sex' => $params['sex'] , 'type' => $params['type'] , 'cancer' => $params['cancers'][$can] ] , $prevalence , $type ) ; 
					
					if ( $prevalence == true )
					{
						// the cumulate the total  
						$numbers += (int)$result[0] ; 
						// exit( $numbers ) ; 
					}
					else
					{
						// get the 10 fields values
						for ( $age = 0 ; $age < 10 ; $age++ ) $Cases[ $age ] += (int) $result[ $age ]  ; // number of field 
					}
				}

				if ( $prevalence == false ) $numbers = ComputeNUM($Cases,$age_from,$age_to,$age_def) ; 

				$tab_i[$pop] 			= $pop ;
				$vale_updated 			= round( $numbers / 1000 )  ; 
				$tab_n[$pop] 			= $vale_updated ;
				$tab_real_values[$pop]	= $numbers ; 
				$tab_asr[$pop]			= getASR( $Cases, $populations ) ;
				$tab_cum[$pop]			= getCumRisk($Cases,$populations) ;
				$Total 					+= $vale_updated ;
			}

			$cpt 			= 0 ; 
			$populations 	= CanGlobocan::getCountries() ; 
			$scaleTotal 	= $Total / 1000 ; 
			

			// type of 
			if ( $params['sort'] == 'number')
			{

				foreach( $tab_n as $i_k => $v )
				{
					$pr_po 				= CanGlobocan::getPrevalencePop( [ "where" => [  "COUNTRY = ".$KEYS[$i_k] ,  "SEX = {$params['sex']}" ] ]) ; 

					// echo " $tab_real_values[$i_k] * 100 000 / $total_population[$i_k] = ".getProportion( $tab_real_values[$i_k] , $total_population[$i_k] ) ; exit() ; 
					$percent 		= ( $scaleTotal > 0 ) ? round( ( ($v * 100 ) / $scaleTotal) / 1000  , 1 ) : 0 ; 
					$data[] = [ 
						'id'		=> $i_k , 
						'country_id'=> $KEYS[$i_k] , 
						'COUNTRY' => $KEYS[$i_k] , 
						'clean_label'=> $populations[ $KEYS[$i_k] ]['LABEL'] , 
						'country_data' 	=> $all_countries[ $KEYS[$i_k] ] , 
						'label' 	=> ( $params['show_percent'] == true ) ? $populations[ $KEYS[$i_k] ]['LABEL'] . ': ' . $percent . '%' : $populations[ $KEYS[$i_k] ]['LABEL'] , 
						'value' 	=> $v , 
						'real_value'=> $tab_real_values[$i_k],
						'crude_rate'=> getProportion( $tab_real_values[$i_k] , $total_population[$i_k] ),
						'proportion'=> getProportion( $tab_real_values[$i_k] , $pr_po[ $KEYS[$i_k] ]['TOTAL']),
						'asr'		=> (float)$tab_asr[$i_k],
						'cum_risk'	=> (float)$tab_cum[$i_k],
						'percent' 	=> $percent, 
						'eDisplay'	=> $all_countries[ $KEYS[$i_k] ]['eDISPLAY']
					] ; 
				}
			}
			else if ( $params['sort'] == 'label')
			{
				asort( $params['type_population'] ); 

				foreach( $params['type_population'] as $key => $continent )
				{
					$percent = ( $scaleTotal > 0 ) ? round( (  ($tab_n[$cpt] * 100 ) / $scaleTotal) / 1000 , 1 ) : 0 ; 
					$data[] = [ 
						'id'		=> $key , 
						'COUNTRY' => $KEYS[$i_k] ,
						'clean_label'=> $populations[ $KEYS[$i_k] ]['LABEL'] , 
						'country_data' 	=> $all_countries[ $KEYS[$i_k] ] , 
						'label' 	=> ( $params['show_percent'] == true ) ?  $continent . ': '. $percent . '%'  : $continent , 
						'value' 	=> $tab_n[$cpt] ,
						'real_value'=> $tab_real_values[$cpt], 
						'crude_rate'=> getProportion( $tab_n[$cpt] , $total_population[$cpt] ),
						'asr'		=> (float)$tab_asr[$cpt],
						'cum_risk'	=> $tab_cum[$cpt],
						'percent' 	=> $percent , 
						'total'		=> $scaleTotal ,
						'eDisplay'	=> $all_countries[ $KEYS[$cpt] ]['eDISPLAY']
						// 'percent_calulation' => [ ($tab_n[$cpt]*100)/$scaleTotal , round( ($tab_n[$cpt]*100)/$scaleTotal  , 1 ) ]
					] ; 
					$cpt++ ; 
				}
			}

			return $data ; 
		}

		public static function getTableData( $url_api , $query_string , $keep_sorting = false  )
		{
			// init
			// echo  "$url_api?$query_string" ; echo '<br>' ; 
			
			$json 			= file_get_contents( "$url_api?$query_string" );
			$data_obj_from 	= json_decode($json);

			$data_tmp 	= [] ;
			$data 		= [] ;

			// var_dump($data_obj_from);

			if (isset($data_obj_from->data) && count($data_obj_from->data) > 0 )
			{	
				// transtype from object to array
				foreach( $data_obj_from->data as $o_c ) 
				{
					$data_tmp[] = (array)$o_c ;  
				}

				if ( $keep_sorting == false )
				{
					// sort by label 
					sksort( $data_tmp , 'label', true ) ; 
				}
				
				// re index by cancer/country key
				foreach( $data_tmp as $v ) $data[ $v['id'] ] = $v ; 
			}
			
			return [ 'title' => $data_obj_from->title ,  'data' => $data , 'all' => isset( $data_obj_from->all ) ? $data_obj_from->all : null ] ; 
		}

	}		