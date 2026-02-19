<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $templates = [
            [
                'name' => 'Registration Submitted',
                'slug' => 'registration-submitted',
                'category' => 'registration',
                'subject' => 'Registration Received - {{event_name}}',
                'html_content' => $this->getRegistrationSubmittedTemplate(),
                'text_content' => $this->getRegistrationSubmittedTextTemplate(),
                'description' => 'Acknowledgment email sent immediately after registration submission',
                'is_active' => true,
                'event_id' => $eventId,
                'org_id' => $orgId,
            ],
            [
                'name' => 'Registration Confirmed',
                'slug' => 'registration-confirmed',
                'category' => 'registration',
                'subject' => 'Registration Confirmed - {{event_name}}',
                'html_content' => $this->getRegistrationConfirmedTemplate(),
                'text_content' => $this->getRegistrationConfirmedTextTemplate(),
                'description' => 'Confirmation email sent after registration is approved/verified',
                'is_active' => true,
                'event_id' => $eventId,
                'org_id' => $orgId,
            ],
            [
                'name' => 'Registration Cancelled',
                'slug' => 'registration-cancelled',
                'category' => 'registration',
                'subject' => 'Registration Cancelled - {{event_name}}',
                'html_content' => $this->getRegistrationCancelledTemplate(),
                'text_content' => $this->getRegistrationCancelledTextTemplate(),
                'description' => 'Email sent when a registration is cancelled',
                'is_active' => true,
                'event_id' => $eventId,
                'org_id' => $orgId,
            ],
            [
                'name' => 'Event Invitation',
                'slug' => 'event-invitation',
                'category' => 'invitation',
                'subject' => 'You\'re Invited to {{event_name}}!',
                'html_content' => $this->getInvitationTemplate(),
                'text_content' => $this->getInvitationTextTemplate(),
                'description' => 'Professional event invitation template with event details and registration CTA',
                'is_active' => true,
                'event_id' => $eventId,
                'org_id' => $orgId,
            ],
            [
                'name' => 'Registration Confirmation',
                'slug' => 'registration-confirmation',
                'category' => 'confirmation',
                'subject' => 'Registration Confirmed - {{event_name}}',
                'html_content' => $this->getConfirmationTemplate(),
                'text_content' => $this->getConfirmationTextTemplate(),
                'description' => 'Confirmation email sent after successful registration with event details',
                'is_active' => true,
                'event_id' => $eventId,
                'org_id' => $orgId,
            ],
            [
                'name' => 'Event Reminder',
                'slug' => 'event-reminder',
                'category' => 'reminder',
                'subject' => 'Reminder: {{event_name}} is Coming Up!',
                'html_content' => $this->getReminderTemplate(),
                'text_content' => $this->getReminderTextTemplate(),
                'description' => 'Reminder email to send a few days before the event',
                'is_active' => true,
                'event_id' => $eventId,
                'org_id' => $orgId,
            ],
            [
                'name' => 'Thank You',
                'slug' => 'thank-you',
                'category' => 'thank_you',
                'subject' => 'Thank You for Attending {{event_name}}',
                'html_content' => $this->getThankYouTemplate(),
                'text_content' => $this->getThankYouTextTemplate(),
                'description' => 'Post-event thank you email with feedback request',
                'is_active' => true,
                'event_id' => $eventId,
                'org_id' => $orgId,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                [
                    'slug' => $template['slug'],
                    'event_id' => $eventId,
                    'org_id' => $orgId,
                ],
                $template
            );
        }
    }

    private function getInvitationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .content h2 { color: #667eea; margin-top: 0; }
        .event-details { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; }
        .event-details p { margin: 8px 0; }
        .cta-button { display: inline-block; background: #667eea; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .cta-button:hover { background: #5568d3; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>You're Invited!</h1>
        </div>
        <div class="content">
            <p>Dear {{first_name}} {{last_name}},</p>
            
            <p>We are excited to invite you to <strong>{{event_name}}</strong>!</p>
            
            <div class="event-details">
                <p><strong>📅 Date:</strong> {{event_date}}</p>
                <p><strong>📍 Location:</strong> {{event_location}}</p>
            </div>
            
            <p>This is a unique opportunity to connect with industry leaders, learn about the latest trends, and network with professionals in your field.</p>
            
            <p style="text-align: center;">
                <a href="#" class="cta-button">Register Now</a>
            </p>
            
            <p>We look forward to seeing you there!</p>
            
            <p>Best regards,<br>The Event Team</p>
        </div>
        <div class="footer">
            <p>If you no longer wish to receive these emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getInvitationTextTemplate(): string
    {
        return <<<'TEXT'
You're Invited!

Dear {{first_name}} {{last_name}},

We are excited to invite you to {{event_name}}!

Event Details:
Date: {{event_date}}
Location: {{event_location}}

This is a unique opportunity to connect with industry leaders, learn about the latest trends, and network with professionals in your field.

Register now to secure your spot!

We look forward to seeing you there!

Best regards,
The Event Team

---
If you no longer wish to receive these emails, you can unsubscribe here: {{unsubscribe_url}}
TEXT;
    }

    private function getConfirmationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .checkmark { font-size: 48px; margin-bottom: 10px; }
        .content { padding: 40px 30px; }
        .registration-box { background: #f0fdf4; border: 2px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .registration-number { font-size: 24px; font-weight: bold; color: #10b981; margin: 10px 0; }
        .event-details { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .event-details p { margin: 8px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #10b981; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="checkmark">✓</div>
            <h1>Registration Confirmed!</h1>
        </div>
        <div class="content">
            <p>Dear {{first_name}} {{last_name}},</p>
            
            <p>Thank you for registering for <strong>{{event_name}}</strong>!</p>
            
            <div class="registration-box">
                <p>Your Registration Number:</p>
                <div class="registration-number">{{registration_number}}</div>
                <p style="font-size: 12px; color: #666;">Please keep this number for your records</p>
            </div>
            
            <div class="event-details">
                <h3 style="margin-top: 0; color: #10b981;">Event Details</h3>
                <p><strong>📅 Date:</strong> {{event_date}}</p>
                <p><strong>📍 Location:</strong> {{event_location}}</p>
            </div>
            
            <p>We're excited to have you join us! You'll receive a reminder email closer to the event date.</p>
            
            <p>If you have any questions, please don't hesitate to contact us.</p>
            
            <p>Best regards,<br>The Event Team</p>
        </div>
        <div class="footer">
            <p>If you no longer wish to receive these emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getConfirmationTextTemplate(): string
    {
        return <<<'TEXT'
Registration Confirmed!

Dear {{first_name}} {{last_name}},

Thank you for registering for {{event_name}}!

Your Registration Number: {{registration_number}}
Please keep this number for your records.

Event Details:
Date: {{event_date}}
Location: {{event_location}}

We're excited to have you join us! You'll receive a reminder email closer to the event date.

If you have any questions, please don't hesitate to contact us.

Best regards,
The Event Team

---
If you no longer wish to receive these emails, you can unsubscribe here: {{unsubscribe_url}}
TEXT;
    }

    private function getReminderTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .countdown-box { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .countdown-box h2 { color: #d97706; margin: 0; font-size: 32px; }
        .event-details { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .event-details p { margin: 8px 0; }
        .checklist { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .checklist li { margin: 10px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #f59e0b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Event Reminder</h1>
        </div>
        <div class="content">
            <p>Dear {{first_name}} {{last_name}},</p>
            
            <p>This is a friendly reminder that <strong>{{event_name}}</strong> is coming up soon!</p>
            
            <div class="countdown-box">
                <h2>Don't Miss It!</h2>
                <p>Mark your calendar</p>
            </div>
            
            <div class="event-details">
                <h3 style="margin-top: 0; color: #d97706;">Event Details</h3>
                <p><strong>📅 Date:</strong> {{event_date}}</p>
                <p><strong>📍 Location:</strong> {{event_location}}</p>
                <p><strong>🎫 Registration:</strong> {{registration_number}}</p>
            </div>
            
            <div class="checklist">
                <h3 style="margin-top: 0;">Before You Arrive:</h3>
                <ul>
                    <li>✓ Save your registration number</li>
                    <li>✓ Plan your travel and parking</li>
                    <li>✓ Review the event agenda</li>
                    <li>✓ Prepare any questions you may have</li>
                </ul>
            </div>
            
            <p>We're looking forward to seeing you there!</p>
            
            <p>Best regards,<br>The Event Team</p>
        </div>
        <div class="footer">
            <p>If you no longer wish to receive these emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getReminderTextTemplate(): string
    {
        return <<<'TEXT'
Event Reminder

Dear {{first_name}} {{last_name}},

This is a friendly reminder that {{event_name}} is coming up soon!

Event Details:
Date: {{event_date}}
Location: {{event_location}}
Registration: {{registration_number}}

Before You Arrive:
- Save your registration number
- Plan your travel and parking
- Review the event agenda
- Prepare any questions you may have

We're looking forward to seeing you there!

Best regards,
The Event Team

---
If you no longer wish to receive these emails, you can unsubscribe here: {{unsubscribe_url}}
TEXT;
    }

    private function getThankYouTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .highlight-box { background: #fdf2f8; border-left: 4px solid #ec4899; padding: 20px; margin: 20px 0; }
        .cta-button { display: inline-block; background: #ec4899; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .cta-button:hover { background: #db2777; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #ec4899; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thank You!</h1>
        </div>
        <div class="content">
            <p>Dear {{first_name}} {{last_name}},</p>
            
            <p>Thank you for attending <strong>{{event_name}}</strong>! We hope you found it valuable and enjoyable.</p>
            
            <div class="highlight-box">
                <p><strong>Your feedback matters!</strong></p>
                <p>Help us improve future events by sharing your thoughts and experiences.</p>
            </div>
            
            <p style="text-align: center;">
                <a href="#" class="cta-button">Share Your Feedback</a>
            </p>
            
            <p>We'd love to hear about:</p>
            <ul>
                <li>What you enjoyed most</li>
                <li>Topics you'd like to see in future events</li>
                <li>Any suggestions for improvement</li>
            </ul>
            
            <p>Stay connected with us for updates on upcoming events and opportunities!</p>
            
            <p>Best regards,<br>The Event Team</p>
        </div>
        <div class="footer">
            <p>If you no longer wish to receive these emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getThankYouTextTemplate(): string
    {
        return <<<'TEXT'
Thank You!

Dear {{first_name}} {{last_name}},

Thank you for attending {{event_name}}! We hope you found it valuable and enjoyable.

Your feedback matters!
Help us improve future events by sharing your thoughts and experiences.

We'd love to hear about:
- What you enjoyed most
- Topics you'd like to see in future events
- Any suggestions for improvement

Stay connected with us for updates on upcoming events and opportunities!

Best regards,
The Event Team

---
If you no longer wish to receive these emails, you can unsubscribe here: {{unsubscribe_url}}
TEXT;
    }

    private function getRegistrationSubmittedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .info-box { background: #eff6ff; border: 2px solid #3b82f6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .event-details { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .event-details p { margin: 8px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #3b82f6; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registration Received</h1>
        </div>
        <div class="content">
            <p>Dear {{first_name}} {{last_name}},</p>
            
            <p>Thank you for submitting your registration for <strong>{{event_name}}</strong>!</p>
            
            <div class="info-box">
                <p><strong>📋 What's Next?</strong></p>
                <p>We have received your registration and are currently processing it. You will receive a confirmation email once your registration has been approved.</p>
            </div>
            
            <div class="event-details">
                <h3 style="margin-top: 0; color: #3b82f6;">Event Details</h3>
                <p><strong>📅 Date:</strong> {{event_date}}</p>
                <p><strong>📍 Location:</strong> {{event_location}}</p>
                <p><strong>🎫 Reference:</strong> {{registration_number}}</p>
            </div>
            
            <p>If you have any questions in the meantime, please don't hesitate to contact us.</p>
            
            <p>Best regards,<br>The Event Team</p>
        </div>
        <div class="footer">
            <p>If you no longer wish to receive these emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getRegistrationSubmittedTextTemplate(): string
    {
        return <<<'TEXT'
Registration Received

Dear {{first_name}} {{last_name}},

Thank you for submitting your registration for {{event_name}}!

What's Next?
We have received your registration and are currently processing it. You will receive a confirmation email once your registration has been approved.

Event Details:
Date: {{event_date}}
Location: {{event_location}}
Reference: {{registration_number}}

If you have any questions in the meantime, please don't hesitate to contact us.

Best regards,
The Event Team

---
If you no longer wish to receive these emails, you can unsubscribe here: {{unsubscribe_url}}
TEXT;
    }

    private function getRegistrationConfirmedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .checkmark { font-size: 48px; margin-bottom: 10px; }
        .content { padding: 40px 30px; }
        .success-box { background: #d1fae5; border: 2px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .registration-number { font-size: 24px; font-weight: bold; color: #10b981; margin: 10px 0; }
        .event-details { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .event-details p { margin: 8px 0; }
        .cta-button { display: inline-block; background: #10b981; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .cta-button:hover { background: #059669; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #10b981; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="checkmark">✓</div>
            <h1>Registration Confirmed!</h1>
        </div>
        <div class="content">
            <p>Dear {{first_name}} {{last_name}},</p>
            
            <p>Great news! Your registration for <strong>{{event_name}}</strong> has been confirmed!</p>
            
            <div class="success-box">
                <p>Your Registration Number:</p>
                <div class="registration-number">{{registration_number}}</div>
                <p style="font-size: 12px; color: #666;">Please save this number - you'll need it at check-in</p>
            </div>
            
            <div class="event-details">
                <h3 style="margin-top: 0; color: #10b981;">Event Details</h3>
                <p><strong>📅 Date:</strong> {{event_date}}</p>
                <p><strong>📍 Location:</strong> {{event_location}}</p>
                <p><strong>✉️ Email:</strong> {{email}}</p>
            </div>
            
            <p style="text-align: center;">
                <a href="#" class="cta-button">View Event Details</a>
            </p>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Save your registration number</li>
                <li>Add the event to your calendar</li>
                <li>Review the event agenda</li>
                <li>Plan your travel arrangements</li>
            </ul>
            
            <p>We're excited to see you at the event!</p>
            
            <p>Best regards,<br>The Event Team</p>
        </div>
        <div class="footer">
            <p>If you no longer wish to receive these emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getRegistrationConfirmedTextTemplate(): string
    {
        return <<<'TEXT'
Registration Confirmed!

Dear {{first_name}} {{last_name}},

Great news! Your registration for {{event_name}} has been confirmed!

Your Registration Number: {{registration_number}}
Please save this number - you'll need it at check-in

Event Details:
Date: {{event_date}}
Location: {{event_location}}
Email: {{email}}

Next Steps:
- Save your registration number
- Add the event to your calendar
- Review the event agenda
- Plan your travel arrangements

We're excited to see you at the event!

Best regards,
The Event Team

---
If you no longer wish to receive these emails, you can unsubscribe here: {{unsubscribe_url}}
TEXT;
    }

    private function getRegistrationCancelledTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .warning-box { background: #fee2e2; border: 2px solid #ef4444; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .event-details { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .event-details p { margin: 8px 0; }
        .cta-button { display: inline-block; background: #3b82f6; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .cta-button:hover { background: #2563eb; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .footer a { color: #ef4444; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registration Cancelled</h1>
        </div>
        <div class="content">
            <p>Dear {{first_name}} {{last_name}},</p>
            
            <p>This email confirms that your registration for <strong>{{event_name}}</strong> has been cancelled.</p>
            
            <div class="warning-box">
                <p><strong>⚠️ Cancellation Details</strong></p>
                <p>Registration Number: <strong>{{registration_number}}</strong></p>
                <p>Status: <strong>Cancelled</strong></p>
            </div>
            
            <div class="event-details">
                <h3 style="margin-top: 0; color: #ef4444;">Event Information</h3>
                <p><strong>Event:</strong> {{event_name}}</p>
                <p><strong>Date:</strong> {{event_date}}</p>
                <p><strong>Location:</strong> {{event_location}}</p>
            </div>
            
            <p>If you cancelled by mistake or would like to register again, you can do so using the button below:</p>
            
            <p style="text-align: center;">
                <a href="#" class="cta-button">Register Again</a>
            </p>
            
            <p>If you have any questions about this cancellation, please contact us.</p>
            
            <p>We hope to see you at future events!</p>
            
            <p>Best regards,<br>The Event Team</p>
        </div>
        <div class="footer">
            <p>If you no longer wish to receive these emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getRegistrationCancelledTextTemplate(): string
    {
        return <<<'TEXT'
Registration Cancelled

Dear {{first_name}} {{last_name}},

This email confirms that your registration for {{event_name}} has been cancelled.

Cancellation Details:
Registration Number: {{registration_number}}
Status: Cancelled

Event Information:
Event: {{event_name}}
Date: {{event_date}}
Location: {{event_location}}

If you cancelled by mistake or would like to register again, please visit our registration page.

If you have any questions about this cancellation, please contact us.

We hope to see you at future events!

Best regards,
The Event Team

---
If you no longer wish to receive these emails, you can unsubscribe here: {{unsubscribe_url}}
TEXT;
    }

}
