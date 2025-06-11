<?php

namespace Tests\Unit\Jobs\User;

use App\Jobs\User\CreateUserJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched()
    {
        Bus::fake();

        $job = new CreateUserJob('Test User', 'test@example.com', 'secret123');
        dispatch($job);

        Bus::assertDispatched(CreateUserJob::class);
    }

    public function test_handle_creates_user()
    {
        $job = new CreateUserJob('Test User', 'test@example.com', 'secret123');
        $job->handle();

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('secret123', $user->password));
    }
}
