<?php

use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\AgendaManagementController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BoothTypeController;
use App\Http\Controllers\Admin\BusinessActivityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomFormAnswerFileController;
use App\Http\Controllers\Admin\CustomFormController;
use App\Http\Controllers\Admin\CustomFormQuestionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailCampaignController;
use App\Http\Controllers\Admin\EmailProviderConfigController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EmailWebhookController;
use App\Http\Controllers\Admin\EventSettingsController;
use App\Http\Controllers\Admin\EventUrlController;
use App\Http\Controllers\Admin\ExhibitorController;
use App\Http\Controllers\Admin\ExhibitorTagController;
use App\Http\Controllers\Admin\ExhibitorTypeController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\GroupTypeController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\LectureController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\ParameterBulkImportController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PersonaController;
use App\Http\Controllers\Admin\ProductTypeController;
use App\Http\Controllers\Admin\RecipientUploadController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\RegistrationDraftController;
use App\Http\Controllers\Admin\RegistrationPaymentController;
use App\Http\Controllers\Admin\RegistrationStatusController;
use App\Http\Controllers\Admin\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Admin\Sales\DealController as SalesDealController;
use App\Http\Controllers\Admin\Sales\InquiryFormController as SalesInquiryFormController;
use App\Http\Controllers\Admin\Sales\InquirySubmissionController as SalesInquirySubmissionController;
use App\Http\Controllers\Admin\Sales\KanbanController as SalesKanbanController;
use App\Http\Controllers\Admin\Sales\PipelineController as SalesPipelineController;
use App\Http\Controllers\Admin\Sales\PipelineTypeController as SalesPipelineTypeController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SpeakerController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\TrackController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PublicSales\EmbedController as SalesEmbedController;
use App\Http\Controllers\PublicSales\InquiryFormController as PublicSalesInquiryFormController;
use App\Http\Controllers\UnsubscribeController;

// Base URL shows event landing page
Route::get('/', [EventController::class, 'landing'])->name('event.landing');

// Public Sales inquiry forms + embed
Route::get('/sales/f/{slug}', [PublicSalesInquiryFormController::class, 'show'])->name('sales.public.form');
Route::post('/sales/f/{slug}', [PublicSalesInquiryFormController::class, 'store'])->name('sales.public.form.store');
Route::get('/sales/embed/{embed_token}', [SalesEmbedController::class, 'show'])->name('sales.public.embed');

// Speaker and abstract submission portal
Route::prefix('submissions')->name('submissions.')->middleware('submissions.enabled')->group(function () {
    Route::get('login', [\App\Http\Controllers\Submissions\PortalController::class, 'login'])->name('portal.login');
    Route::post('login', [\App\Http\Controllers\Submissions\PortalController::class, 'send'])->middleware('throttle:5,1')->name('portal.send');
    Route::get('access/{token}', [\App\Http\Controllers\Submissions\PortalController::class, 'consume'])->middleware('throttle:10,1')->name('portal.consume');
    Route::get('events/{eventSlug}/{typeSlug}', [\App\Http\Controllers\Submissions\PublicSubmissionController::class, 'landing'])->name('public.landing');

    Route::middleware('submission.portal')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Submissions\PortalController::class, 'dashboard'])->name('portal.dashboard');
        Route::post('logout', [\App\Http\Controllers\Submissions\PortalController::class, 'logout'])->name('portal.logout');
        Route::post('events/{eventSlug}/types/{submissionType}/start', [\App\Http\Controllers\Submissions\PublicSubmissionController::class, 'start'])->name('public.start');
        Route::get('events/{eventSlug}/drafts/{submission}/edit', [\App\Http\Controllers\Submissions\PublicSubmissionController::class, 'edit'])->name('public.edit');
        Route::put('events/{eventSlug}/drafts/{submission}', [\App\Http\Controllers\Submissions\PublicSubmissionController::class, 'update'])->name('public.update');
        Route::post('events/{eventSlug}/drafts/{submission}/submit', [\App\Http\Controllers\Submissions\PublicSubmissionController::class, 'submit'])->name('public.submit');
        Route::post('events/{eventSlug}/drafts/{submission}/withdraw', [\App\Http\Controllers\Submissions\PublicSubmissionController::class, 'withdraw'])->name('public.withdraw');
        Route::get('speaker/{link}/onboarding', [\App\Http\Controllers\Submissions\SpeakerPortalController::class, 'show'])->name('speaker.onboarding');
        Route::put('speaker/{link}/onboarding', [\App\Http\Controllers\Submissions\SpeakerPortalController::class, 'update'])->name('speaker.update');
        Route::post('speaker/{link}/presentation', [\App\Http\Controllers\Submissions\SpeakerPortalController::class, 'presentation'])->name('speaker.presentation');
        Route::post('speaker/contracts/{contract}/accept', [\App\Http\Controllers\Submissions\SpeakerPortalController::class, 'acceptContract'])->name('speaker.contract.accept');
    });
});

