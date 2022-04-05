<?php
	

	// @desc : CI5 is based on 3 set of tables, refering to CI5 I-X, CI5+ and CI5X
	if ( !defined( 'CI5_TABLE' ) ) define( 'CI5_TABLE' , 'ci5' ) ; // ci5 , ci5i_x ,  ci5x

	class CanCI5
	{	
		
		public static $_exclude_registries = [ 3602 , 3603 , 3604 , 3605 , 3606 , 3607, 4007 , 4008, 12403 , 12406 , 12413 , 15602 , 15607 , 15630, 25001, 25002, 25003, 25004, 25005, 25006, 25007, 25008, 35604, 35606, 37600, 38001, 38002, 38006, 38007, 38008, 38009, 38010, 38012, 38015, 39203, 39204, 39206, 72401, 72404, 72406, 72407, 72408, 72410, 72413, 75602, 75603, 75605, 75604, 75608, 75611, 76401, 76404, 76405, 82603, 82604, 82605, 82606, 82609, 82610, 82611, 82620, 82630, 84004, 84005, 84006, 84007, 84008, 84011, 84013, 84015, 84016, 84017, 84018, 84099  ] ; 

		public static function getWStdAges()
		{
			return [ '0-4','5-9','10-14','15-19','20-24','25-29','30-34','35-39','40-44','45-49','50-54','55-59','60-64','65-69','70-74','75-79','80-84','85'] ; 
		}

        /**
		* Retrieve all registries
		* @param (no param)
		* @return (array)
		*/ 
		public static function getCancers( $order = 'LABEL' , $exclude_all = false)
		{
			global $o_bdd ; 

			$WHERE_EXCLUDE_ALL = "" ; 
			if ( $exclude_all == true ) $WHERE_EXCLUDE_ALL = " WHERE CANCER <> 1 " ; 

			$query 		= " SELECT * FROM " . CI5_TABLE . "_cancer $WHERE_EXCLUDE_ALL ORDER BY LABEL ASC"  ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$result[ $data['CANCER'] ] = $data ;  
			}

			return $result ; 
		}

		/**
		* Retrieve a specific cancer
		* @param (int)
		* @return (array)
		*/ 
		public static function getCancerById( $ID_CANCER )
		{
			global $o_bdd ; 

			$query   = " SELECT * FROM " . CI5_TABLE . "_cancer WHERE CANCER =  $ID_CANCER " ;
			$execute = $o_bdd->query( $query ) ; 
			$result  = $execute->fetch( PDO::FETCH_ASSOC ) ;  

			return $result ; 
		}

		
		/**
		* Retrieve all registries
		* @param (no param)
		* @return (array)
		*/ 
		public static function getYears()
		{
			global $o_bdd ; 

			$query 		= "SELECT DISTINCT YEAR FROM " . CI5_TABLE . "_pop ORDER BY YEAR DESC" ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$result[] = $data ;  
			}

			return $result ; 
		}


		/**
		* Retrieve all registries
		* @param (no param)
		* @return (array)
		*/ 
		public static function getRegistries( $index_id = false )
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM " .  CI5_TABLE . "_registry WHERE REGISTRY NOT IN (".implode(',', self::$_exclude_registries) .")" ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				if ( $index_id == false )
					$result[] = $data ;  
				else
					$result[ $data['REGISTRY'] ] = $data ;  
			}

			return $result ; 
		}

		/**
		* Retrieve all registries by a continent id
		* @param (int) id continent
		* @return (array)
		*/ 
		public static function getRegistriesByContinent( $get_continent )
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM " .  CI5_TABLE . "_registry WHERE continent = $get_continent ORDER BY LABEL ASC" ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$result[] = $data ;  
			}

			return $result ; 
		}

		

		/**
		* Retrieve a specific registry
		* @param (int)
		* @return (array)
		*/ 
		public static function getRegistryById( $ID_REGISTRY )
		{
			global $o_bdd ; 

			$query   = " SELECT * FROM " . CI5_TABLE . "_registry WHERE REGISTRY =  $ID_REGISTRY " ;
			$execute = $o_bdd->query( $query ) ; 
			$result  = $execute->fetch( PDO::FETCH_ASSOC ) ;  

			return $result ; 
		}

		/**
		* Retrieve all registries ethnic groups
		* @param (int) id registry
		* @return (array)
		*/ 
		public static function getRegistriesEthnicGroups( $ID_REGISTRY )
		{
			global $o_bdd ; 

			$query 		= " SELECT * FROM " . CI5_TABLE . "_registry_ethnic_group"  ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$result[] = $data ;  
			}

			return $result ; 
		}
        
        /**
		* Retrieve all registries ethnic groups
		* @param (no param)
		* @return (array)
		*/ 
		public static function getVolumes()
        {
        	global $o_bdd ; 

			$query 		= " SELECT * FROM " . CI5_TABLE . "_volume GROUP BY `VOLUME` ORDER BY `VOLUME` DESC " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$data['LABEL'] = "Vol. {$data['VOLUME']} ({$data['PERIOD_1']}-{$data['PERIOD_2']})" ; 
				$result[] = $data ;  
			}

			return $result ; 
        }

        /**
		* Retrieve all registries ethnic groups
		* @param (int) registry
		* @return (array)
		*/ 
		public static function getVolumeByRegistry( $get_registry )
        {
        	global $o_bdd ; 

			$query 		= " SELECT * FROM " . CI5_TABLE . "_volume WHERE REGISTRY = $get_registry GROUP BY `VOLUME` ORDER BY `VOLUME` DESC " ;
			$execute 	= $o_bdd->query( $query ) ; 
			$result 	= [] ; 

			while ( $data = $execute->fetch() )
			{
				$data['LABEL'] = "Vol. {$data['VOLUME']} ({$data['PERIOD_1']}-{$data['PERIOD_2']})" ; 
				$result[] = $data ;  
			}

			return $result ; 
        }


        /**
		* Retrieve a specific volume
		* @param (int)
		* @return (array)
		*/ 
		public static function getVolumeById( $ID_VOLUME )
		{
			global $o_bdd ; 

			$query   = " SELECT * FROM " . CI5_TABLE . "_volume WHERE VOLUME =  $ID_VOLUME " ;
			$execute = $o_bdd->query( $query ) ; 
			$result  = $execute->fetch( PDO::FETCH_ASSOC ) ;  

			return $result ; 
		}

		/**
		* Retrieve a specific population
		* @param (array)
		* @return (array)
		*/ 
		public static function getPopulation( $parameters )
		{
			global $o_bdd ; 

			// init
			$result = [] ; 

			$years = explode('-', $parameters['YEAR'] ) ;
			$registries = explode('-', $parameters['REGISTRY']) ; 

			if ( count( $registries ) > 1) 
			{
				$where_registry = "REGISTRY IN (".str_replace( "-" , "," , $parameters['REGISTRY'] ).")" ;
			}
			else
			{
				$where_registry = "REGISTRY = {$parameters['REGISTRY']}" ;
			}

			// test year ranges
			if ( count($years) > 1 ) 
			{
				if ( count($years) == 2 )
				{
					$year_start = $years[0] ; 
					$year_end = $years[1] ;

					if ( (int)$year_start > (int)$year_end ) 
					{
						$year_start = $years[1] ; $year_end = $years[0] ; 
					}

					switch( CI5_TABLE )
					{
						case "ci5" : 
							$where_year = "YEAR BETWEEN $year_start AND $year_end" ;
							break ; 

						case "ci5i_x" : 
								$where_year = "`PERIOD_1` = $year_start AND `PERIOD_2` = $year_end" ;
								break ; 

						case "ci5x" :
							$where_year = "1" ; 
							break ; 	
					}
				}
				else // specific list of years
				{
					switch( CI5_TABLE )
					{
						case "ci5" : 
							$where_year = "YEAR IN ( ".str_replace( "-" , "," , $parameters['YEAR'] )." ) " ;
							break ;
					}
					
				}

				$query   = " SELECT * FROM " . CI5_TABLE . "_pop WHERE $where_registry AND $where_year AND SEX = {$parameters['SEX']} " ;
				$execute = $o_bdd->query( $query ) ; 

				while ( $data = $execute->fetch() )
				{
					$result[] = $data ;  
				}
			}
			else
			{
				// default where year 
				$where_year = "YEAR = {$parameters['YEAR']}" ;

				$query   = " SELECT * FROM " . CI5_TABLE . "_pop WHERE $where_registry AND $where_year AND SEX = {$parameters['SEX']} " ;
				$execute = $o_bdd->query( $query ) ; 

				$result  = $execute->fetch( PDO::FETCH_ASSOC ) ;  
				
			}
			
			return $result ; 
		}


		/**
		* Retrieve cases for a period
		* @param (array)
		* @return (array)
		*/ 
		public static function getCases( $parameters )
		{
			global $o_bdd ; 

			// init
			$result = [] ; 

			$years = explode('-', $parameters['YEAR'] ) ;
			$registries = explode('-', $parameters['REGISTRY']) ; 

			if ( count( $registries ) > 1) 
			{
				$where_registry = "REGISTRY IN (".str_replace( "-" , "," , $parameters['REGISTRY'] ).")" ;
			}
			else
			{
				$where_registry = "REGISTRY = {$parameters['REGISTRY']}" ;
			}
		
			// test year ranges
			if ( count($years) > 1 ) 
			{
				if ( count($years) == 2 )
				{
					$year_start = $years[0] ; 
					$year_end = $years[1] ;

					if ( (int)$year_start > (int)$year_end ) 
					{
						$year_start = $years[1] ; $year_end = $years[0] ; 
					}

					switch( CI5_TABLE )
					{
						case "ci5" : 
							$where_year = "YEAR BETWEEN $year_start AND $year_end" ;
							break ; 
						
						case "ci5i_x" :
							$where_year = "`PERIOD_1` = $year_start AND `PERIOD_2` = $year_end" ;
							break ; 

						case "ci5x" :
							$where_year = "1" ; 
							break ; 	
					}

				}
				else // specific list of years
				{
					$where_year = "YEAR IN ( ".str_replace( "-" , "," , $parameters['YEAR'] )." ) " ;
				}

				$query   = " SELECT * FROM " . CI5_TABLE . "_pop WHERE $where_registry AND $where_year AND SEX = {$parameters['SEX']} " ;
				$execute = $o_bdd->query( $query ) ; 

				while ( $data = $execute->fetch() )
				{
					$result[] = $data ;  
				}
			}
			else
			{
				// default where year 
				$where_year = "YEAR = {$parameters['YEAR']}" ;

				$query   = " SELECT * FROM " . CI5_TABLE . "_pop WHERE $where_registry AND $where_year AND SEX = {$parameters['SEX']} " ;
				$execute = $o_bdd->query( $query ) ; 

				$result  = $execute->fetch( PDO::FETCH_ASSOC ) ;  
				
			}
			
			return $result ; 
		}

		public static function getCasesByFilters( $parameters )
		{
			global $o_bdd ; 

			$WHERE_CONTINENT = '' ; 
			if ( (int)$parameters['continent'] != 0 ) $WHERE_CONTINENT = " AND T3.CONTINENT = {$parameters['continent']} " ; 

			$query = "

				SELECT 
					T3.REGISTRY,
					T3.LABEL,
					T2.PERIOD_1,
					T2.PERIOD_2,
					T2.TOTAL,
					FLAG_1,
					FLAG_2, 
					P0_4,P5_9,P10_14,P15_19,P20_24,P25_29,P30_34,P35_39,P40_44,P45_49,P50_54,P55_59,P60_64,P65_69,P70_74,P75_79,P80_84,P85 , 
					N0_4,N5_9,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85 , 
					T2.N_unk, 
					T1.TOTAL as POPULATION
				FROM 
					CI5I_X_POP T1,
					CI5I_X_CASES T2,
					CI5I_X_REGISTRY T3,
					ETHNIC_GROUP T4,
					CI5I_X_VOLUME T5

				WHERE 

					T1.VOLUME = T2.VOLUME AND 
					T1.PERIOD_1 = T2.PERIOD_1 AND 
					T1.PERIOD_2 = T2.PERIOD_2 AND 
					T1.REGISTRY = T2.REGISTRY AND 
					T1.ETHNIC_GROUP = T2.ETHNIC_GROUP AND 
					T1.SEX = T2.SEX AND 

					T1.VOLUME = T5.VOLUME AND 
					T1.PERIOD_1 = T5.PERIOD_1 AND 
					T1.PERIOD_2 = T5.PERIOD_2 AND 
					T1.REGISTRY = T5.REGISTRY AND 
					T1.ETHNIC_GROUP = T5.ETHNIC_GROUP AND 

					T1.REGISTRY = T3.REGISTRY AND 

					T1.ETHNIC_GROUP = T4.ETHNIC_GROUP AND 

					T2.TOTAL > 0 AND
					T2.CANCER = {$parameters['cancer']} AND
					T2.VOLUME = {$parameters['volume']} AND
					T2.SEX = {$parameters['sex']}

					$WHERE_CONTINENT

				GROUP BY 

					T3.REGISTRY

				ORDER BY 

					T3.LABEL,T4.ETHNIC_GROUP

			" ; 

			// echo $query ; 

			$execute = $o_bdd->query( $query ) ; 

			while ( $data = $execute->fetch() )
			{

				// table cases
				$Populations = [] ; 
				for ( $i = 7 ; $i <= 24 ; $i++ ) $Populations[] = $data[$i] ;

				$Cases = [] ; 
				for ( $i = 25 ; $i <= 42 ; $i++ ) $Cases[] = $data[$i] ; 

				$data['CRUDE_RATE'] = getProportion( $data['TOTAL'] , $data['POPULATION']) ; 
				$rates_asr = getASRCI5( $Cases , $Populations ,  false , true , $data['N_unk'] ) ; 
				$data['ASR'] = $rates_asr['value'] ; 
				$result[] = $data ;  

				// debug( $data ) ;
				// exit() ; 
			}

			return $result ; 
		}

		public static function getCasesByRegistry( $parameters )
		{
			global $o_bdd ; 

			$per_continent = ( $parameters['continent'] != NULL && $parameters['continent'] != 0 ) ? true : false ; 

			if ( $per_continent == true )
				$WHERE_POPULATION = "T3.CONTINENT = {$parameters['continent']} AND" ; 
			else
				$WHERE_POPULATION = "T2.REGISTRY = {$parameters['registry']} AND" ; 

			$query = "

				SELECT 
					T3.REGISTRY,
					T3.LABEL,
					T2.PERIOD_1,
					T2.PERIOD_2,
					T2.TOTAL,
					FLAG_1,
					FLAG_2, 
					P0_4,P5_9,P10_14,P15_19,P20_24,P25_29,P30_34,P35_39,P40_44,P45_49,P50_54,P55_59,P60_64,P65_69,P70_74,P75_79,P80_84,P85 , 
					N0_4,N5_9,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85 , 
					T1.TOTAL as POPULATION
				FROM 
					CI5I_X_POP T1,
					CI5I_X_CASES T2,
					CI5I_X_REGISTRY T3,
					ETHNIC_GROUP T4,
					CI5I_X_VOLUME T5

				WHERE 

					T1.VOLUME = T2.VOLUME AND 
					T1.PERIOD_1 = T2.PERIOD_1 AND 
					T1.PERIOD_2 = T2.PERIOD_2 AND 
					T1.REGISTRY = T2.REGISTRY AND 
					T1.ETHNIC_GROUP = T2.ETHNIC_GROUP AND 
					T1.SEX = T2.SEX AND 

					T1.VOLUME = T5.VOLUME AND 
					T1.PERIOD_1 = T5.PERIOD_1 AND 
					T1.PERIOD_2 = T5.PERIOD_2 AND 
					T1.REGISTRY = T5.REGISTRY AND 
					T1.ETHNIC_GROUP = T5.ETHNIC_GROUP AND 

					T1.REGISTRY = T3.REGISTRY AND 

					T1.ETHNIC_GROUP = T4.ETHNIC_GROUP AND 

					T2.TOTAL > 0 AND
					T2.CANCER = {$parameters['cancer']} AND
					$WHERE_POPULATION
					T2.SEX = {$parameters['sex']}

				GROUP BY 
					T3.REGISTRY
				ORDER BY 
					T3.LABEL,T4.ETHNIC_GROUP
			" ; 

			// echo $query ; 

			$execute = $o_bdd->query( $query ) ; 

			while ( $data = $execute->fetch() )
			{

				// table cases
				$Populations = [] ; 
				for ( $i = 7 ; $i <= 24 ; $i++ ) $Populations[] = $data[$i] ;

				$Cases = [] ; 
				for ( $i = 25 ; $i <= 42 ; $i++ ) $Cases[] = $data[$i] ; 

				$data['CRUDE_RATE'] = getProportion( $data['TOTAL'] , $data['POPULATION']) ; 
				$rates_asr = getASRCI5( $Cases , $Populations ) ; 
				$data['ASR'] = $rates_asr['value'] ; 
				$result[] = $data ;  

				// debug( $data ) ;
				// exit() ; 
			}
			
			return $result ; 
		}

		public static function getCasesByCancer( $parameters )
		{
			global $o_bdd ; 

			$query = "

				SELECT 
					T3.CANCER,
					T3.LABEL,
					T2.PERIOD_1,
					T2.PERIOD_2,
					T2.TOTAL,
					FLAG_1,
					FLAG_2, 
					P0_4,P5_9,P10_14,P15_19,P20_24,P25_29,P30_34,P35_39,P40_44,P45_49,P50_54,P55_59,P60_64,P65_69,P70_74,P75_79,P80_84,P85 , 
					N0_4,N5_9,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85 , 
					T1.TOTAL as POPULATION , 
					T2.N_unk
				FROM 
					CI5I_X_POP T1,
					CI5I_X_CASES T2,
					CI5I_X_CANCER T3,
					ETHNIC_GROUP T4,
					CI5I_X_VOLUME T5

				WHERE 

					T1.VOLUME = T2.VOLUME AND 
					T1.PERIOD_1 = T2.PERIOD_1 AND 
					T1.PERIOD_2 = T2.PERIOD_2 AND 
					T1.REGISTRY = T2.REGISTRY AND 
					T1.ETHNIC_GROUP = T2.ETHNIC_GROUP AND 
					T1.SEX = T2.SEX AND 

					T1.VOLUME = T5.VOLUME AND 
					T1.PERIOD_1 = T5.PERIOD_1 AND 
					T1.PERIOD_2 = T5.PERIOD_2 AND 
					T1.REGISTRY = T5.REGISTRY AND 
					T1.ETHNIC_GROUP = T5.ETHNIC_GROUP AND 

					T2.CANCER = T3.CANCER AND 

					T2.TOTAL > 0 AND
					T1.ETHNIC_GROUP = T4.ETHNIC_GROUP AND 
					T2.REGISTRY = {$parameters['registry']} AND
					T2.SEX = {$parameters['sex']} AND
					T2.VOLUME = {$parameters['volume']}

				GROUP BY 
					T2.CANCER
				ORDER BY 
					T3.ICD
			" ; 

			// echo $query ; 

			$execute = $o_bdd->query( $query ) ; 

			while ( $data = $execute->fetch() )
			{

				// table cases
				$Populations = [] ; 
				for ( $i = 7 ; $i <= 24 ; $i++ ) $Populations[] = $data[$i] ;

				$Cases = [] ; 
				for ( $i = 25 ; $i <= 42 ; $i++ ) $Cases[] = $data[$i] ; 

				$data['CRUDE_RATE'] = getProportion( $data['TOTAL'] , $data['POPULATION']) ; 
				$rates_asr = getASRCI5( $Cases , $Populations ,  false , true , $data['N_unk']  ) ; 
				$data['ASR'] = $rates_asr['value'] ; 
				$result[] = $data ;  

				// debug( $data ) ;
				// exit() ; 
			}
			
			return $result ; 
		}



		public static function getCasesByVolumes( $parameters )
		{
			global $o_bdd ; 

			$query = "

				SELECT 
					T2.VOLUME,
					CONCAT(T2.PERIOD_1,'-',T2.PERIOD_2) AS `PERIOD`,
					T2.PERIOD_1,
					T2.PERIOD_2,
					T2.TOTAL,
					FLAG_1,
					FLAG_2, 
					P0_4,P5_9,P10_14,P15_19,P20_24,P25_29,P30_34,P35_39,P40_44,P45_49,P50_54,P55_59,P60_64,P65_69,P70_74,P75_79,P80_84,P85 , 
					N0_4,N5_9,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85 , 
					T1.TOTAL as POPULATION , 
					T2.N_unk
				FROM 
					CI5I_X_POP T1,
					CI5I_X_CASES T2,
					CI5I_X_CANCER T3,
					ETHNIC_GROUP T4,
					CI5I_X_VOLUME T5

				WHERE 

					T1.VOLUME = T2.VOLUME AND 
					T1.PERIOD_1 = T2.PERIOD_1 AND 
					T1.PERIOD_2 = T2.PERIOD_2 AND 
					T1.REGISTRY = T2.REGISTRY AND 
					T1.ETHNIC_GROUP = T2.ETHNIC_GROUP AND 
					T1.SEX = T2.SEX AND 

					T1.VOLUME = T5.VOLUME AND 
					T1.PERIOD_1 = T5.PERIOD_1 AND 
					T1.PERIOD_2 = T5.PERIOD_2 AND 
					T1.REGISTRY = T5.REGISTRY AND 
					T1.ETHNIC_GROUP = T5.ETHNIC_GROUP AND 

					T2.CANCER = T3.CANCER AND 

					T2.TOTAL > 0 AND
					T1.ETHNIC_GROUP = T4.ETHNIC_GROUP AND 
					T2.REGISTRY = {$parameters['registry']} AND
					T2.SEX = {$parameters['sex']}  AND 
					T2.CANCER = {$parameters['cancer']}

				GROUP BY 
					T2.VOLUME
				ORDER BY 
					T3.ICD
			" ; 

			// echo $query ; 

			$execute = $o_bdd->query( $query ) ; 

			while ( $data = $execute->fetch() )
			{

				// table cases
				$Populations = [] ; 
				for ( $i = 7 ; $i <= 24 ; $i++ ) $Populations[] = $data[$i] ;

				$Cases = [] ; 
				for ( $i = 25 ; $i <= 42 ; $i++ ) $Cases[] = $data[$i] ; 

				$data['CRUDE_RATE'] = getProportion( $data['TOTAL'] , $data['POPULATION']) ; 
				$rates_asr = getASRCI5( $Cases , $Populations ,  false , true , $data['N_unk']  ) ; 
				$data['ASR'] = $rates_asr['value'] ; 
				$result[] = $data ;  

				// debug( $data ) ;
				// exit() ; 
			}
			
			return $result ; 
		}

		public static function getRanges( $parameters )
		{
			global $o_bdd ; 

			$tmp_registries = explode( '-' ,  $parameters['registry'] ) ;
			$registries_id = ( count( $tmp_registries) == 1 ) ? [ (int)$parameters['registry'] ] : $tmp_registries ; 

			$where_cancer = ( $parameters['cancer'] == 'all' ) ? 'CANCER <> 1' : "CANCER = {$parameters['cancer']}" ; 

			$query = "SELECT MIN(YEAR) AS MIN_YEAR FROM ci5_cases WHERE REGISTRY IN (".implode(",",$registries_id).") AND $where_cancer LIMIT 1" ; 
			$execute = $o_bdd->query( $query ) ; 
			$result_min  = $execute->fetch( PDO::FETCH_ASSOC ) ;  

			$query = "SELECT MAX(YEAR) AS MAX_YEAR FROM ci5_cases WHERE REGISTRY IN (".implode(",",$registries_id).") AND $where_cancer LIMIT 1" ; 
			$execute = $o_bdd->query( $query ) ; 
			$result_max  = $execute->fetch( PDO::FETCH_ASSOC ) ;  

			return [ 'min' => (int)$result_min['MIN_YEAR'] , 'max' => (int)$result_max['MAX_YEAR'] ] ; 
		}

		public static function getTrendsCases( $parameters )
		{
			global $o_bdd ; 

			$cancers = [] ; 
			$base_cancers = self::getCancers( 'LABEL', ( $parameters['trends_by'] == 'cancer') ? true : false ) ; 

			if ( $parameters['cancer'] ==  'all' )
			{	
				$cancers = $base_cancers ; 
			}
			else
			{
				$cancers_parameters = explode( '-', $parameters['cancer'] );
				foreach( $base_cancers as $can )
				{
					if ( in_array( $can['CANCER'] , $cancers_parameters ) ) $cancers[] = $can ; 
				}
			}

			// var_dump($cancers); 
			$registries = self::getRegistries( true );

			// list of id registries 
			$tmp_registries = explode( '-' ,  $parameters['registry'] ) ;
			$registries_id = ( count( $tmp_registries) == 1 ) ? [ (int)$parameters['registry'] ] : $tmp_registries ; 

			$a_periods = explode('-' , $parameters['year'] ) ; 

			$dataset = [] ; 

			$wst_ages = self::getWStdAges() ; 

			foreach( $cancers as $cancer )
			{
				$query =  " SELECT " ; 
				$query .= " ci5_cases.TOTAL AS TOTAL,N_AGR,P0_4,P5_9,P10_14,P15_19,P20_24,P25_29,P30_34,P35_39,P40_44,P45_49,P50_54,P55_59,P60_64,P65_69,P70_74,P75_79,P80_84,P85,P_unk, ci5_pop.REGISTRY," ; 
				$query .= " ci5_pop.TOTAL AS POPULATION, N0_4,N5_9,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85,N_unk,ci5_pop.YEAR" ; 
				$query .= " FROM ci5_pop,ci5_cases WHERE " ; 
				$query .= " ci5_pop.YEAR = ci5_cases.YEAR AND " ; 
				$query .= " ci5_pop.REGISTRY = ci5_cases.REGISTRY AND " ; 
				$query .= " ci5_pop.ETHNIC_GROUP = ci5_cases.ETHNIC_GROUP AND " ; 
				$query .= " ci5_pop.SEX = ci5_cases.SEX AND " ; 
				$query .= " ci5_pop.REGISTRY IN (".implode(",",$registries_id).") AND " ; 
				$query .= " ci5_pop.ETHNIC_GROUP = {$parameters['ethnic_group']} AND " ; 

				if ( (int)$parameters['sex'] != 0 ) $query .= " ci5_pop.SEX = {$parameters['sex']} AND " ; // for 0, we want both sexes at the same time

				if ( count( $a_periods ) == 1) 
					$query .= " ci5_pop.YEAR = {$parameters['year']} AND " ; 
				else
					$query .= " ci5_pop.YEAR >= {$a_periods[0]} AND ci5_pop.YEAR <= {$a_periods[1]} AND " ; 


				$query .= " ci5_cases.CANCER = {$cancer['CANCER']}" ; 

				// echo $query; 

				$execute 		= $o_bdd->query( $query ) ; 
				
				while ( $data = $execute->fetch() )
				{
					$row = [
						'CANCER'	 	=> (int)$cancer['CANCER'] , 
						'ICD'			=> $cancer['ICD'] ,
						'LABEL' 		=> $cancer['LABEL'] , 
						'REGISTRY'		=> $registries[ $data['REGISTRY'] ]['LABEL'] ,
						'YEAR'			=> $data['YEAR'] , 
						'TOTAL' 		=> (int)$data['TOTAL'] , 
						'CRUDE_RATE' 	=> getProportion( $data['TOTAL'] , $data['POPULATION'] , 1) , 
						'UNK' 			=> (int)$data['N_unk'] 
					] ; 

					$Cases = []  ; 
					$Populations = [] ; 

					// foreach each row
					foreach( $wst_ages as $age )
					{
						// if ($age == '75+') break ; 
						$st_age 		= str_replace(  '-' , '_' , $age) ; 
						$row[ $age ] 	= getProportion( $data[ "N{$st_age}" ] , $data[ "P{$st_age}" ] , 1  ) ;

						$Cases[] 		= $data[ "N{$st_age}" ] ; 
						$Populations[]  = $data[ "P{$st_age}" ] ; 
					}

					$row['75+'] 		= getProportion( $data[ "N75_79" ] + $data[ "N80_84" ]  + $data[ "N85" ] , $data[ "P75_79" ] + $data[ "P80_84" ]  + $data[ "P85" ] , 1  ) ;

					$rates_asr 			= getASRCI5( $Cases , $Populations , false , true , $data['N_unk'] ) ; 
					$row['ASR'] 		= (float)$rates_asr['value'] ; 

					$dataset[] = $row ;
				}

			} // end foreach 

			return $dataset ; 

			// if dataset is equal to one row, only return row 
			if ( count( $dataset ) == 1)
				return $dataset[0] ; 
			else
				return $dataset ; 

		} // end function 

		public static function getTopCancersPerYear( $parameters )
		{
			global $o_bdd ; 

			$wst_ages = self::getWStdAges() ; 

			$query =  " SELECT t1.* , t1.TOTAL as CASES, t2.LABEL as CANCER_NAME , t3.TOTAL  as POPULATION, t3.* FROM ci5_cases t1 , ci5_cancer t2,  ci5_pop t3 WHERE " ;
			$query .= " t3.YEAR = t1.YEAR AND " ; 
			$query .= " t3.REGISTRY = t1.REGISTRY AND " ; 
			$query .= " t1.CANCER = t2.CANCER AND t1.YEAR = {$parameters['year']} AND"; 
			$query .= " t1.REGISTRY = {$parameters['registry']} AND t1.SEX = {$parameters['sex']} AND t1.CANCER <> 1 GROUP BY t1.CANCER ORDER BY t1.TOTAL DESC" ; 

			// echo $query;

			$execute 		= $o_bdd->query( $query ) ; 
			
			$dataset		= [] ;

			while ( $data = $execute->fetch() )
			{
				$row = [
					'CANCER'	 	=> (int)$data['CANCER'] , 
					'LABEL' 		=> $data['CANCER_NAME'] , 
					'CASES' 		=> (int)$data['CASES'] , 
					'YEAR'			=> (int)$parameters['year'] , 
					'REGISTRY'		=> (int)$parameters['registry'] , 
					'CRUDE_RATE' 	=> getProportion( $data['TOTAL'] , $data['POPULATION'] , 1) , 
					'UNK' 			=> (int)$data['N_unk'] 
				] ; 

				$Cases = []  ; 
				$Populations = [] ; 

				// foreach each row
				foreach( $wst_ages as $age )
				{
					// if ($age == '75+') break ; 
					$st_age 		= str_replace(  '-' , '_' , $age) ; 
					$row[ $age ] 	= getProportion( $data[ "N{$st_age}" ] , $data[ "P{$st_age}" ] , 1  ) ;

					$Cases[] 		= $data[ "N{$st_age}" ] ; 
					$Populations[]  = $data[ "P{$st_age}" ] ; 
				}

				$row['75+'] 		= getProportion( $data[ "N75_79" ] + $data[ "N80_84" ]  + $data[ "N85" ] , $data[ "P75_79" ] + $data[ "P80_84" ]  + $data[ "P85" ] , 1  ) ;

				$rates_asr 			= getASRCI5( $Cases , $Populations , false , true , $data['N_unk'] ) ; 
				$row['ASR'] 		= (float)$rates_asr['value'] ; 

				$dataset[] = $row ;
			}

			return $dataset ; 
		}


		public static function getPopulationByRegistry( $parameters )
		{
			global $o_bdd ; 

			$query =  " SELECT * FROM ci5_pop WHERE " ;
			$query .= " REGISTRY = {$parameters['registry']}" ; 

			$dataset = [ 'males' => [] , 'females' => [] ] ; 

			$execute 		= $o_bdd->query( $query ) ; 
		
			while ( $data = $execute->fetch() )
			{
				if ( $data['SEX'] == 1 ) $dataset['males'][ $data['YEAR'] ] = $data ; 
				if ( $data['SEX'] == 2 ) $dataset['females'][ $data['YEAR'] ] = $data ; 
			}

			return $dataset ; 
		}

	} // end class
