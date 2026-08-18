import { cp, copyFile, mkdir } from 'node:fs/promises';

await mkdir(new URL('../public/vendor/htmx/', import.meta.url), { recursive: true });
await copyFile(
    new URL('../node_modules/htmx.org/dist/htmx.min.js', import.meta.url),
    new URL('../public/vendor/htmx/htmx.min.js', import.meta.url),
);

await mkdir(new URL('../public/vendor/bootstrap/css/', import.meta.url), { recursive: true });
await mkdir(new URL('../public/vendor/bootstrap/js/', import.meta.url), { recursive: true });
await copyFile(
    new URL('../node_modules/bootstrap/dist/css/bootstrap.min.css', import.meta.url),
    new URL('../public/vendor/bootstrap/css/bootstrap.min.css', import.meta.url),
);
await copyFile(
    new URL('../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', import.meta.url),
    new URL('../public/vendor/bootstrap/js/bootstrap.bundle.min.js', import.meta.url),
);

await mkdir(new URL('../public/vendor/fontawesome/css/', import.meta.url), { recursive: true });
await copyFile(
    new URL('../node_modules/@fortawesome/fontawesome-free/css/all.min.css', import.meta.url),
    new URL('../public/vendor/fontawesome/css/all.min.css', import.meta.url),
);
await cp(
    new URL('../node_modules/@fortawesome/fontawesome-free/webfonts/', import.meta.url),
    new URL('../public/vendor/fontawesome/webfonts/', import.meta.url),
    { recursive: true, force: true },
);

console.log('Copied pinned frontend vendor assets.');
