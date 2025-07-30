<?php

if (!function_exists('parseNutritionText')) {
    function parseNutritionText($text)
    {
        $patterns = [
            'kalori' => '/kalori[:\s]+(\d+)/i',
            'lemak_total' => '/lemak total[:\s]+([\d.]+)/i',
            'lemak_jenuh' => '/lemak jenuh[:\s]+([\d.]+)/i',
            'protein' => '/protein[:\s]+([\d.]+)/i',
            'gula' => '/gula[:\s]+([\d.]+)/i',
            'karbohidrat' => '/karbohidrat[:\s]+([\d.]+)/i',
            'garam' => '/garam[:\s]+([\d.]+)/i',
        ];

        $result = [];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, strtolower($text), $matches)) {
                $result[$key] = $matches[1];
            } else {
                $result[$key] = null;
            }
        }

        return $result;
    }
}
