@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'maxFiles' => 20,
    'maxSizeMb' => 10,
    'initialPhotos' => [],
    'compact' => false,
    'cameraOnly' => false,
])

<div class="{{ $compact ? 'mb-2' : 'mb-6' }}" x-data="photoAttach({
    photoKey: '{{ $name }}',
    maxFiles: {{ (int) $maxFiles }},
    maxSizeMb: {{ (int) $maxSizeMb }},
    initialPhotos: @json($initialPhotos ?? []),
    required: {{ $required ? 'true' : 'false' }},
})">
    @if($label)
        <div class="flex items-center justify-between gap-3 mb-1">
            <label class="block {{ $compact ? 'text-xs' : 'text-base sm:text-sm' }} font-medium text-gray-700 dark:text-gray-300">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
                <span class="text-xs text-gray-400 font-normal ml-1"
                      :class="isAtLimit ? 'text-red-500 font-medium' : 'text-gray-400'"
                      x-text="`(${attachedPhotos.length}/${maxFiles})`"></span>
            </label>
            @unless($cameraOnly)
                <button type="button"
                        @click="attachMode = (attachMode === 'camera' ? 'upload' : 'camera')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-2.5 sm:py-1 text-sm sm:text-xs font-medium rounded-md border border-gray-300 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 7l3-3M7 7l3 3M17 17H7m10 0l-3-3m3 3l-3 3"></path>
                    </svg>
                    <span x-text="attachMode === 'camera' ? 'Camera' : 'Gallery'"></span>
                </button>
            @endunless
        </div>
    @endif

    <!-- Limit reached warning -->
    <div x-show="isAtLimit" x-cloak class="mb-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-lg">
        <p class="text-xs text-amber-700 dark:text-amber-300 font-medium">
            Maximum <span x-text="maxFiles"></span> photos reached. Remove existing photos to add new ones.
        </p>
    </div>

    <template x-if="attachedPhotos.length === 0">
        <div class="flex items-center gap-2 {{ $compact ? 'mb-2' : 'mb-6' }}">
            <button
                @click="if(!isAtLimit) { openAttachAction(); } $event.preventDefault()"
                type="button"
                :disabled="isAtLimit"
                class="flex-1 flex items-center justify-center {{ $compact ? 'px-3 py-2.5' : 'px-4 py-6' }} border-2 border-dashed rounded-lg transition"
                :class="isAtLimit ? 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-50' : 'cursor-pointer hover:border-blue-500 border-gray-300 bg-blue-50 hover:bg-blue-100'"
            >
                <template x-if="attachMode === 'camera'">
                    <svg class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }} text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </template>
                <template x-if="attachMode === 'upload'">
                    <svg class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }} text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </template>

                <span class="ml-2 {{ $compact ? 'text-xs' : 'text-base sm:text-sm' }} text-blue-600" x-text="attachMode === 'upload' ? 'Upload photo' : 'Take photo'"></span>
            </button>
        </div>
    </template>

    <template x-if="attachedPhotos.length > 0">
        <div class="flex flex-col sm:flex-row gap-2 {{ $compact ? 'mb-2' : 'mb-6' }}">
            <button
                @click="openCarousel(0)"
                type="button"
                class="flex-1 inline-flex items-center justify-center {{ $compact ? 'px-3 py-1.5 text-xs' : 'px-4 py-2.5 sm:py-2 text-base sm:text-sm' }} bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-150"
            >
                <svg class="{{ $compact ? 'w-3.5 h-3.5 mr-1.5' : 'w-4 h-4 mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                See Photos (<span x-text="attachedPhotos.length"></span>)
            </button>
            <button
                @click="if(!isAtLimit) { openAttachAction(); } $event.preventDefault()"
                type="button"
                :disabled="isAtLimit"
                class="flex-1 flex items-center justify-center {{ $compact ? 'px-3 py-1.5' : 'px-4 py-2.5 sm:py-2' }} border-2 border-dashed rounded-lg transition"
                :class="isAtLimit ? 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-50' : 'cursor-pointer hover:border-blue-500 border-gray-300 bg-blue-50 hover:bg-blue-100'"
            >
                <template x-if="attachMode === 'camera'">
                    <svg class="{{ $compact ? 'w-3.5 h-3.5 mr-1.5' : 'w-4 h-4 mr-2' }} text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </template>
                <template x-if="attachMode === 'upload'">
                    <svg class="{{ $compact ? 'w-3.5 h-3.5 mr-1.5' : 'w-4 h-4 mr-2' }} text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </template>
                <span class="{{ $compact ? 'text-xs' : '' }} text-blue-600" x-text="isAtLimit ? `Limit reached (${maxFiles})` : (attachMode === 'upload' ? 'Upload Photo' : 'Take Photo')"></span>
            </button>
        </div>
    </template>

    <!-- Hidden file input -->
    <input
        type="file"
        name="{{ $name }}[]"
        id="{{ $name }}"
        class="hidden"
        x-ref="originalInput"
        @change="handleInputChange($event)"
        accept="image/*"
        multiple
    >

    <!-- Camera Modal -->
    <div x-show="showCameraModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;">

    <div class="fixed inset-0 bg-black/80" @click="tryCancel()"></div>

    <div class="relative min-h-screen flex items-start sm:items-center justify-center p-2 sm:p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-4 sm:p-6 my-4 sm:my-8 max-h-[95vh] overflow-y-auto">

            <div class="flex items-center justify-between mb-4 sticky top-0 bg-white dark:bg-gray-800 z-10 pb-2">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                    Add Photos
                    <span class="text-xs font-normal text-gray-400" :class="isAtLimit ? 'text-red-500' : ''"
                          x-text="`(${totalPhotoCount}/${maxFiles})`"></span>
                </h3>
                @unless($cameraOnly)
                <div class="flex items-center gap-2">
                    <button type="button" @click="selectFromGallery()"
                            :disabled="isAtLimit || uploading || processingGallery"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border transition-colors"
                            :class="isAtLimit ? 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Gallery
                    </button>
                </div>
                @endunless
            </div>

            {{-- Status Bar --}}
            <div class="mb-3 px-3 py-2 rounded-lg text-xs font-medium"
                :class="isAtLimit ? 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300' : (uploading ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : (processingGallery ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300' : (cameraActive ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300')))">
                <span x-text="isAtLimit ? `Photo limit reached (${maxFiles}/${maxFiles})` : (uploading ? 'Uploading...' : (processingGallery ? 'Processing images...' : (cameraActive ? 'Camera is active' : `Max ${maxSizeMb}MB per file · ${remainingSlots} slots remaining`)))"></span>
            </div>

            {{-- Camera Preview --}}
            <div class="relative w-full max-w-2xl aspect-[4/3] bg-black rounded-lg overflow-hidden mb-4">
                <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline></video>
                <canvas x-ref="canvas" class="hidden"></canvas>
            </div>

            {{-- Captured Photos Preview --}}
            <template x-if="photos.length > 0">
                <div class="mb-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        Captured (<span x-text="photos.length"></span>) — click upload to save
                    </p>
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <template x-for="photo in photos" :key="photo.id">
                            <div class="relative flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                <img :src="photo.data" class="w-full h-full object-cover">
                                <button type="button" @click="deletePhoto(photo.id)"
                                        class="absolute top-0 right-0 bg-red-500 text-white rounded-bl-lg p-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-center gap-4 mt-4 sm:mt-6 sticky bottom-0 bg-white dark:bg-gray-800 pt-2 pb-1 border-t border-gray-100 dark:border-gray-700">
                {{-- Cancel (pinned left) --}}
                <button @click="tryCancel()" type="button" title="Cancel"
                        :disabled="uploading || processingGallery"
                        class="absolute left-0 p-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-full text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                {{-- Flash Toggle --}}
                <button @click="toggleFlash()" type="button"
                        x-show="cameraActive && flashSupported && !uploading && !processingGallery"
                        :title="flashOn ? 'Turn Flash Off' : 'Turn Flash On'"
                        :class="flashOn ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-500' : 'bg-gray-600 text-white hover:bg-gray-700'"
                        class="p-2.5 rounded-full transition-colors">
                    {{-- Flash On icon --}}
                    <svg x-show="flashOn" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11 21h-1l1-7H7.5c-.88 0-.33-.75-.31-.78C8.48 10.94 10.42 7.54 13.01 3h1l-1 7h3.51c.4 0 .62.19.4.66C12.97 17.55 11 21 11 21z"></path>
                    </svg>
                    {{-- Flash Off icon --}}
                    <svg x-show="!flashOn" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </button>

                {{-- Capture Photo --}}
                <button @click="capturePhoto()" type="button" title="Capture Photo"
                        x-show="cameraActive && !uploading && !processingGallery"
                        :disabled="isAtLimit"
                        :class="isAtLimit ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600'"
                        class="p-4 text-white rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>

                {{-- Upload Photos --}}
                <button @click="uploadPhotos()" type="button" title="Upload Photos"
                        x-show="photos.length > 0 && !uploading && !processingGallery"
                        :disabled="uploading || processingGallery"
                        class="p-2.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed relative">
                    <span x-show="!uploading">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center" x-text="photos.length"></span>
                    </span>
                    <span x-show="uploading">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>

        {{-- Cancel Confirmation Modal --}}
        <div x-show="showCancelConfirmation"
            x-cloak
            class="fixed inset-0 z-60 overflow-y-auto"
            style="display: none;">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                    <div class="text-center">
                        <div class="mx-auto mb-4 text-yellow-500 w-16 h-16">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Cancel Photo Capture?</h3>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">Any captured photos that haven't been uploaded will be lost.</p>
                        <div class="flex gap-3 justify-center">
                            <button @click="showCancelConfirmation = false" type="button"
                                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Continue Capturing
                            </button>
                            <button @click="confirmCancel()" type="button"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Yes, Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

    <!-- Carousel Modal -->
    <div x-show="showCarouselModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/80" @click="showCarouselModal = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-sm w-full p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Attached Photos</h3>
                    <button type="button" @click="showCarouselModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="relative w-full max-w-sm aspect-square bg-gray-900 rounded-lg overflow-hidden">
                    <img :src="attachedPhotos[currentPhotoIndex]?.data" class="w-full h-full object-contain">

                    <template x-if="attachedPhotos.length > 1">
                        <button type="button" @click="prevPhoto()" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    </template>

                    <template x-if="attachedPhotos.length > 1">
                        <button type="button" @click="nextPhoto()" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </template>
                </div>

                <div class="mt-3 text-center text-sm text-gray-600">
                    <span x-text="currentPhotoIndex + 1"></span> / <span x-text="attachedPhotos.length"></span>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="button"
                            @click="tryRemoveCurrentAttachedPhoto()"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Remove
                    </button>
                </div>

                {{-- Remove Photo Confirmation Modal --}}
                <div x-show="showRemoveConfirmation"
                    x-cloak
                    class="fixed inset-0 z-60 overflow-y-auto"
                    style="display: none;">
                    <div class="fixed inset-0 bg-black/50"></div>
                    <div class="relative min-h-screen flex items-center justify-center p-4">
                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                            <div class="text-center">
                                <div class="mx-auto mb-4 text-yellow-500 w-16 h-16">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Remove Photo?</h3>
                                <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to remove this photo? This action cannot be undone.</p>
                                <div class="flex gap-3 justify-center">
                                    <button @click="showRemoveConfirmation = false" type="button"
                                            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        Keep Photo
                                    </button>
                                    <button @click="removeCurrentAttachedPhoto()" type="button"
                                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                        Yes, Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

 </div>

