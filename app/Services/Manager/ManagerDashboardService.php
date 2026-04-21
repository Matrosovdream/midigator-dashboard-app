<?php

namespace App\Services\Manager;

use App\Repositories\Activity\ActivityLogRepo;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;
use App\Repositories\User\ManagerProfileRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Support\Facades\DB;

class ManagerDashboardService
{
    public function __construct(
        private ChargebackRepo $chargebackRepo,
        private PreventionAlertRepo $preventionRepo,
        private RdrCaseRepo $rdrRepo,
        private UserRepo $userRepo,
        private ManagerProfileRepo $managerProfileRepo,
        private ActivityLogRepo $activityLogRepo,
    ) {}

    public function home(int $tenantId): array
    {
        $openStages = ['new', 'in_review', 'action_taken', 'responded'];
        $activeAlertStages = ['new', 'in_review'];
        $slaCutoff = now()->addDays(2);

        $teamIds = $this->teamUserIds($tenantId);
        $team = [
            'total' => count($teamIds),
            'active' => (int) DB::table('users')->where('tenant_id', $tenantId)->whereIn('id', $teamIds ?: [0])->where('is_active', true)->count(),
            'avg_score' => $this->avgTeamScore($teamIds),
        ];

        $unassignedChargebacks = $this->chargebackRepo->count([
            'tenant_id' => $tenantId,
            'assigned_to' => ['CONDITION' => 'NULL'],
            'stage' => ['CONDITION' => 'IN', ...$openStages],
        ]);
        $unassignedPreventions = $this->preventionRepo->count([
            'tenant_id' => $tenantId,
            'assigned_to' => ['CONDITION' => 'NULL'],
            'stage' => ['CONDITION' => 'IN', ...$activeAlertStages],
        ]);
        $unassignedRdr = $this->rdrRepo->count([
            'tenant_id' => $tenantId,
            'assigned_to' => ['CONDITION' => 'NULL'],
            'stage' => ['CONDITION' => 'IN', ...$activeAlertStages],
        ]);

        $approvalsPending = $this->approvalsPendingCount($tenantId);

        $slaAtRisk = (int) $this->chargebackRepo->getModel()
            ->where('tenant_id', $tenantId)
            ->whereIn('stage', $openStages)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), $slaCutoff])
            ->count();

        $won = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'result' => 'won']);
        $lost = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'result' => 'lost']);
        $resolved = $won + $lost;

        return [
            'tenant_id' => $tenantId,
            'team' => $team,
            'queues' => [
                'unassigned_chargebacks' => $unassignedChargebacks,
                'unassigned_preventions' => $unassignedPreventions,
                'unassigned_rdr' => $unassignedRdr,
                'approvals_pending' => $approvalsPending,
            ],
            'sla' => ['at_risk' => $slaAtRisk],
            'win_rate' => $resolved ? round(($won / $resolved) * 100, 1) : null,
            'recent_activity' => $this->activityLogRepo->getForTenant($tenantId, 10)['items'] ?? [],
        ];
    }

    public function team(int $tenantId, int $perPage = 50): array
    {
        $teamIds = $this->teamUserIds($tenantId);
        if (empty($teamIds)) {
            return ['items' => []];
        }

        $users = $this->userRepo->getAll(
            ['tenant_id' => $tenantId, 'id' => ['CONDITION' => 'IN', ...$teamIds]],
            $perPage,
            ['name' => 'asc'],
        );

        $since30d = now()->subDays(30);
        $items = ($users['items'] ?? collect())->map(function ($user) use ($since30d) {
            $profile = $this->managerProfileRepo->getByUserID($user['id']);

            $open = (int) DB::table('chargebacks')
                ->where('tenant_id', $user['tenant_id'])
                ->where('assigned_to', $user['id'])
                ->where('result', 'pending')
                ->count();
            $resolved30d = (int) DB::table('chargebacks')
                ->where('tenant_id', $user['tenant_id'])
                ->where('assigned_to', $user['id'])
                ->whereIn('result', ['won', 'lost'])
                ->where('updated_at', '>=', $since30d)
                ->count();
            $won = (int) DB::table('chargebacks')
                ->where('tenant_id', $user['tenant_id'])
                ->where('assigned_to', $user['id'])
                ->where('result', 'won')
                ->count();
            $lost = (int) DB::table('chargebacks')
                ->where('tenant_id', $user['tenant_id'])
                ->where('assigned_to', $user['id'])
                ->where('result', 'lost')
                ->count();
            $total = $won + $lost;

            return [
                'user_id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'is_active' => $user['is_active'],
                'last_login_at' => $user['last_login_at'],
                'score' => $profile['score'] ?? null,
                'open_cases' => $open,
                'resolved_30d' => $resolved30d,
                'win_rate' => $total ? round(($won / $total) * 100, 1) : null,
            ];
        });

        return ['items' => $items->values()->all()];
    }

    public function assignments(int $tenantId, int $perPage = 50): array
    {
        $openStages = ['new', 'in_review', 'action_taken', 'responded'];
        $activeAlertStages = ['new', 'in_review'];

        $chargebacks = $this->chargebackRepo->getForTenant(
            $tenantId,
            ['assigned_to' => ['CONDITION' => 'NULL'], 'stage' => ['CONDITION' => 'IN', ...$openStages]],
            $perPage,
            ['created_at' => 'desc'],
        );

        $preventions = $this->preventionRepo->getForTenant(
            $tenantId,
            ['assigned_to' => ['CONDITION' => 'NULL'], 'stage' => ['CONDITION' => 'IN', ...$activeAlertStages]],
            $perPage,
            ['created_at' => 'desc'],
        );

        $rdr = $this->rdrRepo->getForTenant(
            $tenantId,
            ['assigned_to' => ['CONDITION' => 'NULL'], 'stage' => ['CONDITION' => 'IN', ...$activeAlertStages]],
            $perPage,
            ['created_at' => 'desc'],
        );

        $teamIds = $this->teamUserIds($tenantId);
        $team = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $teamIds ?: [0])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($u) => ['id' => (int) $u->id, 'name' => $u->name])
            ->all();

        return [
            'chargebacks' => array_values(($chargebacks['items'] ?? collect())->all()),
            'preventions' => array_values(($preventions['items'] ?? collect())->all()),
            'rdr' => array_values(($rdr['items'] ?? collect())->all()),
            'team' => $team,
        ];
    }

    public function approvals(int $tenantId, int $perPage = 50): array
    {
        $items = DB::table('stage_transitions as st')
            ->join('users as u', 'u.id', '=', 'st.user_id')
            ->where('u.tenant_id', $tenantId)
            ->whereNull('st.to_stage')
            ->orderBy('st.created_at', 'desc')
            ->limit($perPage)
            ->get([
                'st.id', 'st.trackable_type', 'st.trackable_id',
                'st.from_stage', 'st.to_stage', 'st.notes', 'st.created_at',
                'u.id as user_id', 'u.name as user_name',
            ])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'kind' => $this->kindFromMorph($r->trackable_type),
                'target_id' => (int) $r->trackable_id,
                'case_number' => null,
                'from_stage' => $r->from_stage,
                'to_stage' => $r->to_stage,
                'requested_by' => ['id' => (int) $r->user_id, 'name' => $r->user_name],
                'notes' => $r->notes,
                'created_at' => $r->created_at,
            ])
            ->all();

        return ['items' => $items];
    }

    public function activity(int $tenantId, int $perPage = 50): array
    {
        $items = $this->activityLogRepo->getForTenant($tenantId, $perPage);
        return ['items' => array_values(($items['items'] ?? collect())->all())];
    }

    private function teamUserIds(int $tenantId): array
    {
        return DB::table('users as u')
            ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('u.tenant_id', $tenantId)
            ->whereIn('r.slug', ['manager', 'analyst', 'viewer'])
            ->distinct()
            ->pluck('u.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function avgTeamScore(array $userIds): ?float
    {
        if (empty($userIds)) return null;

        $avg = DB::table('manager_profiles')
            ->whereIn('user_id', $userIds)
            ->whereNotNull('score')
            ->avg('score');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    private function approvalsPendingCount(int $tenantId): int
    {
        return (int) DB::table('stage_transitions as st')
            ->join('users as u', 'u.id', '=', 'st.user_id')
            ->where('u.tenant_id', $tenantId)
            ->whereNull('st.to_stage')
            ->count();
    }

    private function kindFromMorph(?string $morph): string
    {
        return match ($morph) {
            'App\\Models\\Chargeback', 'chargeback' => 'chargeback',
            'App\\Models\\PreventionAlert', 'prevention' => 'prevention',
            'App\\Models\\RdrCase', 'rdr' => 'rdr',
            'App\\Models\\OrderValidation', 'order_validation' => 'order_validation',
            default => (string) $morph,
        };
    }
}
