/**
 * Acquire device GPS for emergency panic button.
 * Only resolves with real coordinates from the device — never fabricates a location.
 */
export function acquireEmergencyPosition(options = {}) {
    const maxWaitMs = options.maxWaitMs ?? 25000;
    const targetAccuracyM = options.targetAccuracyM ?? 50;

    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(makeGeoError('BROWSER_UNSUPPORTED', 'Peramban tidak mendukung GPS.'));
            return;
        }

        const geoOptions = {
            enableHighAccuracy: true,
            timeout: maxWaitMs,
            maximumAge: 0,
        };

        let watchId = null;
        let settled = false;
        let bestPosition = null;
        const startedAt = Date.now();

        const cleanup = () => {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
        };

        const settleResolve = (position) => {
            if (settled) return;
            settled = true;
            cleanup();
            resolve(position);
        };

        const settleReject = (error) => {
            if (settled) return;
            settled = true;
            cleanup();
            reject(normalizeGeoError(error));
        };

        const consider = (position) => {
            if (!position?.coords) return;

            const acc = position.coords.accuracy ?? Infinity;
            if (!bestPosition || acc < (bestPosition.coords.accuracy ?? Infinity)) {
                bestPosition = position;
            }

            const elapsed = Date.now() - startedAt;
            if (acc <= targetAccuracyM || elapsed >= 8000) {
                settleResolve(bestPosition);
            }
        };

        watchId = navigator.geolocation.watchPosition(
            consider,
            () => { /* watch errors handled by getCurrentPosition fallback */ },
            geoOptions
        );

        navigator.geolocation.getCurrentPosition(consider, () => {}, geoOptions);

        setTimeout(() => {
            if (settled) return;
            if (bestPosition) {
                settleResolve(bestPosition);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                settleResolve,
                (err) => {
                    if (bestPosition) {
                        settleResolve(bestPosition);
                    } else {
                        settleReject(err);
                    }
                },
                geoOptions
            );
        }, maxWaitMs);
    });
}

function makeGeoError(code, message) {
    return { code, message };
}

function normalizeGeoError(error) {
    const code = error?.code ?? error?.message ?? 'UNKNOWN';

    if (code === 1 || code === 'PERMISSION_DENIED') {
        return makeGeoError(
            'PERMISSION_DENIED',
            'Akses lokasi ditolak. Izinkan GPS/lokasi untuk situs ini di pengaturan browser, lalu coba lagi.'
        );
    }
    if (code === 2 || code === 'POSITION_UNAVAILABLE') {
        return makeGeoError(
            'POSITION_UNAVAILABLE',
            'GPS tidak tersedia. Aktifkan layanan lokasi di perangkat dan pastikan sinyal GPS bagus (area terbuka).'
        );
    }
    if (code === 3 || code === 'TIMEOUT') {
        return makeGeoError(
            'TIMEOUT',
            'Gagal mendapatkan lokasi GPS tepat waktu. Pindah ke area terbuka, pastikan GPS aktif, lalu coba lagi.'
        );
    }
    if (code === 'BROWSER_UNSUPPORTED') {
        return error;
    }

    return makeGeoError('UNKNOWN', 'Tidak dapat membaca lokasi GPS perangkat. Coba lagi.');
}

export function bindEmergencyPanicButton() {
    const panicButton = document.getElementById('panic-button');
    const form = document.getElementById('emergency-form');
    if (!panicButton || !form) return;

    const latEl = document.getElementById('emergency-lat');
    const lngEl = document.getElementById('emergency-lng');
    const accEl = document.getElementById('emergency-accuracy');
    const defaultLabel = panicButton.innerHTML;

    const setButtonState = (html, disabled = true) => {
        panicButton.disabled = disabled;
        panicButton.innerHTML = html;
    };

    const resetButton = () => {
        panicButton.disabled = false;
        panicButton.classList.add('animate-pulse-slow');
        panicButton.innerHTML = defaultLabel;
        if (window.lucide) window.lucide.createIcons();
    };

    panicButton.addEventListener('click', async (e) => {
        e.preventDefault();

        const confirmed = confirm(
            '⚠️ PERINGATAN: Apakah Anda benar-benar membutuhkan bantuan darurat?\n\n' +
            'Sistem akan membaca lokasi GPS perangkat Anda saat ini. ' +
            'Pastikan GPS/lokasi di perangkat dan browser sudah diizinkan.'
        );
        if (!confirmed) return;

        panicButton.classList.remove('animate-pulse-slow');
        setButtonState(
            '<span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>' +
            '<span>Mencari sinyal GPS...</span>'
        );

        if (window.showToast) {
            window.showToast('Mencari lokasi GPS perangkat Anda...', 'info');
        }

        try {
            const position = await acquireEmergencyPosition();
            const { latitude, longitude, accuracy } = position.coords;

            latEl.value = latitude.toFixed(8);
            lngEl.value = longitude.toFixed(8);
            if (accEl) {
                accEl.value = accuracy != null ? String(Math.round(accuracy)) : '';
            }

            setButtonState(
                '<span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>' +
                '<span>Mengirim sinyal darurat...</span>'
            );

            if (window.showToast) {
                const accText = accuracy != null ? ` (±${Math.round(accuracy)} m)` : '';
                window.showToast(`Lokasi GPS ditemukan${accText}. Mengirim alarm...`, 'info');
            }

            form.submit();
        } catch (error) {
            resetButton();
            alert(error.message || 'Gagal mendapatkan lokasi GPS. Sinyal darurat tidak dikirim.');
            if (window.showToast) {
                window.showToast(error.message || 'GPS gagal — sinyal tidak dikirim.', 'error');
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindEmergencyPanicButton();
});
