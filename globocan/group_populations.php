<?php

	// require __DIR__ . '/../vendor/autoload.php';
	
	set_time_limit(0) ;

	header('Content-Type: text/html; charset=utf-8');

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

    $populations_g = [
    	'910_911_913_914' => [
    		'label' => 'Sub-Saharan Africa' , 
    		'id' => 960 
    	],
    	'912_398_417_762_795_860_922' => [
    		/*
			Central asia = 
			398 = Kazakhstan
			417 = Kyrgystan
			762 = Tajikistan
			795 = Turkmenistan
			860 = Uzbekistan
    		*/
    		'label' => 'Northern Africa, Central Asia and Western Asia' , 
    		'id' => 973 
    	],
    	'4_50_64_356_462_524_586_144_364_906_920' => [
    		/*
			South asia = 
			4_ Afhganisatn
			50_ Bangladesh 
			64_ Bhutan
			356_ India
			462_ Maldive
			524_ Nepal
			586_ pakistan
			144_ Sri Lanka
			364 - Iran
    		*/
    		'label' => 'South, Eastern and South Eastern Asia' ,
    		'id' => 975 
    	],
    	'4_204_854_108_140_148_174_180_232_231_270_324_624_332_408_430_450_454_466_508_524_562_646_686_694_706_728_760_762_834_768_800_887_716' => [
    		'label' => 'Low income' ,
    		'id' => 998 
    	],
    	'8_12_51_31_112_84_70_72_76_100_160_170_188_192_214_218_226_242_266_320_328_364_368_388_400_398_422_434_807_458_462_480_484_499_516_600_604_642_643_882_688_710_662_740_764_792_795_862' => [
    		'label' => 'Upper Middle Income' ,
    		'id' => 999
    	],
    	'24_50_64_68_132_116_120_178_384_262_818_222_268_288_340_356_360_404_417_418_426_478_498_496_504_104_558_566_586_598_608_678_90_144_729_748_626_788_804_860_548_704_275_894' => [
    		'label' => 'Low Middle Income' ,
    		'id' => 1000
    	],
    	'32_36_40_44_48_52_56_96_124_152_191_196_203_208_233_246_250_258_276_300_316_348_352_372_376_380_392_410_414_428_440_442_470_528_540_554_578_512_591_616_620_630_634_682_702_703_705_724_752_756_780_784_826_840_858' => [
    		'label' => 'High income' ,
    		'id' => 1001
    	],
    ] ;


    $populations_g = [
    	'36_50_52_84_72_96_120_124_242_270_288_328_356_388_404_426_454_458_470_480_508_516_554_566_586_598_196_646_882_694_702_90_710_144_748_44_780_800_826_834_548_894_716' => [
    		'label' => "Common wealth countries" , 
    		'id' => 1002 
    	]
    ] ; 

    $ages_field = [ 'N00_04','N05_09','N10_14','N15_19','N20_24','N25_29','N30_34','N35_39','N40_44','N45_49','N50_54','N55_59','N60_64','N65_69','N70_74','N75_79','N80_84','N85'] ; 
    
    // init data
    $total = 0 ; 
	$output = [] ; 
	$types	= [0,1] ; 
	$sexes 	= [0,1,2] ; 
	$statistics = [1,3,5] ; 

	$pops_str = filter_input( INPUT_GET , 'pops' ) ; 
	if ( empty( $pops_str )) exit('Error with pops variable') ; 
	$pop_info = $populations_g[ $pops_str ] ; 

	$pops = explode('_',$pops_str) ; 

	$queries_pop = [] ; 

	// list all cancers 
	$cancers = CanGlobocan::getCancers(true,false,$settings) ; 
	$line = 0 ; 

	echo "<br> ============================================================ I / M ============================================ <br>" ; 

	// array_push( $cancers , [ ['cancer' => 39 ] , ['cancer' => 40 ] ]) ;
	// var_dump($cancers); exit() ;

	// specific to "uspecified and other specified"
	// $cancers = [ ['cancer' => 37 ] , ['cancer' => 38 ] ] ;

	//var_dump($cancers);

	foreach( $cancers as $cancer )
	{
		foreach( $types as $type )
		{
			foreach( $sexes as $sex )
			{
				$url = $_SERVER['HTTP_HOST']."/api/globocan/v2/2018/data/cancer/{$type}/{$sex}/{$pops_str}/{$cancer['cancer']}/" ; 
				// if ( $cancer['cancer'] == 6 ) exit($url) ;

				// $content_file = file_get_contents( $url ) ; 
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				curl_close($ch);

				// exit() ; 
				$row = json_decode( $output )[0] ; 

				// loops for ages
				$ages = [] ; 

				foreach( $row->ages as $pop )
				{
					foreach( $ages_field as $field )
					{
						if ( !isset( $ages[$field] )) $ages[$field] = 0 ;
						$ages[$field] += $pop->{$field} ; 
					}
				}

				$line++ ;

				// FOR NUMBERS 
				$query_insert = "INSERT INTO globocan2018_numbers (UN_CODE,COUNTRY,TYPE,SEX,CANCER,ui_lower,ui_upper,TOTAL,N00_04,N05_09,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85,CRUDE_RATE,ASR,CUM_RISK) VALUES ";

				$query_insert .= " (".int($pop_info['id']).",".int($pop_info['id']).",".int($type).",".int($sex).",".int($cancer['cancer']).",0,0,".int(fAbs($row->total))."" ; 

				foreach( $ages as $age )
				{
					$query_insert .= ",{$age}" ; 
				}

				$query_insert .= ",".fFloat($row->crude_rate).",".fFloat($row->asr).",".fFloat($row->cum_risk).") ; <br/>" ; 

				// FOR POPS
				$demography = [] ; 

				foreach( $row->populations as $pop )
				{
					for( $i = 0 ; $i < 18 ; $i++ )
					{
						if ( !isset( $demography[$i] )) $demography[$i] = 0 ;
						$demography[$i] += $pop[$i] ; 
					}
				}

				$query_insert_pop = "INSERT INTO globocan2018_pop (UN_CODE,COUNTRY,SEX,TOTAL,N00_04,N05_09,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85) VALUES "; 
				$query_insert_pop .= " (".int($pop_info['id']).",".int($pop_info['id']).",".int($sex).",".array_sum($demography)."" ; 
				foreach( $demography as $dem ) $query_insert_pop .= ",{$dem}" ; 
				$query_insert_pop .= ");" ; 

				$queries_pop[$sex] = $query_insert_pop ; 

				$line++ ;
				
				//if ( $cancer['cancer'] == 6 ) 
				echo( $query_insert ); 
			}
		}
	}

	exit($line);

	/*echo "<br> ============================================================ I/M CUM RISK ============================================ <br>" ; 
	foreach( $cancers as $cancer )
	{
		foreach( $types as $type )
		{
			foreach( $sexes as $sex )
			{
				$url = $_SERVER['HTTP_HOST']."/api/globocan/v2/2018/data/cancer/{$type}/{$sex}/{$pops_str}/{$cancer['cancer']}/?ages_group=0_14" ; 
				// exit($url);
				// $content_file = file_get_contents( $url ) ; 
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				curl_close($ch);

				// exit() ; 
				$row = json_decode( $output )[0] ; 

				$line++ ;

				// FOR NUMBERS 
				$query_update = "UPDATE globocan2018_numbers SET CUM_RISK = '{$row->cum_risk}'  WHERE country = {$pop_info['id']} AND type = {$type} AND sex = {$sex} AND cancer = {$cancer['cancer']} LIMIT 1 ; <br> ";
				echo ( $query_update ) ; 
				
			}
		}
	}*/

	// exit(); 

	/*echo "<br> ============================================================ POP ============================================ <br>" ; 
	foreach($queries_pop as $query ) echo "$query<br>" ; 

	exit("Number total of rows : $line");

	exit();*/

	/*echo "<br> ============================================================ PREVALENCE ============================================ <br>" ; 

	foreach( $cancers as $cancer )
	{
		foreach( $statistics as $statistic )
		{
			foreach( $sexes as $sex )
			{
				$url = $_SERVER['HTTP_HOST']."/api/globocan/v2/2018/data/cancer/2/{$sex}/{$pops_str}/{$cancer['cancer']}/?statistic={$statistic}" ; 
				// exit($url);
				// $content_file = file_get_contents( $url ) ; 
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				curl_close($ch);

				// exit() ; 
				$row = json_decode( $output )[0] ; 

				// loops for ages
				$ages = [] ; 

				foreach( $row->ages as $pop )
				{
					foreach( $ages_field as $field )
					{
						if ( !isset( $ages[$field] )) $ages[$field] = 0 ;
						$ages[$field] += $pop->{$field} ; 
					}
				}

				$line++ ;

				// FOR NUMBERS 
				$query_insert = "INSERT INTO globocan2018_prevalence(UN_CODE,COUNTRY,survival,SEX,CANCER,TOTAL,N00_04,N05_09,N10_14,N15_19,N20_24,N25_29,N30_34,N35_39,N40_44,N45_49,N50_54,N55_59,N60_64,N65_69,N70_74,N75_79,N80_84,N85,prop) VALUES ";

				$query_insert .= " (".int($pop_info['id']).",".int($pop_info['id']).",".int($statistic).",".int($sex).",".int($cancer['cancer']).",".int(fAbs($row->total))."" ; 

				foreach( $ages as $age )
				{
					$query_insert .= ",{$age}" ; 
				}

				$total_population = CanCases::getCumulatedPopulations([
					'countries' => $pops , 
					'sex' => $cancer['gender'] ,  
					'ages' => $settings['ages_group']
				]); 

				$proportion = getProportion( array_sum($ages) , $total_population ) ; 

				$query_insert .= ",".fFloat($proportion).") ; <br/>" ; 

				// $line++ ;
				echo( $query_insert ); 
				// exit();
			}
		}
	}*/

	echo "<br> ============================================================ PREDICTIONS ============================================ <br>" ; 

	
	foreach( $sexes as $sex )
	{

		$query = 'SELECT * from globocan2018_predictions WHERE country in ("'.str_replace("_",",",$pops_str).'") ' ;

		$execute = $o_bdd->query( $query ) ; 
		$rows = $execute->fetchAll(PDO::FETCH_ASSOC) ;

		for( $year = 2020 ; $year <= 2040 ; $year++ )
		{
			$sum = 0 ;
			$POO = [] ;

			foreach( $rows as $row )
			{
				if ( $row["year"] == $year )
				{
					foreach( $ages_field as $age )
					{
						$age_ = str_replace("N","P",$age) ; 
						if ( !isset( $POO[$age_] )) $POO[$age_] = 0 ; 
						$POO[$age_] += $row[$age_] ; 
						$sum += $POO[$age_] ; 
					}
				}			
			}

			$query_insert = "INSERT INTO globocan2018_predictions(country,year,sex,total,P00_04,P05_09,P10_14,P15_19,P20_24,P25_29,P30_34,P35_39,P40_44,P45_49,P50_54,P55_59,P60_64,P65_69,P70_74,P75_79,P80_84,P85) VALUES ";

			$query_insert .= " (".int($row['country']).",".int($row['year']).",".int($row['sex']).",".int($sum)."," ;

			var_dump($POO); 

		}

		exit() ; 

	}	


	// exit();

	function fAbs( $val )
	{
		if ( $val == -1) return 0 ; 
		return $val ; 

	}

	function int( $val )
	{
		return (int)$val ; 
	}

	function fFloat( $val )
	{
		return str_replace( ',','.',$val) ; 
	}