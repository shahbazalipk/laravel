<?php

namespace App\Services;

use App\Models\Event;

class MergeCodeService
{
    /**
     * Parse template content and extract all merge codes
     * 
     * @param string $content
     * @return array Array of merge code names found
     */
    public function parse(string $content): array
    {
        preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $content, $matches);
        
        return array_unique($matches[1]);
    }
    
    /**
     * Replace merge codes in content with actual data
     * 
     * @param string $content
     * @param array $data
     * @return string
     */
    public function replace(string $content, array $data): string
    {
        $codes = $this->parse($content);
        
        foreach ($codes as $code) {
            $value = $data[$code] ?? $this->getDefaultValue($code);
            $content = str_replace('{{' . $code . '}}', $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Validate merge codes against available codes
     * 
     * @param array $codes
     * @return array ['valid' => [...], 'invalid' => [...]]
     */
    public function validate(array $codes): array
    {
        $availableCodes = $this->getAvailableCodeNames();
        
        $valid = [];
        $invalid = [];
        
        foreach ($codes as $code) {
            if (in_array($code, $availableCodes)) {
                $valid[] = $code;
            } else {
                $invalid[] = $code;
            }
        }
        
        return [
            'valid' => $valid,
            'invalid' => $invalid,
        ];
    }
    
    /**
     * Get standard merge codes with descriptions
     * 
     * @return array
     */
    public function getStandardCodes(): array
    {
        return [
            'recipient' => [
                'first_name' => 'Recipient\'s first name',
                'last_name' => 'Recipient\'s last name',
                'email' => 'Recipient\'s email address',
                'company' => 'Recipient\'s company name',
            ],
            'registration' => [
                'registration_number' => 'Unique registration number',
            ],
            'event' => [
                'event_name' => 'Name of the event',
                'event_date' => 'Event date',
                'event_location' => 'Event location',
            ],
            'system' => [
                'unsubscribe_url' => 'Unsubscribe link',
            ],
        ];
    }
    
    /**
     * Get flat list of all available merge code names
     * 
     * @return array
     */
    public function getAvailableCodeNames(): array
    {
        $codes = $this->getStandardCodes();
        $names = [];
        
        foreach ($codes as $category => $categoryCodes) {
            $names = array_merge($names, array_keys($categoryCodes));
        }
        
        return $names;
    }
    
    /**
     * Get custom merge codes from event
     * 
     * @param Event $event
     * @return array
     */
    public function getCustomCodes(Event $event): array
    {
        // This would dynamically generate codes from custom registration fields
        // For now, return empty array
        return [];
    }
    
    /**
     * Get default value for a merge code
     * 
     * @param string $code
     * @return string
     */
    public function getDefaultValue(string $code): string
    {
        $defaults = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'company' => '',
            'registration_number' => '',
            'event_name' => '',
            'event_date' => '',
            'event_location' => '',
            'unsubscribe_url' => '',
        ];
        
        return $defaults[$code] ?? '';
    }
}
