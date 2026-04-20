<?php

namespace Tests\Feature\Repositories\Notification;

use App\Models\Chargeback;
use App\Repositories\Notification\NotificationRepo;
use Tests\Feature\Repositories\RepoTestCase;

class NotificationRepoTest extends RepoTestCase
{
    public function test_notify_creates_uuid_keyed_record(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $repo = app(NotificationRepo::class);

        $entry = $repo->notify($tenant->id, $user->id, 'chargeback.new', 'New CB', 'A new chargeback arrived');

        $this->assertIsString($entry['id']);
        $this->assertSame(36, strlen($entry['id']));
        $this->assertNull($entry['read_at']);
    }

    public function test_unread_for_user_and_mark_read(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $repo = app(NotificationRepo::class);

        $a = $repo->notify($tenant->id, $user->id, 't', 'A', 'a');
        $repo->notify($tenant->id, $user->id, 't', 'B', 'b');

        $unread = $repo->getUnreadForUser($user->id);
        $this->assertCount(2, $unread['items']);

        $repo->markRead($a['id']);
        $this->assertCount(1, $repo->getUnreadForUser($user->id)['items']);
    }

    public function test_mark_all_read(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $repo = app(NotificationRepo::class);

        $repo->notify($tenant->id, $user->id, 't', 'A', 'a');
        $repo->notify($tenant->id, $user->id, 't', 'B', 'b');

        $count = $repo->markAllReadForUser($user->id);
        $this->assertSame(2, $count);
        $this->assertCount(0, $repo->getUnreadForUser($user->id)['items']);
    }

    public function test_polymorphic_notifiable(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $cb = Chargeback::create([
            'tenant_id' => $tenant->id,
            'chargeback_guid' => 'cb-n-1',
            'event_guid' => 'evt-n-1',
            'mid' => 'M',
            'amount' => 1,
        ]);
        $repo = app(NotificationRepo::class);

        $entry = $repo->notify($tenant->id, $user->id, 'cb.assigned', 'Assigned', 'You got a CB', $cb);
        $this->assertSame($cb->getMorphClass(), $entry['notifiable_type']);
        $this->assertSame($cb->id, $entry['notifiable_id']);
    }
}
