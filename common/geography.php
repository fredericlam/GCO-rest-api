<?php

	/**
	* Populations dictionnary 
	* for now, only areas are in the database, but for optimization, need to be included here
	*/

	$geography = [
		'hdi' => [
			'field' => 'hdi_group_2015' , 
			'groups' => [
				[ 'id' => 1 , 'label' => 'Very high HDI' , 'country' => 981 , 'color' => '#0b9aba' ] , 
				[ 'id' => 2 , 'label' => 'High HDI' , 'country' => 982 , 'color' => '#5c2d4f' ] , 
				[ 'id' => 3 , 'label' => 'Medium HDI' , 'country' => 983 , 'color' => '#d40f50' ] ,
				[ 'id' => 4 , 'label' => 'Low HDI', 'country' => 984  , 'color' => '#fadb14' ] , 
				[ 'id' => 5 , 'label' => 'China', 'country' => 160 , 'color' => '#5c2d4f' ] , 
				[ 'id' => 6 , 'label' => 'India', 'country' => 356 , 'color' => '#d40f50' ] , 
			]	
		] , 
		'continents' => [
			'field' => 'continent' , 
			'groups' => [
				[ 'id' => 1 , 'label' => 'Africa' , 'country' => 903 , 'color' => '#004529'] ,  // old 
				[ 'id' => 2 , 'label' => 'The Americas' , 'country' => 904 , 'color' => '#FF0000'] , 
				[ 'id' => 3 , 'label' => 'Latin America and the Caribbean' , 'country' => 905 , 'color' => '#E97520'] , 
				[ 'id' => 4 , 'label' => 'Asia' , 'country' => 935 , 'color' => '#7A0177'] ,
				[ 'id' => 5 , 'label' => 'Europe' , 'country' => 908 , 'color' => '#08306B'] ,
				[ 'id' => 6 , 'label' => 'Oceania' , 'country' => 909 , 'color' => '#FFD92F']
			]
		] , 
		'areas' => [
			'field' => 'area' , 
			'groups' => CanGlobocan::getAreas()
		],
		'who' => [
			'field' => 'who_region' , 
			'groups' => [
				[ 'id' => 'AFRO' , 'label' => 'WHO Africa region' , 'country' => 991 , 'color' => '#d9343f' ] , 
				[ 'id' => 'PAHO' , 'label' => 'WHO The Americas region' , 'country' => 992 , 'color' => '#e6b31c' ] , 
				[ 'id' => 'EMRO' , 'label' => 'WHO Eastern Mediterranean region', 'country' => 993 , 'color' => '#0d890f' ] ,
				[ 'id' => 'EURO' , 'label' => 'WHO Europe region' , 'country' => 994 , 'color' => '#14a9e7'] ,
				[ 'id' => 'SEARO' , 'label' => 'WHO South-East Asia region' , 'country' => 995 , 'color' => '#21d998'] ,
				[ 'id' => 'WPRO' , 'label' => 'WHO Western Pacific region' , 'country' => 996 , 'color' => '#4500b5' ] 
			]
		] ,
		'income' => [
			'field' => 'income' , 
			'groups' => [
				[ 'id' => 'HIGH' , 'label' => 'Low Income' , 'country' => 988 , 'color' => '#48c0c0' ] , 
				[ 'id' => 'UPPER_MIDDLE' , 'label' => 'Upper Middle Income' , 'country' => 987 , 'color' => '#f0d818' ] , 
				[ 'id' => 'LOWER_MIDDLE' , 'label' => 'Low Middle Income', 'country' => 989 , 'color' => '#ff9000' ] ,
				[ 'id' => 'LOW' , 'label' => 'High Income' , 'country' => 986 , 'color' => '#f03048'] 
			]
		] , 
		'hubs' => [
			'field' => 'hub' , 
			'groups' =>  [
				[ 'id' => 'AFCRN' , 'label' => 'Sub-Saharan Africa', 'color' => '#71C8E3' , 'country' => 971 ] , 
				[ 'id' => 'CARIB' , 'label' => 'Caribbean', 'color' => '#b21c01' , 'country' => 972 ] , 
				[ 'id' => 'IZMIR' , 'label' => 'North. Africa, Cent. & West. Asia' , 'short_label' => 'North. Africa, Cent. & West. Asia', 'color' => '#724a98' , 'country' => 975 ] ,
				[ 'id' => 'LA' , 'label' => 'Latin America', 'color' => '#e0c61f' , 'country' => 973 ] ,
				[ 'id' => 'MUMB' , 'label' => 'South East and South-Eastern Asia', 'short_label' => 'South, East. & S.-E. Asia', 'color' => '#2eaf81' , 'country' => 974 ] ,
				[ 'id' => 'PI' , 'label' => 'Pacific Islands', 'color' => '#ff6600' , 'country' => 976 ] 
			]
		] , 
	] ; 

