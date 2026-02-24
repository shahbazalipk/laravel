<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingPageTemplate;

class BuildWithShahbazTemplateSeeder extends Seeder
{
    public function run()
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->event_name ?? 'Build With Shahbaz — AI Automation Live' }}</title>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <span class="top-bar-item">📅 28th March 2026, Saturday</span>
                    <span class="top-bar-item">📍 Lahore, Pakistan</span>
                </div>
                <div class="top-bar-right">
                    <a href="https://www.linkedin.com/in/meetshahbazpk/" target="_blank" class="social-link" title="LinkedIn">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/shabazch" target="_blank" class="social-link" title="Facebook">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/shahbaz_ali_pk/" target="_blank" class="social-link" title="Instagram">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="tel:+923334333611" class="top-bar-item">📞 +92 333 4333611</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Build With Shahbaz — AI Automation Live</h1>
                <p class="hero-subtitle">Learn how to create AI calling systems and smart automation workflows in a live 2-hour session.</p>
                <div class="hero-cta">
                    <a href="https://calendly.com/hello-meetshahbaz/30min" target="_blank" class="btn btn-primary">Reserve Your Spot</a>
                    <a href="#what-you-build" class="btn btn-secondary">See What You'll Build</a>
                </div>
                <div class="hero-meta">
                    <span class="meta-item">📅 28th March 2026, Saturday</span>
                    <span class="meta-item">⏱️ 2-Hour Live Session</span>
                    <span class="meta-item">📍 Lahore, Pakistan</span>
                </div>
            </div>
        </div>
    </section>

    <!-- About the Event -->
    <section class="section about-section">
        <div class="container">
            <h2 class="section-title">About the Event</h2>
            <div class="about-grid">
                <div class="about-card">
                    <div class="card-icon">🤖</div>
                    <h3>What You'll Learn</h3>
                    <p>Discover how to build AI systems that can make phone calls, automate tasks, and create intelligent workflows—all without complex coding.</p>
                </div>
                <div class="about-card">
                    <div class="card-icon">⚡</div>
                    <h3>Why AI Automation Matters</h3>
                    <p>AI automation is transforming how businesses operate. Learn to build systems that save time, reduce costs, and scale effortlessly.</p>
                </div>
                <div class="about-card">
                    <div class="card-icon">💡</div>
                    <h3>Real-World Examples</h3>
                    <p>See live demonstrations of AI calling agents, automated workflows, and practical applications you can implement immediately.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- What You Will Build -->
    <section id="what-you-build" class="section build-section">
        <div class="container">
            <h2 class="section-title">What You Will Build</h2>
            <p class="section-subtitle">Walk away with practical skills and working examples</p>
            <div class="build-grid">
                <div class="build-item">
                    <div class="build-number">01</div>
                    <h3>AI Calling Agent</h3>
                    <p>Create an intelligent system that can make and receive phone calls, understand conversations, and respond naturally.</p>
                </div>
                <div class="build-item">
                    <div class="build-number">02</div>
                    <h3>Automated Workflow</h3>
                    <p>Build smart workflows that connect different tools and automate repetitive tasks without manual intervention.</p>
                </div>
                <div class="build-item">
                    <div class="build-number">03</div>
                    <h3>Live Demo Website</h3>
                    <p>Deploy a working demonstration that showcases your AI automation system in action.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Agenda Preview -->
    <section class="section agenda-section">
        <div class="container">
            <h2 class="section-title">Session Agenda</h2>
            <p class="section-subtitle">2 hours of hands-on learning and building</p>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="timeline-time">0:00 - 0:20</span>
                    </div>
                    <div class="timeline-content">
                        <h3>Introduction to AI Automation</h3>
                        <p>Understanding the basics and what's possible with modern AI tools</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="timeline-time">0:20 - 0:50</span>
                    </div>
                    <div class="timeline-content">
                        <h3>Creating Calling Agents</h3>
                        <p>Build your first AI system that can make intelligent phone calls</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="timeline-time">0:50 - 1:20</span>
                    </div>
                    <div class="timeline-content">
                        <h3>Linking Workflows</h3>
                        <p>Connect different systems and create automated processes</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="timeline-time">1:20 - 1:50</span>
                    </div>
                    <div class="timeline-content">
                        <h3>Live Vibe Coding</h3>
                        <p>Watch real-time development and ask questions as we build</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="timeline-time">1:50 - 2:00</span>
                    </div>
                    <div class="timeline-content">
                        <h3>Final Demo & Q&A</h3>
                        <p>See everything working together and get your questions answered</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Shahbaz -->
    <section class="section speaker-section">
        <div class="container">
            <div class="speaker-card">
                <div class="speaker-image">
                    <div class="image-placeholder">
                        <span>👨‍💻</span>
                    </div>
                </div>
                <div class="speaker-content">
                    <h2>About Shahbaz</h2>
                    <p class="speaker-bio">Shahbaz Ali is an AI Engineer and Software Architect with 15+ years of experience building intelligent automation systems and scalable platforms. He specializes in making complex AI technology accessible and practical for businesses of all sizes.</p>
                    <div class="speaker-stats">
                        <div class="stat">
                            <strong>15+</strong>
                            <span>Years Experience</span>
                        </div>
                        <div class="stat">
                            <strong>100+</strong>
                            <span>Projects Delivered</span>
                        </div>
                        <div class="stat">
                            <strong>1000+</strong>
                            <span>Students Taught</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Who Should Join -->
    <section class="section audience-section">
        <div class="container">
            <h2 class="section-title">Who Should Join</h2>
            <p class="section-subtitle">This session is perfect for anyone interested in AI automation</p>
            <div class="audience-grid">
                <div class="audience-card">
                    <div class="audience-icon">🎓</div>
                    <h3>Beginners</h3>
                    <p>Curious about AI and want to understand how automation works</p>
                </div>
                <div class="audience-card">
                    <div class="audience-icon">👨‍💻</div>
                    <h3>Developers</h3>
                    <p>Looking to add AI capabilities to your technical skillset</p>
                </div>
                <div class="audience-card">
                    <div class="audience-icon">🚀</div>
                    <h3>Founders</h3>
                    <p>Want to leverage AI to scale your business operations</p>
                </div>
                <div class="audience-card">
                    <div class="audience-icon">⚙️</div>
                    <h3>Automation Enthusiasts</h3>
                    <p>Passionate about optimizing workflows and processes</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section id="register" class="section cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Build Your First AI Automation System?</h2>
                <p class="cta-subtitle">Join us for this live 2-hour session and start your AI automation journey</p>
                <div class="cta-buttons">
                    <a href="https://calendly.com/hello-meetshahbaz/30min" target="_blank" class="btn btn-primary btn-large">Book Your Spot</a>
                    <a href="mailto:hello@meetshahbaz.pk" class="btn btn-secondary btn-large">Email Us</a>
                </div>
                <p class="cta-note">Limited spots available • Free for early registrants</p>
                <div class="venue-info">
                    <h3>📍 Venue Details</h3>
                    <p>Office # 1209, Alhafeez Executive</p>
                    <p>30 Firdous Market Rd, Block C3, Gulberg III</p>
                    <p>Lahore, 54000, Pakistan</p>
                    <a href="https://maps.app.goo.gl/73Ubzr1aXYnPBCBw9" target="_blank" class="venue-link">View on Google Maps →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>Build With Shahbaz</h3>
                    <p>AI Automation • Live Sessions • Practical Learning</p>
                    <div class="footer-contact">
                        <p>📧 <a href="mailto:hello@meetshahbaz.pk">hello@meetshahbaz.pk</a></p>
                        <p>📞 <a href="tel:+923334333611">+92 333 4333611</a></p>
                        <p>🌐 <a href="https://meetshahbaz.pk" target="_blank">meetshahbaz.pk</a></p>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <a href="https://calendly.com/hello-meetshahbaz/30min" target="_blank">Book a Session</a>
                    <a href="https://meetshahbaz.pk" target="_blank">Website</a>
                    <a href="mailto:hello@meetshahbaz.pk">Contact</a>
                </div>
                <div class="footer-social">
                    <h4>Connect With Us</h4>
                    <div class="social-links">
                        <a href="https://www.linkedin.com/in/meetshahbazpk/" target="_blank" title="LinkedIn">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/shabazch" target="_blank" title="Facebook">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/shahbaz_ali_pk/" target="_blank" title="Instagram">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Build With Shahbaz. All rights reserved.</p>
                <p class="footer-address">Office # 1209, Alhafeez Executive, 30 Firdous Market Rd, Gulberg III, Lahore</p>
            </div>
        </div>
    </footer>
