@props(['messages' => []])

@php
    $messages = $messages ?: [
        'error' => session('error'),
        'success' => session('success'),
        'warning' => session('warning'),
        'info' => session('info'),
    ];
    $messages = array_filter($messages);
@endphp

<!-- Global Toast Container -->
<div wire:ignore
     x-data="{
         toasts: [],
         init() {
             // Add session-based toasts on load
             @foreach($messages as $type => $message)
                this.showToast('{{ $message }}', '{{ $type }}');
             @endforeach

             // Listen for dynamic toasts from Livewire
             window.addEventListener('showToast', (event) => {
                 this.showToast(event.detail.message, event.detail.type);
             });
         },
         showToast(message, type = 'info') {
             const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
             const duration = 5000;
             this.toasts.push({ message, type, id, duration, visible: true });
             setTimeout(() => {
                 const toast = this.toasts.find(t => t.id === id);
                 if (toast) toast.visible = false;
                 setTimeout(() => {
                     this.toasts = this.toasts.filter(t => t.id !== id);
                 }, 300);
             }, duration);
         },
         dismissToast(id) {
             const toast = this.toasts.find(t => t.id === id);
             if (toast) toast.visible = false;
             setTimeout(() => {
                 this.toasts = this.toasts.filter(t => t.id !== id);
             }, 300);
         }
     }"
     class="fixed top-5 right-5 z-50 flex flex-col gap-3">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transform ease-out duration-400 transition"
             x-transition:enter-start="translate-x-full opacity-0 scale-95"
             x-transition:enter-end="translate-x-0 opacity-100 scale-100"
             x-transition:leave="transform ease-in duration-300 transition"
             x-transition:leave-start="translate-x-0 opacity-100 scale-100"
             x-transition:leave-end="translate-x-full opacity-0 scale-95"
             :class="{
                 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/50': toast.type === 'success',
                 'border-red-500 bg-red-50 dark:bg-red-950/50': toast.type === 'error',
                 'border-amber-500 bg-amber-50 dark:bg-amber-950/50': toast.type === 'warning',
                 'border-blue-500 bg-blue-50 dark:bg-blue-950/50': toast.type === 'info'
             }"
             class="relative overflow-hidden border-l-4 rounded-lg shadow-lg shadow-black/10 dark:shadow-black/30 backdrop-blur-sm min-w-80 max-w-md ring-1 ring-black/5 dark:ring-white/10">

            <!-- Content -->
            <div class="flex items-start gap-3 px-4 py-3.5">
                <!-- Icon -->
                <div class="shrink-0 mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-emerald-500/15 dark:bg-emerald-500/20">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-red-500/15 dark:bg-red-500/20">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-500/15 dark:bg-amber-500/20">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-500/15 dark:bg-blue-500/20">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
                            </svg>
                        </div>
                    </template>
                </div>

                <!-- Text -->
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide mb-0.5"
                       :class="{
                           'text-emerald-800 dark:text-emerald-300': toast.type === 'success',
                           'text-red-800 dark:text-red-300': toast.type === 'error',
                           'text-amber-800 dark:text-amber-300': toast.type === 'warning',
                           'text-blue-800 dark:text-blue-300': toast.type === 'info'
                       }"
                       x-text="toast.type === 'success' ? 'Success' : toast.type === 'error' ? 'Error' : toast.type === 'warning' ? 'Warning' : 'Info'">
                    </p>
                    <p class="text-sm leading-snug"
                       :class="{
                           'text-emerald-700 dark:text-emerald-200': toast.type === 'success',
                           'text-red-700 dark:text-red-200': toast.type === 'error',
                           'text-amber-700 dark:text-amber-200': toast.type === 'warning',
                           'text-blue-700 dark:text-blue-200': toast.type === 'info'
                       }"
                       x-text="toast.message">
                    </p>
                </div>

                <!-- Close button -->
                <button @click="dismissToast(toast.id)"
                        class="shrink-0 mt-0.5 p-1 rounded-md transition-colors"
                        :class="{
                            'text-emerald-400 hover:text-emerald-600 hover:bg-emerald-500/10 dark:text-emerald-500 dark:hover:text-emerald-300': toast.type === 'success',
                            'text-red-400 hover:text-red-600 hover:bg-red-500/10 dark:text-red-500 dark:hover:text-red-300': toast.type === 'error',
                            'text-amber-400 hover:text-amber-600 hover:bg-amber-500/10 dark:text-amber-500 dark:hover:text-amber-300': toast.type === 'warning',
                            'text-blue-400 hover:text-blue-600 hover:bg-blue-500/10 dark:text-blue-500 dark:hover:text-blue-300': toast.type === 'info'
                        }">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Progress bar -->
            <div class="h-1 w-full"
                 :class="{
                     'bg-emerald-200/50 dark:bg-emerald-800/30': toast.type === 'success',
                     'bg-red-200/50 dark:bg-red-800/30': toast.type === 'error',
                     'bg-amber-200/50 dark:bg-amber-800/30': toast.type === 'warning',
                     'bg-blue-200/50 dark:bg-blue-800/30': toast.type === 'info'
                 }">
                <div class="h-full toast-progress-bar"
                     :class="{
                         'bg-emerald-500': toast.type === 'success',
                         'bg-red-500': toast.type === 'error',
                         'bg-amber-500': toast.type === 'warning',
                         'bg-blue-500': toast.type === 'info'
                     }"
                     :style="'animation-duration: ' + toast.duration + 'ms'">
                </div>
            </div>
        </div>
    </template>

    <style>
        @keyframes toast-shrink {
            from { width: 100%; }
            to { width: 0%; }
        }
        .toast-progress-bar {
            animation: toast-shrink linear forwards;
        }
    </style>
</div>
