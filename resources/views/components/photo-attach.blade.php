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
