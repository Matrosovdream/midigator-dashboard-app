<?php

namespace Tests\Feature\Repositories\Notification;

use App\Repositories\Notification\NotificationSettingRepo;
use Tests\Feature\Repositories\RepoTestCase;

class NotificationSettingRepoTest extends RepoTestCase
{
    public function test_get_or_default_creates_with_defaults(): void
    {
        $user = $this->makeUser($this->makeTenant()->id);
        $repo = app(NotificationSettingRepo::class);

        $settings = $repo->getOrDefaultForUser($user->id);
        $this->assertSame('both', $settings['channel']);
        $this->assertTrue($settings['chargeback_new']);
        $this->assertFalse($settings['daily_digest']);
    }

    public function test_update_for_user_is_upsert(): void
    {
        $user = $this->makeUser($this->makeTenant()->id);
        $repo = app(NotificationSettingRepo::class);

        $repo->updateForUser($user->id, ['channel' => 'email', 'daily_digest' => true]);
        $repo->updateForUser($user->id, ['weekly_report' => true]);

        $row = $repo->getOrDefaultForUser($user->id);
        $this->assertSame('email', $row['channel']);
        $this->assertTrue($row['daily_digest']);
        $this->assertTrue($row['weekly_report']);
        $this->assertSame(1, $repo->count(['user_id' => $user->id]));
    }
}
