<?php

namespace Tests\Feature\Repositories\Email;

use App\Models\Chargeback;
use App\Repositories\Email\EmailLogRepo;
use Tests\Feature\Repositories\RepoTestCase;

class EmailLogRepoTest extends RepoTestCase
{
    public function test_log_for_model_and_mark_status(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $cb = Chargeback::create([
            'tenant_id' => $tenant->id,
            'chargeback_guid' => 'cb-em-1',
            'event_guid' => 'evt-em-1',
            'mid' => 'M',
            'amount' => 1,
        ]);
        $repo = app(EmailLogRepo::class);

        $entry = $repo->logForModel($cb, $tenant->id, $user->id, [
            'to_email' => 'cust@x.test',
            'subject' => 'Hi',
            'body' => 'Hello',
        ]);

        $this->assertSame('queued', $entry['status']);
        $this->assertSame('cust@x.test', $entry['to_email']);

        $updated = $repo->markStatus($entry['id'], 'sent', new \DateTimeImmutable());
        $this->assertSame('sent', $updated['status']);
        $this->assertNotNull($updated['sent_at']);

        $forModel = $repo->getForModel($cb);
        $this->assertCount(1, $forModel['items']);
    }
}
