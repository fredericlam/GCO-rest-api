<?php

	class CanWhoDB
	{
		public static function getWStdAges()
		{
			return ['P0_4','P5_9','P10_14','P15_19','P20_24','P25_29','P30_34','P35_39','P40_44','P45_49','P50_54','P55_59','P60_64','P65_69','P70_74','P75_79','P80_84','P85'] ; 
		}

		public static function getWStdPosAges()
		{
			return ['5','10','15','20','25','30','35','40','45','50','55','60','65','70','75','80','85'] ; 
		}

		public static function getCountries( $params )
		{
			global $o_bdd ; 

			// select only country where we have datas
			$query 		 = " SELECT t2.* FROM who_pop t1 , who_country t2 WHERE t1.WHO = t2.WHO GROUP BY t1.WHO ORDER BY t2.LABEL  " ;
			// echo $query .'<br>' ; exit() ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$results 	= $execute->fetchAll( PDO::FETCH_ASSOC ) ; 

			return $results ; 
		}

		public static function getCancers( $params )
		{
			global $o_bdd ; 

			$query 		 = " SELECT * FROM who_cancer" ;
			// echo $query .'<br>' ; exit() ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$results 	= $execute->fetchAll( PDO::FETCH_ASSOC ) ; 

			return $results ; 
		}

		public static function getPopulations( $params )
		{
			global $o_bdd ; 

			$query 		 = " SELECT * FROM who_pop WHERE WHO IN ( ".implode( ',' , $params['multiple_population'] ) .")" ;
			// echo $query .'<br>' ; exit() ; 
			
			$execute 	= $o_bdd->query( $query ) ; 
			$results 	= $execute->fetchAll( PDO::FETCH_ASSOC ) ; 

			return $results ; 
		}

		public static function getMaxPerPopulations( $multiple_population )
		{
			global $o_bdd ; 

			$ages_field = [] ; 
			$ages = self::getWStdAges() ;

			foreach( $ages as $a ) $ages_field[] = "MAX({$a})" ; 

			$query 		 = " SELECT GREATEST( ".implode( ',' , $ages_field ) ." ) AS MAX FROM who_pop WHERE WHO IN ( ".implode( ',' , $multiple_population ) .") AND SEX in (1,2)" ;
			
			$execute = $o_bdd->query( $query ) ; 
			$max 	 = $execute->fetch( PDO::FETCH_ASSOC ) ; 

			return $max['MAX'] ; 
		}



	} // end class