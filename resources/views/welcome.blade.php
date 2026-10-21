<x-main-layout title="Shaghalni - Find your dream job">
    <div x-data="{ show : false }"  x-init = "setTimeout(() => { show = true }, 1000)"  >
        <div class="inline-flex items-center " x-clock x-show="show" x-transition:enter="transition ease-out duration-1000" 
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
         <h4 class="text-sm text-white/60 rounded-full bg-white/10 px-3 py-1 w-fit">Shaghalni</h4> 
        </div>
    </div>
    
    <div x-data="{ show : false }"  x-init = "setTimeout(() => { show = true }, 1000)"  >
        <div class="inline-flex" x-clock x-show="show" x-transition:enter="transition ease-out duration-1000" 
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
            <h1 class="text-4xl sm:text-6xl md:text-8xl mb-6 tracking-tight font-bold">
                <span class="text-white">Find Your</span> <br>
                <span class="text-white/60 font-serif italic">Dream Job</span>
            </h1>
        </div>
    </div>

    <div x-data="{ show : false }"  x-init = "setTimeout(() => { show = true }, 1000)"  >
        <div class="mb-6" x-clock x-show="show" x-transition:enter="transition ease-out duration-1000" 
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
            <p class="text-white/60 text-lg">connect with top employers, and find exiciting opportunities</p>
        </div>
    </div>
    
    <div x-data="{ show : false }"  x-init = "setTimeout(() => { show = true }, 1000)"  >
        <div class="inline-flex" x-clock x-show="show" x-transition:enter="transition ease-out duration-1000" 
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
            <a href="{{ route('register') }}" class="text-white bg-white/10 text-lg px-4 py-2 rounded-lg mr-4">Create an account</a>
            <a href="{{ route('login') }}" class="text-white bg-gradient-to-r from-indigo-500 to-rose-500 text-lg px-4 py-2 rounded-lg">Login</a>
        </div>
    </div>



    </x-main-layout>