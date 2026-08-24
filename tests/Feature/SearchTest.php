<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_search_api()
    {
        $response = $this->getJson('/api/search?q=test');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_search_members()
    {
        $member = Member::create([
            'name' => 'John Doe',
            'phone' => '03001234567',
            'gender' => 'male',
            'joining_date' => now()->toDateString(),
            'status' => 'active'
        ]);
        
        $response = $this->actingAs($this->user)->getJson('/api/search?q=John');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'John Doe']);
    }

    public function test_authenticated_user_can_search_trainers()
    {
        $trainer = Trainer::create([
            'name' => 'Jane Smith',
            'phone' => '03007654321',
            'gender' => 'female',
            'specialization' => 'Yoga',
            'joining_date' => now()->toDateString(),
            'status' => 'active',
            'salary' => 50000
        ]);
        
        $response = $this->actingAs($this->user)->getJson('/api/search?q=Jane');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Jane Smith']);
    }

    public function test_authenticated_user_can_search_membership_plans()
    {
        $plan = MembershipPlan::create([
            'name' => 'Premium Plan',
            'price' => 5000,
            'duration_days' => 30,
            'is_active' => true
        ]);
        
        $response = $this->actingAs($this->user)->getJson('/api/search?q=Premium');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Premium Plan']);
    }

    public function test_search_with_empty_query_returns_empty_results()
    {
        $response = $this->actingAs($this->user)->getJson('/api/search?q=');
        
        $response->assertStatus(200);
        $response->assertExactJson([
            'members' => [],
            'trainers' => [],
            'plans' => []
        ]);
    }
}
