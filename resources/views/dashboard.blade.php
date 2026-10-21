<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
       <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            <h3 class="text-white text-2xl font-bold">
                welcome, {{ Auth::user()->name }}!
            </h3>

            <!-- Search & filters -->
            <div class="flex items-center justify-between">
                <!-- Search bar -->
                <form action="{{route('dashboard')}}" method="get" class="flex items-center justify-center w-1/4">
                    <input type="text" name="search" value="{{request('search')}}" placeholder="search" class="w-full p-2 rounded-l-lg bg-gray-800 text-white">
                    <button type="submit" class="bg-indigo-500 text-white rounded-r-lg p-2 border border-indigo-500">Search</button>

                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{request('filter')}}">
                    @endif

                    @if(request('search'))
                        <a href="{{ route('dashboard', ['filter' => request('filter')]) }}" class="text-white rounded-lg p-2 hover:underline">Clear</a>
                    @endif
                </form>
                <!-- filters -->
                 <div>
                    <a href="{{ route('dashboard', ['filter' => 'hybrid' , 'search' => request('search')]) }}" class="bg-indigo-500 text-white rounded-lg p-2 mr-1">Hybrid</a>
                    <a href="{{ route('dashboard', ['filter' => 'remote' , 'search' => request('search')]) }}" class="bg-indigo-500 text-white rounded-lg p-2 mr-1">Remote</a>
                    <a href="{{ route('dashboard', ['filter' => 'full-time' , 'search' => request('search')]) }}" class="bg-indigo-500 text-white rounded-lg p-2 mr-1">Full Time</a>
                    <a href="{{ route('dashboard', ['filter' => 'contract' , 'search' => request('search')]) }}" class="bg-indigo-500 text-white rounded-lg p-2 mr-1">Contract</a>
                    @if(request('filter'))
                        <a href="{{ route('dashboard', ['search' => request('search')]) }}" class="text-white rounded-lg p-2 hover:underline">Clear Filter</a>
                    @endif
                 </div>

            </div>

            <!-- job list -->
            <div class="space-y-4 mt-6">
                <!--job item -->
                @forelse($jobs as $job)
                <div class="border-b border-white/10 pb-4 flex items-center justify-between">
                    <div>
                        <a href="{{route('job-vacancies.show', $job->id)}}" class="text-blue-400 text-lg font-semibold hover:underline">{{$job->title}}</a>
                        <p class="text-white text-sm">{{$job->company->name}} - {{$job->location}}</p>
                        <p class="text-white text-sm">{{'$'. number_format($job->salary)}} /Year</p>
                    </div>
                    
                    <span class="bg-indigo-500 text-white rounded-lg p-2">{{ucfirst($job->type)}}</span>
                </div>
                @empty
                    <p class="text-white text-center">No jobs found matching your search.</p>
                @endforelse
                
            </div>

            <!-- pagination -->
            <div class="mt-6">
                {{$jobs->links()}}
            </div>
        </div>
    </div>
</x-app-layout>
