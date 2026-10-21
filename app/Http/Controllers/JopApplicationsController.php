<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;

class JopApplicationsController extends Controller
{
    public function index()
    {
        $jobApplications = JobApplication::where('user_id', auth()->id())->paginate(10);
        return view('job-applications.index', compact('jobApplications'));
    }
}
