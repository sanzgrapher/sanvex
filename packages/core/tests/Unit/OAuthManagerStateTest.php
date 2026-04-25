<?php

namespace Sanvex\Core\Tests\Unit;

use Sanvex\Core\Auth\OAuthManager;
use Sanvex\Core\Tenancy\Owner;
use Sanvex\Core\Tests\CoreTestCase;

class OAuthManagerStateTest extends CoreTestCase
{
    public function test_verify_state_returns_owner_on_valid_state(): void
    {
        $manager = new OAuthManager('notion');

        $owner = new Owner('App\\Models\\User', '42');
        $state = $manager->buildState($owner);

        $resolved = $manager->verifyState($state);

        $this->assertNotNull($resolved);
        $this->assertSame('App\\Models\\User', $resolved->type());
        $this->assertSame('42', $resolved->id());
    }

    public function test_verify_state_returns_null_on_replay(): void
    {
        $manager = new OAuthManager('notion');
        $state   = $manager->buildState();

        $manager->verifyState($state);
        $second = $manager->verifyState($state);

        $this->assertNull($second);
    }

    public function test_verify_state_returns_null_on_tampered_signature(): void
    {
        $manager = new OAuthManager('notion');
        $state   = $manager->buildState();

        $tampered = $state.'X';

        $this->assertNull($manager->verifyState($tampered));
    }

    public function test_verify_state_returns_null_on_malformed_state(): void
    {
        $manager = new OAuthManager('notion');

        $this->assertNull($manager->verifyState('not-a-valid-state'));
        $this->assertNull($manager->verifyState(''));
    }

    public function test_build_state_encodes_global_owner_by_default(): void
    {
        $manager  = new OAuthManager('github');
        $state    = $manager->buildState();
        $resolved = $manager->verifyState($state);

        $this->assertNotNull($resolved);
        $this->assertTrue($resolved->isGlobal());
    }

    public function test_build_state_throws_when_app_key_missing(): void
    {
        config()->set('app.key', null);

        $manager = new OAuthManager('github');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OAuth state signing requires a non-empty app.key configuration value.');

        $manager->buildState();
    }
}
