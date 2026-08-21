<?php
// includes/ScoreCalculator.php

class ScoreCalculator {
    private static $cardPoints = [
        'A' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5,
        '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        '10' => 10, 'J' => 10, 'Q' => 10, 'K' => 10
    ];
    
    public static function calculateHandScore($hand) {
        $score = 0;
        foreach ($hand as $card) {
            if (!$card['is_joker']) {
                $score += self::$cardPoints[$card['value']] ?? 0;
            }
        }
        return $score;
    }
    
    public static function calculateMeldScore($melds) {
        $score = 0;
        foreach ($melds as $meld) {
            foreach ($meld as $card) {
                if (!$card['is_joker']) {
                    $score += self::$cardPoints[$card['value']] ?? 0;
                }
            }
        }
        return $score;
    }
    
    public static function calculateTotalScore($hand, $melds) {
        return self::calculateHandScore($hand) + self::calculateMeldScore($melds);
    }
    
    public static function getWinRate($wins, $games) {
        if ($games === 0) return 0;
        return round(($wins / $games) * 100, 1);
    }
    
    public static function calculatePot($numPlayers, $betAmount) {
        return $numPlayers * $betAmount;
    }
    
    public static function calculateCommission($pot) {
        return $pot * COMMISSION_RATE;
    }
    
    public static function calculateNetWin($pot, $commission, $bonus) {
        return ($pot - $commission) + $bonus;
    }
    
    public static function calculatePlayerScore($hand, $melds) {
        $handScore = self::calculateHandScore($hand);
        $meldScore = self::calculateMeldScore($melds);
        return $handScore + $meldScore;
    }
    
    public static function getCardPoints($value) {
        return self::$cardPoints[$value] ?? 0;
    }
}