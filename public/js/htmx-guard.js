(() => {
    if (!window.htmx) return;

    document.body.addEventListener('htmx:beforeSwap', (event) => {
        const target = event.detail?.target;
        if (!(target instanceof Element) || !target.matches('[data-list-container]')) return;

        const response = event.detail?.xhr?.responseText || '';
        const isFullDocument = /<html[\s>]/i.test(response) || /<body[\s>]/i.test(response) || response.includes('class="app-navbar"');
        if (!isFullDocument) return;

        const parsed = new DOMParser().parseFromString(response, 'text/html');
        const replacement = parsed.querySelector('[data-list-container]');

        if (!replacement) {
            event.detail.shouldSwap = false;
            const responseUrl = event.detail?.xhr?.responseURL;
            window.location.assign(responseUrl || window.location.href);
            return;
        }

        // Some legacy/list templates still return the complete Blade layout for
        // HTMX requests. Select only the intended results container so the app
        // shell (navigation, command bar, scripts) can never be nested inside it.
        event.detail.selectOverride = '[data-list-container]';
    });
})();
