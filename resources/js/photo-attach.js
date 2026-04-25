/**
 * Photo-attach Alpine.js component.
 *
 * Exported and registered via app.js using the Livewire ESM import pattern.
 * Alpine.data('photoAttach', ...) is called before Livewire.start(), so the
 * component is guaranteed to be available when Alpine processes x-data.
 */

export const photoAttachComponent = (config) => ({
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

    toast(type, message) {
        window.dispatchEvent(new CustomEvent('showToast', {
            detail: { type, message }
        }));
    },

    get totalPhotoCount() {
        return this.attachedPhotos.length + this.photos.length;
    },

    get remainingSlots() {
        return Math.max(0, this.maxFiles - this.totalPhotoCount);
    },

    get isAtLimit() {
        return this.totalPhotoCount >= this.maxFiles;
    },

    checkFileSize(file) {
        const maxBytes = this.maxSizeMb * 1024 * 1024;
        if (file.size > maxBytes) {
            this.toast('error', `File '${file.name}' exceeds ${this.maxSizeMb}MB limit (${(file.size / 1024 / 1024).toFixed(1)}MB)`);
            return false;
        }
        return true;
    },

    checkCanAddPhotos(count = 1) {
        if (this.totalPhotoCount + count > this.maxFiles) {
            const remaining = this.remainingSlots;
            this.toast('error', `Maximum ${this.maxFiles} photos allowed. ${remaining > 0 ? `You can add ${remaining} more.` : 'Limit reached.'}`);
            return false;
        }
        return true;
    },

    init() {
        const preloaded = config.initialPhotos || [];
        if (preloaded && preloaded.length > 0) {
            for (const p of preloaded) {
                this.attachedPhotos.push({
                    id: p.id,
                    data: p.url,
                    serverPhotoId: p.id,
                    serverUrl: p.url,
                });
                this.attachedFiles.push(null);
            }
        }

        window.addEventListener('photoLimitReached', (event) => {
            if (!event || !event.detail || event.detail.photoKey !== this.photoKey) return;
            this.toast('error', `Maximum ${event.detail.max} photos allowed per field.`);
        });

        window.addEventListener('photoStored', (event) => {
            if (!event || !event.detail) return;
            if (event.detail.photoKey !== this.photoKey) return;
            this.serverPhotoQueue.push({
                photoId: event.detail.photoId,
                url: event.detail.url,
            });
        });

        window.addEventListener('formSubmitted', () => {
            this.photos = [];
            this.attachedPhotos = [];
            this.attachedFiles = [];
            this.serverPhotoQueue = [];
            this.currentPhotoIndex = 0;
            this.showCarouselModal = false;
            this.showCameraModal = false;
            this.showCancelConfirmation = false;
            this.showRemoveConfirmation = false;
            if (this.$refs && this.$refs.originalInput) {
                this.suppressInputChange = true;
                this.$refs.originalInput.value = '';
                this.suppressInputChange = false;
            }
            this.stopCamera();
        });

        window.addEventListener('formReset', () => {
            this.photos = [];
            this.attachedPhotos = [];
            this.attachedFiles = [];
            this.serverPhotoQueue = [];
            this.currentPhotoIndex = 0;
            this.showCarouselModal = false;
            this.showCameraModal = false;
            this.showCancelConfirmation = false;
            this.showRemoveConfirmation = false;
            if (this.$refs && this.$refs.originalInput) {
                this.suppressInputChange = true;
                this.$refs.originalInput.value = '';
                this.suppressInputChange = false;
            }
            this.stopCamera();
        });
    },

    assignServerPhotosToLastAttached(count) {
        if (!count || count <= 0) return;
        const startIndex = this.attachedPhotos.length - count;
        for (let i = 0; i < count; i++) {
            const queueItem = this.serverPhotoQueue.shift();
            if (!queueItem) continue;
            const index = startIndex + i;
            if (!this.attachedPhotos[index]) continue;
            this.attachedPhotos[index].serverPhotoId = queueItem.photoId;
            this.attachedPhotos[index].serverUrl = queueItem.url;
        }
    },

    async uploadFilesToServer(files) {
        if (!files || files.length === 0) return;
        if (!this.$wire) {
            throw new Error('Livewire ($wire) is not available. The photo-attach component may not have initialized correctly.');
        }
        await new Promise((resolve, reject) => {
            this.$wire.uploadMultiple('photoUploads.' + this.photoKey, files,
                () => resolve(true),
                (err) => reject(err)
            );
        });
    },

    openAttachAction() {
        if (this.attachMode === 'upload') {
            this.triggerUpload();
            return;
        }
        this.showCameraModal = true;
        this.$nextTick(() => this.startCamera());
    },

    triggerUpload() {
        if (this.uploading || this.processingGallery) return;
        this.$refs.originalInput.click();
    },

    async handleInputChange(e) {
        if (this.suppressInputChange) return;
        const selected = e && e.target && e.target.files ? Array.from(e.target.files) : [];
        if (selected.length === 0) return;
        if (!this.checkCanAddPhotos(selected.length)) {
            e.target.value = '';
            return;
        }

        this.processingGallery = true;
        try {
            const processed = [];
            for (const file of selected) {
                if (!this.checkFileSize(file)) continue;
                if (this.attachedPhotos.length + processed.length >= this.maxFiles) {
                    this.toast('warning', `Maximum ${this.maxFiles} photos reached. Remaining files skipped.`);
                    break;
                }
                const result = await this.processUploadFile(file);
                if (result) processed.push(result);
            }

            const newFiles = processed.map(p => p.file);
            const newPhotos = processed.map(p => p.photo);
            const dataTransfer = new DataTransfer();
            const allFiles = [...this.attachedFiles, ...newFiles];
            allFiles.filter(f => f).forEach(file => dataTransfer.items.add(file));

            this.suppressInputChange = true;
            this.$refs.originalInput.files = dataTransfer.files;
            this.suppressInputChange = false;

            this.attachedFiles = allFiles;
            this.attachedPhotos = [...this.attachedPhotos, ...newPhotos];

            this.uploading = true;
            try {
                await this.uploadFilesToServer(newFiles);
                this.assignServerPhotosToLastAttached(newPhotos.length);
            } finally {
                this.uploading = false;
            }
        } finally {
            this.processingGallery = false;
        }
    },

    async processUploadFile(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');
                    const maxDimension = 1920;
                    let targetWidth = img.width;
                    let targetHeight = img.height;
                    if (img.width > maxDimension || img.height > maxDimension) {
                        const scale = Math.min(maxDimension / img.width, maxDimension / img.height);
                        targetWidth = Math.floor(img.width * scale);
                        targetHeight = Math.floor(img.height * scale);
                    }
                    canvas.width = targetWidth;
                    canvas.height = targetHeight;
                    ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                    this.addTimestampWatermark(ctx, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    fetch(dataUrl)
                        .then(r => r.blob())
                        .then((blob) => {
                            const processedFile = new File([blob], file.name, { type: 'image/jpeg' });
                            resolve({ file: processedFile, photo: { id: Date.now() + Math.random(), data: dataUrl } });
                        })
                        .catch(() => resolve(null));
                };
                img.onerror = () => resolve(null);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(null);
            reader.readAsDataURL(file);
        });
    },

    async startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            this.toast('error', 'Camera not supported! You need HTTPS or localhost.');
            return;
        }
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1920 } },
                audio: false
            });
            this.$refs.video.srcObject = this.stream;
            this.cameraActive = true;
            const track = this.stream.getVideoTracks()[0];
            if (track) {
                const capabilities = track.getCapabilities ? track.getCapabilities() : {};
                this.flashSupported = !!(capabilities.torch);
            }
            this.flashOn = false;
        } catch(err) {
            this.toast('error', 'Camera error: ' + err.message);
        }
    },

    async toggleFlash() {
        if (!this.stream || !this.flashSupported) return;
        const track = this.stream.getVideoTracks()[0];
        if (!track) return;
        try {
            this.flashOn = !this.flashOn;
            await track.applyConstraints({ advanced: [{ torch: this.flashOn }] });
        } catch(err) {
            this.flashOn = false;
            this.toast('error', 'Flash not available');
        }
    },

    capturePhoto() {
        if (!this.checkCanAddPhotos(1)) return;
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        this.addTimestampWatermark(ctx, canvas.width, canvas.height);
        const imageData = canvas.toDataURL('image/jpeg', 0.85);
        this.photos.push({ id: Date.now(), data: imageData });
    },

    addTimestampWatermark(ctx, width, height) {
        const now = new Date(new Date().toLocaleString('en-US', {timeZone: 'Asia/Manila'}));
        const dateStr = now.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' });
        const timeStr = now.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        const timestamp = `${dateStr} ${timeStr}`;
        const fontSize = Math.max(16, height * 0.03);
        ctx.font = `bold ${fontSize}px Arial`;
        ctx.textBaseline = 'bottom';
        const padding = fontSize * 0.3;
        const textWidth = ctx.measureText(timestamp).width;
        const textHeight = fontSize;
        const bgX = padding;
        const bgY = height - textHeight - padding * 2;
        const bgWidth = textWidth + padding * 2;
        const bgHeight = textHeight + padding * 2;
        ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
        ctx.fillRect(bgX, bgY, bgWidth, bgHeight);
        const textX = padding * 2;
        const textY = height - padding * 2;
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = fontSize * 0.15;
        ctx.lineJoin = 'round';
        ctx.miterLimit = 2;
        ctx.strokeText(timestamp, textX, textY);
        ctx.fillStyle = '#FFFFFF';
        ctx.fillText(timestamp, textX, textY);
    },

    async selectFromGallery() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.multiple = true;
        input.onchange = async (e) => {
            const files = Array.from(e.target.files);
            if (files.length === 0) return;
            if (!this.checkCanAddPhotos(files.length)) return;
            this.processingGallery = true;
            for (const file of files) {
                if (!this.checkFileSize(file)) continue;
                if (this.isAtLimit) {
                    this.toast('warning', `Maximum ${this.maxFiles} photos reached. Remaining files skipped.`);
                    break;
                }
                await this.processGalleryImage(file);
            }
            this.processingGallery = false;
        };
        input.click();
    },

    async processGalleryImage(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');
                    const maxDimension = 1920;
                    let targetWidth = img.width;
                    let targetHeight = img.height;
                    if (img.width > maxDimension || img.height > maxDimension) {
                        const scale = Math.min(maxDimension / img.width, maxDimension / img.height);
                        targetWidth = Math.floor(img.width * scale);
                        targetHeight = Math.floor(img.height * scale);
                    }
                    canvas.width = targetWidth;
                    canvas.height = targetHeight;
                    ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                    this.addTimestampWatermark(ctx, canvas.width, canvas.height);
                    const imageData = canvas.toDataURL('image/jpeg', 0.85);
                    const base64Length = imageData.length - 'data:image/jpeg;base64,'.length;
                    const sizeInBytes = (base64Length * 3) / 4;
                    if (sizeInBytes > this.maxSizeMb * 1024 * 1024) {
                        this.toast('error', `Photo '${file.name}' exceeds ${this.maxSizeMb}MB limit even after resizing`);
                        resolve();
                        return;
                    }
                    this.photos.push({ id: Date.now(), data: imageData });
                    resolve();
                };
                img.onerror = () => { this.toast('error', 'Failed to load image: ' + file.name); resolve(); };
                img.src = e.target.result;
            };
            reader.onerror = () => { this.toast('error', 'Failed to read file: ' + file.name); resolve(); };
            reader.readAsDataURL(file);
        });
    },

    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.$refs.video) {
            this.$refs.video.srcObject = null;
        }
        this.cameraActive = false;
        this.flashOn = false;
        this.flashSupported = false;
    },

    tryCancel() {
        if (this.photos.length > 0 || this.cameraActive) {
            this.showCancelConfirmation = true;
        } else {
            this.confirmCancel();
        }
    },

    confirmCancel() {
        this.stopCamera();
        this.photos = [];
        this.showCancelConfirmation = false;
        this.showCameraModal = false;
        this.showGalleryUpload = false;
        this.uploading = false;
        this.processingGallery = false;
    },

    openCarousel(index = 0) {
        if (this.attachedPhotos.length === 0) return;
        this.currentPhotoIndex = Math.min(Math.max(index, 0), this.attachedPhotos.length - 1);
        this.showCarouselModal = true;
    },

    nextPhoto() {
        if (this.attachedPhotos.length === 0) return;
        this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.attachedPhotos.length;
    },

    prevPhoto() {
        if (this.attachedPhotos.length === 0) return;
        this.currentPhotoIndex = (this.currentPhotoIndex - 1 + this.attachedPhotos.length) % this.attachedPhotos.length;
    },

    tryRemoveCurrentAttachedPhoto() {
        this.showRemoveConfirmation = true;
    },

    async removeCurrentAttachedPhoto() {
        this.showRemoveConfirmation = false;
        if (this.attachedPhotos.length === 0) return;

        const index = this.currentPhotoIndex;
        const photo = this.attachedPhotos[index];
        const serverPhotoId = photo && photo.serverPhotoId ? photo.serverPhotoId : null;

        if (this.$wire && serverPhotoId) {
            try {
                await this.$wire.call('deleteUploadedPhoto', this.photoKey, serverPhotoId);
            } catch (err) {
                this.toast('error', 'Failed to remove photo: ' + (err && err.message ? err.message : 'Unknown error'));
                return;
            }
        }

        this.attachedPhotos.splice(index, 1);
        this.attachedFiles.splice(index, 1);

        const dataTransfer = new DataTransfer();
        this.attachedFiles.filter(f => f).forEach(f => dataTransfer.items.add(f));
        this.suppressInputChange = true;
        this.$refs.originalInput.files = dataTransfer.files;
        this.suppressInputChange = false;

        if (this.attachedPhotos.length === 0) {
            this.showCarouselModal = false;
            this.currentPhotoIndex = 0;
            this.toast('success', 'Photo removed');
            return;
        }

        this.currentPhotoIndex = Math.min(this.currentPhotoIndex, this.attachedPhotos.length - 1);
        this.toast('success', 'Photo removed');
    },

    deletePhoto(id) {
        this.photos = this.photos.filter(p => p.id !== id);
    },

    validatePhotos() {
        if (this.isRequired) {
            const originalInput = this.$refs.originalInput;
            const hasFiles = originalInput && originalInput.files && originalInput.files.length > 0;
            if (!hasFiles) {
                return 'Please take at least one photo';
            }
        }
        return null;
    },

    async uploadPhotos() {
        if (this.photos.length === 0) {
            this.toast('warning', 'No photos to upload!');
            return;
        }

        const wouldBeTotal = this.attachedPhotos.length + this.photos.length;
        if (wouldBeTotal > this.maxFiles) {
            const canAdd = this.maxFiles - this.attachedPhotos.length;
            if (canAdd <= 0) {
                this.toast('error', `Maximum ${this.maxFiles} photos already attached.`);
                return;
            }
            this.toast('warning', `Only uploading first ${canAdd} of ${this.photos.length} photos to stay within the ${this.maxFiles} photo limit.`);
            this.photos = this.photos.slice(0, canAdd);
        }

        this.uploading = true;
        try {
            const files = await Promise.all(this.photos.map(async (photo, index) => {
                const response = await fetch(photo.data);
                const blob = await response.blob();
                return new File([blob], 'photo_' + (index + 1) + '.jpg', { type: 'image/jpeg' });
            }));

            await this.uploadFilesToServer(files);

            const dataTransfer = new DataTransfer();
            const allFiles = [...this.attachedFiles, ...files];
            allFiles.filter(f => f).forEach(file => dataTransfer.items.add(file));

            this.$refs.originalInput.files = dataTransfer.files;

            this.suppressInputChange = true;
            this.suppressInputChange = false;

            this.toast('success', files.length + ' photo(s) uploaded successfully!');

            this.attachedFiles = allFiles;
            this.attachedPhotos = [...this.attachedPhotos, ...this.photos];
            this.assignServerPhotosToLastAttached(this.photos.length);
            this.photos = [];
            this.stopCamera();
            this.showCameraModal = false;
        } catch(err) {
            console.error('[photo-attach] Upload error:', err);
            this.toast('error', 'Upload failed: ' + (err && err.message ? err.message : 'Unknown error'));
        } finally {
            this.uploading = false;
        }
    },
});