Route::prefix('reviewer')->name('reviewer.')->middleware(['submissions.enabled', 'submission.portal:reviewer'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Submissions\ReviewerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('assignments/{assignment}', [\App\Http\Controllers\Submissions\ReviewerPortalController::class, 'show'])->name('assignments.show');
    Route::post('assignments/{assignment}/conflict', [\App\Http\Controllers\Submissions\ReviewerPortalController::class, 'conflict'])->name('assignments.conflict');
    Route::post('assignments/{assignment}/review', [\App\Http\Controllers\Submissions\ReviewerPortalController::class, 'save'])->name('assignments.review');
});
Route::get('submission-files/{file}/download', [\App\Http\Controllers\Submissions\SubmissionFileController::class, 'download'])
    ->middleware('signed')
    ->name('submissions.files.download');

// Attendee Authentication Routes
Route::prefix('attendee')->name('attendee.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AttendeeAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AttendeeAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [\App\Http\Controllers\AttendeeAuthController::class, 'logout'])->name('logout');

    // Protected attendee routes
    Route::middleware(['attendee.auth'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AttendeeDashboardController::class, 'index'])->name('dashboard');
        Route::get('/exhibitors', [\App\Http\Controllers\AttendeeDashboardController::class, 'exhibitors'])->name('exhibitors');
        Route::get('/exhibitors/{exhibitor}', [\App\Http\Controllers\AttendeeDashboardController::class, 'exhibitorDetail'])->name('exhibitors.show');
        Route::get('/jobs', [\App\Http\Controllers\AttendeeDashboardController::class, 'jobs'])->name('jobs');
        Route::get('/products', [\App\Http\Controllers\AttendeeDashboardController::class, 'products'])->name('products');
        Route::get('/speakers', [\App\Http\Controllers\AttendeeDashboardController::class, 'speakers'])->name('speakers');
        Route::get('/speakers/{speaker}', [\App\Http\Controllers\AttendeeDashboardController::class, 'speakerDetail'])->name('speakers.show');
        Route::get('/sessions', [\App\Http\Controllers\AttendeeDashboardController::class, 'sessions'])->name('sessions');
        Route::get('/sessions/{session}', [\App\Http\Controllers\AttendeeDashboardController::class, 'sessionDetail'])->name('sessions.show');
        Route::get('/agenda', [\App\Http\Controllers\AttendeeDashboardController::class, 'agenda'])->name('agenda');
        Route::get('/attendees', [\App\Http\Controllers\AttendeeDashboardController::class, 'attendees'])->name('attendees');
        Route::get('/attendees/{registration}', [\App\Http\Controllers\AttendeeDashboardController::class, 'attendeeDetail'])->name('attendees.show');
        Route::get('/sponsors', [\App\Http\Controllers\AttendeeDashboardController::class, 'sponsors'])->name('sponsors');
        Route::get('/partners', [\App\Http\Controllers\AttendeeDashboardController::class, 'partners'])->name('partners');
        Route::get('/gallery', [\App\Http\Controllers\AttendeeDashboardController::class, 'gallery'])->name('gallery');
        Route::get('/gallery/{gallery}', [\App\Http\Controllers\AttendeeDashboardController::class, 'galleryPhoto'])->name('gallery.photo');
        Route::post('/gallery/{gallery}/download', [\App\Http\Controllers\AttendeeDashboardController::class, 'downloadPhoto'])->name('gallery.download');
        Route::get('/gallery/{gallery}/download-file', [\App\Http\Controllers\AttendeeDashboardController::class, 'downloadFile'])->name('gallery.download-file');
        Route::get('/favorites', [\App\Http\Controllers\FavoritesController::class, 'index'])->name('favorites');
        Route::post('/favorites/toggle', [\App\Http\Controllers\FavoritesController::class, 'toggle'])->name('favorites.toggle');
        Route::get('/profile', [\App\Http\Controllers\AttendeeDashboardController::class, 'profile'])->name('profile');

        // Event Wall Routes
        Route::get('/event-wall', [\App\Http\Controllers\EventWallController::class, 'index'])->name('event-wall');
        Route::post('/event-wall', [\App\Http\Controllers\EventWallController::class, 'store'])->name('event-wall.store');
        Route::delete('/event-wall/{post}', [\App\Http\Controllers\EventWallController::class, 'destroy'])->name('event-wall.destroy');
        Route::post('/event-wall/{post}/like', [\App\Http\Controllers\EventWallController::class, 'toggleLike'])->name('event-wall.like');
        Route::post('/event-wall/{post}/comments', [\App\Http\Controllers\EventWallController::class, 'storeComment'])->name('event-wall.comments.store');
        Route::delete('/event-wall/comments/{comment}', [\App\Http\Controllers\EventWallController::class, 'destroyComment'])->name('event-wall.comments.destroy');
        Route::post('/event-wall/comments/{comment}/like', [\App\Http\Controllers\EventWallController::class, 'toggleCommentLike'])->name('event-wall.comments.like');

        // Connection Routes
        Route::get('/connections', [\App\Http\Controllers\AttendeeConnectionController::class, 'index'])->name('connections');
        Route::post('/connections/send', [\App\Http\Controllers\AttendeeConnectionController::class, 'sendRequest'])->name('connections.send');
        Route::post('/connections/{connection}/accept', [\App\Http\Controllers\AttendeeConnectionController::class, 'acceptRequest'])->name('connections.accept');
        Route::post('/connections/{connection}/reject', [\App\Http\Controllers\AttendeeConnectionController::class, 'rejectRequest'])->name('connections.reject');
        Route::delete('/connections/{connection}/cancel', [\App\Http\Controllers\AttendeeConnectionController::class, 'cancelRequest'])->name('connections.cancel');
        Route::delete('/connections/{connection}', [\App\Http\Controllers\AttendeeConnectionController::class, 'removeConnection'])->name('connections.remove');

        // Messaging Routes
        Route::get('/messages', [\App\Http\Controllers\AttendeeMessageController::class, 'index'])->name('messages');
        Route::get('/messages/{attendee}', [\App\Http\Controllers\AttendeeMessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{attendee}/send', [\App\Http\Controllers\AttendeeMessageController::class, 'send'])->name('messages.send');
        Route::get('/messages/{attendee}/get', [\App\Http\Controllers\AttendeeMessageController::class, 'getMessages'])->name('messages.get');
        Route::get('/messages-unread-count', [\App\Http\Controllers\AttendeeMessageController::class, 'getUnreadCount'])->name('messages.unread-count');

        // Marketing Assets (Attendee View)
        Route::get('/marketing-hub', [\App\Http\Controllers\AttendeeMarketingController::class, 'index'])->name('marketing-hub');
        Route::get('/marketing-hub/{asset}/download', [\App\Http\Controllers\AttendeeMarketingController::class, 'download'])->name('marketing-hub.download');
        Route::post('/marketing-hub/{asset}/share', [\App\Http\Controllers\AttendeeMarketingController::class, 'trackShare'])->name('marketing-hub.share');
    });
});

