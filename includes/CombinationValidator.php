<?php
// includes/CombinationValidator.php

class CombinationValidator {
    private static $valuesOrder = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
    
    public static function validate($cards) {
        if (count($cards) < 3) return false;
        
        $normalCards = array_filter($cards, function($c) {
            return !$c['is_joker'];
        });
        $jokerCount = count($cards) - count($normalCards);
        
        if (empty($normalCards)) return false;
        
        $normalCards = array_values($normalCards);
        $isSameSuit = count(array_unique(array_column($normalCards, 'suit'))) === 1;
        
        if ($isSameSuit) {
            return self::validateSequence($normalCards, $jokerCount);
        }
        
        $isSameValue = count(array_unique(array_column($normalCards, 'value'))) === 1;
        if ($isSameValue) {
            return self::validateGroup($normalCards, $jokerCount);
        }
        
        return false;
    }
    
    private static function validateSequence($cards, $jokerCount) {
        $values = array_column($cards, 'value');
        $indices = array_map(function($v) {
            return array_search($v, self::$valuesOrder);
        }, $values);
        sort($indices);
        
        $needed = 0;
        for ($i = 0; $i < count($indices) - 1; $i++) {
            $gap = $indices[$i + 1] - $indices[$i];
            if ($gap > 1) $needed += $gap - 1;
        }
        
        return $needed <= $jokerCount;
    }
    
    private static function validateGroup($cards, $jokerCount) {
        $count = count($cards);
        return $count + $jokerCount >= 3 && $count + $jokerCount <= 4;
    }
    
    public static function validateAceSequence($cards) {
        $values = array_column($cards, 'value');
        $indices = array_map(function($v) {
            return array_search($v, self::$valuesOrder);
        }, $values);
        sort($indices);
        
        if ($indices === [0, 1, 2]) return true;
        if ($indices === [11, 12, 0]) return true;
        
        return false;
    }
}