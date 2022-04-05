<?php

	class CanGlobocan
	{	
		public static function getWStdAges()
		{
			return [ '0-14','15-39','40-44','45-49','50-54','55-59','60-64','65-69','70-74','75+'] ; 
		}

		public static function imp( $data ){
			return ( is_array( $data ) ) ? "(".implode(',',$data).")" : '()' ; 
		}

		/**
		* Retrieve all sexes
		* @param (no param)
		*/ 
		public static function getSexes()
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM globocan2012_sex " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() ) $result[ $data['SEX'] ] = $data->LABEL ;  
			
			return $result ; 
		}

		/**
		* Retrieve all sexes
		* @param (no param)
		*/ 
		public static function getAreas()
		{
			global $o_bdd , $conf ;


			$query 		= " SELECT AREA as id , LABEL as label, globocan_id , globocan_id as country , color FROM area WHERE AREA > 0 AND AREA < 99" ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result = $execute->fetchAll(PDO::FETCH_ASSOC) ;
			
			return $result ; 
		}


		public static function getPopulationsPredictions( $settings )
		{	
			global $o_bdd , $conf ; 

			$year = $settings['year'] ; 
			$table = "globocan{$year}_predictions" ; 
			$table_pop = "globocan{$year}_country" ; 

			$populations = self::imp( $settings['populations'] ) ; 
			$sexes = self::imp( $settings['sexes'] ) ; 
			$predictions_year = ( $settings['predictions_year'][0] == 'all' ) ? '' : self::imp( $settings['predictions_year']) ; 
			$query_year = ( $predictions_year == ''  ) ? '' : " AND year IN $predictions_year " ; 

			$query 	= " SELECT t1.* , t2.label , t2.iso_3_code FROM $table t1 , $table_pop t2  WHERE t1.country = t2.country AND t1.country IN $populations AND t1.sex IN $sexes $query_year" ;

			// echo $query ; 
			$execute = $o_bdd->query( $query ) ; 
			$result = $execute->fetchAll(PDO::FETCH_ASSOC) ;

			$output = [] ; 

			foreach( $result as $i => $row )
			{
				/*foreach( $result[$i] as $field => $value )
				{
					$result[$i][$field] = (float)$value ; 
				}*/

				$data = [
					//"id" 		=> (int)$row['id'] , 
					"country" 	=> (int)$row['country'] , 
					"year" 		=> (int)$row['year'] , 
					"sex" 		=> (int)$row['sex'] , 
					"total" 	=> (int)$row['total']
				] ; 	

				if ( $settings['show_details'] == true ) 
				{
					$data['label'] = $row['label'] ; 
					$data['iso_3_code'] = $row['iso_3_code'] ; 
				}
				

				$ages = [] ; 

				foreach( $conf['y'][ $settings['year'] ]['ages'] as $age ) 
				{
					$field = str_replace('N','P', $age) ; 
					$ages[$field] 		= (int)$row[$field] ;  
				}


				if ( $settings['show_ages'] == true ) $data['ages'] = $ages ; 

				$output[] = $data ; 
			}

			return $output ; 
		}

		/**
		* Check if a country is upper than 900: if true, then it's a grouping population that needs to be exploded > a list of countries id
		* @param (array) list of settings
		* @return (array) list of countries id
		*/
		public static function getChildren( $country_id )
		{
			global $geography ; 

			$group_info = "" ; 

			if ( (int)$country_id > 900 )
			{
				// search for children 
				foreach( $geography as $group_key => $grouping )
				{	
					// loops on sub group
					foreach( $grouping['groups'] as $group )
					{
						// debug( $group ) ; 

						$group_globocan_id = ( isset( $group['country']) ) ?  $group['country'] : ( (isset( $group['globocan_id'])) ? $group['globocan_id'] : null )  ;

						// compare globocan id and current one
						if ( (int)$group_globocan_id == (int)$country_id && (int)$group_globocan_id != null )
						{
							// echo "{$grouping['field']} {$group_globocan_id} {$group['id']}" ; 

							$group_info	 		= $group ; 
							$group_info['ids'] 	= self::getCountriesPerGroup( $grouping['field'] , $group['id'] ); 
							break ; 
						}
					}
				}
			}

			//var_dump($group_info);
			return $group_info ;
		}

		/**
		* Retrieve getNumbers
		* @param (array) list of settings
		* @return (array)
		*/
		public static function getNumbers( $settings ){

			global $o_bdd , $conf ;

			// build cancers per id 
			if ( $settings['sub_mode'] == 'cancer')
			{
				$cancers_list =  $settings['items'] ; 

				$cancers = [] ; 
				foreach( $cancers_list as $cancer ) $cancers[ $cancer['cancer'] ] = $cancer ;  

				$populations_array = [] ; 

				// check if a population is a grouping: if yes, then split it into a sub groups
				// ex /api/globocan/v1/2018/numbers/cancer/0/1/250_966_964/all/
				foreach ( $settings['populations'] as $id )
				{
					$group_info = self::getChildren( $id );

					// var_dump($group_inf) ; 

					/*if ( isset( $group_info['ids'] ) &&  count( $group_info['ids'] ) > 0 )
					{
						$populations_array = array_merge( $populations_array , $group_info['ids'] ) ; 
						// var_dump($populations_array);
					}*/
					
					$populations_array[] = $id  ; 
					
				}
			}
			//else if ( $settings['allow_search_group'] == true ) //"sfsfsfs" ) // then we are in a population mode 
			// @todo: get list of countries + any grouping
			else if ( $settings['sub_mode'] == 'population')
			{
				$pop_list =  $settings['items'] ; 
				$countries = [] ; 
				foreach( $pop_list as $pop ) $countries[ $pop['country'] ] = $pop ; 

				// here, for each population, we compute the countries in a first table, then as many grouping as need (example, Spain, Togo, + WHO )
				// ex : /api/globocan/v1/2018/numbers/populations/0/1/840_388_967_957/39/
				$search_group = false ; 
				$populations_groups_array = []; 
				$populations_array = []; 

				// var_dump($settings['populations']);

				foreach ( $settings['populations'] as $id )
				{
					$populations_array[] = $id ; 

					// @deprecated (don't check if an id is a grouping)
					/*$group_info = self::getChildren( $id );
					if ( isset( $group_info['ids'] ) &&  count( $group_info['ids'] ) > 0 ) 
					{
						$populations_groups_array[] = $group_info ; 
						$search_group = true ; 
					}
					else
					{
						$populations_array[] = $id ; 
					}*/

					
				}

				// if a group is defined, then we have to loop on the data to group populations
				//if ( $search_group == true ){
				if ( false == true ){
					$pop_group_numbers = [] ; 
					// debug( $populations_groups_array ) ; 
					
					foreach( $populations_groups_array as $pop_group ) 
					{
						// fix the type of items, we want a grouping pop
						$settings['items'] = CanGlobocan::getCancers(true,false,$settings) ;
						$settings['populations'] = $pop_group['ids'] ;
						$settings['sub_mode'] = 'cancer' ; 

						// get the number
						$numbers = self::getNumbers( $settings ) ;
						$numbers = self::optimizeNumbers( $numbers ) ; 

						foreach( $numbers as $k => $number )
						{	
							$numbers[$k]['label'] = $pop_group['label'] ; 
						}

						$pop_group_numbers[] = $numbers ; 
					} 
					
					$settings['items'] = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $settings ) ; 
					$settings['sub_mode'] = 'population' ; 
					$settings['populations'] = $populations_array ; 
					$numbers = self::getNumbers( $settings ) ; 
					$output = self::optimizeNumbers( $numbers ) ; 

					foreach( $pop_group_numbers as $pop_group )
					{
						foreach( $pop_group as $row ) $output[] = $row ; 
					}
					
					return $output ; 
				}
			}
			else
			{
				$populations_array = $settings['population'] ; 
			}	

			// then implode list of ids for query
			$populations = self::imp( $populations_array ) ; 

			$year = $settings['year'] ; 
			
			if ( $settings['type'] == 2 )
			{
				$table = "globocan{$year}_prevalence" ; 
				$query = " SELECT t1.*,t2.who_region,t2.continent,t2.area,t2.hub,t2.hdi_group_2015,t2.hdi_group_2018,t3.ICD,t3.label as cancer_label FROM $table t1 " ; 

				// left join to get information on population (continent, area, hdi , hub ... )
				$query .= "LEFT JOIN globocan{$year}_country t2 ON t2.country = t1.country " ; 
				$query .= "LEFT JOIN globocan{$year}_cancer t3 ON t3.cancer = t1.cancer " ; 

				// Where clause
				$query .= "WHERE " ; 

				$stat_query = ( $settings['statistic'] == 'all' ) ? '(1,3,5)' : "({$settings['statistic']})" ;
				$query .= "survival IN $stat_query AND " ; 
			}
			else
			{
				$table = "globocan{$year}_numbers" ; 
				$query = " SELECT t1.*,t2.who_region,t2.continent,t2.area,t2.hub,t2.hdi_group_2015,t2.hdi_group_2018,t2.hdi_2015,t2.hdi_2018,t3.ICD,t3.label as cancer_label,t2.total as total_population FROM $table t1 " ; 

				// left join to get information on population (continent, area, hdi , hub ... )
				$query .= "LEFT JOIN globocan{$year}_country t2 ON t2.country = t1.country " ; 
				$query .= "LEFT JOIN globocan{$year}_cancer t3 ON t3.cancer = t1.cancer " ; 

				// Where clause
				$query .= "WHERE " ; 
				$query .= "type IN ".self::imp($settings['types'])." AND " ; 
			}


			$query .= "sex IN ".self::imp($settings['sexes'])." AND " ;

			// specific mode (all countries)
		 	// debug( $populations ) ; 
			
			if ( $settings['population'] == "all" )
				$query .= "t1.country < 900 AND " ;
			else if ( $settings['population'] == "all_with_world" )
				$query .= "t1.country <= 900 AND " ;
			else if ( $populations != "()" )
				$query .= "t1.country IN ".$populations." AND " ;

			// if key word == all, get all cancers except all 
			if ( $settings['cancers'][0] != 'all') 
			{
				$cancers_ids  = $settings['cancers'] ; 

				// refuse grouping if force not
				$groups_cancers = $conf['y'][ $year ]['grouped_cancers' ] ;
				
				foreach( $cancers_ids as $can )
				{
					// loop through cancer to get all data for cancer grouping 
					foreach( $groups_cancers as $g )
					{				
	                	if ( $can == $g['cancer'] )
	                	{
	                		$cancers_ids = array_merge( $cancers_ids , $g['ids'] ) ; 
	                		break ; 
	                	}
					}
				}

				$query .= "t1.cancer IN ".self::imp( $cancers_ids )." " ;
			}
			else 
			{

				$cancers_ids = array_merge( $conf['y'][ $settings['year'] ]['all_cancers_id'] , $conf['y'][ $settings['year'] ]['forbidden_cancers'] , $conf['y'][ $settings['year'] ]['hidden_cancers']  ) ; 

				if ( (bool)$settings['include_nmsc'] == false )
				{					
					// not include, so we include it in the exluding query
					array_push( $cancers_ids , $conf['y'][ $year ][ 'nmsc_cancers' ]) ; 

					// we all all cancers + value of total (all cancers)
					if ( $settings['cancers'][0] == "all" && 
						isset( $settings['cancers'][1] ) && $settings['cancers'][1] == "with" && 
						isset( $settings['cancers'][2] ) &&  $settings['cancers'][2] == "all" )
					{
						array_splice( $cancers_ids, array_search( 40 , $cancers_ids ) , 1 );
					}
				}
				else // include nmsc true
				{
					if ( $settings['cancers'][0] == "all" && 
						isset( $settings['cancers'][1] ) && $settings['cancers'][1] == "with" && 
						isset( $settings['cancers'][2] ) &&  $settings['cancers'][2] == "all" )
					{
						array_splice( $cancers_ids, array_search( 39 , $cancers_ids ) , 1 );
					}

					if((bool)$settings['include_nmsc_other'] == true )
					{
						array_push( $cancers_ids , $conf['y'][ $settings['year'] ]['nmsc_cancers'] ) ;
					}
				}

				// check grouping cancer


				$query .= "t1.cancer NOT IN ".self::imp( $cancers_ids )." " ; 
			}

			if ( $settings['type'] == 2 )
				$query .= " ORDER BY total {$settings['sort_dir']} " ;
			else
				$query .= " ORDER BY {$settings['sort']} {$settings['sort_dir']} " ; 

			// echo $query .'<br>' ; 
			
			$execute = $o_bdd->query( $query ) ; 
			$result = [] ; 

			if ( $settings['type'] == 2)
			{
				$items = self::getCancers(true,false,$settings) ;
			}

			while ( $data_row = $execute->fetch() )
			{
				$label = "" ; 

				if( $settings['sub_mode']=='cancer' ) 
					$label = $settings['items'][(int)$data_row['cancer']]['label'] ; 
				else{
					if ( !isset( $settings['items'][(int)$data_row['country']]  ) )
					{
						//debug($data_row); exit() ; 
					}
					$label = (isset($settings['items'][(int)$data_row['country']])) ? $settings['items'][(int)$data_row['country']]['label'] : '' ;
				}

				if ( $settings['type'] == 2 )
				{
					$row = [
						"label"		=> $label  , 
						"continent" => (int)$data_row['continent'] ,
					    "area"		=> (int)$data_row['area'] ,
					    "who_region"=> $data_row['who_region'] , 
					    "hub"		=> $data_row['hub'] , 		
					    "hdi_group_2015" => $data_row['hdi_group_2015'] , 
					    "hdi_group_2018" => $data_row['hdi_group_2018'] , 
					    "country"	=> (int)$data_row['country'] ,
					    "prop"		=> (float)$data_row['prop'] ,
					    "sex"		=> (int)$data_row['sex'] ,
					    "cancer"	=> (int)$data_row['cancer'] ,
					    "cancer_label" => $data_row['cancer_label'] , 
	    				"total"		=> (int)$data_row['total'] , 
	    				"ages"		=> [] , 
	    				"ICD"		=> $data_row['ICD'] , 
	    				"survival"	=> $data_row['survival'] , 
	    				"type"		=> $settings['type'] ,
					] ; 

				}
				else
				{
					$asr = 0 ;
					/*$cases_ages = [] ; 
					$sex = $data_row['sex'] ; 
					$ages_values = array_values( $data_row['ages'] ) ; 

					for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
					{	
						$age = $ages_values[$i] ;

						if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
						$cases_ages[$i] += $age ; 	
					}

					$gender = ( $items[$data_row['cancer']]['gender'] != 0 ) ? $items[$data_row['cancer']]['gender'] : $sex ; 

					$total_population = CanCases::getCumulatedPopulations([
						'countries' => [$data_row['country']] , 
						'sex' 		=> $gender ,  
						'ages' 		=> 'all' 
					]); 

					$populations = CanCases::getPop( [
						'country' =>  $data_row['country'] , 
						'sex' => $gender ] , 
						true ) ; 

					$asr 		= getASRPopulation( $cases_ages, $populations ) ;*/
					//$crude_rate = getCrudeRate( array_sum($cases_ages) , $total_population ) ;

					/*if ( $settings['ages_group'] == 'all' || $settings['ages_group'][1] == 17 )
						$cum_risk = 0 ; 
					else
						$cum_risk 	= getCumRiskPopulation(  $cases_ages, $populations ) ;*/

					$row = [
						"label"		=> $label  , 
						"continent" => (int)$data_row['continent'] ,
					    "area"		=> (int)$data_row['area'] ,
					    "who_region"=> $data_row['who_region'] , 
					    "hub"		=> $data_row['hub'] , 		
					    "hdi" 		=> (int)$data_row['hdi_group_2018'] , 
					    "hdi_value"	=> (float)$data_row['hdi_2018'] , 
					    "country"	=> (int)$data_row['country'] ,
					    "type"		=> (int)$data_row['type'] ,
					    "sex"		=> (int)$data_row['sex'] ,
					    "cancer"	=> (int)$data_row['cancer'] ,
					    "cancer_label" => $data_row['cancer_label'] , 
	    				"total"		=> (int)$data_row['total'] , 
	    				// "color"		=>  isset( $data_row['color']) ? $data_row['color'] : '' ,
	    				"total_population" => (int)$data_row['total_population'] ,
	    				//"ages"		=> [] , 
	    				"ICD"		=> $data_row['ICD'] ,

	    				"asr"		=> ($data_row['asr'] != 0)?(float)$data_row['asr']:$asr ,
	    				"cum_risk"	=> (float)$data_row['cum_risk'] , 
	    				"crude_rate"=> (float)$data_row['crude_rate'] ,

	    				"ui_upper" => (float)$data_row['ui_upper'] , 
	    				"ui_lower" => (float)$data_row['ui_lower']
					] ; 

					// var_dump( $row ); exit() ; 

					/*if ( ( $settings['populations'][0] != 'all' && count($settings['populations']) > 1 ) || ( count( $settings['cancers'] ) > 1 && $settings['cancers'][0] != 'all' ) )
					{
						unset( $row['ui_upper'] ) ; 
						unset( $row['ui_lower'] ) ; 
					}*/
					// var_dump($settings['ages']);
				}

				// var_dump( $row ); exit() ; 

				// loop on ages
				foreach( $conf['y'][ $settings['year'] ]['ages'] as $age ) 
				{
					// $row[$age] 		= (int)$data_row[$age] ;  
					$all_ages[$age]	= (int)$data_row[$age] ; 
				}

				/*if ( $settings['sub_mode'] == 'population' && count( $settings['populations'] ) == 1 ) {
					$row['ages'][(int)$data_row['cancer']] =  $all_ages ; 
				}
				else*/
				
				$row['ages'] =  $all_ages ; 


				// if total == 0, then remove line
				if ( $data_row['total'] >= 0 ) $result[] = $row ;
				
			} // end while 

			// @specific grouping: if population | or cancer are mentionned, then grouping is activated to build sum of result
			// to get all data separately, then add any key word "full" instead of population | cancer
			if ( $settings['grouped'] == 0 ) // if there's no grouping, then get all per population/countries and check if there is a grouping per cancer
			{
				if ( $settings['sub_mode'] == 'population'  ) 
				{
					// debug( $result ) ; exit() ; 
					// first, we have to nest by population: grouping cancers per multiple populations is not possible 
					$result = self::nestPopulationGrouping( $result ); 
					// $result = self::checkGroupingCancers( $result ); 
				}

				// var_dump($result) ; exit() ; 

				if ( $settings['recalculate'] == true )
				{
					$result = self::recalculateRates( $result ) ; 
					// var_dump( $result[0]['asr'] ); exit();
				}

				sksort( $result , $settings['sort'] , ( $settings['sort_dir'] == 'ASC' ) ? true  : false ) ; 
			}
			// grouping by population 
			// ex : http://www.gco.local/api/globocan/v1/2018/numbers/population/0/2/56_40/1_2_3_4/ 
			else if ( ( count( $settings['populations']) > 0 || $settings['population'] == 'all' ) && $settings['sub_mode'] == 'population')
			{
				$result = self::numbersGroupBy( $result , 'country' , $settings['sub_mode'] , $countries , $settings['sex'] ); 
			} 
			// list of cancers or keyword 'all'
			// ex : http://www.gco.local/api/globocan/v1/2018/numbers/cancer/0/2/900_910/1_2_3/
			else if ( ( count( $settings['cancers']) > 0 || $settings['cancers'][0] == 'all' ) && $settings['sub_mode'] == 'cancer' )
			{
				$result = self::numbersGroupBy( $result , 'cancer' , $settings['sub_mode'] , $cancers  , $settings['sex'] ); 
				$result = self::checkGroupingCancers( $result ); 
				sksort( $result , $settings['sort'] , ( $settings['sort_dir'] == 'ASC' ) ? true  : false ) ; 
			}

			$result = array_values( is_array( $result ) ? $result : [] ) ; 

			// var_dump($result); exit();

			if ( $settings['type'] == 2)
			{
				$items = self::getCancers(true,false,$settings) ;

				// var_dump($result);

				foreach( $result as  $key => $row )
				{
					// var_dump($row) ; 

					if ( $row['prop'] == 0 )
					{
						$sex = $row['sex'] ; 
						// for each cancer, check the gender
						$gender = ( $items[$row['cancer']]['gender'] != 0 ) ? $items[$row['cancer']]['gender'] : $sex ; 
						// var_dump(( $items[$row['cancer']]['gender'] != 0 ),$items[$row['cancer']]['gender']);
						// echo " ===>" ; var_dump($row) ;

						$total_population = CanCases::getCumulatedPopulations([
							'countries' => [(isset($row['country']))?$row['country']:$row['id']] , 
							'sex' 		=> $gender ,  
							'ages' 		=> 'all' //$settings['ages_group'] 
						]); 

						$ages_values = array_values( $row['ages'] ) ; 
						if ( is_array( $ages_values[0] ) ) $ages_values = $ages_values[0] ;
						// echo '<pre>' ; var_dump($ages_values) ; echo '</pre>' ; 

						$cases_ages = [] ; 

						$from_fct      = ( isset($_GET['from_fct']) && !empty( $_GET['from_fct'] )) ? true : false ;

						$start =  ( $from_fct == true ) ? 0 : (int)$settings['ages_group'][0] ;

						for( $i = $start ; $i <= (int)$settings['ages_group'][1] ; $i++ )
						{	
							$age = @$ages_values[$i] ;

							if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
							$cases_ages[$i] += $age ; 	
						}

						// tenative get prop.
						$prop = getProportion( array_sum($cases_ages) , $total_population ) ; 
						//debug();
						//debug($total_population);
						//exit();

						$result[$key]['prop'] = $prop ; 
						
					}
				}
			}

			return $result ; 
		}

		/**
		* Group data by a single field
		* @param (array) data to manipulate
		* @param (string) name of key to group by
		* @param (string) sub mode of grouping population | cancer,
		* @param (int) sex 
		* @return (array)
		*/
		public static function numbersGroupBy( $data , $key , $sub_mode , $items = [] , $sex = 0 )
		{	
			global $settings , $conf ; 

			$year = $settings['year'] ; 
			$final = [] ; 

			// debug( $data ) ; exit() ; 

			foreach( $data as $index => $row )
			{
				// control key for population
				$key_row = ( $sub_mode == 'population' && ( $settings['mode_population'] == 'countries' || $settings['grouped'] == true ) && $settings['mode'] != 'predictions' ) ? (int)$row['country'] : $index ; // 

				// controle key for cancer
				if ( $sub_mode == 'cancer' && $settings['grouped'] == true ) 
				{
					$key_row = (int)$row['cancer'] ; 

					// specific to grouping in cancer mode
					/*$groups_cancers = $conf['y'][ $year ]['grouped_cancers' ] ;
					foreach( $groups_cancers as $g )
					{				
						if ( in_array( (int)$row['cancer'] , $g['ids']) == true )
						{
							$key_row = (int)$g['cancer'] ;
						}
					} // end foreach */					
				}

				if ( !isset( $final[ $key_row ]))  
				{
					if ( $settings['type'] == 2 )
					{
						$final[ $key_row ] = [ 
						    "id"		=> ($sub_mode=='cancer') ?  (int)$row['cancer'] : (int)$row['country'] ,
						    "country"	=> ($sub_mode=='cancer') ? [] : (int)$row['country'] ,
						    "area"		=> (int)$row['area'] ,
					    	"continent"	=> (int)$row['continent'] ,
						    "prop"		=> 0 , //(float)$row['prop'] ,
						    "sex"		=> (int)$row['sex'] ,
						    "survival"	=> (int)$row['survival'] , 
						    "label"		=> ($sub_mode=='cancer') ? $items[(int)$row['cancer']]['label'] : $items[(int)$row['country']]['label'] , 
						    "short_label" => ($sub_mode=='cancer') ? $items[(int)$row['cancer']]['short_label'] : "" ,
						    "color" 	=> ($sub_mode=='cancer') ?  $items[(int)$row['cancer']]['color'] : "" ,
						    "cancer"	=> ($sub_mode=='cancer') ? (int)$row['cancer'] : [] ,
						    "ages"		=> [] , 
		    				"total"		=> 0  , 
	    					"ICD"		=> $row['ICD'] ,
	    					"type"		=> $settings['type'] ,
						] ;

					}
					else
					{

						if ($sub_mode=='cancer') {
							$label = $items[(int)$row['cancer']]['label'] ; 
						}
						else{
							$label = (isset($items[(int)$row['country']])) ? $items[(int)$row['country']]['label'] : '' ;
						}

						$final[ $key_row ] = [ 
						    "id"		=> ($sub_mode=='cancer') ?  (int)$row['cancer'] : (int)$row['country'] ,
						    
						    // to be delete later
						    "continent" => (int)$row['continent'] ,
						    "country"	=> ($sub_mode=='cancer') ? [] : (int)$row['country'] ,
						    "cancer"	=> ($sub_mode=='cancer') ? (int)$row['cancer'] : [] ,
						    "color" 	=> ($sub_mode=='cancer') ?  $items[(int)$row['cancer']]['color'] : '' ,
						    
						    "type"		=> (int)$row['type'] ,
						    "sex"		=> (int)$row['sex'] ,
						    
						    "label"		=> $label ,
						    "short_label" => ($sub_mode=='cancer') ? $items[(int)$row['cancer']]['short_label'] : "" ,
						    "ages"		=> [] , 
		    				"total"		=> 0  , 
	    					"ICD"		=> $row['ICD']
						] ; 

						// get per cancer 
						if ( $sub_mode == 'cancer')
						{
							if ( $settings['show_details'] == true )
							{
								// details on main entry point (population)
								$final[ $row[$key] ]['details'] = [
									'label' 	=> $items[(int)$row['country']]['label'] , 
									'country' 	=> (int)$row['country'] , 
									"area"		=> (int)$row['area'] ,
						    		"continent"	=> (int)$row['continent'] , 
						    		'hdi' 		=> '' , 
						    		'hub' 		=> '' 
								] ;
							} 
						} 
						// get per population 
						else if ( $sub_mode == 'population')
						{

							$cancers = [] ; 
							foreach( $settings['cancers'] as $cancer_id )
							{
								// just init
								$cancers[(int)$cancer_id] = [ 
									'id' 	=> (int)$cancer_id , 
									'label' => '' ,
									'total' => 0 , 
									'ages' 	=> [] , 
								] ; 
							}

							// var_dump($final[ $row[$key] ]['cancer_label']);

							$final[ $key_row ]['cancers'] = $cancers ; 

							if ( $settings['show_details'] == true )
							{
								// details on main entry point (population)
								$final[ $key_row ]['details'] = [
									'label' 	=> $items[(int)$row['country']]['label'] , 
									'country' 	=> (int)$row['country'] , 
									"area"		=> (int)$row['area'] ,
						    		"continent"	=> (int)$row['continent'] , 
						    		'hdi' 		=> '' , 
						    		'hub' 		=> '' 
								] ;
							} 
						}


						

						// if mode is one of the 2 population|cancer and not a population grouping
						if ( $settings['sub_mode'] == 'population'  )
						{
							// for populations, if in list, there id > 900
							if ( count($settings['cancers']) <= 1 && $settings['cancers'][0] != 41 && $settings['ages_group'] == 'all')
							{

								$final[ $key_row ]["ui_upper"] = (float)$row['ui_upper'] ;
		    					$final[ $key_row ]["ui_lower"] = (float)$row['ui_lower'] ;
							}
						}
						else if ( $settings['sub_mode'] == 'cancer' )
						{	
							// deifferent cases allowing to show ui
							if ( 
								// get all population OR 1 population
								( $settings['populations'][0] == 'all' || count($settings['populations']) == 1 ) 

								// one cancer at a time
								&& ( count($settings['cancers']) <= 1 

									// all cancers with total (all cancer exclu NMSC)
									|| ($settings['cancers'][0] == "all" && 
											isset( $settings['cancers'][1] ) && $settings['cancers'][1] == "with" && 
											isset( $settings['cancers'][2] ) &&  $settings['cancers'][2] == "all") 
									)  

								// no age group
								&& $settings['ages_group'] == 'all')
							{

								$final[ $key_row ]["ui_upper"] = (float)$row['ui_upper'] ;
		    					$final[ $key_row ]["ui_lower"] = (float)$row['ui_lower'] ;
							}
						}
					}
				}
				/*else
				{
					$final[ $key_row ]['total'] = $total ; 
				}*/
				
				// 
				// var_dump($row['ages']); 

				$total = 0 ; 

				// var_dump($settings['ages']); exit();

				if( $settings['ages_group'] == 'all') 
				{
					// all ages  for 
					// if ( in_array( $row['cancer'] , [8,9,10] ) == true ) var_dump( $row['cancer'] , $row['total'] , $total ); /// exit();
					$total += (int)$row['total'] ;  
				}
				else
				{
					//
					// var_dump($settings['ages_group']); 
					// var_dump($row['ages']); 

					$ages_values = array_values( $row['ages'] ) ; 

					for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
					{	
						$total += (int)$ages_values[$i] ; 
					}	
					// exit(); 
				}

				// cumul
				// echo  $final[ $key_row ]['label'] + " => <br>" ;
				$final[ $key_row ]['total'] 		+= $total ; 

				if ( $sub_mode == 'population')
				{
					//$final[ $row[$key] ]['cancer'][]	= (int)$row['cancer'] ; 
					//$final[ $row[$key] ]['ages'][(int)$row['cancer']]	= $row['ages'] ; 
					
					$final[ $key_row ]['cancers'][ (int)$row['cancer'] ][ 'label' ] 	= (string)$row['cancer_label'] ; 
					$final[ $key_row ]['cancers'][ (int)$row['cancer'] ][ 'total' ] 	= sum( $row['ages'] ) ; 
					$final[ $key_row ]['cancers'][ (int)$row['cancer'] ][ 'ages' ] 	= $row['ages'] ; 
				}
				
				if ( $sub_mode == 'cancer')
				{
					$final[ $key_row ]['country'][]	= (int)$row['country'] ; 
					$final[ $key_row ]['ages'][(int)$row['country']]	= $row['ages'] ; 
				}	
			}

			// debug( $final ) ; exit() ; 

			// 
			switch ( $sub_mode )
			{
				case "cancer" : 

					// calculate extra field (asr, cum_risk, crude rate)
					foreach( $final as $key => $row )
					{
						// if ( $key == 41 ) debug( $row['total'] ) ; 

						// for each cancer, check the gender
						$gender = ( $items[$row['cancer']]['gender'] != 0 ) ? $items[$row['cancer']]['gender'] : $sex ; 

						// population @to optimize
						$populations = [] ; 
						foreach( $row['country'] as $population_id )
						{
							$tmp_pop = CanCases::getPop( [
								'country' => $population_id , 
								'sex' => $gender ] , 
								true ) ; 
							
							if( $settings['ages_group'] != 'all') 
							{
								for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
								{
									$populations_per_ages[$i] = $tmp_pop[$i] ; 
								}
							}
							else
							{
								$populations_per_ages = $tmp_pop ; 
							}

							$populations[$population_id] = $populations_per_ages ; 
						}

						// compute populations 
						$final[ $key ][ 'populations'] = $populations ; 

						// get pop total 
						$total_population = CanCases::getCumulatedPopulations([
							'countries' => $row['country'] , 
							'sex' 		=> $gender ,  
							'ages' 		=> $settings['ages_group']
						]); 
						
						// ages
						$cases_ages = [] ;
						$case_numeros = [] ; 
						foreach( $row['ages'] as $population_id => $row_ages )
						{
							if( $settings['ages_group'] == 'all') 
							{
								$i = 0 ; 
								foreach( $row_ages as $age ) 
								{
									if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
									$cases_ages[$i] += $age ; 	
									$case_numeros[$i][] = $age ; 
									$i++ ; 
								}
							}
							else // specific ages groups
							{

								$ages_values = array_values( $row_ages ) ; 

								for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
								{	
									$age = $ages_values[$i] ;

									if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
									$cases_ages[$i] += $age ; 	
									$case_numeros[$i][] = $age ; 
								}	
							}
						}

						// debug($cases_ages); debug( $populations) ; 
						

						// calculate new values, with cumulated population
						$asr 		= getASRPopulation( $cases_ages, $populations ) ;
						$crude_rate = getCrudeRate( array_sum($cases_ages) , $total_population ) ;

						/*if ( $settings['ages_group'] == 'all' || $settings['ages_group'][1] == 17 )
							$cum_risk = 0 ; 
						else*/
						$cum_risk 	= getCumRiskPopulation(  $cases_ages, $populations ) ;

						// $final[ $key ]['ASR_I'] = $asr ; 
						$final[ $key ]['asr'] = $asr ; 
						$final[ $key ]['crude_rate'] = $crude_rate ; 
						$final[ $key ]['cum_risk'] = $cum_risk ; 

						if ( isset( $final[ $key ]['prop']) )
						{
							$final[ $key ]['prop'] = getProportion( array_sum($cases_ages) , $total_population ) ; 
						}

						// force removing population information
						unset( $final[ $key ][ 'country'] ) ; 
						unset( $final[ $key ][ 'continent'] ) ; 
						unset( $final[ $key ][ 'area'] ) ; 

					} // end foreach 

					

					break ; 

				case "population" :  

					// calculate extra field (asr, cum_risk, crude rate)
					// debug( $settings ) ; exit() ; 

					foreach( $final as $key => $row )
					{
						// debug( $row ) ; 

						$key_population = ( $settings['mode_population'] == 'countries' && $settings['mode'] != 'predictions' ) ? $row['country'] : $key ;

						// for each cancer, check the gender
						$gender = $row['sex'] ; 

						$populations = CanCases::getPop( [
							'country' =>  $row['country'] , 
							'sex' => $gender ] , 
							true ) ; 

						// get pop total 
						$total_population = CanCases::getCumulatedPopulations([
							'countries' => [$row['country']] , 
							'sex' => $gender ,  
							'ages' => $settings['ages_group']
						]); 
						
						// ages
						$cases_ages = [] ;

						//foreach( $row['ages'] as $cancer_id => $row_ages )
						foreach( $row['cancers'] as $cancer_id => $cancer_infos )
						{
							$row_ages = $cancer_infos['ages'] ; 

							if( $settings['ages_group'] == 'all') 
							{
								$i = 0 ; 
								foreach( $row_ages as $age ) 
								{
									if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
									$cases_ages[$i] += $age ; 	
									$i++ ; 
								}
							}
							else
							{
								$ages_values = array_values( $row_ages ) ; 

								if ( count( $ages_values ) > 0 )
								{
									for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
									{	
										$age = $ages_values[$i] ;

										if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
										$cases_ages[$i] += $age ; 	
										$case_numeros[$i][] = $age ; 
									}
								}
							}	
						}

						// debug($row); debug($cases_ages); debug( $populations) ; exit() ; 

						// calculate new values, with cumulated population
						$asr 		= getASRCancer( $cases_ages, $populations ) ; 
						$crude_rate = getCrudeRate( array_sum($cases_ages) , $total_population ) ;

						/*if ( $settings['ages_group'] == 'all' || $settings['ages_group'][1] == 17  )
							$cum_risk = 0 ; 
						else
						*/
						$cum_risk 	= getCumRiskCancer(  $cases_ages, $populations ) ;

						// $final[ $key ]['ASR_I'] = $asr ; 
						$final[ $key_population ]['asr'] = $asr ; 
						$final[ $key_population ]['crude_rate'] = $crude_rate ; 
						$final[ $key_population ]['cum_risk'] = $cum_risk ; 

						if ( isset( $final[ $key_population ]['prop']) )
						{
							$final[ $key_population ]['prop'] = getProportion( array_sum($cases_ages) , $total_population ) ; 
						}

						// uncertainty intervals

						// exit() ; 
						// force removing population information
						unset( $final[ $key_population ][ 'cancer'] ) ; 

					} // end foreach 

					// exit

					break ; 
			}

			// debug( $final ) ; exit() ; 

			return $final ; 
		}

		/**
		* Ages and populations are used in other methods to build grouping 
		* But clearing them optimize the json rendering (and prevent from exposing these datas)
		* @param (array) the array to optimize
		* @return (array)
		*/
		public static function optimizeNumbers( $output ){

			global $settings ; 
			
			foreach( $output as $i => $row )
			{

				// always remove ages index
				// if ( isset( $output[$i]['ages'] ) ) unset( $output[$i]['ages'] ) ; 

				if ( $settings['show_ages'] == false ) 
				{
					// loop in each cancers
					if ( isset( $row['cancers'] ))
					{
						foreach( $row['cancers'] as $r => $cancer_data )
						{
							// debug( $row['cancers'][ $r ][ 'ages' ] ) ; 
							unset( $output[ $i ]['cancers'][ $r ][ 'ages' ] ) ; 
						}
					}
				}
				else
				{
					// check if entry is cancer id 
					if ( $settings['sub_mode'] == 'population' && count( $settings['populations'] ) == 1 && $settings['population'] != 'all' )
					{
						// $ages = $output[$i]['ages'] ; 
						// unset( $output[$i]['ages'] ) ; 
						// debug($output[$i]); 
						// debug($settings);
						// $output[$i]['ages'][ $output[$i]['cancer'] ] = $ages  ; 
						// exit();
						if ( isset( $row['cancers'] ))
						{
							foreach( $row['cancers'] as $r => $cancer_data )
							{
								// unset( $row['cancers'][ $r ][ 'ages' ] ) ; 
							}
						}
					}
				}

				// clean ids not used
				// unset( $output[$i]['country'] ) ;
				// unset( $output[$i]['cancer'] ) ; 

				// unset( $output[$i]['populations'] ) ; 
				// unset( $output[$i]['sex'] ) ; 
				// unset( $output[$i]['type'] ) ; 
			}

			return $output ;
		}

		/**
		* This functon is nesting grouping cancers before sorting them
		* ex: if we want inc/mort for both,males and females, we have to merge colon inc males + rectum inc + anus inc males
		*/
		public static function nestPopulationGrouping( $final )
		{
			global $settings ; 

			$output = $settings['types'] ; 
			$sub_keys = $settings['sexes'] ; 

			foreach( $final as $row )
			{
				// enter per type 
				if ( isset( $output[ $row['type']] ) && !is_array( $output[ $row['type']] ) ) 
					$output[ $row['type']] = [] ; 

				// loops on sexes
				foreach( $sub_keys as $sex )
				{
					// create rows with sex
					if ( isset( $output[ $row['type']][ $sex ] ) && !is_array( $output[ $row['type']][ $sex ] ) ) 
						$output[ $row['type']][ $sex ] = [] ; 

					// create row with pop
					if ( !isset( $output[ $row['type']][ $sex ][ $row['country']] ) ) 
						$output[ $row['type']][ $sex ][ $row['country']] = [] ; 					
				}

				// enter value
				$output[ $row['type']][ $sex ][ $row['country'] ][] = $row ; 
			}

			

			foreach( $output as $key => $pop )
			{
				if ( !is_array( $pop )) continue ; 

				// debug( $pop ) ; exit(); 

				foreach( $pop as $sex => $sexes )
				{	
					foreach( $sexes as $pop_id => $rows )
					{
						// recompose 
						$sub_settings = $settings ; 

						$sub_settings['types'] 			= [$key] ; 
						$sub_settings['sexes']			= [$sex] ; 
						$sub_settings['populations'] 	= [$pop_id] ; 
						$result_cancers_grouped 		= self::checkGroupingCancers( $rows , $sub_settings ) ; 
						sksort( $result_cancers_grouped , $settings['sort'] , ( $settings['sort_dir'] == 'ASC' ) ? true  : false ) ; 
						$output[$key][$sex][$pop_id] = $result_cancers_grouped ;
					}
					
				}
			}

			$final = [] ; 

			// debug( $output[0] ) ; exit();

			foreach( $output as $key => $type )
			{
				if ( !is_array( $type )) continue ; 

				foreach( $type as $sex => $sexes )
				{	
					foreach( $sexes as $pop_id => $rows )
					{
						foreach( $rows as $row )
						{
							$final[] = $row ; 
						}
					}
				}
			}

			return $final ; 
		}

		public static function checkGroupingCancers( $final , $params_settings = NULL )
		{
			global $settings , $conf ; 
			$settings = ( $params_settings != NULL ) ? $params_settings : $settings ; 

			// $settings['grouping_cancer'] = true ; 

			// refuse grouping if force not
			if ( $settings['grouping_cancer'] == false ) return $final ; 

			// refuse grouping if cancer is not "all"
			if ( $settings['cancers'][0] != 'all') return $final ; 

			$year = $settings['year'] ; 
			
			$groups_cancers = $conf['y'][ $year ]['grouped_cancers' ] ;

			// if ( count($groups_cancers) == 0 ) return [] ; 

			foreach( $final as $k => $row )
			{
				foreach( $groups_cancers as $g )
				{
					// if cancer is found, then remove it 
					if ( in_array( (int)$row['cancer'] , $g['ids'] ) ) 
					{
						//var_dump($final[$k]);exit();
						unset( $final[$k] ) ;
					}
				}
			}

			$sub_settings = $settings ; 
			
			// loop through cancer to get all data for cancer grouping 
			foreach( $groups_cancers as $g )
			{				
				$sub_settings['cancers'] = $g['ids'] ; 
				if ( $sub_settings['statistic'] == 'all') 
					$sub_settings['grouped'] = false ; 
				else
					 $sub_settings['grouped'] = true ; 

				// !!! why !!??!!! 
				$sub_settings['grouped'] = true ; 

				// ($sub_settings['grouped']); exit();
				$sub_settings['items'] = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $sub_settings ) ; 
                $sub_settings['sub_mode'] = 'population' ;                 
                $output = CanGlobocan::getNumbers( $sub_settings ) ; 
                // $output = CanGlobocan::optimizeNumbers( $output );
                
                
                // specific to prevalence, when the 3 should retrieved
               	// var_dump( $sub_settings['t'] ); 
                
                if ( $sub_settings['statistic'] === 'all'  ) 
                {
               		foreach( [ 1 , 3 , 5 ] as $statistic )
               		{
               			$object = [
               				'cancer' => $g['cancer'] ,
		                 	'label' => $g['label'] ,
		                	'ICD' => $g['ICD'] , 
		               		'color' => $g['color'] , 
		               		'survival' => $statistic , 
		               		'total' => 0 , 
		               		'prop' => 0
		               	] ; 

               			foreach( $output as $out )
	               	 	{
	               			if ( $statistic == $out['survival'] ) 
	               			{
	               				$object['total'] += $out['total'] ; 
								$object['prop'] += $out['prop'] ; 
							}
						}

						$final[] = $object ; 
               		}
                }
                else
                {
                	// default  object (get first row)
                	if ( isset( $output[0] ))
                		$object = $output[0] ;  
                	else
                		$object = [] ;  

                	// recalculate total if multiple populations for cancer grouping (ex: Colorectum)
                	if ( count( $output ) > 0 )
                	{
                		// var_dump($sub_settings);
                		$object['total'] = 0 ;
                		$populations = [] ;
                		$populations_tmp = [] ; 
                		$cases_ages = [] ;
                		// $total_pops = 

                		// var_dump( $output );

                		foreach( $output as $out )
	               	 	{
	               	 		// echo " => ".$out['total']." = ".$object['total']." <br>" ;

	               	 		$object['total'] += $out['total'] ;

	               	 		$populations_tmp[] = CanCases::getPop( [
								'country' =>  $out['country'] , 
								'sex' => $sub_settings['sex'] ] , 
								true ) ; 


	               	 		//foreach( $row['ages'] as $cancer_id => $row_ages )
							foreach( $out['cancers'] as $cancer_id => $cancer_infos )
							{
								$row_ages = $cancer_infos['ages'] ; 

								if( $sub_settings['ages_group'] == 'all') 
								{
									$i = 0 ; 
									foreach( $row_ages as $age ) 
									{
										if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
										$cases_ages[$i] += $age ; 	
										$i++ ; 
									}
								}
								else
								{
									$ages_values = array_values( $row_ages ) ; 

									if ( count( $ages_values ) > 0 )
									{
										for( $i = (int)$sub_settings['ages_group'][0] ; $i <= (int)$sub_settings['ages_group'][1] ; $i++ )
										{	
											$age = $ages_values[$i] ;

											if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
											$cases_ages[$i] += $age ; 	
											$case_numeros[$i][] = $age ; 
										}
									}
								}	
							} // end foreach 
	               	 	} // end foreach 

	               	 	// compute totals
	               	 	$total_population = CanCases::getCumulatedPopulations([
							'countries' => $sub_settings['populations'] , 
							'sex' => $sub_settings['sex'] ,  
							'ages' => $sub_settings['ages_group']
						]); 

	               	 	// compute all pops
	               	 	foreach ( $populations_tmp as $pops ) {
	               	 		foreach( $pops as $cpt => $pop ){
	               	 			if ( !isset( $populations[$cpt] )) $populations[$cpt] = 0 ; 
	               	 			$populations[$cpt] += $pop ; 
	               	 		}
	               	 	}

	        			$asr 		= getASRCancer( $cases_ages, $populations ) ; 
						$crude_rate = getCrudeRate( array_sum($cases_ages) , $total_population ) ;

						if ( $settings['ages_group'] == 'all' || $settings['ages_group'][1] == 17  )
							$cum_risk = 0 ; 
						else
							$cum_risk 	= getCumRiskCancer(  $cases_ages, $populations ) ;

						// $final[ $key ]['ASR_I'] = $asr ; 
						$object['asr'] = $asr ; 
						$object['crude_rate'] = $crude_rate ; 
						$object['cum_risk'] = $cum_risk ; 

						$object['ages'] = $cases_ages ; 

						if ( isset( $object['prop']) )
						{
							$object['prop'] = getProportion( array_sum($cases_ages) , $total_population ) ; 
						}
	        			
                	} // enf if 

	                $object['cancer'] = $g['cancer'] ; 

	                if ( $settings['sub_mode'] == 'population'){
		                $object['cancer_label'] = $g['label'] ; 
	                }
		            else{
		            	$object['id'] = $g['cancer'] ; 
		            	$object['label'] = $g['label'] ; 
		            }

	                $object['ICD'] = $g['ICD'] ; 
	               	$object['color'] = $g['color'] ;

	               	$object['ui_lower'] = 0 ; 
	               	$object['ui_upper'] = 0 ; 

					$final[] = $object ; 

                }
			}

			// debug( $final ) ; exit() ; 

			return $final ; 

			// foreach( $groups_cancers )  
		}

		public static function recalculateRates( $results )
		{
			global $settings ; 

			// var_dump($settings); exit();
			$cpt = 0 ; 

			foreach ( $results as $i => $row )
			{
				$cases_ages = [] ; // self::getCasesAges( $row ) ; 
				// foreach( $row['ages'] as $age ) $cases_ages[] = $age ;

				if ( $settings['ages_group'] == 'all')
				{
					$i = 0 ; 
					foreach( $row['ages'] as $age ) 
					{
						if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
						$cases_ages[$i] += $age ; 	
						$i++ ; 
					}
				}
				else
				{

					$ages = array_values( $row['ages'] ) ; 

					for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
					{	
						$age = $ages[$i] ;

						if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
						$cases_ages[$i] += $age ; 	
					}
					// var_dump($populations); exit();
				}


				$populations = [] ; 
				$gender = $row['sex'] ; 

				// for each cancer, check the gender
				$items = self::getCancers(true,false,$settings) ;

				if ( isset( $items[$row['cancer']]['gender'] ))
					$gender = ( $items[$row['cancer']]['gender'] != 0 ) ? $items[$row['cancer']]['gender'] : $row['sex'] ;

				// $populations = CanCases::getPop( [ 'country' => $row['country'] , 'sex' => $gender ] , true ) ; 
				// var_dump($populations);exit();

				$tmp_pop = CanCases::getPop( [ 'country' => $row['country'] , 'sex' => $gender ] , true ) ; 

				if( $settings['ages_group'] != 'all') 
				{
					for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
					{
						$populations[$i] = $tmp_pop[$i] ; 
					}

					$cum_risk = getCumRiskCancer( array_slice( $cases_ages , 0 , 15 ) , array_slice($populations, 0 , 15 ) ) ; 
				}
				else
				{
					$populations = $tmp_pop ;

					$cum_risk = getCumRiskCancer( $cases_ages , $populations ) ; 
				}	

				if ( $settings['sub_mode'] == 'population' || $settings['sub_mode'] == 'country' )
				{
					$asr = getASRCancer( $cases_ages , $populations ) ;  
					$results[ $cpt ]['asr'] = $asr  ; 
					
					// echo $i .' => '. $row['country'] . ' - ' . $asr.' = '.array_sum( $cases_ages ).'<br>' ; 

					// ages is harcoded for fact sheets
					$results[ $cpt ]['cum_risk'] = $cum_risk ; 

					$crude_rate = getCrudeRate( array_sum( $cases_ages ) , array_sum( $populations )) ;  
					$results[ $cpt ]['crude_rate'] = $crude_rate ; 

					$results[ $cpt ]['total'] = array_sum( $cases_ages ) ;

					// var_dump($cases_ages); exit() ; 
				}

				// $row['crude_rate'] = $crude_rate ; 
				// $row['cum_risk'] = $cum_risk ; 
				$cpt++  ; 
			}

			// var_dump($results[0]['total']); exit() ; 

			return $results ; 
		}

		/**
		* Retrieve all data for countries and cancer : specific to sunburst/ treemap 
		* @param (no param)
		* @return (array)
		*/
		public static function getFullNumbers()
		{
			$contents   = file_get_contents( ROOT . "api/globocan" . CACHE_PATH . "/full/data.json" );
            $output     = json_decode($contents); 

            // return $output ; 

			global $o_bdd ; 

			$query = "SELECT t1.country as co, t1.type as t, t1.sex as s, t1.cancer as ca, t1.total, t2.continent as cont, t2.area as a, t2.label " ; // t2.who_region, t2.hdi_group_2015, 
			$query .= " FROM `globocan2020_numbers` t1 , `globocan2020_country` t2 " ; 
			$query .= " WHERE t2.country < 900 AND t1.country = t2.country" ; 

			$execute 	= $o_bdd->query( $query ) ; 
			$result = $execute->fetchAll(PDO::FETCH_ASSOC) ;

			// special colorectum 
			/*$query = "SELECT t1.country, t1.type , t1.sex, t1.cancer, t1.total , t2.continent, t2.area, t2.who_region, t2.hdi_group_2015, t2.label " ; 
			$query .= " FROM `globocan2018_numbers` t1 , `globocan2018_country` t2 " ; 
			$query .= " WHERE t2.country < 900 AND t1.country = t2.country AND CANCER IN (8,9,10)" ; 

			$execute 	= $o_bdd->query( $query ) ; 
			$result_col = $execute->fetchAll(PDO::FETCH_ASSOC) ;

			var_dump($result_col);*/

			return $result ; 
		}

		/**
		* Get the grouping (reading geography file)
		* @param (array)
		* @return (array) the field + populations groups
		*/
		public static function getGroupingPopulations( $settings )
		{
			global $geography ; 

			if ( $settings['mode_population'] == 'hdi' )
			{
				$key = $geography['hdi']['field'] ; 
				$groups = $geography['hdi']['groups'] ;
			} 
			else if ( $settings['mode_population'] == 'continents' )
			{
				$key = $geography['continents']['field'] ; 
				$groups = $geography['continents']['groups'] ;
			}
			else if ( $settings['mode_population'] == 'areas' || $settings['mode_population'] == 'regions' )
			{
				$key = $geography['areas']['field'] ; 
				$groups = $geography['areas']['groups'] ;
			}
			else if ( $settings['mode_population'] == 'who' )
			{
				$key = $geography['who']['field'] ; 
				$groups = $geography['who']['groups'] ;
			}
			else if ( $settings['mode_population'] == 'hubs' )
			{
				$key = $geography['hubs']['field'] ; 
				$groups = $geography['hubs']['groups'] ;
			}
			else if ( $settings['mode_population'] == 'income' )
			{
				$key = $geography['income']['field'] ; 
				$groups = $geography['income']['groups'] ;
			}

			$output = [ 'key' => $key , 'groups' => $groups ] ; 

			return $output ; 
		}

		public static function getRealGroupingNumbers( $settings )
		{
			$data = self::getGroupingPopulations( $settings ); 
			// var_dump($data);exit(); 
			$pop_ids = [] ; 
			$output = [] ; 

			foreach( $data['groups'] as $pop ) $pop_ids[] = $pop['country'] ; 

			$sub_settings					= $settings ; 
			$sub_settings['populations']    = $pop_ids ;
			$sub_settings['population']    	= implode('_',$pop_ids) ;

			if ( $sub_settings['extra_pop'] != '' ) 
			{
				$sub_settings['populations'] = array_merge( $sub_settings['populations'] , explode('_', $sub_settings['extra_pop'] ) )  ; 
				// var_dump($sub_settings['populations']) ;
			}

            // $sub_settings['items']          = CanGlobocan::getCancers(true,false,$sub_settings) ;  
			// var_dump($sub_settings);exit(); 

            $output = CanGlobocan::getNumbers( $sub_settings ) ; 
            $rows = CanGlobocan::optimizeNumbers( $output );

            // grab color
            foreach( $rows as $i => $row )
            {
            	foreach( $data['groups'] as $d )
            	{
            		if ( $row['country'] == $d['country'] )
            		{
            			$rows[$i]['color'] = $d['color'] ; 
            			break ; 
            		}
            	}
            }

         	$pie      = ( isset($_GET['pie']) && !empty( $_GET['pie'] )) ? true : false ; 

            if ( $settings['mode'] == 'hdi' )
	        {
	        	$hdi_high_key = [] ; 
	        	$hdi_medium_key = [] ; 
	        	$china_key = [] ; 
	        	$india_key = [] ; 

	        	// reduce India (3) and China (2)
	        	// var_dump($rows);exit();
	        	foreach( $rows as $k => $hd )
	        	{
	        		if ( $hd['country'] == 982 ) $hdi_high_key = $k ; // high
	        		if ( $hd['country'] == 983 ) $hdi_medium_key = $k ; // medium
	        		if ( $hd['country'] == 160 ) $china_key = $k ; 
	        		if ( $hd['country'] == 356 ) $india_key = $k ; 
	        	}

	        	// remove total from regions
	        	if ( $pie == true )
	        	{
	        		$rows[$hdi_high_key]['total'] -= $rows[$china_key]['total'] ;
	        		$rows[$hdi_high_key]['label'] .= ' but China' ; 

	        		$rows[$hdi_medium_key]['total'] -= $rows[$india_key]['total'] ;
	        		$rows[$hdi_medium_key]['label'] .= ' but India' ; 
	        	}
	        }

            return $rows ; 

            // var_dump($rows); exit() ; 
            $total = 0 ; 	
	            
            foreach( $rows as $iKey => $row )
            {
            	$cases_ages = [] ;
            	$populations_cumulated = [] ; 

            	// echo " => {$row['total']} <br>" ; 
            	// compute total number of cases
            	$total += $row['total'] ; 

            	if ( $settings['grouped'] == false ) 
            	{

            	} 
            	else
            	{	
            		if ( isset( $row['ages'] ))
            		{            		
						foreach( $row['ages'] as $population_id => $row_ages )
						{

							if ( $settings['ages_group'] == 'all')
							{
								$i = 0 ; 
								foreach( $row_ages as $age ) 
								{
									if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
									$cases_ages[$i] += $age ; 	
									$i++ ; 
								}
							}
							else
							{

								$ages_values = array_values( $row_ages ) ; 

								for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
								{	
									$age = $ages_values[$i] ;

									if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
									$cases_ages[$i] += $age ; 	
								}
							}	

						} // end foreach
					} // end if ages
				}

				$total_population = CanCases::getCumulatedPopulations([
					'countries' => $sub_settings['populations'] , 
					'sex' => $sub_settings['sex'] ,  
					'ages' => $sub_settings['ages_group']
				]); 

				if ( $settings['grouped'] == false )
				{
					$total_cancer = 0 ; 
					// foreach grouping row (ex: Hub 1 = Izmir, list all data from the cancers selection)
					foreach( $settings['cancers'] as $cancer )
					{
						// get total cancers for a grouping
						$sub_settings['cancer'] = $cancer ; 
						$sub_settings['sub_mode'] = 'cancer' ; 
 						$rows_cancers = CanGlobocan::getNumbers( $sub_settings ) ; 

 						// debug( $rows_cancers ) ; 

						// if grouping of cancer is false, then we list all cancer per population 
	            		// ex: /api/globocan/v1/2018/hubs/population/0/0/all/15_7/?sort=total&grouped=0
	            		$final_group = [
							'id' 	=> $group['id'] , 
							'country' => $group['country'] , 
							'label' => $group['label'] ,
							'cancer'=> $cancer ,
							'total'	=> $total_cancer
						] ; 

						$output[] = $final_group ; 
					}
				}
				else
				{
					$populations = [] ;
					$gender = $row['sex'] ; 

					$tmp_pop = CanCases::getPop( [
						'country' => $row['country'] , 
						'sex' => $gender ] , 
						true ) ; 
					
					if( $settings['ages_group'] != 'all') 
					{
						for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
						{
							$populations_per_ages[$i] = $tmp_pop[$i] ; 
						}
					}
					else
					{
						$populations_per_ages = $tmp_pop ; 
					}

					$populations[] = $populations_per_ages ; 

					// debug( $populations ) ; exit() ; 

					// binding
					$final_group = [
						'id' 	=> $row['id'] , 
						//'country' => ( isset( $group['globocan_id'] ) ) ?  $group['globocan_id'] :  $group['country'] , 
						'label' => $row['label'] , 
						'total' => $total , 
						'asr' 		=> getASRCancer($cases_ages,$populations) ,
						'crude_rate' => getCrudeRate(array_sum($cases_ages),$total_population) ,
						'cum_risk' 	=> getCumRiskCancer($cases_ages,$populations) ,
						'color' => $row['color'] , 
					] ;


					/*$total_all += $total ; 

					if ( isset( $row['globocan_id'] ) )  	
						$final_group['country'] = $row['globocan_id'] ; 
					else if ( isset( $group['country'] ) )  
						$final_group['country'] = $row['country'] ; 
					else $final_group['country'] = 
						$k ; */

					$output[] = $final_group ; 
				
				} // end else
	            
	        } // end foreach 

	        // debug( $output ) ; 
	        return $output ; 

	        exit() ; 
		}

		/**
		* Get grouping population numbers, such as HDI, continent or areas (regions)
		* @param (string) name of field
		* @param (int/string) can be anything
		* @return (array) by default, we are getting an array of ids
		*/
		public static function getGroupingNumbers( $settings )
		{		
			$data = self::getGroupingPopulations( $settings ); 

			// get result 
			$groups = $data['groups'] ; 
			$key = $data['key'] ; 
			$output = [] ; 

			$total_all 	= 0 ; 

			// debug( $settings['populations'] ) ; exit() ; 
			foreach( $groups as $k => $group )
			{
				// echo " ==> {$group['label']} = {$group['id']} <br>" ; 
				
				// compare ids with the "populations settings"
				if ( $settings['population'] != 'all' && is_array( $settings['populations'] ) )
				{
					if ( !in_array( $group['id'] , $settings['populations'] ) ) continue ; 
				}

				$ids = CanGlobocan::getCountriesPerGroup( $key , $group['id']  ); 
				

	            $sub_settings = $settings ; 
	            $sub_settings['sub_mode']       = 'cancer' ; // we want a grouping per cancer
	            $sub_settings['population']     = implode('_',$ids);

	            // exception for HDI (add India + China)

	            // echo $group['id'].'/' ; 

	            // if ( $group['id'] == 6 ) var_dump($sub_settings) ;

	            $sub_settings['populations']    = ( empty($ids) ) ? [$group['country']] : $ids ;
	            $sub_settings['items']          = CanGlobocan::getCancers(true,false,$sub_settings) ;  

	            $rows = CanGlobocan::getNumbers( $sub_settings ) ; 

	            // debug( $rows  ) ; 

	            // foreach( $rows as $key => $row ) $row['label'] = $group['label'] ;
	            // now, we just want one row per group

	            $total = 0 ; 
	            
	            foreach( $rows as $iKey => $row )
	            {
	            	$cases_ages = [] ;
	            	$populations_cumulated = [] ; 

	            	// echo " => {$row['total']} <br>" ; 
	            	// compute total number of cases
	            	$total += $row['total'] ; 

	            	if ( $settings['grouped'] == false ) 
	            	{

	            	} 
	            	else
	            	{	
	            		if ( isset( $row['ages'] ))
	            		{            		
							foreach( $row['ages'] as $population_id => $row_ages )
							{

								if ( $settings['ages_group'] == 'all')
								{
									$i = 0 ; 
									foreach( $row_ages as $age ) 
									{
										if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
										$cases_ages[$i] += $age ; 	
										$i++ ; 
									}
								}
								else
								{

									$ages_values = array_values( $row_ages ) ; 

									for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
									{	
										$age = $ages_values[$i] ;

										if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
										$cases_ages[$i] += $age ; 	
									}
								}	

							} // end foreach
						} // end if ages
					}
				}

				$total_population = CanCases::getCumulatedPopulations([
					'countries' => $sub_settings['populations'] , 
					'sex' => $sub_settings['sex'] ,  
					'ages' => $sub_settings['ages_group']
				]); 

				if ( $settings['grouped'] == false )
				{
					$total_cancer = 0 ; 
					// foreach grouping row (ex: Hub 1 = Izmir, list all data from the cancers selection)
					foreach( $settings['cancers'] as $cancer )
					{
						// get total cancers for a grouping
						$sub_settings['cancer'] = $cancer ; 
						$sub_settings['sub_mode'] = 'cancer' ; 
 						$rows_cancers = CanGlobocan::getNumbers( $sub_settings ) ; 

 						// debug( $rows_cancers ) ; 

						// if grouping of cancer is false, then we list all cancer per population 
	            		// ex: /api/globocan/v1/2018/hubs/population/0/0/all/15_7/?sort=total&grouped=0
	            		$final_group = [
							'id' 	=> $group['id'] , 
							'country' => $group['country'] , 
							'label' => $group['label'] ,
							'cancer'=> $cancer ,
							'total'	=> $total_cancer
						] ; 

						$output[] = $final_group ; 
					}
				}
				else
				{
					if( !isset( $row['populations'] ) ) continue ; 
					
					// binding
					$final_group = [
						'id' 	=> $group['id'] , 
						//'country' => ( isset( $group['globocan_id'] ) ) ?  $group['globocan_id'] :  $group['country'] , 
						'label' => $group['label'] , 
						'total' => $total , 
						'asr' 		=> getASRPopulation($cases_ages,$row['populations']) ,
						'crude_rate' => getCrudeRate(array_sum($cases_ages),$total_population) ,
						'cum_risk' 	=> getCumRiskPopulation($cases_ages,$row['populations']) ,
						'color' => $group['color'] , 
					] ;


					$total_all += $total ; 

					if ( isset( $group['globocan_id'] ) )  	
						$final_group['country'] = $group['globocan_id'] ; 
					else if ( isset( $group['country'] ) )  
						$final_group['country'] = $group['country'] ; 
					else $final_group['country'] = 
						$k ; 

					$output[] = $final_group ; 
				
				} // end else
	            
	        } // end foreach 

	        if ( $settings['mode'] == 'hubs' )
	        {
	        	// var_dump($settings); 
	        	// get total all 
	        	$sub_settings = $settings ; 
	        	$sub_settings['mode'] = 'numbers' ; 
	        	$sub_settings['population'] = 900 ; 
	        	$sub_settings['populations'] = [900] ; 
	        	$data = self::getNumbers( $sub_settings ) ;

	        	array_push( $output , [
					'id' 	=> '-1', 
					'label' => 'Other regions' , 
					'total' => $data[0]['total'] - $total_all ,
					'color' => '#cccccc' , 
				]) ;

				// var_dump($data[0]['total'] );
	        }
	        else if ( $settings['mode'] == 'hdi' )
	        {
	        	exit("here");

	        	$hdi_high_key = [] ; 
	        	$hdi_medium_key = [] ; 
	        	$china_key = [] ; 
	        	$india_key = [] ; 

	        	// reduce India (3) and China (2)
	        	foreach( $output as $k => $hd )
	        	{
	        		if ( $hd['id'] == 2 ) $hdi_high_key = $k ;
	        		if ( $hd['id'] == 3 ) $hdi_medium_key = $k ;
	        		if ( $hd['country'] = 160 ) $china_key = $k ; 
	        		if ( $hd['country'] = 356 ) $india_key = $k ; 
	        	}

	        	// remove total from regions
	        	$output[$hdi_high_key]['total'] -= $output[$china_key]['total'] ;
	        	$output[$hdi_medium_key]['total'] -= $output[$india_key]['total'] ;
	        }

	        // exit() ; 

            return $output ; 
 
		}

		/**
		* Get list of cases per age (taking into account age groups)
		* @param (array) list of rows
		* @return (array) cases ages
		*/
		public static function getCasesAges( $row ){

			global $settings ; 
			$cases_ages = [] ; 

			if ( isset( $row['ages'] ))
    		{            		
				foreach( $row['ages'] as $population_id => $row_ages )
				{

					if ( $settings['ages_group'] == 'all')
					{
						$i = 0 ; 
						foreach( $row_ages as $age ) 
						{
							if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
							$cases_ages[$i] += $age ; 	
							$i++ ; 
						}
					}
					else
					{

						$ages_values = array_values( $row_ages ) ; 

						for( $i = (int)$settings['ages_group'][0] ; $i <= (int)$settings['ages_group'][1] ; $i++ )
						{	
							$age = $ages_values[$i] ;

							if ( !isset($cases_ages[$i])) $cases_ages[$i] = 0 ; 
							$cases_ages[$i] += $age ; 	
						}
					}	

				} // end foreach
			} // end if ages

			return $cases_ages ; 
		}

		/**
		* Get all countries for a specific grouping ( can be whatever specified with a field and a value)
		* @param (string) name of field
		* @param (int/string) can be anything
		* @return (array) by default, we are getting an array of ids
		*/
		public static function getCountriesPerGroup( $field , $value )
		{
			global $o_bdd , $conf , $settings ;

			$year = $settings['year'] ; 
			
			$table = "globocan{$year}_numbers" ; 
			$query = " SELECT country FROM globocan{$year}_country WHERE $field = '$value' AND iso_3_code <> '' " ; 
			// echo $query . '<br>' ; 

			$execute = $o_bdd->query( $query ) ; 
			$result = $execute->fetchAll(PDO::FETCH_NUM) ;

			$output = [] ; 
			foreach( $result as $row ) $output[] = $row[0] ; 

			return $output ; 
		}

		/**
		* Get all countries with specific keys ( can be whatever specified with a field and a value)
		* @param (string) name of field
		* @param (string) array imploded
		* @return (array) by default, we are getting an array of ids
		*/
		public static function getCountriesPerGroupKeys( $field , $values )
		{
			global $o_bdd , $conf , $settings ;

			$year = $settings['year'] ; 
			
			$table = "globocan{$year}_numbers" ; 
			$query = " SELECT country FROM globocan{$year}_country WHERE $field IN ('$value') AND iso_3_code <> '' " ; 

			$execute = $o_bdd->query( $query ) ; 
			$result = $execute->fetchAll(PDO::FETCH_NUM) ;

			$output = [] ; 
			foreach( $result as $row ) $output[] = $row[0] ; 

			return $output ; 
		}

		public static function getTopCancerPerCountries( $settings )
		{	
			global $o_bdd ; 

			// get all countries first
			$countries = self::getPopulations( 'country' , false , [] , 'country DESC' , $settings  ) ; 

			$query = "SELECT t2.cancer , t2.label , t2.color , MAX(t1.total) as total " ;
			$query .= " FROM `globocan2020_numbers` t1 , `globocan2020_cancer` t2 " ;
			$query .= " WHERE t1.cancer = t2.cancer AND t1.type = {$settings['type']} AND t1.sex = {$settings['sex']} " ;
			$query .= " AND t1.cancer NOT IN (39,40)  " ;

			$query .= " AND t1.country < 900 " ; 

			if ( count( $settings['populations'] ) > 0 && $settings['populations'][0] != 'all' ) $query .= " AND country in (".implode(',',$settings['populations'] ).") " ; 

			$query .= " GROUP BY t1.cancer ORDER BY total DESC LIMIT 0,5" ; 

			// echo $query ; 

			$execute = $o_bdd->query( $query ) ; 
			$output = $execute->fetchAll(PDO::FETCH_ASSOC) ;
			
			return $output ; 
		}

		public static function getMaxPerCountries( $settings )
		{
			global $o_bdd , $conf  ; 
			// var_dump($settings);exit();
			$year = $settings['year'] ; 

			$query 		= "SELECT country , label FROM `globocan2020_country` WHERE country < 900 " ;

			$excludes_cancers = array_merge( $conf['y'][ $year ][ 'all_cancers_id' ] , $conf['y'][ $year ][ 'hidden_cancers' ] ) ;

			if ( (bool)$settings['include_nmsc'] == false )
			{
				array_push( $excludes_cancers , $conf['y'][ $year ][ 'nmsc_cancers' ]) ; 
			}


			$output 		= [] ; 
			$execute 	= $o_bdd->query( $query ) ; 
			$results =	 $execute->fetchAll(PDO::FETCH_ASSOC) ;

			foreach( $results as $row )
			{

				$sub_query = "SELECT t1.cancer , t1.asr , t1.total , t1.crude_rate  , t1.cum_risk , t2.label , t2.color FROM `globocan2020_numbers` t1 , `globocan2020_cancer` t2 WHERE t1.country = {$row['country']} AND t1.type = {$settings['type']} AND t1.sex = {$settings['sex']} AND t1.cancer NOT IN (".implode(',',$excludes_cancers).") AND t1.cancer = t2.cancer ORDER BY {$settings['field_key']} DESC LIMIT 1 " ;

				// exit( $sub_query ); 

				$data_cancer 	= $o_bdd->query( $sub_query )->fetch() ;

				if ( (int)$data_cancer['asr'] == 0 ) continue ; 

				$obj = [ 
					'label'		=> $row['label'] , 
					'globocan_id' 	=> (int)$row['country'] , 
					'country' 	=> (int)$row['country'] , 
					'cancer' 	=> (int)$data_cancer['cancer'] , 
					'total' 	=> (int)$data_cancer['total'] , 
					'cum_risk'  => (float)$data_cancer['cum_risk'] , 
					'crude_rate'  => (float)$data_cancer['crude_rate'] , 
					'asr' 		=> (float)$data_cancer['asr'] , 
					'cancer_name' => $data_cancer['label'] , 
					'color'		=> $data_cancer['color']
				] ; 

				if ( (bool)$settings['grouping_cancer'] == true )
				{
					$sub_settings = $settings ; 

					$sub_settings['mode'] = 'data' ; 
					$sub_settings['sub_mode'] = 'population' ; 
					$sub_settings['cancers'] = [8,9,10] ; 
					$sub_settings['population'] = $row['country'] ;
					$sub_settings['populations'] = [$row['country']] ;
					$sub_settings['grouped'] = true ; 
					$sub_settings['items'] = CanGlobocan::getPopulations( 'country' , false , [] , 'country DESC' , $sub_settings ) ;
					
					$tmp = CanGlobocan::getNumbers( $sub_settings ) ; 
					$row_grouped = $tmp[0] ; 

					if ( $row_grouped[ $settings['field_key'] ] > $data_cancer[ $settings['field_key']] )
					{
						$obj = [ 
							'label'		=> $row['label'] , 
							'globocan_id' 	=> (int)$row['country'] , 
							'country' 	=> (int)$row['country'] , 
							'cancer' 	=> 'Colorectum' , 
							'total' 	=> (int)$row_grouped['total'] , 
							'cum_risk'  => (float)$row_grouped['cum_risk'] , 
							'crude_rate'  => (float)$row_grouped['crude_rate'] , 
							'asr' 		=> (float)$row_grouped['asr'] , 
							'cancer_name' => 'Colorectum' , 
							'color'		=> '#FFD803'
						] ; 
					}
				}

				$output[] = $obj ; 
			}

			return $output ; 

		}

		public static function getTotalCancers( $type = 0 , $sex = 0 , $population = 900 , $prevalence = false , $statistic = 0 )
		{
			global $o_bdd , $settings , $conf  ; 

			$year = $settings['year'] ; 
			$all_cancer_id = ( $settings['include_nmsc'] == true ) ? 39 : 40 ; // cancers id to put in dynamic

            if ( $prevalence == false )
                $query = " SELECT total FROM globocan{$year}_numbers WHERE cancer = $all_cancer_id AND type = $type AND sex = $sex AND country = $population " ;
            else
                $query = " SELECT total FROM globocan{$year}_prevalence WHERE cancer = $all_cancer_id AND survival = $statistic AND sex = $sex AND country = $population " ;
            
			$execute = $o_bdd->query( $query ) ; 
			$total = $execute->fetch() ;

			return (int)$total[ 'total' ] ;
		}

		/**
		* Retrieve all cancers
		* @param (bool) get all cancer 
		* @param (bool) all ages
		* @param (array) settings 
		*/ 
		public static function getCancers( $with_all = false , $ages = false , $settings = []  )
		{
			global $o_bdd , $conf  ; 

			$year = $settings['year'] ; 
			$cancer_table = "globocan{$year}_cancer" ; 

			$query = " SELECT t1.* FROM $cancer_table t1 " ; //, globocan2012_category_cancer t2 " ;

			// debug( $settings );
			// $conf['y'][ $settings['year'] ]['all_cancers_id'] 

			$cancers_ids = array_merge(  $conf['y'][ $settings['year'] ]['forbidden_cancers'] , $conf['y'][ $settings['year'] ]['hidden_cancers']  ) ; 

			$QUERY_ALL = '' ; 
			if ( $with_all == false ) $QUERY_ALL = ' AND cancer NOT IN ('.implode(',',$conf['y'][ $settings['year'] ]['all_cancers_id']).') ' ; 
			// $query .= "  WHERE t1.CATEGORY = t2.CATEGORY AND 
			
			// $forbidden_cancers_query = ( count( $conf['y'][ $settings['year'] ]['forbidden_cancers'] ) > 0 ) ? "cancer NOT IN (".implode(',', $cancers_ids ).") " : " 1 " ; 

			$forbidden_cancers_query = "cancer NOT IN (".implode(',', $cancers_ids ).") " ; 

			$query .= "  WHERE $forbidden_cancers_query $QUERY_ALL ORDER BY cancer  " ;
			$execute = $o_bdd->query( $query ) ; 

			// echo $query ; exit(); 

			$result = [] ; 

			while ( $data = $execute->fetch() )
			{
				$result[ (int)$data['cancer'] ] = $data ;  
			}

			$output = [] ; 

			foreach( $result as $id_cancer => $cancer )
			{
                if ( $ages == false )
                {
                    $row_cancer = [
                        'id' => (int)$cancer['cancer'] , 
                        'cancer' => (int)$cancer['cancer'] , 
                        'label' => $cancer['label'] , 
                        'title' => $cancer['title'] , 
                        'short_label' => $cancer['short_label'] , 
                        'long_label' => $cancer['long_label'] , 
                        'ICD' => $cancer['ICD'] , 
                        'gender' => (int)$cancer['gender'] , 
                        //'class' => "cancer-color" . $cancer['CANCER'] , 
                        //'total' => 0 , 
                        'color' => $cancer['color']
                    ] ; 
                }
                else
                {
                    $row_cancer = [
                        'id' => (int)$cancer['cancer'] , 
                        'cancer' => (int)$cancer['cancer'] , 
                        'label' => $cancer['label'] , 
                        'short_label' => $cancer['short_label'] , 
                        'title' => $cancer['title'] ,  
                        'ICD' => $cancer['ICD'] , 
                        'gender' => (int)$cancer['gender'] , 
                        //'class' => "cancer-color" . $cancer['CANCER'] , 
                        //'total' => 0 , 
                        'asr' => 0 , 
                        'cum0_74' => 0 , 
                        'cum_risk' => 0 ,
                        'per1000' => 0 , 
                        'N0_14'  => 0 , 
                        'N15_39' => 0 , 
                        'N40_44' => 0 , 
                        'N45_49' => 0 , 
                        'N50_54' => 0 , 
                        'N55_59' => 0 , 
                        'N60_64' => 0 , 
                        'N65_69' => 0 , 
                        'N70_74' => 0 , 
                        'N75' => 0 , 
                        'color' => $cancer['color']
                    ] ; 
                }

	    		$output[ $row_cancer['id'] ] = $row_cancer ; 
			}

			return $output ; 
		}
        
        /**
		* Retrieve a specific cancer
		* @param (int)
		*/ 
		public static function getCancerById( $ID_CANCER )
		{
			global $o_bdd ; 

			$query   = " SELECT * FROM globocan2012_cancer WHERE CANCER =  $ID_CANCER " ;
			$execute = $o_bdd->query( $query ) ; 
			$result  = $execute->fetch( PDO::FETCH_ASSOC ) ;  

			return $result ; 
		}

		/**
		* Retrieve all data sources
		* @param (no param)
		*/ 
		public static function getDataSources()
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM globocan2012_data " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$result[ $data['DATA'] ] = $data ;  
			}

			return $result ; 
		}

		/**
		* Retrieve all data methods
		* @param (no param)
		*/ 
		public static function getDataMethods()
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM globocan2012_methods " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$result[ $data['METHODS'].'_'.$data['TYPE'] ] = $data ;  
			}

			return $result ; 
		}

		/**
		* Retrieve all countries
		* @param (string) the key name for index
		* @return (array)
		*/ 
		public static function getPopulations( $key = 'country' , $addStats = false , $stats = [] , $order_label = 'country DESC' , $settings = [] )
		{
			global $o_bdd , $conf  ; 

			$year = $settings['year'] ; 
			$populations_table = "globocan{$year}_country" ;

			$query 		= " SELECT * FROM $populations_table ORDER BY $order_label  " ;

			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{

				/*$data = [
					"country" 	=> $data['country'] ,
				    "area"		=> $data['area'] ,
				    "continent"	=> $data['continent'] ,
				    "label"		=> $data['label'] ,
				    //"eDISPLAY"	=> $data['eDISPLAY'] ,
				    "i_Method"	=> $data['i_Method'] ,
				    "m_Method"	=> $data['m_Method'] ,
				    "total"		=> $data['total'] ,
				    "i_data"	=> $data['i_data'] ,
				    "m_data"	=> $data['m_data'] , 
				    "hdi_value" => $data['hdi_2015'] , 
				    'hdi_group_2015' => $data['hdi_group_2015'],
				    "gdp"		=> $data['gdp_2012'] , 
				    'who_region'=> $data['who_region']  ,
				    'hub'		=> $data['hub']
				] ; */

				$data 		= self::setPopulationRow( $data ) ; 

				if ( $addStats == true )
				{
					$data['stats'] = [
						'max_cancers' => self::getMaxCancers([
								'country' => $data['country'] , 
								'sex' => $stats['sex'] , 
								'type' => $stats['type']
							]) 
						] ; 
				}
				$result[ $data[$key] ] = $data ;  
			}
			return $result ; 
		}

		/**
		* Retrieve country pop by id 
		* @param (int)
		*/ 
		public static function getPopulationById( $ID_COUNTRY )
		{
			global $o_bdd , $conf , $settings  ; 

			$year = $settings['year'] ; 
			$populations_table = "globocan{$year}_country" ;

			$query 		= " SELECT * FROM $populations_table WHERE country =  $ID_COUNTRY " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= $execute->fetch( PDO::FETCH_ASSOC ) ;  
			$out 		= self::setPopulationRow( $result ) ; 

			return $out ; 
		}

		/**
		* Retrieve all data source and methods
		* @param (int)
		*/ 
		public static function getAllDataSourceMethod()
		{
			global $o_bdd , $conf , $settings  ; 

			$year = $settings['year'] ; 
			$populations_table = "globocan{$year}_source_methods" ;

			$query 		= " SELECT * FROM $populations_table WHERE " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= $execute->fetchAll( PDO::FETCH_ASSOC ) ;  
			

			return $result ; 
		}

		/**
		* Retrieve data source and methods by id 
		* @param (int)
		*/ 
		public static function getDataSourceMethod( $ID_COUNTRY )
		{
			global $o_bdd , $conf , $settings , $methods_txt  ; 

			$year = $settings['year'] ; 
			$populations_table = "globocan{$year}_source_methods" ;

			$query 		= " SELECT * FROM $populations_table WHERE country =  $ID_COUNTRY ORDER BY TYPE ASC " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= $execute->fetchAll( PDO::FETCH_ASSOC ) ;  
		
			if ( isset( $result[0] )  && isset( $methods_txt['inc'][ $result[0]['method']] ) )
				$result[0]['method_desc'] = $methods_txt['inc'][ $result[0]['method']] ; 

			if ( isset( $result[1] )  && isset( $methods_txt['mort'][ $result[1]['method']] ) )
				$result[1]['method_desc'] = $methods_txt['mort'][ $result[1]['method']] ; 

			return $result ; 
		}

		

		/**
		* Control keys that will be returned to dataset (common to getPopulationById & getPopulations)
		* @param (array)
		* @return (array)
		*/
		private static function setPopulationRow( $data )
		{
			return [
				// "id" 		=> $data['country'] ,
				"country" 	=> (int)$data['country'] ,
			    "area"		=> (int)$data['area'] ,
			    "continent"	=> (int)$data['continent'] ,
			    "label"		=> $data['label'] ,
			    'who_region'=> $data['who_region']  ,
			    'hub'		=> $data['hub'] , 
			    'income'		=> $data['income'] , 
			    "iso_2_code"=> $data['iso_2_code'] , 
			    "iso_3_code"=> $data['iso_3_code'] , 
			    "i_Method"	=> (int)$data['i_Method'] ,
			    "m_Method"	=> (int)$data['m_Method'] ,
			    "i_data"	=> $data['i_data'] ,
			    "m_data"	=> (int)$data['m_data'] ,
			    "eu"		=> (bool)$data['eu'], 
			    "hdi" => [
			    	2012 => [
			    		"value" => (float)$data['hdi_2012'] , 
			    		"group" => (int)$data['hdi_group_2012']
			    	] , 
			    	2015 => [
			    		"value" => (float)$data['hdi_2015'] , 
			    		"group" => (int)$data['hdi_group_2015']
			    	] , 
			    	2018 => [
			    		"value" => (float)$data['hdi_2018'] , 
			    		"group" => (int)$data['hdi_group_2018']
			    	]
			    ]
			] ; 	
		}

		/**
		* Retrieve the top cancers by country
		* @param (string) the key name for index
		* @return (array)
		*/ 
		public static function getMaxCancers( $p , $limit = 5 )
		{
			global $o_bdd ; 

			$query = " SELECT t2.CANCER, total , t2.LABEL AS cancer FROM `globocan2012_numbers` t1 , `globocan2012_cancer` t2 WHERE t1.COUNTRY = {$p['country']} AND t1.type = {$p['type']} AND t1.sex = {$p['sex']} AND t2.CANCER <> 29 AND t1.CANCER = t2.CANCER GROUP BY t1.CANCER  ORDER BY total DESC LIMIT $limit " ; 
			$execute 	= $o_bdd->query( $query ) ; 
			$result = $execute->fetchAll() ;

			return $result ;
		}

		/**
		* Retrieve all prevalence populations indexed by id country
		* @param (int)
		*/ 
		public static function getPrevalencePop( $params )
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM globocan2012_prevalence_pop WHERE ".implode(' AND ' , $params['where'] ) ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() ) $result[ $data['COUNTRY'] ] = $data ;  

			return $result ; 
		}


		/**
		* Retrieve all countries by id 
		* @param (int)
		*/ 
		public static function getCountriesById( $ID_COUNTRY )
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM globocan2012_country WHERE COUNTRY =  $ID_COUNTRY " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() ) $result[ $data['COUNTRY'] ] = $data ;  

			return $result ; 
		}
        
        /**
		* Retrieve all countries by id 
		* @param (int)
		*/ 
		public static function getCountryById( $ID_COUNTRY )
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM globocan2012_country WHERE COUNTRY =  $ID_COUNTRY " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= $execute->fetch( PDO::FETCH_ASSOC ) ;  

			return $result ; 
		}

		
		/**
		* Retrieve all countries by id 
		* @param (int)
		*/ 
		public static function getCountriesWhere( $params , $order = null  )
		{
			global $o_bdd ; 

			$query_order= ( $order == null ) ? '' : " ORDER BY ".implode(' ',$order) ;

			$where = ( count( $params['where'] ) > 1 ) ? implode(' AND ' , $params['where'] ) : $params['where'] ; 

			$query 		= " SELECT * FROM globocan2012_country WHERE ".implode(' AND ' , $params['where'] )." $query_order " ;
			// echo "$query <br>" ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() ) $result[ $data['COUNTRY'] ] = $data ;  

			return $result ; 
		}
		
		/**
		* Retrieve all countries by continents
		* @param (array)
		*/ 
		public static function getCountriesByContinents( $params  , $limit = null )
		{
			global $o_bdd ; 

			$query_limit= ( $limit == null ) ? '' : " LIMIT 0,$limit " ;
			$query 		= " SELECT * FROM globocan2012_country WHERE COUNTRY < 900 AND CONTINENT IN (" . implode(',' , $params['CONTINENTS'] ) . ") $query_limit ORDER BY LABEL ASC"  ;
			// echo "$query <br>" ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() ) $result[ $data['COUNTRY'] ] = $data ;  

			return $result ; 
		}

		/**
		* Retrieve all countries by area
		* @param (array)
		*/ 
		public static function getCountriesByArea( $params , $limit = null )
		{
			global $o_bdd ; 

			$query_limit= ( $limit == null ) ? '' : " LIMIT 0,$limit " ;
			echo $query 		= " SELECT * FROM globocan2012_country WHERE AREA IN (" .implode(',' , $params['AREA'])." ) $query_limit" ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() ) $result[ $data['COUNTRY'] ] = $data ;  

			return $result ; 
		}

	} // end class