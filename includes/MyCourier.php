<?php
class MyCourier {
    const ZONE_LOCAL = 'local';
    const ZONE_REGIONAL = 'regional';
    const ZONE_NATIONAL = 'national';
    
    private static $baseRates = ['local' => 50, 'regional' => 80, 'national' => 120];
    
    public static function calculateShipping($fromTownship, $fromProvince, $toTownship, $toProvince, $weight = 1, $dimensions = [20, 20, 20]) {
        $zone = self::determineZone($fromTownship, $fromProvince, $toTownship, $toProvince);
        $baseCost = self::$baseRates[$zone];
        
        if ($weight > 1) {
            $baseCost += ($weight - 1) * 10;
        }
        
        $volume = ($dimensions[0] * $dimensions[1] * $dimensions[2]) / 1000;
        if ($volume > 20) {
            $baseCost += ($volume - 20) * 2;
        }
        
        $estimateDays = match($zone) {
            'local' => rand(1, 2),
            'regional' => rand(2, 4),
            'national' => rand(3, 7),
            default => 3
        };
        
        return ['cost' => round($baseCost, 2), 'zone' => $zone, 'estimate_days' => $estimateDays, 'currency' => 'ZAR'];
    }
    
    private static function determineZone($fromTownship, $fromProvince, $toTownship, $toProvince) {
        if (strtolower($fromTownship) === strtolower($toTownship)) return self::ZONE_LOCAL;
        if (strtolower($fromProvince) === strtolower($toProvince)) return self::ZONE_REGIONAL;
        return self::ZONE_NATIONAL;
    }
    
    public static function generateTrackingNumber() {
        return 'MC' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
    }
}
?>
