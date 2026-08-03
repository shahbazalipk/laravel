{{-- Shared helpers for compressing registration profile photos before POST. --}}
<script>
    window.OnlineProfilePhoto = window.OnlineProfilePhoto || {
        maxWidth: 1280,
        maxHeight: 1280,
        quality: 0.72,

        compressCanvas(sourceCanvas, options = {}) {
            const maxWidth = options.maxWidth ?? this.maxWidth;
            const maxHeight = options.maxHeight ?? this.maxHeight;
            const quality = options.quality ?? this.quality;
            const width = sourceCanvas.width || 1;
            const height = sourceCanvas.height || 1;
            const ratio = Math.min(1, maxWidth / width, maxHeight / height);
            const outW = Math.max(1, Math.round(width * ratio));
            const outH = Math.max(1, Math.round(height * ratio));
            const out = document.createElement('canvas');
            out.width = outW;
            out.height = outH;
            const ctx = out.getContext('2d');
            ctx.drawImage(sourceCanvas, 0, 0, outW, outH);
            return out.toDataURL('image/jpeg', quality);
        },

        async compressFile(file, options = {}) {
            if (!file || !file.type?.startsWith('image/')) {
                throw new Error('Please choose an image file.');
            }

            if (typeof createImageBitmap === 'function') {
                const bitmap = await createImageBitmap(file);
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = bitmap.width;
                    canvas.height = bitmap.height;
                    canvas.getContext('2d').drawImage(bitmap, 0, 0);
                    return this.compressCanvas(canvas, options);
                } finally {
                    bitmap.close?.();
                }
            }

            const dataUrl = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(new Error('Unable to read the selected image.'));
                reader.readAsDataURL(file);
            });

            const image = await new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = () => reject(new Error('Unable to process the selected image.'));
                img.src = dataUrl;
            });

            const canvas = document.createElement('canvas');
            canvas.width = image.naturalWidth || image.width;
            canvas.height = image.naturalHeight || image.height;
            canvas.getContext('2d').drawImage(image, 0, 0);
            return this.compressCanvas(canvas, options);
        },
    };
</script>
