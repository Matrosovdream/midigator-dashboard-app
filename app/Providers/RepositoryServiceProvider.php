<?php

namespace App\Providers;

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
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
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

    public function register(): void
    {
        foreach ($this->repos as $repo) {
            $this->app->scoped($repo);
        }
    }
}
