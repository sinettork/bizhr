(() => {
    const showActionToasts = (root = document) => {
        if (typeof window.bootstrap === 'undefined' || typeof window.bootstrap.Toast === 'undefined') {
            return;
        }

        root.querySelectorAll('[data-app-toast]').forEach((element) => {
            if (element.dataset.appToastReady === 'true') {
                return;
            }

            element.dataset.appToastReady = 'true';
            window.bootstrap.Toast.getOrCreateInstance(element).show();
        });
    };

    document.addEventListener('DOMContentLoaded', () => showActionToasts());
    document.addEventListener('htmx:afterSwap', (event) => showActionToasts(event.target));
})();
