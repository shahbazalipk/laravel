<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingPageTemplate;

class LandingPageTemplateSeeder extends Seeder
{
    public function run()
    {
        $eventId = config('event.event_id', 1);
        $orgId = config('event.org_id', 1);

        // Check if templates already exist for this event
        $existingCount = LandingPageTemplate::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->count();

        if ($existingCount > 0) {
            $this->command->info("Templates already exist for this event. Skipping seeding.");
            return;
        }

        // Template 1: Modern Gradient
        LandingPageTemplate::create([
            'event_id' => $eventId,
            'org_id' => $orgId,
            'name' => 'Modern Gradient',
            'description' => 'A modern design with gradient backgrounds and clean typography',
            'html_content' => $this->getModernGradientHtml(),
            'css_content' => $this->getModernGradientCss(),
            'js_content' => '',
            'is_active' => true,
            'customizable_sections' => json_encode(['hero', 'about', 'speakers', 'schedule', 'sponsors']),
            'default_settings' => json_encode([
                'primary_color' => '#6366f1',
                'secondary_color' => '#8b5cf6',
                'font_family' => 'Inter, sans-serif'
            ])
        ]);

        // Template 2: Classic Professional
        LandingPageTemplate::create([
            'event_id' => $eventId,
            'org_id' => $orgId,
            'name' => 'Classic Professional',
            'description' => 'A professional and elegant design suitable for corporate events',
            'html_content' => $this->getClassicProfessionalHtml(),
            'css_content' => $this->getClassicProfessionalCss(),
            'js_content' => '',
            'is_active' => true,
            'customizable_sections' => json_encode(['header', 'hero', 'features', 'speakers', 'footer']),
            'default_settings' => json_encode([
                'primary_color' => '#1e40af',
                'secondary_color' => '#3b82f6',
                'font_family' => 'Georgia, serif'
            ])
        ]);

        // Template 3: Vibrant Creative
        LandingPageTemplate::create([
            'event_id' => $eventId,
            'org_id' => $orgId,
            'name' => 'Vibrant Creative',
            'description' => 'A bold and colorful design perfect for creative and tech events',
            'html_content' => $this->getVibrantCreativeHtml(),
            'css_content' => $this->getVibrantCreativeCss(),
            'js_content' => '',
            'is_active' => true,
            'customizable_sections' => json_encode(['hero', 'highlights', 'agenda', 'speakers', 'register']),
            'default_settings' => json_encode([
                'primary_color' => '#ec4899',
                'secondary_color' => '#f59e0b',
                'font_family' => 'Poppins, sans-serif'
            ])
        ]);

        $this->command->info("Successfully seeded 3 landing page templates.");
    }

