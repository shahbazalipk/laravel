@once
<script>
(function () {
    const cfg = document.getElementById('event-url-analytics');
    if (!cfg) return;

    const endpoint = cfg.dataset.endpoint;
    const step = cfg.dataset.step || 'entry';
    const storageVisitor = 'event_url_vid';
    const storageSession = 'event_url_sid';

    function readCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function uuid() {
        if (window.crypto?.randomUUID) return window.crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function sessionKey() {
        const existing = localStorage.getItem(storageSession) || readCookie('event_url_sid');
        if (existing && /^[a-f0-9]{32,64}$/i.test(existing)) return existing.toLowerCase();
        const generated = Array.from(crypto.getRandomValues(new Uint8Array(16)))
            .map((b) => b.toString(16).padStart(2, '0')).join('');
        localStorage.setItem(storageSession, generated);
        return generated;
    }

    function visitorUuid() {
        const existing = localStorage.getItem(storageVisitor) || readCookie('event_url_vid');
        if (existing && /^[0-9a-f-]{36}$/i.test(existing)) return existing;
        const generated = uuid();
        localStorage.setItem(storageVisitor, generated);
        return generated;
    }

    const params = new URLSearchParams(window.location.search);
    const startedAt = Date.now();
    let activeSeconds = 0;
    let lastTick = Date.now();
    let visible = document.visibilityState !== 'hidden';

    function tick() {
        const now = Date.now();
        if (visible) {
            activeSeconds += Math.max(0, Math.round((now - lastTick) / 1000));
        }
        lastTick = now;
    }

    function payload(eventType, extra = {}) {
        tick();
        return {
            event_type: eventType,
            step: extra.step || step,
            path: window.location.pathname,
            query: window.location.search ? window.location.search.slice(1) : '',
            referrer: document.referrer || '',
            language: navigator.language || '',
            screen_size: `${window.screen?.width || 0}x${window.screen?.height || 0}`,
            active_seconds: activeSeconds,
            visitor_uuid: visitorUuid(),
            session_key: sessionKey(),
            utm_source: params.get('utm_source') || '',
            utm_medium: params.get('utm_medium') || '',
            utm_campaign: params.get('utm_campaign') || '',
            utm_term: params.get('utm_term') || '',
            utm_content: params.get('utm_content') || '',
            meta: extra.meta || null,
        };
    }

    function send(eventType, extra = {}, useBeacon = false) {
        if (!endpoint) return;
        const body = JSON.stringify(payload(eventType, extra));
        if (useBeacon && navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/json' });
            navigator.sendBeacon(endpoint, blob);
            return;
        }
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
            credentials: 'same-origin',
            keepalive: true,
        }).catch(() => {});
    }

    document.addEventListener('visibilitychange', () => {
        tick();
        visible = document.visibilityState !== 'hidden';
        lastTick = Date.now();
        if (!visible) {
            send('heartbeat', {}, true);
        }
    });

    window.addEventListener('pagehide', () => send('exit', {}, true));

    send(cfg.dataset.eventType || 'page_view');
    setInterval(() => {
        if (visible) send('heartbeat');
    }, 15000);

    window.EventUrlAnalytics = {
        track(eventType, extra = {}) {
            send(eventType, extra);
        },
        setStep(nextStep) {
            send('step_view', { step: nextStep });
        },
    };
})();
</script>
@endonce
