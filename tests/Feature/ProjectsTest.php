<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Facades\Tests\Setup\ProjectFactory;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    // GUEST

    public function test_guest_cannot_create_project(): void
    {
        $project = $this->createRawProject();

        $this->post(route('projects.store'), $project)->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_a_single_project(): void
    {
        $project = $this->createProject();

        $this->get($project->path())->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_create_project_page(): void
    {
        $this->get(route('projects.create'))->assertRedirect(route('login'));
    }


    // AUTHENTICATED USER

    public function test_user_can_create_project(): void
    {
        $this->signIn();

        $this->get(route('projects.create'))->assertStatus(200);

        $attributes = [
            'title' => $this->faker->title,
            'description' => $this->faker->sentence,
            'notes' => $this->faker->text
        ];

        $response = $this->post(route('projects.store'), $attributes);

        $project = Project::where($attributes)->get()->first();

        $response->assertRedirect($project->path());

        // $this->assertDatabaseHas('projects', $attributes);

        $this->get(route('projects.index'))->assertSee($attributes['title']);
    }

    public function test_user_can_update_project(): void
    {
        // $this->signIn();
        //
        // $project = $this->createProject('auth_user');

        $project = ProjectFactory::ownedBy($this->signIn())->create();

        $this->patch($project->path(), ['notes' => 'new notes, right here!']);

        $this->assertDatabaseHas('projects', ['notes' => 'new notes, right here!']);
    }

    public function test_user_cannot_update_project_of_other_user(): void
    {
        $this->signIn();

        $project = $this->createProject();

        $this->patch($project->path(), $data = ['notes' => 'new notes, right here!'])->assertStatus(403);

        $this->assertDatabaseMissing('projects', $data);
    }

    public function test_user_can_view_only_their_projects(): void
    {
        $project = ProjectFactory::ownedBy($this->signIn())->create();

        $otherProject = $this->createProject();

        $this->get(route('projects.index'))
            ->assertSee($project->title)
            ->assertDontSee($otherProject->title);
    }

    public function test_user_can_view_their_single_project(): void
    {
        $project = ProjectFactory::ownedBy($this->signIn())->create();

        $this->get($project->path())
            ->assertSee($project->title)
            ->assertSee($project->description);
    }

    public function test_user_cannot_view_single_project_of_other_user(): void
    {
        $this->signIn();

        $project = $this->createProject();

        $this->get($project->path())->assertStatus(403);
    }

    public function test_project_must_have_title(): void
    {
        $this->signIn();

        $project = $this->createRawProject(['title' => '']);

        $this->post(route('projects.store'), $project)->assertSessionHasErrors('title');
    }

    public function test_project_must_have_description(): void
    {
        $this->signIn();

        $project = $this->createRawProject(['description' => '']);

        $this->post(route('projects.store'), $project)->assertSessionHasErrors('description');
    }
}
