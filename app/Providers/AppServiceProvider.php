<?php

namespace App\Providers;

use App\Models\Chargeback;
use App\Models\PreventionAlert;
use App\Models\User;
use App\Observers\ChargebackObserver;
use App\Observers\PreventionObserver;
use App\Observers\UserObserver;
use App\Repositories\Activity\ActivityLogRepo;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Comment\CommentRepo;
use App\Repositories\Email\EmailLogRepo;
use App\Repositories\Email\EmailTemplateRepo;
use App\Repositories\Notification\NotificationRepo;
use App\Repositories\Notification\NotificationSettingRepo;
use App\Repositories\Order\OrderRepo;
use App\Repositories\Order\OrderValidationRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;
use App\Repositories\Tenant\SiteSettingRepo;
use App\Repositories\Tenant\TenantRepo;
use App\Repositories\User\ManagerProfileRepo;
use App\Repositories\User\RightRepo;
use App\Repositories\User\RoleRepo;
use App\Repositories\User\UserRepo;
use App\Repositories\Webhook\WebhookLogRepo;
use App\Repositories\Workflow\StageTransitionRepo;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Repository bindings — scoped (one instance per request).
     * Scoped (not singleton) so Octane workers don't leak mutable repo state (e.g. $withRelations) across requests.
     * Register every concrete Repo here so Services and Actions can constructor-inject them.
     */
    protected array $repos = [
        // User domain
        UserRepo::class,
        RoleRepo::class,
        RightRepo::class,
        ManagerProfileRepo::class,
        // Tenant
        TenantRepo::class,
        SiteSettingRepo::class,
        // Midigator data
        ChargebackRepo::class,
        PreventionAlertRepo::class,
        OrderRepo::class,
        OrderValidationRepo::class,
        RdrCaseRepo::class,
        // Workflow / collaboration
        CommentRepo::class,
        StageTransitionRepo::class,
        ActivityLogRepo::class,
        // Comms
        EmailTemplateRepo::class,
        EmailLogRepo::class,
        WebhookLogRepo::class,
        NotificationRepo::class,
        NotificationSettingRepo::class,
    ];

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
        foreach ($this->repos as $repo) {
            $this->app->scoped($repo);
        }

        foreach ($this->services as $service) {
            $this->app->scoped($service);
        }
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isPlatformAdmin') && $user->isPlatformAdmin()) {
                return true;
            }
            return null;
        });

        Gate::define('right', function ($user, string $right) {
            return method_exists($user, 'hasRight') && $user->hasRight($right);
        });

        Chargeback::observe(ChargebackObserver::class);
        PreventionAlert::observe(PreventionObserver::class);
        User::observe(UserObserver::class);
    }
}
