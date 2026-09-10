@props(['name', 'title'])

<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-slate-900/50"
         @click="open = false"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl"
         @click.stop>
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>
            <button type="button" @click="open = false"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <span class="sr-only">Fermer</span>
                ✕
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
