<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index() {
        $projects = Project::all();

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project) {

        return view('projects.show', compact('project'));
    }

    public function store()
    {
        $attributes = request()->validate(['title' => 'required', 'description' => 'required']);

        Project::create(request(['title', 'description']));

        return redirect()->route('projects.index');
    }
}