@once
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('photoAttach', function(config) {
        return {
            showCameraModal: false,
            showCancelConfirmation: false,
            showCarouselModal: false,
            showRemoveConfirmation: false,
            attachMode: 'camera',
            photoKey: config.photoKey || '',
            maxFiles: config.maxFiles || 20,
            maxSizeMb: config.maxSizeMb || 10,
            isRequired: config.required || false,
            stream: null,
            flashOn: false,
            flashSupported: false,
            photos: [],
            attachedPhotos: [],
            attachedFiles: [],
            suppressInputChange: false,
            currentPhotoIndex: 0,
            cameraActive: false,
            uploading: false,
            processingGallery: false,
            serverPhotoQueue: [],

            toast: function(type, message) {
                window.dispatchEvent(new CustomEvent('showToast', { detail: { type: type, message: message } }));
            },

            get totalPhotoCount() { return this.attachedPhotos.length + this.photos.length; },
            get remainingSlots() { return Math.max(0, this.maxFiles - this.totalPhotoCount); },
            get isAtLimit() { return this.totalPhotoCount >= this.maxFiles; },

            checkFileSize: function(file) {
                var maxBytes = this.maxSizeMb * 1024 * 1024;
                if (file.size > maxBytes) {
                    this.toast('error', 'File \'' + file.name + '\' exceeds ' + this.maxSizeMb + 'MB limit (' + (file.size / 1024 / 1024).toFixed(1) + 'MB)');
                    return false;
                }
                return true;
            },

            checkCanAddPhotos: function(count) {
                count = count || 1;
                if (this.totalPhotoCount + count > this.maxFiles) {
                    var remaining = this.remainingSlots;
                    this.toast('error', 'Maximum ' + this.maxFiles + ' photos allowed. ' + (remaining > 0 ? 'You can add ' + remaining + ' more.' : 'Limit reached.'));
                    return false;
                }
                return true;
            },

            init: function() {
                var self = this;
                var preloaded = config.initialPhotos || [];
                if (preloaded && preloaded.length > 0) {
                    for (var i = 0; i < preloaded.length; i++) {
                        self.attachedPhotos.push({ id: preloaded[i].id, data: preloaded[i].url, serverPhotoId: preloaded[i].id, serverUrl: preloaded[i].url });
                        self.attachedFiles.push(null);
                    }
                }

                window.addEventListener('photoLimitReached', function(event) {
                    if (!event || !event.detail || event.detail.photoKey !== self.photoKey) return;
                    self.toast('error', 'Maximum ' + event.detail.max + ' photos allowed per field.');
                });

                window.addEventListener('photoStored', function(event) {
                    if (!event || !event.detail) return;
                    if (event.detail.photoKey !== self.photoKey) return;
                    self.serverPhotoQueue.push({ photoId: event.detail.photoId, url: event.detail.url });
                });

                window.addEventListener('formSubmitted', function() {
                    self.photos = []; self.attachedPhotos = []; self.attachedFiles = []; self.serverPhotoQueue = [];
                    self.currentPhotoIndex = 0; self.showCarouselModal = false; self.showCameraModal = false;
                    self.showCancelConfirmation = false; self.showRemoveConfirmation = false;
                    if (self.$refs && self.$refs.originalInput) { self.suppressInputChange = true; self.$refs.originalInput.value = ''; self.suppressInputChange = false; }
                    self.stopCamera();
                });

                window.addEventListener('formReset', function() {
                    self.photos = []; self.attachedPhotos = []; self.attachedFiles = []; self.serverPhotoQueue = [];
                    self.currentPhotoIndex = 0; self.showCarouselModal = false; self.showCameraModal = false;
                    self.showCancelConfirmation = false; self.showRemoveConfirmation = false;
                    if (self.$refs && self.$refs.originalInput) { self.suppressInputChange = true; self.$refs.originalInput.value = ''; self.suppressInputChange = false; }
                    self.stopCamera();
                });
            },

            assignServerPhotosToLastAttached: function(count) {
                if (!count || count <= 0) return;
                var startIndex = this.attachedPhotos.length - count;
                for (var i = 0; i < count; i++) {
                    var queueItem = this.serverPhotoQueue.shift();
                    if (!queueItem) continue;
                    var index = startIndex + i;
                    if (!this.attachedPhotos[index]) continue;
                    this.attachedPhotos[index].serverPhotoId = queueItem.photoId;
                    this.attachedPhotos[index].serverUrl = queueItem.url;
                }
            },

            uploadFilesToServer: function(files) {
                if (!files || files.length === 0) return Promise.resolve();
                if (!this.$wire) {
                    return Promise.reject(new Error('Livewire ($wire) is not available.'));
                }
                var self = this;
                return new Promise(function(resolve, reject) {
                    self.$wire.uploadMultiple('photoUploads.' + self.photoKey, files,
                        function() { resolve(true); },
                        function(err) { reject(err); }
                    );
                });
            },

            openAttachAction: function() {
                if (this.attachMode === 'upload') { this.triggerUpload(); return; }
                this.showCameraModal = true;
                var self = this;
                this.$nextTick(function() { self.startCamera(); });
            },

            triggerUpload: function() {
                if (this.uploading || this.processingGallery) return;
                this.$refs.originalInput.click();
            },

            handleInputChange: function(e) {
                if (this.suppressInputChange) return;
                var selected = e && e.target && e.target.files ? Array.from(e.target.files) : [];
                if (selected.length === 0) return;
                if (!this.checkCanAddPhotos(selected.length)) { e.target.value = ''; return; }

                var self = this;
                self.processingGallery = true;

                (async function() {
                    try {
                        var processed = [];
                        for (var i = 0; i < selected.length; i++) {
                            var file = selected[i];
                            if (!self.checkFileSize(file)) continue;
                            if (self.attachedPhotos.length + processed.length >= self.maxFiles) {
                                self.toast('warning', 'Maximum ' + self.maxFiles + ' photos reached. Remaining files skipped.');
                                break;
                            }
                            var result = await self.processUploadFile(file);
                            if (result) processed.push(result);
                        }

                        var newFiles = processed.map(function(p) { return p.file; });
                        var newPhotos = processed.map(function(p) { return p.photo; });
                        var dataTransfer = new DataTransfer();
                        var allFiles = self.attachedFiles.concat(newFiles);
                        allFiles.filter(function(f) { return f; }).forEach(function(file) { dataTransfer.items.add(file); });

                        self.suppressInputChange = true;
                        self.$refs.originalInput.files = dataTransfer.files;
                        self.suppressInputChange = false;

                        self.attachedFiles = allFiles;
                        self.attachedPhotos = self.attachedPhotos.concat(newPhotos);

                        self.uploading = true;
                        try {
                            await self.uploadFilesToServer(newFiles);
                            self.assignServerPhotosToLastAttached(newPhotos.length);
                        } finally {
                            self.uploading = false;
                        }
                    } finally {
                        self.processingGallery = false;
                    }
                })();
            },

            processUploadFile: function(file) {
                var self = this;
                return new Promise(function(resolve) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = new Image();
                        img.onload = function() {
                            var canvas = self.$refs.canvas;
                            var ctx = canvas.getContext('2d');
                            var maxDimension = 1920;
                            var targetWidth = img.width;
                            var targetHeight = img.height;
                            if (img.width > maxDimension || img.height > maxDimension) {
                                var scale = Math.min(maxDimension / img.width, maxDimension / img.height);
                                targetWidth = Math.floor(img.width * scale);
                                targetHeight = Math.floor(img.height * scale);
                            }
                            canvas.width = targetWidth;
                            canvas.height = targetHeight;
                            ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                            self.addTimestampWatermark(ctx, canvas.width, canvas.height);
                            var dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                            fetch(dataUrl).then(function(r) { return r.blob(); }).then(function(blob) {
                                var processedFile = new File([blob], file.name, { type: 'image/jpeg' });
                                resolve({ file: processedFile, photo: { id: Date.now() + Math.random(), data: dataUrl } });
                            }).catch(function() { resolve(null); });
                        };
                        img.onerror = function() { resolve(null); };
                        img.src = e.target.result;
                    };
                    reader.onerror = function() { resolve(null); };
                    reader.readAsDataURL(file);
                });
            },

            startCamera: function() {
                var self = this;
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    self.toast('error', 'Camera not supported! You need HTTPS or localhost.');
                    return;
                }
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1920 } },
                    audio: false
                }).then(function(stream) {
                    self.stream = stream;
                    self.$refs.video.srcObject = stream;
                    self.cameraActive = true;
                    var track = stream.getVideoTracks()[0];
                    if (track) {
                        var capabilities = track.getCapabilities ? track.getCapabilities() : {};
                        self.flashSupported = !!(capabilities.torch);
                    }
                    self.flashOn = false;
                }).catch(function(err) {
                    self.toast('error', 'Camera error: ' + err.message);
                });
            },

            toggleFlash: function() {
                if (!this.stream || !this.flashSupported) return;
                var track = this.stream.getVideoTracks()[0];
                if (!track) return;
                var self = this;
                self.flashOn = !self.flashOn;
                track.applyConstraints({ advanced: [{ torch: self.flashOn }] }).catch(function() {
                    self.flashOn = false;
                    self.toast('error', 'Flash not available');
                });
            },

            capturePhoto: function() {
                if (!this.checkCanAddPhotos(1)) return;
                var video = this.$refs.video;
                var canvas = this.$refs.canvas;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);
                this.addTimestampWatermark(ctx, canvas.width, canvas.height);
                var imageData = canvas.toDataURL('image/jpeg', 0.85);
                this.photos.push({ id: Date.now(), data: imageData });
            },

            addTimestampWatermark: function(ctx, width, height) {
                var now = new Date(new Date().toLocaleString('en-US', {timeZone: 'Asia/Manila'}));
                var dateStr = now.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' });
                var timeStr = now.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                var timestamp = dateStr + ' ' + timeStr;
                var fontSize = Math.max(16, height * 0.03);
                ctx.font = 'bold ' + fontSize + 'px Arial';
                ctx.textBaseline = 'bottom';
                var padding = fontSize * 0.3;
                var textWidth = ctx.measureText(timestamp).width;
                var textHeight = fontSize;
                var bgX = padding;
                var bgY = height - textHeight - padding * 2;
                var bgWidth = textWidth + padding * 2;
                var bgHeight = textHeight + padding * 2;
                ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                ctx.fillRect(bgX, bgY, bgWidth, bgHeight);
                var textX = padding * 2;
                var textY = height - padding * 2;
                ctx.strokeStyle = '#000000';
                ctx.lineWidth = fontSize * 0.15;
                ctx.lineJoin = 'round';
                ctx.miterLimit = 2;
                ctx.strokeText(timestamp, textX, textY);
                ctx.fillStyle = '#FFFFFF';
                ctx.fillText(timestamp, textX, textY);
            },

            selectFromGallery: function() {
                var self = this;
                var input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.multiple = true;
                input.onchange = function(e) {
                    var files = Array.from(e.target.files);
                    if (files.length === 0) return;
                    if (!self.checkCanAddPhotos(files.length)) return;
                    self.processingGallery = true;

                    (async function() {
                        for (var i = 0; i < files.length; i++) {
                            if (!self.checkFileSize(files[i])) continue;
                            if (self.isAtLimit) {
                                self.toast('warning', 'Maximum ' + self.maxFiles + ' photos reached. Remaining files skipped.');
                                break;
                            }
                            await self.processGalleryImage(files[i]);
                        }
                        self.processingGallery = false;
                    })();
                };
                input.click();
            },

            processGalleryImage: function(file) {
                var self = this;
                return new Promise(function(resolve) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = new Image();
                        img.onload = function() {
                            var canvas = self.$refs.canvas;
                            var ctx = canvas.getContext('2d');
                            var maxDimension = 1920;
                            var targetWidth = img.width;
                            var targetHeight = img.height;
                            if (img.width > maxDimension || img.height > maxDimension) {
                                var scale = Math.min(maxDimension / img.width, maxDimension / img.height);
                                targetWidth = Math.floor(img.width * scale);
                                targetHeight = Math.floor(img.height * scale);
                            }
                            canvas.width = targetWidth;
                            canvas.height = targetHeight;
                            ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                            self.addTimestampWatermark(ctx, canvas.width, canvas.height);
                            var imageData = canvas.toDataURL('image/jpeg', 0.85);
                            var base64Length = imageData.length - 'data:image/jpeg;base64,'.length;
                            var sizeInBytes = (base64Length * 3) / 4;
                            if (sizeInBytes > self.maxSizeMb * 1024 * 1024) {
                                self.toast('error', 'Photo \'' + file.name + '\' exceeds ' + self.maxSizeMb + 'MB limit even after resizing');
                                resolve();
                                return;
                            }
                            self.photos.push({ id: Date.now(), data: imageData });
                            resolve();
                        };
                        img.onerror = function() { self.toast('error', 'Failed to load image: ' + file.name); resolve(); };
                        img.src = e.target.result;
                    };
                    reader.onerror = function() { self.toast('error', 'Failed to read file: ' + file.name); resolve(); };
                    reader.readAsDataURL(file);
                });
            },

            stopCamera: function() {
                if (this.stream) { this.stream.getTracks().forEach(function(track) { track.stop(); }); this.stream = null; }
                if (this.$refs.video) { this.$refs.video.srcObject = null; }
                this.cameraActive = false;
                this.flashOn = false;
                this.flashSupported = false;
            },

            tryCancel: function() {
                if (this.photos.length > 0 || this.cameraActive) { this.showCancelConfirmation = true; }
                else { this.confirmCancel(); }
            },

            confirmCancel: function() {
                this.stopCamera();
                this.photos = [];
                this.showCancelConfirmation = false;
                this.showCameraModal = false;
                this.uploading = false;
                this.processingGallery = false;
            },

            openCarousel: function(index) {
                index = index || 0;
                if (this.attachedPhotos.length === 0) return;
                this.currentPhotoIndex = Math.min(Math.max(index, 0), this.attachedPhotos.length - 1);
                this.showCarouselModal = true;
            },

            nextPhoto: function() {
                if (this.attachedPhotos.length === 0) return;
                this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.attachedPhotos.length;
            },

            prevPhoto: function() {
                if (this.attachedPhotos.length === 0) return;
                this.currentPhotoIndex = (this.currentPhotoIndex - 1 + this.attachedPhotos.length) % this.attachedPhotos.length;
            },

            tryRemoveCurrentAttachedPhoto: function() { this.showRemoveConfirmation = true; },

            removeCurrentAttachedPhoto: function() {
                this.showRemoveConfirmation = false;
                if (this.attachedPhotos.length === 0) return;

                var self = this;
                var index = self.currentPhotoIndex;
                var photo = self.attachedPhotos[index];
                var serverPhotoId = photo && photo.serverPhotoId ? photo.serverPhotoId : null;

                (async function() {
                    if (self.$wire && serverPhotoId) {
                        try {
                            await self.$wire.call('deleteUploadedPhoto', self.photoKey, serverPhotoId);
                        } catch (err) {
                            self.toast('error', 'Failed to remove photo: ' + (err && err.message ? err.message : 'Unknown error'));
                            return;
                        }
                    }

                    self.attachedPhotos.splice(index, 1);
                    self.attachedFiles.splice(index, 1);

                    var dataTransfer = new DataTransfer();
                    self.attachedFiles.filter(function(f) { return f; }).forEach(function(f) { dataTransfer.items.add(f); });
                    self.suppressInputChange = true;
                    self.$refs.originalInput.files = dataTransfer.files;
                    self.suppressInputChange = false;

                    if (self.attachedPhotos.length === 0) {
                        self.showCarouselModal = false;
                        self.currentPhotoIndex = 0;
                        self.toast('success', 'Photo removed');
                        return;
                    }

                    self.currentPhotoIndex = Math.min(self.currentPhotoIndex, self.attachedPhotos.length - 1);
                    self.toast('success', 'Photo removed');
                })();
            },

            deletePhoto: function(id) { this.photos = this.photos.filter(function(p) { return p.id !== id; }); },

            validatePhotos: function() {
                if (this.isRequired) {
                    var originalInput = this.$refs.originalInput;
                    var hasFiles = originalInput && originalInput.files && originalInput.files.length > 0;
                    if (!hasFiles) return 'Please take at least one photo';
                }
                return null;
            },

            uploadPhotos: function() {
                if (this.photos.length === 0) { this.toast('warning', 'No photos to upload!'); return; }

                var wouldBeTotal = this.attachedPhotos.length + this.photos.length;
                if (wouldBeTotal > this.maxFiles) {
                    var canAdd = this.maxFiles - this.attachedPhotos.length;
                    if (canAdd <= 0) { this.toast('error', 'Maximum ' + this.maxFiles + ' photos already attached.'); return; }
                    this.toast('warning', 'Only uploading first ' + canAdd + ' of ' + this.photos.length + ' photos to stay within the ' + this.maxFiles + ' photo limit.');
                    this.photos = this.photos.slice(0, canAdd);
                }

                var self = this;
                self.uploading = true;

                (async function() {
                    try {
                        var files = await Promise.all(self.photos.map(function(photo, index) {
                            return fetch(photo.data).then(function(r) { return r.blob(); }).then(function(blob) {
                                return new File([blob], 'photo_' + (index + 1) + '.jpg', { type: 'image/jpeg' });
                            });
                        }));

                        await self.uploadFilesToServer(files);

                        var dataTransfer = new DataTransfer();
                        var allFiles = self.attachedFiles.concat(files);
                        allFiles.filter(function(f) { return f; }).forEach(function(file) { dataTransfer.items.add(file); });

                        self.$refs.originalInput.files = dataTransfer.files;

                        self.toast('success', files.length + ' photo(s) uploaded successfully!');

                        self.attachedFiles = allFiles;
                        self.attachedPhotos = self.attachedPhotos.concat(self.photos);
                        self.assignServerPhotosToLastAttached(self.photos.length);
                        self.photos = [];
                        self.stopCamera();
                        self.showCameraModal = false;
                    } catch(err) {
                        console.error('[photo-attach] Upload error:', err);
                        self.toast('error', 'Upload failed: ' + (err && err.message ? err.message : 'Unknown error'));
                    } finally {
                        self.uploading = false;
                    }
                })();
            }
        };
    });
});
</script>
@endonce