    private function getModernGradientHtml()
    {
        return <<<'HTML'
<div class="landing-page">
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                @if($event->logo)
                    <img src="{{ storage_public_url($event->logo) }}" alt="{{ $event->event_name }}" class="event-logo">
                @endif
                <h1 class="hero-title">{{ $event->event_name ?? 'Event Name' }}</h1>
                <p class="hero-subtitle">{{ $event->start_date ? $event->start_date->format('F d, Y') : 'Event Date' }}</p>
                <p class="hero-location">{{ $event->location ?? 'Event Location' }}</p>
                <div class="hero-actions">
                    <a href="{{ route('registration.form') }}" class="btn btn-primary">Register Now</a>
                    <a href="{{ route('attendee.login') }}" class="btn btn-secondary">Attendee Login</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <h2 class="section-title">About the Event</h2>
            <div class="about-content">
                <p>{{ $event->description ?? 'Join us for an amazing event experience.' }}</p>
            </div>
        </div>
    </section>

    <!-- Registration Options -->
    @if($registrationCategories->count() > 0)
    <section class="registration">
        <div class="container">
            <h2 class="section-title">Registration Options</h2>
            <div class="registration-grid">
                @foreach($registrationCategories as $category)
                <div class="registration-card">
                    <h3>{{ $category->name }}</h3>
                    <p class="price">{{ $event->currency ?? '$' }}{{ number_format($category->price, 2) }}</p>
                    @if($category->description)
                        <p class="description">{{ $category->description }}</p>
                    @endif
                    <a href="{{ route('registration.form') }}?category={{ $category->id }}" class="btn btn-register">
                        Register Now
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Speakers Section -->
    @if($speakers->count() > 0)
    <section class="speakers">
        <div class="container">
            <h2 class="section-title">Featured Speakers</h2>
            <div class="speakers-grid">
                @foreach($speakers->take(6) as $speaker)
                <div class="speaker-card">
                    @if($speaker->profile_image)
                        <img src="{{ storage_public_url($speaker->profile_image) }}" alt="{{ $speaker->full_name }}" class="speaker-image">
                    @endif
                    <h3 class="speaker-name">{{ $speaker->full_name }}</h3>
                    <p class="speaker-title">{{ $speaker->job_title }}</p>
                    @if($speaker->company)
                        <p class="speaker-company">{{ $speaker->company }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Sessions Section -->
    @if($sessions->count() > 0)
    <section class="sessions">
        <div class="container">
            <h2 class="section-title">Event Sessions</h2>
            <div class="sessions-list">
                @foreach($sessions->take(8) as $session)
                <div class="session-item">
                    <h3>{{ $session->title }}</h3>
                    <p class="session-time">
                        {{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}
                    </p>
                    @if($session->speaker)
                        <p class="session-speaker">Speaker: {{ $session->speaker->full_name }}</p>
                    @endif
                    @if($session->track)
                        <span class="session-track">{{ $session->track->name }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Exhibitors Section -->
    @if($exhibitors->count() > 0)
    <section class="exhibitors">
        <div class="container">
            <h2 class="section-title">Our Exhibitors</h2>
            <div class="exhibitors-grid">
                @foreach($exhibitors->take(12) as $exhibitor)
                <div class="exhibitor-card">
                    @if($exhibitor->logo)
                        <img src="{{ storage_public_url($exhibitor->logo) }}" alt="{{ $exhibitor->name }}">
                    @endif
                    <h3>{{ $exhibitor->name }}</h3>
                    @if($exhibitor->booth_number)
                        <p class="booth">Booth: {{ $exhibitor->booth_number }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Sponsors Section -->
    @if($sponsors->count() > 0)
    <section class="sponsors">
        <div class="container">
            <h2 class="section-title">Our Sponsors</h2>
            <div class="sponsors-grid">
                @foreach($sponsors as $sponsor)
                <div class="sponsor-logo">
                    @if($sponsor->logo)
                        @if($sponsor->website)
                            <a href="{{ $sponsor->website }}" target="_blank">
                                <img src="{{ storage_public_url($sponsor->logo) }}" alt="{{ $sponsor->name }}">
                            </a>
                        @else
                            <img src="{{ storage_public_url($sponsor->logo) }}" alt="{{ $sponsor->name }}">
                        @endif
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Partners Section -->
    @if($partners->count() > 0)
    <section class="partners">
        <div class="container">
            <h2 class="section-title">Our Partners</h2>
            <div class="partners-grid">
                @foreach($partners as $partner)
                <div class="partner-logo">
                    @if($partner->logo)
                        <img src="{{ storage_public_url($partner->logo) }}" alt="{{ $partner->name }}">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ $event->event_name ?? 'Event' }}. All rights reserved.</p>
            @if($event->manager_email)
                <p>Contact: <a href="mailto:{{ $event->manager_email }}">{{ $event->manager_email }}</a></p>
            @endif
        </div>
    </footer>
</div>
HTML;
    }

    private function getModernGradientCss()
    {
        return <<<'CSS'
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    line-height: 1.6;
    color: #1f2937;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.hero {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 100px 0;
    text-align: center;
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
}

.event-logo {
    max-width: 200px;
    height: auto;
    margin-bottom: 30px;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 20px;
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 10px;
    opacity: 0.9;
}

.hero-location {
    font-size: 1.2rem;
    margin-bottom: 40px;
    opacity: 0.8;
}

.hero-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 15px 40px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s;
}

.btn-primary {
    background: white;
    color: #6366f1;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-secondary:hover {
    background: white;
    color: #6366f1;
}

.about, .speakers, .sponsors {
    padding: 80px 0;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 50px;
    color: #1f2937;
}

.about-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
    font-size: 1.2rem;
    line-height: 1.8;
}

.speakers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.speaker-card {
    text-align: center;
    padding: 20px;
    border-radius: 15px;
    transition: transform 0.3s;
}

.speaker-card:hover {
    transform: translateY(-5px);
}

.speaker-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
}

.speaker-name {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.speaker-title {
    color: #6b7280;
    font-size: 1rem;
}

.sponsors {
    background: #f9fafb;
}

.sponsors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    align-items: center;
}

