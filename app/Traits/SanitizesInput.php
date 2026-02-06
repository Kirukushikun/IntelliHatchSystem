<?php

namespace App\Traits;

trait SanitizesInput
{
    /**
     * Sanitize input by capitalizing first character and removing whitespace
     * 
     * @param string $input
     * @return string
     */
    protected function sanitizeInput(string $input): string
    {
        // Remove all whitespace
        $sanitized = preg_replace('/\s+/', '', $input);
        
        // Capitalize first character
        if (!empty($sanitized)) {
            $sanitized = ucfirst(strtolower($sanitized));
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize name input (capitalize first letter of each word, remove extra whitespace)
     * 
     * @param string $input
     * @return string
     */
    protected function sanitizeName(string $input): string
    {
        // Remove extra whitespace and trim
        $sanitized = preg_replace('/\s+/', ' ', trim($input));
        
        // Capitalize first letter of each word
        if (!empty($sanitized)) {
            $sanitized = ucwords(strtolower($sanitized));
        }
        
        return $sanitized;
    }
}
