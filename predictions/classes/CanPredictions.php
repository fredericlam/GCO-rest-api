<?php

	class CanPredictions
	{	

		public static function getWStdAges()
		{
			return [ '0_14','15_39','40_44','45_49','50_54','55_59','60_64','65_69','70_74','75'] ; 
		}

		public static function getPredictions( $settings )
		{
			global $conf ; 

			$year_reference 	= $settings['year'] ; 
			$std_ages 			= $conf['y'][ $settings['year'] ]['ages'] ; 
			$output 			= [] ; 

			$settings['items'] 		= CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
			$settings['mode_population'] = 'countriesfd' ;
			$settings['sub_mode'] 	= 'population' ;
			$settings['show_ages']  = true ;
			// $settings['grouped']  = false ; 
			// debug( $settings ) ; exit() ; 

			// get data from reference year 
			$numbers = CanGlobocan::getNumbers( $settings ) ; 
			$numbers = CanGlobocan::optimizeNumbers( $numbers ); 
			
			// debug( $numbers ) ; exit() ; 
			// $cancer_id = $settings['cancer'][]

			$values = [] ;
			foreach ( $numbers as $key => $number )
			{
				$cancer_id = $settings['cancers'][0] ; 
				// debug( $number['country'] )  ;
				//debug( $number['cancers'][ (int)$cancer_id  ][ 'ages'] ) ; exit() ; 
				// values
				$values[ (int)$number['country'] ][ (int)$number['sex'] ] = [] ; 
				foreach ( $std_ages as $age ) 
				{
					$values[ (int)$number['country']  ][ (int)$number['sex'] ][ $age ] = (int)$number['cancers'][ (int)$cancer_id  ][ 'ages'][ $age ] ; 
				} // end foreach 
			}
			
			if ( empty( $values ) ) return [] ; 

			// debug( $values ) ; exit() ; 
		
			// get predictions
			$pop_predictions = CanGlobocan::getPopulationsPredictions( $settings ) ; 
			
			// debug( $pop_predictions ) ; exit() ; 

			$pred = [] ;
			$key_young = 14 ; 
			$data_prediction = [] ; 

			foreach( $pop_predictions as $i => $prediction )
			{				
				$prediction['prediction'] = 0 ; 

				//if( !isset( $data_prediction[(int)$prediction['country']] )) $data_prediction[(int)$prediction['country']] = [] ; 
				
				if( !isset( $data_prediction[(int)$prediction['country']][(int)$prediction['year']][(int)$prediction['sex']] )) 
				{
					$data_prediction[(int)$prediction['country']][(int)$prediction['year']][(int)$prediction['sex']] = [ 
						'value' => 0 , 
						'apc' => 0 , 
						'ages' => [] 
					] ; 
				}

				// $data_prediction[(int)$prediction['country']][(int)$prediction['year']][(int)$prediction['sex']] = [ 'value' => 0 , 'apc' => 0 , 'ages' => [] ] ; 
				$data_prediction_row = $data_prediction[(int)$prediction['country']][(int)$prediction['year']][(int)$prediction['sex']] ; 

				// $prediction['year_reference'] 		= $year_reference ; 

				$reference_pop = CanCases::getPop( [ 'country' => (int)$prediction['country'] ,  'sex' => $prediction['sex'] ] , true ) ; 
				// var_dump($prediction);
				// debug($reference_pop);

				// populations predictions  init
				$pred[ (int)$prediction['country'] ] = [] ; 
				$predictions_ages 		= [] ;
				$age_groups_pred_apc 	= [] ; 

				foreach ( $std_ages as $k => $age ) 
				{
					$str_age = str_replace( 'N' , 'P' , $age ) ; 

					$pred[ (int)$prediction['country'] ][ $str_age ] = (int)$prediction['ages'][ $str_age ] ; 
					$value_age 		= $values[ (int)$prediction['country'] ][ $prediction['sex'] ][ $age ] ; 
					$ref_pop_age 	= $reference_pop[ $k ] ;
					$pred_age 		= (int)$prediction['ages'][ $str_age ] ;
					
					$pred_value 	= round( ( $value_age / $ref_pop_age ) * $pred_age ) ;  	

					// echo "[{$prediction['year']} - {$prediction['country']} - ({$prediction['sex']})] = {$value_age} / {$ref_pop_age} * {$pred_age} = $pred_value <br> " ; 

					$data_prediction_row['value'] += $pred_value ; 
					$predictions_ages[$age ] = $pred_value ; 

					// factor 
					# calculate rate per 10000 per age group
					$per100000_ref = $value_age / $ref_pop_age ; 

					if ( (int)$prediction['sex'] > 0 )
					{
						# calculate factor
						$f_apc 	= (float)$settings['apc'][ $prediction['sex'] - 1 ]  ; 
						$factor = pow( ( 1 + ( $f_apc / 100 ) ) , ( (int)$prediction['year'] - (int)$settings['year'] ) ) ; 
						
						$number_of_cases_pred 	= $per100000_ref * $factor * $pred_age ; 

						// echo "[ $k =>{$per100000_ref} * {$factor} * {$pred_age} = $number_of_cases_pred <br> " ; 

						$age_groups_pred_apc[] 	= round( $number_of_cases_pred )  ; 
					}
				}


				// output results

				if ( (int)$prediction['sex'] > 0 )
				{
					// prediction data
					$data_prediction_row['apc'] = sum( $age_groups_pred_apc ) ; 
					$data_prediction_row['apc_0_65'] = sum( array_slice($age_groups_pred_apc , 0 , $key_young  ) ) ; 
					$data_prediction_row['apc_65+'] = sum( array_slice($age_groups_pred_apc , $key_young  , count($age_groups_pred_apc) -1 ) ) ; 
				}

				$data_prediction_row['ages'] = $predictions_ages ; 
				// calculate youg/adults
				$predictions_ages_values 		= array_values($data_prediction_row['ages']) ; 
				$data_prediction_row['0_65'] = sum( array_slice( $predictions_ages_values , 0 , $key_young  ) ) ; 
				$data_prediction_row['65+'] = sum( array_slice( $predictions_ages_values , $key_young  , count($predictions_ages_values) -1 ) ) ; 

				$prediction['prediction']		= $data_prediction_row ;

				// reference 
				$references_per_ages			= $values[ (int)$prediction['country'] ][ $prediction['sex'] ] ; 

				/*if ( $references_per_ages == NULL) {
					debug( $values ) ; 
					debug( $prediction['country'] ) ; 
					debug( $prediction['sex'] ) ; 
					exit('stop') ; 
				}*/
				$prediction['reference']			= [
					'value' 		=> sum( $references_per_ages ) , 
					'0_65'			=> sum( array_slice( $references_per_ages , 0 , $key_young  ) ) , 
					'65+'			=> sum( array_slice( $references_per_ages , $key_young , count( $references_per_ages ) - 1 ) ), 
					'population' 	=> $prediction['total'] , 
					'year'			=> $year_reference 
				] ; 

				/*$prediction['cases_reference']		= sum( $values[ (int)$prediction['country'] ][ $prediction['sex'] ] ) ; 
				$prediction['population_reference'] = $prediction['total'] ; */
				
				// changes 
				$change = $data_prediction_row['value'] - $prediction['reference']['value'] ; 
				$prediction['changes'] = [ 'structural' => [] , 'risk' => [] , 'overall' => [] ] ; 

				$prediction['changes']['structural'] = [
					'value' 	=> $change ,
					'0_65'		=> $data_prediction_row['0_65'] - $prediction['reference']['0_65'] , 
					'65+'		=> $data_prediction_row['65+'] - $prediction['reference']['65+'] ,
					'percent' 	=> ( $change * 100 ) / $prediction['reference']['value'] , 
					'percent_0_65' => ( ($data_prediction_row['0_65'] - $prediction['reference']['0_65']) * 100 ) / $prediction['reference']['0_65'], 
					'percent_65+' => ( ($data_prediction_row['65+'] - $prediction['reference']['65+']) * 100 ) / $prediction['reference']['65+'], 
				] ; 
 
				// apc
				// $prediction['apc_pred']				= $age_groups_pred_apc ; 
				$change_risk 	= $data_prediction_row['apc'] - $data_prediction_row['value'] ; 

				if ( (int)$prediction['sex'] > 0 )
				{
					$prediction['changes']['risk'] = [
						'value' 		=> $change_risk ,
						'0_65'		=> $data_prediction_row['apc_0_65'] - $data_prediction_row['0_65']  , 
						'65+'		=> $data_prediction_row['apc_65+'] - $data_prediction_row['65+']  ,
						'percent' 	=> ( $change_risk * 100 ) / $data_prediction_row['value'] , 
						'percent_0_65' => ( ($data_prediction_row['apc_0_65'] - $data_prediction_row['0_65']) * 100 ) / $data_prediction_row['0_65'], 
						'percent_65+' => ( ($data_prediction_row['apc_65+'] - $data_prediction_row['65+']) * 100 ) / $data_prediction_row['65+'], 
					] ; 
				}

				$change_overall = $data_prediction_row['apc'] - $prediction['reference']['value'] ;
				$change_overall_65 = 0 ; 
				$change_overall_65_plus = 0 ; 
				
				if ( (int)$prediction['sex'] > 0 )
				{
					$change_overall_65 = $data_prediction_row['apc_0_65'] - $prediction['reference']['0_65'] ;
					$change_overall_65_plus = $data_prediction_row['apc_65+'] - $prediction['reference']['65+'] ; 
				}

				$prediction['changes']['overall'] = [
					'value' 	=> $change_overall ,
					'0_65'		=> $change_overall_65  , 
					'65+'		=> $change_overall_65_plus  ,
					'percent' 	=> ( $change_overall * 100 ) / $prediction['reference']['value'] , 
					'percent_0_65' => ( $change_overall_65 * 100 ) / $prediction['reference']['0_65'] , 
					'percent_65+' => ( $change_overall_65_plus * 100 ) / $prediction['reference']['65+'] , 
				] ; 
	
				unset( $prediction['total']) ; 

				$output[$i] = $prediction ; 
			
				if ( isset($_GET['show_ages']) && $_GET['show_ages'] == 1 )
				{

				}
				else
				{
					unset( $output[ $i ][ 'ages' ] ) ; 
				}

				unset( $output[ $i ][ 'ages' ] ) ; 
			}

			// exit() ; 
			return $output ; 
		}

		public static function getPerPopulations( $params )
		{
			global $o_bdd ; 

			$years = [ 2015 , 2020 , 2025 , 2030 , 2035 ] ;
			$start_year = 2012 ; 

			$countries 	= CanGlobocan::getCountries();
			$ages = self::getWStdAges() ; 

			$get_type = $params['type'] ; 
			$get_cancers = $params['cancers'] ;  
			$get_remove = $params['remove'] ; 

			$query 	 = " SELECT " ; 
			$query 	 .= " t1.CONTINENT, t1.COUNTRY, t1.TYPE , t1.SEX, t1.CANCER, "; 
			$query 	 .= " t1.N0_14,t1.N15_39,t1.N40_44,t1.N45_49,t1.N50_54,t1.N55_59,t1.N60_64,t1.N65_69,t1.N70_74,t1.N75, " ; 
			$query   .= " t2.AGE_DEF,t2.P0_14,t2.P15_39,t2.P40_44,t2.P45_49,t2.P50_54,t2.P55_59,t2.P60_64,t2.P65_69,t2.P70_74,t2.P75 "; 

			$query 	.= " FROM globocan2012_numbers t1 , globocan2012_pop t2 WHERE " ;
			$query  .= " t2.SEX = ".$params['sex']." AND t1.COUNTRY = t2.COUNTRY AND" ;
			$query  .= " t1.SEX = ".$params['sex']." AND t1.TYPE = ".$params['type']  ; 
			
			if ( $get_cancers != 'all') 
				$query 	.= " AND t1.CANCER = " .$get_cancers ;
			else
				$query 	.= " AND t1.CANCER <> 29 " ;

			// echo $query ; 

			$execute = $o_bdd->query( $query ) ; 
			
			$dataset = [] ; 

			while(  $result = $execute->fetch( )  )
			{
				$id_country = $result['COUNTRY'] ; 

				// echo "===== {$result['COUNTRY']} ". $countries[ $result['COUNTRY'] ]['LABEL'] ."==== <br>" ; 

				// populations
				$populations = [] ;
				foreach ( $ages as $age ) $populations[] = (int)$result[ "P$age" ] ; 

				// values
				$values = [] ;
				foreach ( $ages as $age ) $values[] = (int)$result[ "N$age" ] ; 

				$data = [ 2012 => sum( $values ) ] ; 

				foreach( $years as $iYear => $year )
				{
					$predictions = [] ; 
					
					if ( !isset( $data[ $year ] )) $data[ $year ] = 0 ; 

					$results = CanCases::getPopByYear( ['country' => $id_country , 'sex' => $params['sex'] ] , true , $year  ) ; 
					for ($age = 0; $age < 10; $age++) 
					{
						if ( !isset( $predictions[$year] ))  $predictions[$year] = [] ; 
						$predictions[$year][$age] = (int)$results[ $age ] ; 
					}
					
					for ( $age = 0 ; $age < 10 ; $age++ )
					{
						if ( $populations[$age] == 0 )
							$value = 0 ; 
						else
							$value =  round( ( $values[$age] / $populations[$age] ) * $predictions[$year][$age] ) ;  						
						
						// echo "[$year - $age - {$data[ $year ]}] = {$values[$age]} / {$populations[$age]} * {$predictions[$year][$age]} = $value <br> " ; 
						
						$data[ $year ] += $value ; 
					}
				}

				$item = [
					'country' => $countries[ $id_country ]['LABEL'] , 
					'country_id' => $id_country , 
					'cancer_id' => $result['CANCER'] ,
					'sex' => $result['SEX'] , 
					'type' => $result['TYPE'] , 
					'data' => $data
				] ; 

				// debug( $item ) ; exit() ; 	

				$dataset[] = $item ; 
			}

			return $dataset ; 

		}
	}

?>