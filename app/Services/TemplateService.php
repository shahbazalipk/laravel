<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Str;

class TemplateService
{
    protected MergeCodeService $mergeCodeService;
    
    public function __construct(MergeCodeService $mergeCodeService)
    {
        $this->mergeCodeService = $mergeCodeService;
    }
    
    /**
     * Create a new email template
     * 
     * @param array $data
     * @return EmailTemplate
     */
    public function create(array $data): EmailTemplate
    {
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Validate merge codes in content
        $codes = $this->mergeCodeService->parse($data['html_content']);
        $validation = $this->mergeCodeService->validate($codes);
        
        if (!empty($validation['invalid'])) {
            throw new \InvalidArgumentException(
                'Invalid merge codes found: ' . implode(', ', $validation['invalid'])
            );
        }
        
        return EmailTemplate::create($data);
    }
    
    /**
     * Update an existing email template
     * 
     * @param EmailTemplate $template
     * @param array $data
     * @return EmailTemplate
     */
    public function update(EmailTemplate $template, array $data): EmailTemplate
    {
        // Validate merge codes if html_content is being updated
        if (isset($data['html_content'])) {
            $codes = $this->mergeCodeService->parse($data['html_content']);
            $validation = $this->mergeCodeService->validate($codes);
            
            if (!empty($validation['invalid'])) {
                throw new \InvalidArgumentException(
                    'Invalid merge codes found: ' . implode(', ', $validation['invalid'])
                );
            }
        }
        
        $template->update($data);
        
        return $template->fresh();
    }
    
    /**
     * Delete an email template (soft delete)
     * 
     * @param EmailTemplate $template
     * @return bool
     */
    public function delete(EmailTemplate $template): bool
    {
        return $template->delete();
    }
    
    /**
     * Clone an existing template with a new name
     * 
     * @param EmailTemplate $template
     * @param string $newName
     * @return EmailTemplate
     */
    public function clone(EmailTemplate $template, string $newName): EmailTemplate
    {
        $data = $template->toArray();
        
        // Remove fields that shouldn't be cloned
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        
        // Set new name and slug
        $data['name'] = $newName;
        $data['slug'] = Str::slug($newName);
        $data['usage_count'] = 0;
        
        return EmailTemplate::create($data);
    }
    
    /**
     * Preview template with sample data
     * 
     * @param EmailTemplate $template
     * @param array $sampleData
     * @return string
     */
    public function preview(EmailTemplate $template, array $sampleData): string
    {
        return $this->mergeCodeService->replace($template->html_content, $sampleData);
    }
    
    /**
     * Validate merge codes in template content
     * 
     * @param string $content
     * @return array
     */
    public function validateMergeCodes(string $content): array
    {
        $codes = $this->mergeCodeService->parse($content);
        return $this->mergeCodeService->validate($codes);
    }
    
    /**
     * Get available merge codes
     * 
     * @return array
     */
    public function getAvailableMergeCodes(): array
    {
        return $this->mergeCodeService->getStandardCodes();
    }
    
    /**
     * Render template with actual data
     * 
     * @param EmailTemplate $template
     * @param array $data
     * @return array ['html' => string, 'text' => string]
     */
    public function renderTemplate(EmailTemplate $template, array $data): array
    {
        $html = $this->mergeCodeService->replace($template->html_content, $data);
        $text = $template->text_content 
            ? $this->mergeCodeService->replace($template->text_content, $data)
            : strip_tags($html);
        
        return [
            'html' => $html,
            'text' => $text,
        ];
    }
}