.sponsor-logo {
    text-align: center;
}

.sponsor-logo img {
    max-width: 180px;
    height: auto;
    filter: grayscale(100%);
    opacity: 0.7;
    transition: all 0.3s;
}

.sponsor-logo img:hover {
    filter: grayscale(0%);
    opacity: 1;
}

.footer {
    background: #1f2937;
    color: white;
    padding: 40px 0;
    text-align: center;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
}
CSS;
    }

    private function getClassicProfessionalHtml()
    {
        return <<<'HTML'
<div class="classic-landing">
    <header class="header">
        <div class="container">
            <div class="header-content">
                @if($event->logo)
                    <img src="{{ storage_public_url($event->logo) }}" alt="{{ $event->event_name }}" class="logo">
                @endif
                <nav class="nav">
                    <a href="#about">About</a>
                    <a href="#speakers">Speakers</a>
                    <a href="{{ route('registration.form') }}" class="nav-cta">Register</a>
                </nav>
            </div>
        </div>
    </header>

    <section class="hero-classic">
        <div class="container">
            <h1>{{ $event->event_name ?? 'Event Name' }}</h1>
            <p class="date">{{ $event->start_date ? $event->start_date->format('F d, Y') : 'Event Date' }}</p>
            <p class="location">{{ $event->location ?? 'Event Location' }}</p>
        </div>
    </section>

    <section class="content-section" id="about">
        <div class="container">
            <h2>About</h2>
            <p>{{ $event->description ?? 'Event description goes here.' }}</p>
        </div>
    </section>

    @if($speakers->count() > 0)
    <section class="content-section speakers-classic" id="speakers">
        <div class="container">
            <h2>Speakers</h2>
            <div class="speakers-list">
                @foreach($speakers->take(4) as $speaker)
                <div class="speaker-item">
                    @if($speaker->profile_image)
                        <img src="{{ storage_public_url($speaker->profile_image) }}" alt="{{ $speaker->full_name }}">
                    @endif
                    <div>
                        <h3>{{ $speaker->full_name }}</h3>
                        <p>{{ $speaker->job_title }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <footer class="footer-classic">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ $event->event_name ?? 'Event' }}</p>
        </div>
    </footer>
</div>
HTML;
    }

    private function getClassicProfessionalCss()
    {
        return <<<'CSS'
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Georgia, 'Times New Roman', serif;
    color: #1e293b;
    line-height: 1.7;
}

.container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
}

.header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    max-height: 50px;
}

.nav {
    display: flex;
    gap: 30px;
    align-items: center;
}

.nav a {
    text-decoration: none;
    color: #475569;
    font-weight: 500;
    transition: color 0.3s;
}

.nav a:hover {
    color: #1e40af;
}

.nav-cta {
    background: #1e40af;
    color: white !important;
    padding: 10px 25px;
    border-radius: 5px;
}

.hero-classic {
    background: #f8fafc;
    padding: 100px 0;
    text-align: center;
    border-bottom: 3px solid #1e40af;
}

.hero-classic h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    color: #1e293b;
}

.hero-classic .date {
    font-size: 1.3rem;
    color: #1e40af;
    margin-bottom: 10px;
}

.hero-classic .location {
    font-size: 1.1rem;
    color: #64748b;
}

.content-section {
    padding: 80px 0;
}

.content-section h2 {
    font-size: 2.2rem;
    margin-bottom: 30px;
    color: #1e293b;
    border-bottom: 2px solid #1e40af;
    padding-bottom: 10px;
}

.speakers-list {
    display: grid;
    gap: 30px;
    margin-top: 40px;
}

.speaker-item {
    display: flex;
    gap: 20px;
    align-items: center;
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
}

.speaker-item img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
}

.speaker-item h3 {
    font-size: 1.3rem;
    margin-bottom: 5px;
}

.speaker-item p {
    color: #64748b;
}

