<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __($jobVacancy->title) }} - Apply
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            <a href="{{ route('job-vacancies.show', $jobVacancy->id) }}"
                class="text-white rounded-lg bg-blue-500/80 hover:bg-blue-500 p-2 mb-6 inline-block">
                ← Back to Job Details
            </a>

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


                </div>

            </div>



            <form action="{{route('job-vacancies.processApplication', $jobVacancy->id)}}" method="POST" class="space-y-6" enctype="multipart/form-data">
                @csrf

                @if($errors->any())
                <div class="bg-red-500 text-white p-4 rounded-lg">
                    <ul class="list-disc list-inside ">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <!-- Resume Selection -->
                <div>
                    <h3 class="text-lg font-semibold text-white">Choose your resume :</h3>

                    <div class="mb-6">
                        <x-input-label for="resume" value="Selcet from your existing resumes:"></x-input-label>
                        <!-- list of resumes -->
                        <div>
                            @forelse($resumes as $resume)
                            <div class="flex items-center gap-2">
                                <input type="radio" name="resume_option" value="{{$resume->id}}" id="{{$resume->id}}" 
                                @error('resume_option') class="border-red-500" @else class="border-gray-600" @enderror>
                                <x-input-label for="existing_{{$resume->id}}" class="text-white cursor-pointer">
                                    {{$resume->fileName}}
                                    <span class="text-white-400 text-sm">(Last updated: {{ $resume->updated_at->format('M d, Y') }})</span>
                                </x-input-label>


                            </div>
                            @empty
                            <div class="text-white-400 text-sm">No resumes found.</div>
                            @endforelse

                        </div>
                    </div>

                </div>

                <!-- upload new resume  -->
                <div x-data="{fileName: '' ,hasError: '{{$errors ->has('resume_file') ? 'true':'false' }}'}">
                    <div class="flex items-center gap-2">
                        <input x-ref="newResumeRadio" type="radio" name="resume_option" value="new_resume" id="new_resume"               
                        @error('resume_option') class="border-red-500" @else class="border-gray-600" @enderror>
                        <x-input-label class="text-white cursor-pointer" for="new_resume" value="or upload new resume:"></x-input-label>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1">
                            <label for="new_resume_file" class="block text-white cursor-pointer">
                                <div
                                    class="border-2 border-dashed border-gray-600 rounded-lg p-4 hover:border-blue-500 transition"
                                    :class="{'border-blue-500 : fileName, 'border-red-500': hasError }">
                                    <input @change="fileName = event.target.files[0].name; $refs.newResumeRadio.checked=true"
                                        type="file" id="new_resume_file" name="resume_file" class="hidden" accept=".pdf">
                                    <div class="text-center">
                                        <template x-if="!fileName">
                                            <p>click to upload PDF (MAX 5MB)</p>
                                        </template>
                                        <template x-if="fileName">
                                            <div>
                                                <p x-text="fileName" class="mt-2 text-blue-400"></p>
                                                <p class="text-sm mt-1 text-gray-400">Click to upload a different file</p>
                                            </div>
                                        </template>

                                    </div>

                                </div>

                            </label>

                        </div>
                    </div>
                </div>

                <!-- submit button -->
                <div>
                    <x-primary-button type="submit" class="w-full">Apply Now</x-primary-button>
                </div>

            </form>

        </div>

    </div>
</x-app-layout>