// Public Registration Routes
Route::prefix('register')->name('registration.')->group(function () {
    Route::get('/', [\App\Http\Controllers\RegistrationController::class, 'showForm'])->name('form');
    Route::post('/', [\App\Http\Controllers\RegistrationController::class, 'store'])->name('store');
    Route::get('/category/{category}', [\App\Http\Controllers\RegistrationController::class, 'getCategoryDetails'])->name('category.details');
    Route::post('/validate-step', [\App\Http\Controllers\RegistrationController::class, 'validateStep'])->name('validate-step');
    Route::get('/confirmation/{hash}', [\App\Http\Controllers\RegistrationController::class, 'confirmation'])->name('confirmation');
    Route::get('/verify-email/{token}', [\App\Http\Controllers\RegistrationController::class, 'verifyEmail'])->name('verify-email');
});

// Online Registration Routes (separate file)
Route::prefix('online')->group(base_path('routes/online.php'));

// Badge Printing Routes
Route::prefix('badge')->name('badge.')->group(function () {
    Route::get('/{slug}', [\App\Http\Controllers\BadgePrintingController::class, 'show'])->name('show');
    Route::post('/{slug}/search', [\App\Http\Controllers\BadgePrintingController::class, 'search'])->name('search');
    Route::post('/{slug}/search-face', [\App\Http\Controllers\BadgePrintingController::class, 'searchByFace'])->name('search-face');
    Route::post('/{slug}/print/{hash}', [\App\Http\Controllers\BadgePrintingController::class, 'print'])->name('print');
});