</body>
</html>
HTML;

        $css = <<<'CSS'
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --secondary: #8b5cf6;
    --dark: #0f172a;
    --dark-light: #1e293b;
    --gray: #64748b;
    --gray-light: #cbd5e1;
    --light: #f8fafc;
    --white: #ffffff;
    --radius: 12px;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
    line-height: 1.6;
    color: var(--dark);
    background: var(--white);
}

/* Top Bar */
.top-bar {
    background: var(--dark);
    color: var(--white);
    padding: 10px 0;
    font-size: 0.875rem;
}

.top-bar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.top-bar-left,
.top-bar-right {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.top-bar-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--white);
    text-decoration: none;
    opacity: 0.9;
}

.top-bar-item:hover {
    opacity: 1;
}

.social-link {
    color: var(--white);
    opacity: 0.8;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
}

.social-link:hover {
    opacity: 1;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section {
    padding: 80px 0;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 16px;
    color: var(--dark);
}

.section-subtitle {
    font-size: 1.125rem;
    text-align: center;
    color: var(--gray);
    margin-bottom: 48px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: var(--white);
    padding: 120px 0 80px;
    text-align: center;
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 24px;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 40px;
    opacity: 0.95;
    line-height: 1.8;
}

.hero-cta {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 48px;
}

.hero-meta {
    display: flex;
    gap: 32px;
    justify-content: center;
    flex-wrap: wrap;
    font-size: 0.95rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Buttons */
.btn {
    display: inline-block;
    padding: 14px 32px;
    border-radius: var(--radius);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-primary {
    background: var(--white);
    color: var(--primary);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-secondary {
    background: transparent;
    color: var(--white);
    border: 2px solid var(--white);
}

.btn-secondary:hover {
    background: var(--white);
    color: var(--primary);
}

.btn-large {
    padding: 18px 48px;
    font-size: 1.125rem;
}

/* About Section */
.about-section {
    background: var(--light);
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 32px;
}

.about-card {
    background: var(--white);
    padding: 40px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-align: center;
}

.card-icon {
    font-size: 3rem;
    margin-bottom: 20px;
}

.about-card h3 {
    font-size: 1.5rem;
    margin-bottom: 16px;
    color: var(--dark);
}

.about-card p {
    color: var(--gray);
    line-height: 1.8;
}

/* Build Section */
.build-grid {
    display: grid;
    gap: 32px;
    max-width: 800px;
    margin: 0 auto;
}

.build-item {
    background: var(--white);
    padding: 32px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border-left: 4px solid var(--primary);
}

.build-number {
    display: inline-block;
    background: var(--primary);
    color: var(--white);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-bottom: 16px;
}

.build-item h3 {
    font-size: 1.5rem;
    margin-bottom: 12px;
    color: var(--dark);
}

.build-item p {
    color: var(--gray);
    line-height: 1.8;
}

/* Timeline */
.agenda-section {
    background: var(--light);
}

.timeline {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: 100%;
    background: var(--gray-light);
}

.timeline-item {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    margin-bottom: 48px;
    position: relative;
}

.timeline-marker {
    text-align: right;
    padding-right: 32px;
}

.timeline-time {
    display: inline-block;
    background: var(--primary);
    color: var(--white);
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
}

.timeline-content {
    background: var(--white);
    padding: 24px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.timeline-content h3 {
    font-size: 1.25rem;
    margin-bottom: 8px;
    color: var(--dark);
}

.timeline-content p {
    color: var(--gray);
}

/* Speaker Section */
.speaker-card {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 48px;
    align-items: center;
    background: var(--white);
    padding: 48px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
}

.speaker-image {
    text-align: center;
}

.image-placeholder {
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 6rem;
    margin: 0 auto;
}

.speaker-content h2 {
    font-size: 2rem;
    margin-bottom: 16px;
    color: var(--dark);
}

.speaker-bio {
    color: var(--gray);
    line-height: 1.8;
    margin-bottom: 32px;
}

.speaker-stats {
    display: flex;
    gap: 48px;
}

.stat {
    display: flex;
    flex-direction: column;
}

.stat strong {
    font-size: 2rem;
    color: var(--primary);
    font-weight: 700;
}

.stat span {
    color: var(--gray);
    font-size: 0.875rem;
}

/* Audience Section */
.audience-section {
    background: var(--light);
}

.audience-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
}

.audience-card {
    background: var(--white);
    padding: 32px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-align: center;
    transition: transform 0.3s ease;
}

.audience-card:hover {
    transform: translateY(-4px);
}

.audience-icon {
    font-size: 3rem;
    margin-bottom: 16px;
}

.audience-card h3 {
    font-size: 1.25rem;
    margin-bottom: 12px;
    color: var(--dark);
}

.audience-card p {
    color: var(--gray);
    font-size: 0.95rem;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
    color: var(--white);
    text-align: center;
}

.cta-content {
    max-width: 700px;
    margin: 0 auto;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 16px;
}

.cta-subtitle {
    font-size: 1.125rem;
    margin-bottom: 40px;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

.cta-note {
    margin-top: 24px;
    font-size: 0.95rem;
    opacity: 0.8;
}

.venue-info {
    margin-top: 48px;
    padding: 32px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--radius);
    backdrop-filter: blur(10px);
}

.venue-info h3 {
    font-size: 1.5rem;
    margin-bottom: 16px;
}

.venue-info p {
    margin: 8px 0;
    opacity: 0.9;
}

.venue-link {
    display: inline-block;
    margin-top: 16px;
    color: var(--white);
    text-decoration: none;
    font-weight: 600;
    border-bottom: 2px solid var(--white);
    padding-bottom: 4px;
    transition: all 0.3s ease;
}

.venue-link:hover {
    border-bottom-color: transparent;
    transform: translateX(4px);
}

/* Registration Section */
.registration-section {
    background: var(--light);
}

.registration-card {
    max-width: 600px;
    margin: 0 auto;
    background: var(--white);
    padding: 48px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    text-align: center;
}

.registration-card h2 {
    font-size: 2rem;
    margin-bottom: 12px;
    color: var(--dark);
}

.registration-card p {
    color: var(--gray);
    margin-bottom: 32px;
}

.form-placeholder {
    padding: 48px;
    background: var(--light);
    border-radius: var(--radius);
    color: var(--gray);
}

/* Footer */
.footer {
    background: var(--dark);
    color: var(--white);
    padding: 48px 0 24px;
}

.footer-content {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 48px;
    margin-bottom: 32px;
}

.footer-brand h3 {
    font-size: 1.5rem;
    margin-bottom: 8px;
}

.footer-brand p {
    opacity: 0.8;
    font-size: 0.95rem;
    margin-bottom: 16px;
}

.footer-contact {
    margin-top: 16px;
}

.footer-contact p {
    margin: 8px 0;
    font-size: 0.95rem;
}

.footer-contact a {
    color: var(--white);
    text-decoration: none;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.footer-contact a:hover {
    opacity: 1;
}

.footer-links h4,
.footer-social h4 {
    font-size: 1rem;
    margin-bottom: 16px;
    font-weight: 600;
}

.footer-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-links a {
    color: var(--white);
    text-decoration: none;
    opacity: 0.8;
    transition: opacity 0.3s ease;
    font-size: 0.95rem;
}

.footer-links a:hover {
    opacity: 1;
}

.social-links {
    display: flex;
    gap: 16px;
}

.social-links a {
    color: var(--white);
    opacity: 0.8;
    transition: all 0.3s ease;
}

.social-links a:hover {
    opacity: 1;
    transform: translateY(-2px);
}

.footer-bottom {
    text-align: center;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    opacity: 0.6;
    font-size: 0.875rem;
}

.footer-address {
    margin-top: 8px;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .top-bar-content {
        flex-direction: column;
        text-align: center;
    }
    
    .top-bar-left,
    .top-bar-right {
        justify-content: center;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .timeline::before {
        left: 0;
    }
    
    .timeline-item {
        grid-template-columns: 1fr;
    }
    
    .timeline-marker {
        text-align: left;
        padding-right: 0;
        padding-left: 32px;
    }
    
    .speaker-card {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .speaker-stats {
        justify-content: center;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .footer-links {
        align-items: center;
    }
    
    .social-links {
        justify-content: center;
    }
}
CSS;

        LandingPageTemplate::updateOrCreate(
            ['slug' => 'build-with-shahbaz-ai'],
            [
                'name' => 'Build With Shahbaz - AI Automation',
                'slug' => 'build-with-shahbaz-ai',
                'description' => 'Modern, premium landing page for AI automation events. Features clean design, timeline agenda, speaker section, and strong CTAs. Perfect for tech events, workshops, and live sessions.',
                'html_content' => $html,
                'css_content' => $css,
                'js_content' => '',
                'is_active' => true,
                'event_id' => config('event.event_id'),
                'org_id' => config('event.org_id'),
            ]
        );

        $this->command->info('Build With Shahbaz template created successfully!');
    }
}
