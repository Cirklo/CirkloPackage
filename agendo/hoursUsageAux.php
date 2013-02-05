<?php
	require_once("commonCode.php");
	
	class HappyHour{
	
		private $discount;
		private $startHour;
		private $endHour;
		private $startDay;
		private $endDay;

		function __construct($id){
			$sql = "select happyhour_discount, happyhour_starthour, happyhour_endhour, happyhour_startday, happyhour_endday from happyhour where happyhour_id = :0";
			$prep = dbHelp::query($sql, array($id));
			$res = dbHelp::fetchRowByName($prep);

			$this->discount = $res['happyhour_discount'];
			$this->startHour = $res['happyhour_starthour'];
			$this->endHour = $res['happyhour_endhour'];
			$this->startDay = $res['happyhour_startday'];
			$this->endDay = $res['happyhour_endday'];
		}
		
		function getCostAndDiscountTime($date, $slots, $resourceResolution, $price){
			$weekday = date('N', strtotime($date));
			if($weekday < $this->startDay || $weekday > $this->endDay){
				return false;
			}
			
			// in minutes
			$entryLength = $slots * $resourceResolution;
			$entryStart = date('i', strtotime($date)) + (date('G', strtotime($date)) * 60);
			$entryEnd = $entryStart + $entryLength;
			$discountStart = $this->startHour * 60;
			$discountEnd = $this->endHour * 60;
			$diff1 = $discountEnd - $entryStart;
			$diff2 = $entryEnd - $discountStart;
			if(
				(($diff1 = $discountEnd - $entryStart) > 0 
				|| ($diff2 = $entryEnd - $discountStart) > 0)
				&& ($time = min($diff1, $diff2, $entryLength)) > 0
			){
				$time = min($diff1, $diff2, $entryLength);
				return array('cost' => ($time / 60 * $price * (100 - $this->discount) * 0.01), 'time' => $time);
			}
			
			return false;
		}
	}

	class Resource{
		private $name;
		private $resolution;
		private $hhIndexList;

		function __construct($id, &$happyHourArray){
			$sql = "select resource_name, resource_resolution, resource_starttime, resource_stoptime from resource where resource_id = :0";
			$prep = dbHelp::query($sql, array($id));
			$res = dbHelp::fetchRowByName($prep);
			$this->name = $res['resource_name'];
			$this->resolution = $res['resource_resolution'];
			
			$this->hhIndexList = array();
			$sql = "select happyhour_assoc_happyhour from happyhour_assoc where happyhour_assoc_resource = :0";
			$prep = dbHelp::query($sql, array($id));
			while($resHH = dbHelp::fetchRowByName($prep)){
				$this->hhIndexList[] = $resHH['happyhour_assoc_happyhour'];
				if(!isset($happyHourArray[$resHH['happyhour_assoc_happyhour']])){
					$happyHourArray[$resHH['happyhour_assoc_happyhour']] = new HappyHour($resHH['happyhour_assoc_happyhour']);
				}
			}
		}
		
		function getCostFromEntry($date, $slots, $price, &$happyHourArray){
			$discountTime = 0;
			$discountCost = 0;
			$noDiscountTime = $slots * $this->resolution;
			
			foreach($this->hhIndexList as $hh){
				$tempCost = $happyHourArray[$hh]->getCostAndDiscountTime($date, $slots, $this->resolution, $price);
				if($tempCost !== false){
					$discountCost += $tempCost['cost'];
					$discountTime += $tempCost['time'];
					$noDiscountTime -= $tempCost['time'];
				}
			}
			
			return array('noDiscountCost' => ($price * $noDiscountTime / 60), 'noDiscountTime' => $noDiscountTime, 'discountTime' => $discountTime, 'discountCost' => $discountCost);
		}
		
		function getName(){
			return $this->name;
		}
	}
?>