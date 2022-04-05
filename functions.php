<?php
	
	/**
	* Compute number
	* @param number 
	* @param age 2
	* @param age 2
	* @param age d
	*/ 
	function ComputeNUM( $N1, $age1, $age2, $age_d)
	{
		$num = 0;
		$N = [] ; // 10 

		for ($age = 0; $age < 10; $age++) $N[$age] = $N1[$age];
		$age_d--;  	// age_d = number of age bands available in population: so latest available index = age_d-1...
		
		if ($age2 == 9) {				// Lastest age group requested is 75+ (open class)
			if ($age2 >= $age_d) {     
				for ($age = $age_d+1; $age <= $age2; $age++) {
					$N[$age_d] += $N[$age];
					$N[$age] = 0;
				}
				$age2 = $age_d;
				if ($age1 > $age2)  {
					$num = -1;
					return $num;
				}
			}
		}
		else {                               
			if ($age2 >= $age_d)  {              
				$num = -1; 
				return $num;
			}
		}
		// echo "from $age1 < $age2 <br>" ; 
		for ($age = $age1; $age <= $age2; $age++) $num += $N[$age];
		return $num;
	}

	/**
	* Debug function with <pre> formatting
	* @param number 
	*/
	function debug( $d ){
		echo '<pre>' ; 
		var_dump($d) ; 
		echo '</pre>';
	}

	/**
	* Aliases functions
	*/
	function sum( $array ){
		return array_sum( $array ) ; 
	}
	function slice( $array , $start , $end ){
		return array_slice( $array , $start , $end ) ; 
	}

	/**
	* Sort an array by key
	* @param array to sort
	* @param key with which to sort
	* @param type of sort
	*/
	function sksort(&$array, $subkey="id", $sort_ascending=false) {

		$temp_array = [] ; 
		
	    if (count($array))
	        $temp_array[key($array)] = array_shift($array);

	    foreach($array as $key => $val){
	        $offset = 0;
	        $found = false;
	        foreach($temp_array as $tmp_key => $tmp_val)
	        {
	            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
	            {
	                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
	                                            array($key => $val),
	                                            array_slice($temp_array,$offset)
	                                          );
	                $found = true;
	            }
	            $offset++;
	        }
	        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
	    }

	    if ($sort_ascending) $array = array_reverse($temp_array);

	    else $array = $temp_array;
	}

	/**
	* Compute CR
	* @param number 
	* @param p1
	* @param age 2
	* @param age 2
	* @param age d
	*/
	function ComputeCR ($N1, $P1, $age1, $age2, $age_d, $rtype) {
		$N = [];
		$P = [];
		for ($age = 0; $age < 10; $age++) {
			$N[$age] = $N1[$age];
			$P[$age] = $P1[$age];
		}
		/*debug( $N);
		debug( $P);
		exit() ;*/

		$total = $CR = $SE_CR = $all = 0;
		$age_d--;  // age_d = number of age bands available in population: so latest available index = age_d-1...
		
		if ( $age2 == 9) {				// Lastest age group requested is 75+ (open class)
			if ( $age1 == 0) $all = 1;     // All age-groups: so add age unknown cases and population in the computation
			if ( $age2 >= $age_d) {     
				for ( $age = $age_d+1; $age <= $age2; $age++ ) {
					$N[$age_d] += $N[$age];
					$N[$age] = 0;
				}
				$age2 = $age_d;
				if ($age1 > $age2)  {
					$CR = $SE_CR = -1;
					return $CR;
				}
			}
		}
		else {                               // truncated rates (AGE SPECIFIC RATE)
			if ($age2 >= $age_d)  {          // no population available for selected "last" class
				$CR = $SE_CR = -1; 
				return $CR;
			}
		}

		for ( $age = $age1; $age <= $age2; $age++) {
			$CR += $N[$age];  
			$total += $P[$age];
		}

		if ($total) {
			$all = $CR;
			$CR = $CR * 100000 / $total ;
			if ($CR) $SE_CR = $CR / sqrt($all);
		}
		if ($rtype == 0)	return $CR;
		else return $SE_CR;
	}

	/**
	* Compute number
	* @param number 
	* @param p1
	* @param age 2
	* @param age 2
	* @param age d
	*/
	function ComputeASR ( $N1, $P1, $age1, $age2, $age_d, $rtype) {
		$N = [];
		$P = [];
		for ($age = 0; $age < 10; $age++) {
			$N[$age] = $N1[$age];
			$P[$age] = $P1[$age];
		}
		$rates = [];
		$WstdPop = [];
		
		$WstdPop[0] = 0.31; // 0-14
		$WstdPop[1] = 0.37; // 15-39
		$WstdPop[2] = $WstdPop[3] = 0.06; // 40-44 * 45-49
		$WstdPop[4] = 0.05; // 50-54
		$WstdPop[5] = $WstdPop[6] = 0.04; // 55-59 * 60-64
		$WstdPop[7] = 0.03; // 65-69
		$WstdPop[8] = $WstdPop[9]  = 0.02 ; // 70-75 * 75+
		
		$sum = $ASR = $SE_ASR = 0;
		for ($age = 0; $age < 10; $age++) {
			$rates[$age] = 0;
			$sum += $N[$age];
		}

		$age_d--;  // age_d = number of age bands available in population: so latest available index = age_d-1...
		if ($age2 == 9) {          // Lastest age group requested is 75+ (open class)
			if ($age2 >= $age_d) {    
				for ($age = $age_d+1; $age <= $age2; $age++) {
					$N[$age_d] += $N[$age];
					$WstdPop[$age_d] += $WstdPop[$age];
					$N[$age] = 0;
				}
				$age2 = $age_d;
				if ($age1 > $age2)  {
					$ASR = $SE_ASR = -1;
					return -1;
				}
			}
		}
		else {                               // truncated rates
			if ($age2 >= $age_d)  {               // no population available for selected "last" class
				$ASR = $SE_ASR = -1; 
				return -1;
			}
		}
		$sum = 0;
		for ($age = $age1; $age <= $age2; $age++) {
			if ($P[$age]) $rates[$age] = $N[$age] * 100000 / $P[$age];
			$ASR += ($rates[$age] * $WstdPop[$age]);
			$sum += $WstdPop[$age];
			$SE_ASR += ($rates[$age] * $WstdPop[$age] * $WstdPop[$age] / $P[$age]);
		}
		if ($sum) {
			$ASR = ($ASR / $sum);
			$SE_ASR = sqrt($SE_ASR * 100000 );
			$SE_ASR = ($SE_ASR / $sum);
		}
		//echo round( $ASR , 1) ; echo '<br>' ; 
		
		if ($rtype == 0)	
			return round( $ASR , 1);
		else 
			return $SE_ASR;
	}

	function ComputeCUM ($N1, $P1, $age1, $age2, $age_d, $rtype) {
		$N = [];
		$P = [];
		for ($age = 0; $age < 10; $age++) {
			$N[$age] = $N1[$age];
			$P[$age] = $P1[$age];
		}
		$rates = [];
		$WstdPop = [];
		
		$WstdPop[0] = 15;
		$WstdPop[1] = 25;
		$WstdPop[2] = $WstdPop[3] = $WstdPop[4] = $WstdPop[5] = $WstdPop[6] = $WstdPop[7] = $WstdPop[8] = $WstdPop[9]  = 5;
		
		$sum = $CUM = $SE_CUM = $RISK = $SE_RISK = 0;
		$age_d--;  // age_d = number of age bands available in population: so latest available index = age_d-1...
		
		for ($age = 0; $age < 10; $age++) {
			$rates[$age] = 0;
			$sum += $N[$age];
		}
		if ($age2 >= $age_d) {  // for CUM, last open class is not valid: stop
	  		$CUM = $SE_CUM = -1;
			return -1;
		}
		for ($age = $age1; $age <= $age2; $age++) {
			if ($P[$age]) $rates[$age] = $N[$age] * 100000.0 / $P[$age];
			$CUM += ($rates[$age] * $WstdPop[$age]);
			$SE_CUM += ($rates[$age] * $WstdPop[$age] * $WstdPop[$age] / $P[$age]);
		}
		$CUM = $CUM / 1000.0; 
		$SE_CUM = sqrt($SE_CUM / 100000.0);
		$SE_CUM = ($SE_CUM * 100.0);    // Percent
		
		$RISK = $CUM / 100.0;  // Cumulative rates was percentage..
		$RISK = (1.0 - (exp(-$CUM/100.0))) * 100.0;
		$SE_RISK= $SE_CUM * 100.0;    // Percent
		$SE_RISK = (1.0 - (exp(-$SE_CUM/100.0))) * 100.0;
		if ($rtype == 0)	return $RISK;
		else return $SE_RISK;
	}

	function getProportion( $value , $population , $precision = 2 )
	{
		if ( $population == 0 ) return 0 ; 
		return $number = $value * 100000 / $population ;
		// $format = number_format($number, $precision , '.', ',') ;
		//return $format ; 
	}

	function getASR( $Cases , $table_population , $age_rates = false, $format = true  )
	{
		$WstdPop 	= [ 0.31 , 0.37 , 0.06 , 0.06 , 0.05 , 0.04 , 0.04 , 0.03 , 0.02, 0.02 ] ; 
		$WstAges 	= CanGlobocan::getWStdAges()  ; 
		$AgesRates 	= [] ; 
		$AgesNumbers= [] ; 

		$cumul = 0 ; 
		foreach( $Cases as $iKey => $Number )
		{
			if ( $table_population[$iKey+1] == 0 || $table_population[$iKey+1] == null )
				$dividor = 1 ; 
			else
				$dividor = $table_population[$iKey+1] ; ; 

			$rate 			= ( $Number * 100000 / $dividor ) * $WstdPop[$iKey ]  ; 
			$cumul 			+= $rate ; 

			$AgesRates[] 	= number_format( ( $Number * 100000 / $dividor ) , 1 ) ; 
			$AgesNumbers[] 	= $Number  ; 
			$k = $iKey + 1 ;
			// echo " Rate = $Number * 100000 / {$table_population[$k]} * {$WstdPop[$iKey]}  = ".(( $Number * 100000 / $dividor )*$WstdPop[$iKey])." <br> " ; 
		}

		if ( $format == true ) 
			$format = number_format( $cumul, 2 , '.', ',' ) ;
		else
			$format = $cumul ; 

		//echo " <br> Total : $cumul <br> ================== <br> "; 

		if ( $age_rates == false )
			return $format ;
		else if ( $age_rates == 'rate')
			return $AgesRates ;
		else if ( $age_rates == 'number')
			return $AgesNumbers ; 
	}

	function getASRCI5( $Cases , $table_population , $age_rates = false, $format = true , $unknown_cases = 0  )
	{
		$WstdPop 	= [ 0.12 , 0.10 , 0.09 , 0.09 , 0.08 , 0.08 , 0.06 , 0.06 , 0.06, 0.06 , 0.05, 0.04, 0.04, 0.03, 0.02, 0.01, 0.005, 0.005 ] ; 
		$AgesRates 	= [] ; 
		$AgesNumbers= [] ; 

		$cumul = 0 ; 
		$sum_numbers = 0 ; 
		$sum_populations = 0 ; 

		$unkfact = 0 ; 

		foreach( $Cases as $iKey => $Number )
		{
			$sum_numbers	 += $Number ; 
			$sum_populations += $table_population[$iKey] ;
		}
		$sum_numbers += $unknown_cases ; 
		
		foreach( $Cases as $iKey => $Number )
		{
			if ( $table_population[$iKey] == 0 || $table_population[$iKey] == null )
				$dividor = 1 ; 
			else
				$dividor = $table_population[$iKey] ;

			$k = $iKey  ;

			if ( $table_population[$k] == 0 )
				$rate = 0 ; 
			else
				$rate  = ( $Number * 100000 / $dividor * $WstdPop[$iKey])  ; 

			$value = ( $Number * 100000 / $dividor ) ; 
			
			$cumul 			+= $rate  ; 

			$AgesRates[] 	= $rate ; 
			$AgesASR[]		= $value ;
			$AgesNumbers[] 	= $Number  ; 

			// echo "<br> <strong>$iKey</strong> : Rate = $Number * 100000 / {$table_population[$k]} * {$WstdPop[$iKey]}  = ". number_format( $value , 1 )." " ; 

			// if ( $iKey == 14 ) break ; 

			//echo "<br> <strong>$iKey</strong> : Rate = $Number * 100000 / {$table_population[$k]} = ".(( $Number * 100000 / $dividor ))." " ; 
		}

		// specific 75+
		$elder_cases = 0 ;
		$elder_pop = 0 ;  
		$elder_std = 0 ;
	
		for ( $i = 15 ; $i < count($Cases) ; $i++ )
		{
			$elder_cases += $Cases[$i] ; 
			$elder_pop += $table_population[$i] ; 

			$dividor    = ( $table_population[$i] == 0) ? 1 : $table_population[$i] ; 
			$tmp_value  = ( $Cases[$i] * 100000 / $dividor ) ; 
			$tmp_rate   = $tmp_value * $WstdPop[$i] ; 

			// echo "<br> <strong>$i</strong> : Rate = {$Cases[$i]} * 100000 / $dividor * {$WstdPop[$i]}  = ". number_format( $tmp_rate , 1 )." & " . number_format( $tmp_value , 1 ) ;

			$elder_std += $WstdPop[$i] ; 
			// $cumul += round( $tmp_rate , 1) ; 
		}

		// echo '<pre>' ; var_dump($AgesRates) ; echo '</pre>' ;  exit();

		$elder_value = ( $elder_pop == 0 ) ? 0 : ( $elder_cases * 100000 / $elder_pop ) ; 
		
		if ( $sum_numbers - $unknown_cases <= 0 )
			$unkfact = 1 ; 
		else
			$unkfact = $sum_numbers / ( $sum_numbers - $unknown_cases );

		if ( $elder_pop  == 0 )
			$elder_rate = 0 ; 
		else
			$elder_rate = ( $elder_cases * 100000 / $elder_pop ) * $elder_std ; 
		// echo "<br><br> <strong>75+</strong> : Rate = $elder_cases * 100000 / $elder_pop = ". number_format( $elder_value , 1 ) . " Taux : $elder_rate "  ; 
		$cumul += $elder_rate  ; 

		$AgesRates[] 	= $rate ; 
		$AgesASR[]		= $elder_value ;

		$ASR 			= $cumul * $unkfact ; 

		if ( $format == true ) 
			$format = number_format( $ASR, 1 , '.', ',' ) ;
		else
			$format = $cumul ; 

		// echo " <br> Total cases : $sum_numbers <br> ASR : $cumul <br> unkfact : $unkfact  <br> ASR = ($cumul*$unkfact)  = ".($cumul*$unkfact)." ================== <br> "; 
		
		// debug( $AgesASR) ;

		return [
			'value' => $format , 'rate' => $AgesRates , 'number' => $AgesNumbers
		] ; 

		if ( $age_rates == false )
			return $format ;
		else if ( $age_rates == 'rate')
			return $AgesRates ;
		else if ( $age_rates == 'number')
			return $AgesNumbers ;
	}



	/**
	* Calculate crude rate
	* @param (int) total cumulated cases
	* @param (int) total cumulated population
	* @return (float)
	*/
	function getCrudeRate( $total_cases , $total_populations )
	{
		if ( $total_populations == null ) return 0 ; 

		$math = $total_cases * 100000 / $total_populations; 
		// echo " Crude rate : $total_cases * 100000 / $total_populations = $math <br>" ; 
		return $math   ; 
	}

	/**
	* Calculate cum risk 0-74 (for globocan 2012). The grouping is "cumulated cancers" data
	* @param (array) List of cases, per cancer
	* @param (array) List of populations - The sum of cases (per age) are divided by the unique population 
	* @return (float)
	*/
	function getCumRiskCancer( $Cases_per_ages , $Populations , $last = false )
	{
		global $conf , $settings ; 

		$sum_cases 	= [] ; 
		$WstdPop	= $conf['y'][ $settings['year'] ]['age_threshold'] ;  // ; [ 31000,37000,6000,6000,5000,4000,4000,3000,2000,2000 ] ; 
		$sum_pops 	= []  ;
		$index = 0 ; 

		// echo count( $Cases_per_ages);

		if ( $Populations == false ) return 0 ; 

		foreach( $Cases_per_ages as $iKey => $case_age )
		{
			//$index = 0 ; 
			// if ( $iKey > 14) continue ;
			// if ( $last == false && $iKey == (count($Cases_per_ages)-1)  ) continue ; 

			if ( !isset( $sum_cases[$iKey]) ) $sum_cases[$iKey] = 0 ; 
			$cal_per_age = ( $WstdPop[$iKey] * $case_age / $Populations[$iKey] ) ; 
			// echo " $iKey => {$WstdPop[$iKey]} * $case_age / {$Populations[$iKey]} = $cal_per_age <br> " ; 
			$sum_cases[$iKey] += $cal_per_age ; 
			//$index++ ; 
		}

		$sum = array_sum( $sum_cases ) ; 
		$exp =  ( (1 - exp(-$sum)) * 100 )  ; 

		// echo "<br><br> Cum risk is = $exp" ; exit() ; 

		return  $exp ;
	}

	/**
	* Calculate cum risk 0-74 (for globocan 2012). The grouping is "cumulated population" data
	* @param (array) List of cases, per cancer
	* @param (array) List of populations - The sum of cases (per age) are divided by all populations combined 
	* @return (float)
	*/
	function getCumRiskPopulation( $Cases_per_ages , $Populations , $last = false )
	{
		global $conf , $settings ; 

		$sum_cases 	= [] ; 
		$WstdPop	= $conf['y'][ $settings['year'] ]['age_threshold'] ;  // ; [ 31000,37000,6000,6000,5000,4000,4000,3000,2000,2000 ] ; 
		$sum_pops 	= []  ;
		$iKey = 0 ; 
		foreach( $Populations as $pop_id => $pop_ages )
		{
			//if ( $iKey > 14) continue ;

			// var_dump($pop_ages);
			if ( !is_array( $pop_ages ) ) continue ; 
			foreach($pop_ages as $ikA => $pop_age)
			{
				if ( !isset( $sum_pops[$ikA]) ) $sum_pops[$ikA] = 0 ; 
				$sum_pops[$ikA] += $pop_age ; 
			}
			$iKey++ ; 
		}


		$index = 0 ;  
		foreach( $Cases_per_ages as $iKey => $case_age )
		{
			//if ( $iKey > 14) continue ;
			//if ( $last == false && $iKey == (count($Cases_per_ages)-1)  ) continue ; 

			if ( !isset( $sum_cases[$iKey]) ) $sum_cases[$iKey] = 0 ; 
			$cal_per_age = ( $WstdPop[$iKey] * $case_age / $sum_pops[$iKey] ) ; 
			//echo " $iKey => {$WstdPop[$iKey]} * $case_age / {$sum_pops[$iKey]} = $cal_per_age <br> " ; 
			$sum_cases[$iKey] += $cal_per_age ; 
			$index++ ; 
		}

		$sum = array_sum( $sum_cases ) ; 
		$exp = ( (1 - exp(-$sum)) * 100 ) ;

		return  $exp ;
	}

	/**
	* Calculate ASR (for globocan 2012). The grouping is "cumulated cancers" data
	* @param (array) List of cases, per cancer
	* @param (array) List of populations - The sum of cases (per age) are divided by the unique population 
	* @return (float)
	*/
	function getASRCancer( $Cases_per_ages , $Populations , $last = true )
	{
		global $conf , $settings ; 
		// var_dump($Populations);exit();

		$sum_cases = [] ; 
		$WstdPop = $conf['y'][ $settings['year'] ]['segi'] ; // [ 31000,37000,6000,6000,5000,4000,4000,3000,2000,2000 ] ; 

		if ( $Populations == false ) return 0 ; 

		$index = 0 ; 

		$sum_segi = 0 ; 
		foreach( $Cases_per_ages as $iKey => $case_age )
		{
			if ( !isset( $sum_cases[$iKey]) ) $sum_cases[$iKey] = 0 ; 
			$cal_per_age = ( $case_age * $WstdPop[$iKey] / $Populations[$iKey] ) ; 
			$sum_segi += $WstdPop[$iKey] ; 
			// echo " $iKey => {$WstdPop[$index]} * $case_age / {$Populations[$iKey]} = $cal_per_age <br> " ; 
			$sum_cases[$iKey] += $cal_per_age ; 
			// $index++ ; 

		}
		$sum = array_sum( $sum_cases ) ; 
		
		if ( $sum_segi == 0 ) return 0 ; 

		$rate = $sum * 100000 / $sum_segi ; 

		return  $rate ;
	}

	/**
	* Calculate ASR (for globocan 2012). The grouping is "cumulated population" data
	* @param (array) List of cases, per cancer
	* @param (array) List of populations - The sum of cases (per age) are divided by the sum of population per age 
	* @return (float)
	*/
	function getASRPopulation( $Cases_per_ages , $Populations , $last = true )
	{
		global $conf , $settings ; 

		$sum_cases 	= [] ; 
		$WstdPop	= $conf['y'][ $settings['year'] ]['segi'] ;  // ; [ 31000,37000,6000,6000,5000,4000,4000,3000,2000,2000 ] ; 

		$sum_pops 	= []  ;

		foreach( $Populations as $pop_id => $pop_ages )
		{
			if ( !is_array( $pop_ages ) ) continue ; 
			foreach($pop_ages as $ikA => $pop_age)
			{
				if ( !isset( $sum_pops[$ikA]) ) $sum_pops[$ikA] = 0 ; 
				$sum_pops[$ikA] += $pop_age ; 
			}
		}	

		//$index = 0 ; 
		$sum_segi = 0 ; 
		foreach( $Cases_per_ages as $iKey => $case_age )
		{
			// if ( $last == false && $index == 9 ) break ; 
			if ( !isset( $sum_cases[$iKey]) ) $sum_cases[$iKey] = 0 ; 
			$cal_per_age = ( $case_age * $WstdPop[$iKey] / $sum_pops[$iKey] ) ; 
			$sum_segi += $WstdPop[$iKey] ; 
			// echo " $iKey => {$WstdPop[$iKey]} * $case_age / {$sum_pops[$iKey]} = $cal_per_age <br> " ; 
			$sum_cases[$iKey] += $cal_per_age ; 
			// $index++ ; 		

		}
		$sum = array_sum( $sum_cases ) ; 
		$asr = $sum * 100000 / $sum_segi ;
		// echo " asr = $asr <br> ============= <br> " ; 
		return $asr ; 
	}

	function array_to_xml( $data, &$xml_data ) {
	    foreach( $data as $key => $value ) {
	        if( is_array($value) ) {
	            if( is_numeric($key) ){
	                $key = 'item'.$key; //dealing with <0/>..<n/> issues
	            }
	            $subnode = $xml_data->addChild($key);
	            array_to_xml($value, $subnode);
	        } else {
	            $xml_data->addChild("$key",htmlspecialchars("$value"));
	        }
	     }
	}

	function getHTTPJson( $url )
	{
		$json 			= file_get_contents( "$url" );
		$data_obj_from 	= json_decode($json);

		return $data_obj_from; 
	}

	//Slice an array but keep numeric keys
	function key_array_slice($array, $offset, $length) {
	    
	    //Check if this version already supports it
	    if (str_replace('.', '', PHP_VERSION) >= 502)
	       return array_slice($array, $offset, $length, true);
	        
	    foreach ($array as $key => $value) {
	    
	        if ($a >= $offset && $a - $offset <= $length)
	            $output_array[$key] = $value;
	        $a++;
	        
	    }
	    
	    return $output_array;

	}

	function exists( $var , $default = 0 )
	{
		return ( isset( $var ) ? $var : $default ) ; 
	}


	function getCumRisk( $Cases , $table_population , $exclude_75 = true )
	{
		$WstdPop = [ 15,25,5,5,5,5,5,5,5,5 ] ; 
		$WstAges = [ '0-14','15-39','40-44','45-49','50-54','55-59','60-64','65-69','70-74','75' ] ; 

		$cumul 	= 0 ; 
		$cpt 	= 0 ;  

		// debug( $Cases ) ; debug( $table_population ) ; 

		foreach( $Cases as $iKey => $Number )
		{

			$rate 		= ( $Number * 100000 / $table_population[$iKey+1] ) * $WstdPop[$iKey]  ; 
			$cumul 		+= $rate ; 
			$iKeyPlusUn = $iKey + 1 ; 
			// echo " Age [{$WstAges[$iKey]}] :  $Number * 100000 / {$table_population[$iKeyPlusUn]} * $WstdPop[$iKey] = $rate <br>  " ; 

			if ( $cpt == 8 && $exclude_75 == true ) break ; 
			$cpt++  ; 
		}

		// echo " <br> cumul : $cumul <br>  "; 

		$cumul 	= $cumul / 1000.0; 
		$risk 	= $cumul / 100.0;
		$risk 	= ( 1.0 - ( exp( -$cumul / 100.0 ))) * 100.0;
		$format = number_format( $risk, 2, '.', ',' ) ;

		// echo " <br> Format : $format <br> ================== <br> "; 
		return $format ; 
	}