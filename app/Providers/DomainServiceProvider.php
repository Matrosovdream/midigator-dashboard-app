<?php

namespace App\Providers;

use App\Services\Activity\ActivityLogService;
use App\Services\Cases\CaseAssignmentService;
use App\Services\Cases\CaseHideService;
use App\Services\Cases\CaseQueryService;
use App\Services\Cases\CaseRegistry;
use App\Services\Cases\CaseStageService;
use App\Services\Comments\CommentService;
use App\Services\Emails\EmailRenderService;
use App\Services\Emails\EmailSendService;
use App\Services\Emails\EmailTemplateService;
use App\Services\Export\ExportService;
use App\Services\Manager\ManagerDashboardService;
use App\Services\Midigator\AuthService as MidigatorAuthService;
use App\Services\Midigator\EventSubscriptionService as MidigatorEventSubscriptionService;
use App\Services\Midigator\MidigatorClient;
use App\Services\Midigator\OrderService as MidigatorOrderService;
use App\Services\Midigator\PreventionService as MidigatorPreventionService;
use App\Services\Midigator\WebhookProcessor;
use App\Services\Notifications\NotificationService;
use App\Services\Platform\ActivityLogService as PlatformActivityLogService;
use App\Services\Platform\EmailLogService as PlatformEmailLogService;
use App\Services\Platform\EmailTemplateService as PlatformEmailTemplateService;
use App\Services\Platform\ImpersonationService;
use App\Services\Platform\IntegrationHealthService;
use App\Services\Platform\OverviewService as PlatformOverviewService;
use App\Services\Platform\RightsCatalogService;
use App\Services\Platform\SettingsService as PlatformSettingsService;
use App\Services\Platform\TenantService as PlatformTenantService;
use App\Services\Platform\UserService as PlatformUserService;
use App\Services\Platform\WebhookLogService as PlatformWebhookLogService;
use App\Services\Search\SearchService;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Domain service bindings — scoped so per-request state does not leak across Octane requests.
     */
    protected array $services = [
        // Midigator
        MidigatorAuthService::class,
        MidigatorClient::class,
        MidigatorOrderService::class,
        MidigatorPreventionService::class,
        MidigatorEventSubscriptionService::class,
        WebhookProcessor::class,
        // Cases
        CaseRegistry::class,
        CaseStageService::class,
        CaseAssignmentService::class,
        CaseHideService::class,
        CaseQueryService::class,
        // Comments
        CommentService::class,
        // Emails
        EmailRenderService::class,
        EmailTemplateService::class,
        EmailSendService::class,
        // Notifications
        NotificationService::class,
        // Activity / Search / Export
        ActivityLogService::class,
        SearchService::class,
        ExportService::class,
        // Manager dashboard
        ManagerDashboardService::class,
        // Platform (superadmin)
        PlatformTenantService::class,
        PlatformOverviewService::class,
        PlatformUserService::class,
        PlatformWebhookLogService::class,
        IntegrationHealthService::class,
        RightsCatalogService::class,
        PlatformActivityLogService::class,
        PlatformEmailLogService::class,
        PlatformEmailTemplateService::class,
        PlatformSettingsService::class,
        ImpersonationService::class,
    ];

    public function register(): void
    {
        foreach ($this->services as $service) {
            $this->app->scoped($service);
        }
    }
}