.footer-classic {
    background: #1e293b;
    color: white;
    padding: 30px 0;
    text-align: center;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 20px;
    }
    
    .hero-classic h1 {
        font-size: 2rem;
    }
}
CSS;
    }

    private function getVibrantCreativeHtml()
    {
        return <<<'HTML'
<div class="vibrant-landing">
    <section class="hero-vibrant">
        <div class="hero-bg"></div>
        <div class="container">
            <div class="hero-box">
                @if($event->logo)
                    <img src="{{ storage_public_url($event->logo) }}" alt="{{ $event->event_name }}" class="logo-vibrant">
                @endif
                <h1 class="title-vibrant">{{ $event->event_name ?? 'Event Name' }}</h1>
                <div class="event-meta">
                    <span class="meta-item">📅 {{ $event->start_date ? $event->start_date->format('M d, Y') : 'Date' }}</span>
                    <span class="meta-item">📍 {{ $event->location ?? 'Location' }}</span>
                </div>
                <a href="{{ route('registration.form') }}" class="cta-vibrant">Get Your Ticket</a>
            </div>
        </div>
    </section>

    @if($speakers->count() > 0)
    <section class="speakers-vibrant">
        <div class="container">
            <h2 class="section-heading">Amazing Speakers</h2>
            <div class="speakers-carousel">
                @foreach($speakers->take(6) as $speaker)
                <div class="speaker-box">
                    @if($speaker->profile_image)
                        <div class="speaker-photo" style="background-image: url('{{ storage_public_url($speaker->profile_image) }}')"></div>
                    @endif
                    <h3>{{ $speaker->full_name }}</h3>
                    <p>{{ $speaker->job_title }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($sponsors->count() > 0)
    <section class="sponsors-vibrant">
        <div class="container">
            <h2 class="section-heading">Powered By</h2>
            <div class="sponsors-flex">
                @foreach($sponsors as $sponsor)
                @if($sponsor->logo)
                    <img src="{{ storage_public_url($sponsor->logo) }}" alt="{{ $sponsor->name }}">
                @endif
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <footer class="footer-vibrant">
        <div class="container">
            <p>✨ {{ date('Y') }} {{ $event->event_name ?? 'Event' }} ✨</p>
        </div>
    </footer>
</div>
HTML;
    }

    private function getVibrantCreativeCss()
    {
        return <<<'CSS'
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', -apple-system, sans-serif;
    color: #1f2937;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.hero-vibrant {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, #ec4899, #f59e0b, #8b5cf6, #3b82f6);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.hero-box {
    position: relative;
    z-index: 10;
    text-align: center;
    background: rgba(255, 255, 255, 0.95);
    padding: 60px;
    border-radius: 30px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.logo-vibrant {
    max-width: 180px;
    margin-bottom: 30px;
}

.title-vibrant {
    font-size: 3.5rem;
    font-weight: 900;
    background: linear-gradient(45deg, #ec4899, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
}

.event-meta {
    display: flex;
    gap: 30px;
    justify-content: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.meta-item {
    font-size: 1.2rem;
    font-weight: 600;
    color: #6b7280;
}

.cta-vibrant {
    display: inline-block;
    padding: 18px 50px;
    background: linear-gradient(45deg, #ec4899, #f59e0b);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.2rem;
    transition: transform 0.3s;
}

.cta-vibrant:hover {
    transform: scale(1.05);
}

.speakers-vibrant, .sponsors-vibrant {
    padding: 80px 0;
}

.section-heading {
    font-size: 3rem;
    font-weight: 900;
    text-align: center;
    margin-bottom: 60px;
    background: linear-gradient(45deg, #ec4899, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.speakers-carousel {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
}

.speaker-box {
    text-align: center;
    padding: 20px;
    border-radius: 20px;
    background: linear-gradient(135deg, #fef3c7, #fce7f3);
    transition: transform 0.3s;
}

.speaker-box:hover {
    transform: translateY(-10px);
}

.speaker-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    margin: 0 auto 20px;
    border: 4px solid white;
}

.speaker-box h3 {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.speaker-box p {
    color: #6b7280;
    font-size: 0.9rem;
}

.sponsors-vibrant {
    background: #f9fafb;
}

.sponsors-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 40px;
    justify-content: center;
    align-items: center;
}

.sponsors-flex img {
    max-width: 150px;
    height: auto;
    opacity: 0.6;
    transition: opacity 0.3s;
}

.sponsors-flex img:hover {
    opacity: 1;
}

.footer-vibrant {
    background: linear-gradient(45deg, #1f2937, #374151);
    color: white;
    padding: 40px 0;
    text-align: center;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .hero-box {
        padding: 40px 20px;
    }
    
    .title-vibrant {
        font-size: 2.5rem;
    }
    
    .section-heading {
        font-size: 2rem;
    }
}
CSS;
    }
}
