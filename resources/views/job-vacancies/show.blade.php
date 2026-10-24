<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __($jobVacancy->title) }} - Job Details
        </h2>
    </x-slot>
      <h1> Views: {{ var_export($jobVacancy->view_count, true) }}</h1>
    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            <a href="{{ route('dashboard') }}" class="text-white rounded-lg bg-blue-500/80 hover:bg-blue-500 p-2 mb-6 inline-block"> ← Back to Jobs</a>

            <div class="border-b border-white/10 pb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{$jobVacancy->title}}</h1>
                        <p class="text-md text-gray-400">{{$jobVacancy->company->name}}</p>
                        <div class="text-sm flex items-center gap-2 ">
                            <p class="text-gray-400">{{$jobVacancy->location}}</p>
                            <span class="text-gray-400">.</span>
                            <p class="text-gray-400">{{'$' . number_format($jobVacancy->salary) . '/year'}}</p>
                            <p class="bg-indigo-500 text-white rounded-lg p-2 mr-1">{{$jobVacancy->type}}</p>
                        </div>
    
                    </div>

                    <!-- Apply Button-->
                    <div>
                        <a href="{{ route('job-vacancies.apply', $jobVacancy->id) }}"
                         class="justify-center bg-gradient-to-r from-indigo-500 to-rose-500 text-white px-4 py-2 rounded-lg transition hover:from-indigo-600 hover:to-rose-600">
                         Apply now
                        </a>
                    </div>
                </div>
    
            </div>
            <!-- Job Description-->
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="col-span-2 text-gray-400">
                    <h2 class="font-bold text-lg text-white">Job Description</h2>
                    <p class="text-gray-400">{{$jobVacancy->description}}</p>
                </div>

                <div class="col-span-1">
                    <h2 class="font-bold text-lg text-white">Job Overview</h2>
                    <div class="bg-gray-900 p-6 rounded-lg space-y-4">
                        <div>
                            <p class="text-gray-400">Published date</p>
                            <p class="text-white">{{$jobVacancy->created_at->format('M d Y')}}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Company</p>
                            <p class="text-white">{{$jobVacancy->company->name}}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Location</p>
                            <p class="text-white">{{$jobVacancy->location}}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Type</p>
                            <p class="text-white">{{$jobVacancy->type}}</p>
                        </div>
                        
                        <div>
                            <p class="text-gray-400">Salary</p>
                            <p class="text-white">{{'$' . number_format($jobVacancy->salary) . '/year'}}</p>
                        </div>

                        <div>
                            <p class="text-gray-400">Job Category</p>
                            <p class="text-white">{{$jobVacancy->JobCategory->name}}</p>
                        </div>
                    </div>

                    
                </div>
            </div>

        </div>

    </div>
</x-app-layout>