<?php
// includes/CardManager.php

class CardManager {
    private static $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
    private static $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
    private static $points = [
        'A' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5,
        '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        '10' => 10, 'J' => 10, 'Q' => 10, 'K' => 10
    ];
    
    public static function createDeck() {
        $deck = [];
        $id = 0;
        
        for ($copy = 0; $copy < 2; $copy++) {
            foreach (self::$suits as $suit) {
                foreach (self::$values as $value) {
                    $deck[] = [
                        'id' => $id++,
                        'value' => $value,
                        'suit' => $suit,
                        'points' => self::$points[$value],
                        'is_joker' => false,
                        'image' => "cards/{$suit}/{$value}.png"
                    ];
                }
            }
        }
        
        for ($j = 0; $j < 4; $j++) {
            $deck[] = [
                'id' => $id++,
                'value' => 'JOKER',
                'suit' => 'joker',
                'points' => 0,
                'is_joker' => true,
                'image' => 'cards/joker.png'
            ];
        }
        
        return $deck;
    }
    
    public static function shuffleDeck($deck) {
        shuffle($deck);
        return $deck;
    }
    
    public static function dealCards(&$deck, $numPlayers) {
        $hands = [];
        for ($i = 0; $i < $numPlayers; $i++) {
            $hands[$i] = array_splice($deck, 0, CARDS_PER_PLAYER);
        }
        return $hands;
    }
    
    public static function checkJokerWin($hand) {
        $jokerCount = 0;
        foreach ($hand as $card) {
            if ($card['is_joker']) $jokerCount++;
        }
        if ($jokerCount >= 4) return 'quadri_joker';
        if ($jokerCount >= 3) return 'tri_joker';
        return null;
    }
    
    public static function getSuitSymbol($suit) {
        $symbols = [
            'hearts' => '♥',
            'diamonds' => '♦',
            'clubs' => '♣',
            'spades' => '♠'
        ];
        return $symbols[$suit] ?? '⭐';
    }
    
    public static function getSuitColor($suit) {
        $colors = [
            'hearts' => 'red',
            'diamonds' => 'red',
            'clubs' => 'black',
            'spades' => 'black'
        ];
        return $colors[$suit] ?? 'purple';
    }
}