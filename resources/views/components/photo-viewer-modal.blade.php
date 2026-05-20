@props(['photos' => []])

@if(count($photos) > 0)
<div x-data="{
    showPhotoModal: @entangle('showPhotoModal'),
    currentPhotoIndex: 0,
    selectedPhotos: @js($photos),
    scale: 1,
    translateX: 0,
    translateY: 0,
    isDragging: false,
    startX: 0,
    startY: 0,
    lastTranslateX: 0,
    lastTranslateY: 0,
    initialPinchDistance: 0,
    initialPinchScale: 1,
    minScale: 1,
    maxScale: 4,

    get isZoomed() { return this.scale > 1.05 },

    resetZoom() {
        this.scale = 1;
        this.translateX = 0;
        this.translateY = 0;
        this.lastTranslateX = 0;
        this.lastTranslateY = 0;
    },

    clampTranslation() {
        if (this.scale <= 1) {
            this.translateX = 0;
            this.translateY = 0;
            return;
        }
        const container = this.$refs.imageContainer;
        if (!container) return;
        const rect = container.getBoundingClientRect();
        const maxX = (rect.width * (this.scale - 1)) / 2;
        const maxY = (rect.height * (this.scale - 1)) / 2;
        this.translateX = Math.max(-maxX, Math.min(maxX, this.translateX));
        this.translateY = Math.max(-maxY, Math.min(maxY, this.translateY));
    },

    toggleZoom(e) {
        if (this.isZoomed) {
            this.resetZoom();
        } else {
            this.scale = 2.5;
            this.translateX = 0;
            this.translateY = 0;
        }
        this.lastTranslateX = this.translateX;
        this.lastTranslateY = this.translateY;
    },

    onWheel(e) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.25 : 0.25;
        const newScale = Math.max(this.minScale, Math.min(this.maxScale, this.scale + delta));
        this.scale = newScale;
        if (this.scale <= 1) {
            this.resetZoom();
        } else {
            this.clampTranslation();
        }
        this.lastTranslateX = this.translateX;
        this.lastTranslateY = this.translateY;
    },

    onPointerDown(e) {
        if (!this.isZoomed || e.pointerType === 'touch') return;
        this.isDragging = true;
        this.startX = e.clientX;
        this.startY = e.clientY;
        this.lastTranslateX = this.translateX;
        this.lastTranslateY = this.translateY;
        e.preventDefault();
    },

    onPointerMove(e) {
        if (!this.isDragging || e.pointerType === 'touch') return;
        this.translateX = this.lastTranslateX + (e.clientX - this.startX);
        this.translateY = this.lastTranslateY + (e.clientY - this.startY);
        this.clampTranslation();
    },

    onPointerUp(e) {
        if (e.pointerType === 'touch') return;
        this.isDragging = false;
        this.lastTranslateX = this.translateX;
        this.lastTranslateY = this.translateY;
    },

    onTouchStart(e) {
        if (e.touches.length === 2) {
            this.initialPinchDistance = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            this.initialPinchScale = this.scale;
        } else if (e.touches.length === 1 && this.isZoomed) {
            this.isDragging = true;
            this.startX = e.touches[0].clientX;
            this.startY = e.touches[0].clientY;
            this.lastTranslateX = this.translateX;
            this.lastTranslateY = this.translateY;
        }
    },

    onTouchMove(e) {
        if (e.touches.length === 2) {
            e.preventDefault();
            const distance = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const newScale = this.initialPinchScale * (distance / this.initialPinchDistance);
            this.scale = Math.max(this.minScale, Math.min(this.maxScale, newScale));
            if (this.scale <= 1) {
                this.translateX = 0;
                this.translateY = 0;
            }
            this.clampTranslation();
        } else if (e.touches.length === 1 && this.isDragging && this.isZoomed) {
            e.preventDefault();
            this.translateX = this.lastTranslateX + (e.touches[0].clientX - this.startX);
            this.translateY = this.lastTranslateY + (e.touches[0].clientY - this.startY);
            this.clampTranslation();
        }
    },

    onTouchEnd(e) {
        if (e.touches.length < 2) {
            this.initialPinchDistance = 0;
        }
        if (e.touches.length === 0) {
            this.isDragging = false;
            this.lastTranslateX = this.translateX;
            this.lastTranslateY = this.translateY;
        }
    },

    prevPhoto() {
        this.currentPhotoIndex = (this.currentPhotoIndex - 1 + this.selectedPhotos.length) % this.selectedPhotos.length;
        this.resetZoom();
    },

    nextPhoto() {
        this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.selectedPhotos.length;
        this.resetZoom();
    }
}"
    x-init="currentPhotoIndex = Math.min(currentPhotoIndex, selectedPhotos.length - 1) || 0"
    x-show="showPhotoModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto bg-black/50 dark:bg-black/80"
    style="display: none;">

    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <!-- Modal Panel -->
        <div x-show="showPhotoModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl dark:shadow-2xl transition-all w-full max-w-sm">

            <!-- Modal Header -->
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                        Photos
                    </h3>
                    <div class="flex items-center gap-2">
                        <span x-show="isZoomed" x-transition
                              class="text-xs text-gray-500 dark:text-gray-400 tabular-nums"
                              x-text="Math.round(scale * 100) + '%'"></span>
                        <button type="button"
                                @click="showPhotoModal = false; resetZoom(); $wire.closePhotoModal()"
                                class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 shrink-0 cursor-pointer">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:p-6 sm:pb-4">
                <div class="relative w-full max-w-sm aspect-square bg-gray-900 rounded-lg overflow-hidden select-none"
                     x-ref="imageContainer"
                     :class="isZoomed ? 'cursor-grab' : 'cursor-zoom-in'"
                     :style="isDragging && isZoomed ? 'cursor: grabbing' : ''"
                     @dblclick="toggleZoom($event)"
                     @wheel.prevent="onWheel($event)"
                     @pointerdown="onPointerDown($event)"
                     @pointermove="onPointerMove($event)"
                     @pointerup="onPointerUp($event)"
                     @pointerleave="onPointerUp($event)"
                     @touchstart.passive="onTouchStart($event)"
                     @touchmove="onTouchMove($event)"
                     @touchend="onTouchEnd($event)">
                    <template x-if="selectedPhotos[currentPhotoIndex]">
                        <img :src="selectedPhotos[currentPhotoIndex]?.url || ''"
                             class="w-full h-full object-contain transition-transform duration-150 pointer-events-none"
                             :style="`transform: scale(${scale}) translate(${translateX / scale}px, ${translateY / scale}px)`"
                             draggable="false">
                    </template>

                    @if(count($photos) > 1)
                        <button type="button"
                                x-show="!isZoomed"
                                x-transition
                                @click.stop="prevPhoto()"
                                class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <button type="button"
                                x-show="!isZoomed"
                                x-transition
                                @click.stop="nextPhoto()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @endif

                    <!-- Zoom hint -->
                    <div x-show="!isZoomed" x-transition
                         class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/60 text-white text-xs px-2 py-1 rounded-full pointer-events-none">
                        Double-tap to zoom
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-center gap-3">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="currentPhotoIndex + 1"></span> / <span>{{ count($photos) }}</span>
                    </div>
                    <button x-show="isZoomed" x-transition
                            @click="resetZoom()"
                            type="button"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 cursor-pointer">
                        Reset zoom
                    </button>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                        @click="showPhotoModal = false; resetZoom(); $wire.closePhotoModal()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
