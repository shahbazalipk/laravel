<?php

namespace App\Services;

use App\Models\EmailUnsubscribe;
use App\Models\Registration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class RecipientService
{
    /**
     * Resolve recipients from registrations with filters
     * 
     * @param array $filters
     * @return Collection
     */
    public function resolveFromRegistrations(array $filters): Collection
    {
        $query = Registration::query();
        
        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('registration_category_id', $filters['category_id']);
        }
        
        if (isset($filters['status_id'])) {
            $query->where('registration_status_id', $filters['status_id']);
        }
        
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        if (isset($filters['checked_in'])) {
            $query->where('checked_in', $filters['checked_in']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        $registrations = $query->get();
        
        return $registrations->map(function ($registration) {
            return [
                'email' => $registration->email,
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'company' => $registration->company ?? '',
                'registration_number' => $registration->registration_number ?? '',
            ];
        });
    }
    
    /**
     * Resolve recipients from CSV file
     * 
     * @param UploadedFile $file
     * @param array $mapping Column name to merge code mapping
     * @return Collection
     */
    public function resolveFromCsv(UploadedFile $file, array $mapping): Collection
    {
        $recipients = collect();
        
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                
                $recipient = [];
                foreach ($mapping as $csvColumn => $mergeCode) {
                    $recipient[$mergeCode] = $data[$csvColumn] ?? '';
                }
                
                // Ensure email is present
                if (!empty($recipient['email'])) {
                    $recipients->push($recipient);
                }
            }
            
            fclose($handle);
        }
        
        return $recipients;
    }
    
    /**
     * Resolve recipients from saved segment
     * 
     * @param mixed $segment
     * @return Collection
     */
    public function resolveFromSegment($segment): Collection
    {
        // This would load a saved segment configuration
        // For now, return empty collection
        return collect();
    }
    
    /**
     * Validate recipients
     * 
     * @param Collection $recipients
     * @return array ['valid' => Collection, 'invalid' => Collection]
     */
    public function validateRecipients(Collection $recipients): array
    {
        $valid = collect();
        $invalid = collect();
        
        foreach ($recipients as $recipient) {
            $validator = Validator::make($recipient, [
                'email' => 'required|email:rfc,dns',
            ]);
            
            if ($validator->fails()) {
                $invalid->push($recipient);
            } else {
                $valid->push($recipient);
            }
        }
        
        return [
            'valid' => $valid,
            'invalid' => $invalid,
        ];
    }
    
    /**
     * Deduplicate emails in recipient list
     * 
     * @param Collection $recipients
     * @return Collection
     */
    public function deduplicateEmails(Collection $recipients): Collection
    {
        return $recipients->unique('email')->values();
    }
    
    /**
     * Exclude unsubscribed emails
     * 
     * @param Collection $recipients
     * @return Collection
     */
    public function excludeUnsubscribed(Collection $recipients): Collection
    {
        $unsubscribedEmails = EmailUnsubscribe::pluck('email')->toArray();
        
        return $recipients->filter(function ($recipient) use ($unsubscribedEmails) {
            return !in_array($recipient['email'], $unsubscribedEmails);
        })->values();
    }
    
    /**
     * Get recipient count with filters
     * 
     * @param array $filters
     * @return int
     */
    public function getRecipientCount(array $filters): int
    {
        return $this->resolveFromRegistrations($filters)->count();
    }
}
