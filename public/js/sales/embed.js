(function () {
    function parentHost() {
        try {
            return window.location.hostname || '';
        } catch (e) {
            return '';
        }
    }

    function mount(el) {
        var token = el.getAttribute('data-sales-form-token');
        if (!token) return;
        var base = el.getAttribute('data-sales-form-base') || '';
        var parent = encodeURIComponent(parentHost());
        var iframe = document.createElement('iframe');
        iframe.src = base + '/sales/embed/' + encodeURIComponent(token) + (parent ? ('?parent=' + parent) : '');
        iframe.style.width = '100%';
        iframe.style.border = '0';
        iframe.style.minHeight = '480px';
        iframe.setAttribute('title', 'Inquiry form');
        el.appendChild(iframe);
        window.addEventListener('message', function (event) {
            if (!event.data || event.data.type !== 'sales-form-resize') return;
            if (event.source !== iframe.contentWindow) return;
            iframe.style.height = Math.max(320, Number(event.data.height) || 480) + 'px';
        });
    }
    document.querySelectorAll('[data-sales-form-token]').forEach(mount);
})();