// Admin authentication routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware(['event.admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('custom-forms', CustomFormController::class)->except('show');
        Route::post('custom-forms/{custom_form}/questions', [CustomFormQuestionController::class, 'store'])
            ->name('custom-forms.questions.store');
        Route::put('custom-forms/{custom_form}/questions/{question}', [CustomFormQuestionController::class, 'update'])
            ->name('custom-forms.questions.update');
        Route::delete('custom-forms/{custom_form}/questions/{question}', [CustomFormQuestionController::class, 'destroy'])
            ->name('custom-forms.questions.destroy');
        Route::patch('custom-forms/{custom_form}/questions/{question}/reorder', [CustomFormQuestionController::class, 'reorder'])
            ->name('custom-forms.questions.reorder');

        // Sales module
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('dashboard', [SalesDashboardController::class, 'index'])->name('dashboard');

            Route::resource('pipeline-types', SalesPipelineTypeController::class);
            Route::post('pipeline-types/{pipeline_type}/duplicate', [SalesPipelineTypeController::class, 'duplicate'])
                ->name('pipeline-types.duplicate');
            Route::post('pipeline-types/{pipeline_type}/toggle', [SalesPipelineTypeController::class, 'toggle'])
                ->name('pipeline-types.toggle');

            Route::resource('pipelines', SalesPipelineController::class);
            Route::post('pipelines/{pipeline}/archive', [SalesPipelineController::class, 'archive'])
                ->name('pipelines.archive');
            Route::get('pipelines/{pipeline}/kanban', [SalesKanbanController::class, 'show'])
                ->name('pipelines.kanban');

            Route::resource('deals', SalesDealController::class);
            Route::post('deals/{deal}/move-stage', [SalesDealController::class, 'moveStage'])
                ->name('deals.move-stage');

            Route::resource('inquiry-forms', SalesInquiryFormController::class);
            Route::post('inquiry-forms/{inquiry_form}/publish', [SalesInquiryFormController::class, 'publish'])
                ->name('inquiry-forms.publish');
            Route::post('inquiry-forms/{inquiry_form}/unpublish', [SalesInquiryFormController::class, 'unpublish'])
                ->name('inquiry-forms.unpublish');
            Route::post('inquiry-forms/{inquiry_form}/duplicate', [SalesInquiryFormController::class, 'duplicate'])
                ->name('inquiry-forms.duplicate');

            Route::get('submissions', [SalesInquirySubmissionController::class, 'index'])->name('submissions.index');
            Route::get('submissions/export', [SalesInquirySubmissionController::class, 'export'])->name('submissions.export');
            Route::get('submissions/{inquiry_submission}', [SalesInquirySubmissionController::class, 'show'])->name('submissions.show');
            Route::patch('submissions/{inquiry_submission}/status', [SalesInquirySubmissionController::class, 'updateStatus'])->name('submissions.status');
            Route::post('submissions/{inquiry_submission}/convert', [SalesInquirySubmissionController::class, 'convert'])->name('submissions.convert');
            Route::delete('submissions/{inquiry_submission}', [SalesInquirySubmissionController::class, 'destroy'])->name('submissions.destroy');
        });

        // Speaker and abstract submission management
        Route::prefix('submissions')->name('submissions.')->middleware('submissions.enabled')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Admin\Submissions\SubmissionController::class, 'dashboard'])->name('dashboard');
            Route::get('all', [\App\Http\Controllers\Admin\Submissions\SubmissionController::class, 'index'])->name('index');
            Route::get('kanban', [\App\Http\Controllers\Admin\Submissions\SubmissionController::class, 'kanban'])->name('kanban');
            Route::get('export', [\App\Http\Controllers\Admin\Submissions\SubmissionController::class, 'export'])->middleware('submission.ability:submissions.export')->name('export');
            Route::get('all/{submission}', [\App\Http\Controllers\Admin\Submissions\SubmissionController::class, 'show'])->name('show');
            Route::post('all/{submission}/stage', [\App\Http\Controllers\Admin\Submissions\SubmissionController::class, 'move'])->name('stage');
            Route::post('all/{submission}/decision', [\App\Http\Controllers\Admin\Submissions\DecisionController::class, 'store'])->middleware('submission.ability:submissions.decide')->name('decision');
            Route::post('all/{submission}/revision', [\App\Http\Controllers\Admin\Submissions\DecisionController::class, 'revision'])->middleware('submission.ability:submissions.request-revision')->name('revision');
            Route::post('all/{submission}/convert', [\App\Http\Controllers\Admin\Submissions\SpeakerOperationsController::class, 'convert'])->middleware('submission.ability:submissions.convert-speaker')->name('convert');
            Route::post('all/{submission}/reviewers', [\App\Http\Controllers\Admin\Submissions\ReviewerController::class, 'assign'])->middleware('submission.ability:submissions.assign-reviewers')->name('reviewers.assign');

            Route::resource('types', \App\Http\Controllers\Admin\Submissions\SubmissionTypeController::class)->parameters(['types' => 'submission_type'])->middleware('submission.ability:submissions.configure');
            Route::post('types/{submission_type}/duplicate', [\App\Http\Controllers\Admin\Submissions\SubmissionTypeController::class, 'duplicate'])->name('types.duplicate');
            Route::post('types/{submission_type}/publish', [\App\Http\Controllers\Admin\Submissions\SubmissionTypeController::class, 'publish'])->name('types.publish');
            Route::post('types/{submission_type}/sections', [\App\Http\Controllers\Admin\Submissions\ConfigurationController::class, 'storeSection'])->name('sections.store');
            Route::post('sections/{section}/questions', [\App\Http\Controllers\Admin\Submissions\ConfigurationController::class, 'storeQuestion'])->name('questions.store');
            Route::delete('questions/{question}', [\App\Http\Controllers\Admin\Submissions\ConfigurationController::class, 'destroyQuestion'])->name('questions.destroy');
            Route::post('types/{submission_type}/rules', [\App\Http\Controllers\Admin\Submissions\ConfigurationController::class, 'storeRule'])->name('rules.store');
            Route::post('types/{submission_type}/stages', [\App\Http\Controllers\Admin\Submissions\ConfigurationController::class, 'storeStage'])->name('stages.store');
            Route::post('types/{submission_type}/criteria', [\App\Http\Controllers\Admin\Submissions\ConfigurationController::class, 'storeCriterion'])->name('criteria.store');

            Route::get('reviewers', [\App\Http\Controllers\Admin\Submissions\ReviewerController::class, 'index'])->name('reviewers.index');
            Route::post('reviewers', [\App\Http\Controllers\Admin\Submissions\ReviewerController::class, 'store'])->name('reviewers.store');
            Route::post('speakers/{speaker}/sessions', [\App\Http\Controllers\Admin\Submissions\SpeakerOperationsController::class, 'assignSession'])->name('speakers.sessions');
            Route::put('speakers/{speaker}/commercial', [\App\Http\Controllers\Admin\Submissions\SpeakerOperationsController::class, 'commercial'])->middleware('submission.ability:submissions.view-financial')->name('speakers.commercial');
            Route::post('speakers/{speaker}/contracts', [\App\Http\Controllers\Admin\Submissions\SpeakerOperationsController::class, 'contract'])->middleware('submission.ability:submissions.manage-contracts')->name('speakers.contracts');
            Route::put('speakers/{speaker}/travel', [\App\Http\Controllers\Admin\Submissions\SpeakerOperationsController::class, 'travel'])->middleware('submission.ability:submissions.manage-travel')->name('speakers.travel');
        });

        Route::resource('categories', CategoryController::class);
        Route::resource('agenda', AgendaController::class);

        // Parameters
        Route::post('parameters/{parameter}/bulk-import', ParameterBulkImportController::class)
            ->whereIn('parameter', array_keys(config('parameter_imports')))
            ->name('parameters.bulk-import');

        Route::resource('registration-statuses', RegistrationStatusController::class);
        Route::post('registration-statuses/{registrationStatus}/toggle', [RegistrationStatusController::class, 'toggleActive'])
            ->name('registration-statuses.toggle');

        Route::resource('personas', PersonaController::class);
        Route::post('personas/{persona}/toggle', [PersonaController::class, 'toggleActive'])
            ->name('personas.toggle');

        // Category Types
        Route::resource('category-types', \App\Http\Controllers\Admin\CategoryTypeController::class);
        Route::post('category-types/{categoryType}/toggle', [\App\Http\Controllers\Admin\CategoryTypeController::class, 'toggleActive'])
            ->name('category-types.toggle');

        // Product Types
        Route::resource('product-types', ProductTypeController::class);
        Route::patch('product-types/{productType}/toggle-active', [ProductTypeController::class, 'toggleActive'])
            ->name('product-types.toggle-active');

        // Exhibitor Tags
        Route::resource('exhibitor-tags', ExhibitorTagController::class);
        Route::patch('exhibitor-tags/{exhibitorTag}/toggle-active', [ExhibitorTagController::class, 'toggleActive'])
            ->name('exhibitor-tags.toggle-active');

        // Booth Types
        Route::resource('booth-types', BoothTypeController::class);
        Route::patch('booth-types/{boothType}/toggle-active', [BoothTypeController::class, 'toggleActive'])
            ->name('booth-types.toggle-active');

        // Exhibitor Types
        Route::resource('exhibitor-types', ExhibitorTypeController::class);
        Route::patch('exhibitor-types/{exhibitorType}/toggle-active', [ExhibitorTypeController::class, 'toggleActive'])
            ->name('exhibitor-types.toggle-active');

        // Industries
        Route::post('industries/bulk-import', [IndustryController::class, 'bulkImport'])
            ->name('industries.bulk-import');
        Route::resource('industries', IndustryController::class);
        Route::patch('industries/{industry}/toggle-active', [IndustryController::class, 'toggleActive'])
            ->name('industries.toggle-active');

        // Business Activities
        Route::resource('business-activities', BusinessActivityController::class);
        Route::patch('business-activities/{businessActivity}/toggle-active', [BusinessActivityController::class, 'toggleActive'])
            ->name('business-activities.toggle-active');

        // Group Types
        Route::resource('group-types', GroupTypeController::class);
        Route::patch('group-types/{groupType}/toggle-active', [GroupTypeController::class, 'toggleActive'])
            ->name('group-types.toggle-active');

        // Groups
        Route::resource('groups', GroupController::class);
        Route::patch('groups/{group}/toggle-active', [GroupController::class, 'toggleActive'])
            ->name('groups.toggle-active');
        Route::patch('groups/{group}/toggle-vip', [GroupController::class, 'toggleVip'])
            ->name('groups.toggle-vip');

        // Exhibitors
        Route::resource('exhibitors', ExhibitorController::class);
        Route::patch('exhibitors/{exhibitor}/toggle-active', [ExhibitorController::class, 'toggleActive'])
            ->name('exhibitors.toggle-active');
        Route::patch('exhibitors/{exhibitor}/toggle-featured', [ExhibitorController::class, 'toggleFeatured'])
            ->name('exhibitors.toggle-featured');

        // Exhibitor Jobs
        Route::get('exhibitor-jobs', [\App\Http\Controllers\Admin\ExhibitorJobController::class, 'index'])->name('exhibitor-jobs.index');
        Route::resource('exhibitor-jobs', \App\Http\Controllers\Admin\ExhibitorJobController::class)->only(['store', 'update', 'destroy']);
        Route::post('exhibitor-jobs/{exhibitorJob}/toggle-active', [\App\Http\Controllers\Admin\ExhibitorJobController::class, 'toggleActive'])->name('exhibitor-jobs.toggle-active');

        // Exhibitor Products
        Route::get('exhibitor-products', [\App\Http\Controllers\Admin\ExhibitorProductController::class, 'index'])->name('exhibitor-products.index');
        Route::resource('exhibitor-products', \App\Http\Controllers\Admin\ExhibitorProductController::class)->only(['store', 'update', 'destroy']);
        Route::post('exhibitor-products/{exhibitorProduct}/toggle-active', [\App\Http\Controllers\Admin\ExhibitorProductController::class, 'toggleActive'])->name('exhibitor-products.toggle-active');

        // Sponsors
        Route::resource('sponsors', SponsorController::class);
        Route::post('sponsors/{sponsor}/toggle', [SponsorController::class, 'toggleActive'])
            ->name('sponsors.toggle');

        // Partners
        Route::resource('partners', PartnerController::class);
        Route::post('partners/{partner}/toggle', [PartnerController::class, 'toggleActive'])
            ->name('partners.toggle');

        // Settings
        Route::get('event-settings', [EventSettingsController::class, 'edit'])
            ->name('event-settings.edit');
        Route::put('event-settings', [EventSettingsController::class, 'update'])
            ->name('event-settings.update');

        // Landing Page Templates
        Route::resource('landing-page-templates', \App\Http\Controllers\Admin\LandingPageTemplateController::class);
        Route::post('landing-page-templates/{landingPageTemplate}/toggle-active', [\App\Http\Controllers\Admin\LandingPageTemplateController::class, 'toggleActive'])
            ->name('landing-page-templates.toggle-active');
        Route::get('landing-page-templates/{landingPageTemplate}/preview', [\App\Http\Controllers\Admin\LandingPageTemplateController::class, 'preview'])
            ->name('landing-page-templates.preview');
        Route::post('landing-page-templates/{landingPageTemplate}/ai-edit', [\App\Http\Controllers\Admin\LandingPageTemplateController::class, 'aiEdit'])
            ->name('landing-page-templates.ai-edit');

        // LLM Integration
        Route::post('llm/test-connection', [\App\Http\Controllers\Admin\LLMController::class, 'testConnection'])
            ->name('llm.test-connection');
        Route::post('llm/generate-template', [\App\Http\Controllers\Admin\LLMController::class, 'generateTemplate'])
            ->name('llm.generate-template');
        Route::post('llm/improve-template', [\App\Http\Controllers\Admin\LLMController::class, 'improveTemplate'])
            ->name('llm.improve-template');
        Route::post('llm/generate-seo', [\App\Http\Controllers\Admin\LLMController::class, 'generateSEO'])
            ->name('llm.generate-seo');

        // Event URLs
        Route::resource('event-urls', EventUrlController::class);

        // Badge Designs
        Route::resource('badge-designs', \App\Http\Controllers\Admin\BadgeDesignController::class);

        // Gallery Albums & Photos
        Route::resource('gallery-albums', \App\Http\Controllers\Admin\GalleryAlbumController::class);
        Route::resource('gallery', \App\Http\Controllers\Admin\GalleryController::class);
        Route::get('gallery-batch-editor', [\App\Http\Controllers\Admin\GalleryController::class, 'batchEditor'])->name('gallery.batch-editor');
        Route::post('gallery-batch-update', [\App\Http\Controllers\Admin\GalleryController::class, 'batchUpdate'])->name('gallery.batch-update');

        // Marketing Assets
        Route::resource('marketing-assets', \App\Http\Controllers\Admin\MarketingAssetController::class);
        Route::post('marketing-assets/{marketingAsset}/duplicate', [\App\Http\Controllers\Admin\MarketingAssetController::class, 'duplicate'])->name('marketing-assets.duplicate');

        // Ads Management
        Route::resource('ads', \App\Http\Controllers\Admin\AdController::class);
        Route::post('ads/{ad}/toggle-active', [\App\Http\Controllers\Admin\AdController::class, 'toggleActive'])->name('ads.toggle-active');

        // Gallery Settings
        Route::prefix('gallery-settings')->name('gallery-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'index'])->name('index');
            Route::get('/store', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'store'])->name('store');
            Route::post('/store', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'updateStore'])->name('store.update');
            Route::get('/photo-sizes', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'photoSizes'])->name('photo-sizes');
            Route::post('/photo-sizes', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'updatePhotoSizes'])->name('photo-sizes.update');
            Route::get('/presets', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'presets'])->name('presets');
            Route::post('/presets', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'updatePresets'])->name('presets.update');
            Route::get('/branding', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'branding'])->name('branding');
            Route::post('/branding', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'updateBranding'])->name('branding.update');
            Route::get('/forms', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'forms'])->name('forms');
            Route::post('/forms', [\App\Http\Controllers\Admin\GallerySettingsController::class, 'updateForms'])->name('forms.update');
        });

        // Gallery Forms
        Route::resource('gallery-forms', \App\Http\Controllers\Admin\GalleryFormController::class)->except(['index', 'show']);
        Route::post('gallery-forms/{galleryForm}/set-default', [\App\Http\Controllers\Admin\GalleryFormController::class, 'setDefault'])->name('gallery-forms.set-default');

        Route::resource('memberships', MembershipController::class);
        Route::post('memberships/{membership}/toggle', [MembershipController::class, 'toggleActive'])
            ->name('memberships.toggle');
        Route::post('memberships/{membership}/codes', [MembershipController::class, 'storeCode'])
            ->name('memberships.codes.store');
        Route::delete('memberships/{membership}/codes/{code}', [MembershipController::class, 'destroyCode'])
            ->name('memberships.codes.destroy');
        Route::post('memberships/{membership}/codes/import', [MembershipController::class, 'importCodes'])
            ->name('memberships.codes.import');

        // File Manager
        Route::resource('files', FileController::class);
        Route::get('files/{file}/download', [FileController::class, 'download'])
            ->name('files.download');

        // Registration Categories (Categories > List)
        Route::resource('registration-categories', \App\Http\Controllers\Admin\RegistrationCategoryController::class);
        Route::post('registration-categories/{registrationCategory}/types/attach', [\App\Http\Controllers\Admin\RegistrationCategoryController::class, 'attachType'])
            ->name('registration-categories.types.attach');
        Route::delete('registration-categories/{registrationCategory}/types/{categoryType}', [\App\Http\Controllers\Admin\RegistrationCategoryController::class, 'detachType'])
            ->name('registration-categories.types.detach');

        // Registrations Management
        Route::resource('registrations', AdminRegistrationController::class);
        Route::get('registration-drafts/{draft}', [RegistrationDraftController::class, 'show'])
            ->name('registration-drafts.show');
        Route::get('custom-form-answer-files/{file}/download', CustomFormAnswerFileController::class)
            ->name('custom-form-answer-files.download');
        Route::patch('registrations/{registration}/status', [AdminRegistrationController::class, 'updateStatus'])
            ->name('registrations.status.update');
        Route::post('registrations/{registration}/payments', [RegistrationPaymentController::class, 'store'])
            ->name('registrations.payments.store');
        Route::post('registrations/{registration}/payments/{payment}/refund', [RegistrationPaymentController::class, 'refund'])
            ->name('registrations.payments.refund');
        Route::post('registrations/{registration}/payments/{payment}/reverse', [RegistrationPaymentController::class, 'reverse'])
            ->name('registrations.payments.reverse');

        // Check-in
        Route::get('registrations-checkin', [AdminRegistrationController::class, 'showCheckin'])
            ->name('registrations.checkin');
        Route::post('registrations/{registration}/check-in', [AdminRegistrationController::class, 'checkIn'])
            ->name('registrations.check-in');

        // Badge Printing
        Route::get('registrations-badges', [AdminRegistrationController::class, 'showBadges'])
            ->name('registrations.badges');
        Route::get('registrations/{registration}/preview-badge', [AdminRegistrationController::class, 'previewBadge'])
            ->name('registrations.preview-badge');
        Route::post('registrations/{registration}/print-badge', [AdminRegistrationController::class, 'printBadge'])
            ->name('registrations.print-badge');
        Route::post('registrations-print-all-badges', [AdminRegistrationController::class, 'printAllBadges'])
            ->name('registrations.print-all-badges');

        // Export
        Route::get('registrations-export', [AdminRegistrationController::class, 'showExport'])
            ->name('registrations.export-page');
        Route::post('registrations-export', [AdminRegistrationController::class, 'export'])
            ->name('registrations.export');

        // Other actions
        Route::post('registrations/{registration}/resend-verification', [AdminRegistrationController::class, 'resendVerification'])
            ->name('registrations.resend-verification');

        // Agenda Management System
        Route::resource('agenda-management', AgendaManagementController::class)->parameters([
            'agenda-management' => 'agenda',
        ]);
        Route::resource('tracks', TrackController::class);
        Route::resource('locations', LocationController::class);
        Route::resource('speakers', SpeakerController::class);
        Route::resource('sessions', SessionController::class);
        Route::resource('lectures', LectureController::class);

        // Email Campaigns System
        Route::prefix('email-campaigns')->name('email-campaigns.')->group(function () {
            // Templates
            Route::resource('templates', EmailTemplateController::class)->names([
                'index' => 'email-templates.index',
                'create' => 'email-templates.create',
                'store' => 'email-templates.store',
                'show' => 'email-templates.show',
                'edit' => 'email-templates.edit',
                'update' => 'email-templates.update',
                'destroy' => 'email-templates.destroy',
            ])->parameters(['templates' => 'emailTemplate']);

            Route::post('templates/{emailTemplate}/clone', [EmailTemplateController::class, 'clone'])
                ->name('email-templates.clone');
            Route::get('templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])
                ->name('email-templates.preview');

            // Campaigns
            Route::resource('campaigns', EmailCampaignController::class)->names([
                'index' => 'email-campaigns.index',
                'create' => 'email-campaigns.create',
                'store' => 'email-campaigns.store',
                'show' => 'email-campaigns.show',
                'edit' => 'email-campaigns.edit',
                'update' => 'email-campaigns.update',
                'destroy' => 'email-campaigns.destroy',
            ])->parameters(['campaigns' => 'emailCampaign']);

            Route::post('campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send'])
                ->name('email-campaigns.send');
            Route::post('campaigns/{emailCampaign}/pause', [EmailCampaignController::class, 'pause'])
                ->name('email-campaigns.pause');
            Route::post('campaigns/{emailCampaign}/resume', [EmailCampaignController::class, 'resume'])
                ->name('email-campaigns.resume');
            Route::post('campaigns/{emailCampaign}/cancel', [EmailCampaignController::class, 'cancel'])
                ->name('email-campaigns.cancel');

            // Recipient Upload
            Route::get('campaigns/{emailCampaign}/recipients/upload', [RecipientUploadController::class, 'create'])
                ->name('email-campaigns.recipients.create');
            Route::post('campaigns/{emailCampaign}/recipients/upload', [RecipientUploadController::class, 'store'])
                ->name('email-campaigns.recipients.store');
            Route::get('campaigns/{emailCampaign}/recipients/preview', [RecipientUploadController::class, 'preview'])
                ->name('email-campaigns.recipients.preview');
            Route::post('campaigns/{emailCampaign}/recipients/confirm', [RecipientUploadController::class, 'confirm'])
                ->name('email-campaigns.recipients.confirm');

            // Provider Configurations
            Route::resource('provider-configs', EmailProviderConfigController::class)->names([
                'index' => 'provider-configs.index',
                'create' => 'provider-configs.create',
                'store' => 'provider-configs.store',
                'edit' => 'provider-configs.edit',
                'update' => 'provider-configs.update',
                'destroy' => 'provider-configs.destroy',
            ])->parameters(['provider-configs' => 'providerConfig']);

            Route::post('provider-configs/{providerConfig}/test', [EmailProviderConfigController::class, 'test'])
                ->name('provider-configs.test');
        });
    });
});

// Public unsubscribe route (no authentication required)
Route::get('/unsubscribe/{hash}', [UnsubscribeController::class, 'show'])
    ->name('email.unsubscribe');
Route::post('/unsubscribe', [UnsubscribeController::class, 'store'])
    ->name('email.unsubscribe.store');

// Email provider webhook routes (no authentication, signature validation in controller)
Route::post('/event/webhooks/infobip', [EmailWebhookController::class, 'infobip'])
    ->name('webhooks.email.infobip');
Route::post('/event/webhooks/mailchimp', [EmailWebhookController::class, 'mailchimp'])
    ->name('webhooks.email.mailchimp');
