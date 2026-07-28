# BizHR cleanup and UI preservation audit

## Root cause of the moved-folder UI glitch

The previous cleaned archive still contained generated files under `storage/framework/views`, including compiled Blade, Flux, Blaze, and Livewire views. Many of those files embedded the old absolute Windows path:

```text
D:\www\bizhr\...
```

Compiled views and framework caches must not be moved between project folders. They can load stale component markup and make the interface appear broken or inconsistent.

## Preserved unchanged

- All active Blade pages and layouts
- Sidebar and navigation UX/UI
- Tailwind application stylesheet
- JavaScript source
- Flux and Livewire view usage
- Existing `public/build` production assets
- Khmer typography and current visual styling
- Existing SQLite database
- Existing employee profile-photo uploads

## Removed

- Old compiled Blade/Flux/Livewire views
- Framework cache and session runtime files
- Old logs
- Old UI backup folder under `storage/app`
- Temporary Livewire upload files
- Unused duplicate/dead code identified in the previous audit
- `vendor`, `node_modules`, and the nested unrelated Laravel scaffold

## Verification

- Active UI source and compiled assets match the original project byte-for-byte.
- PHP syntax validation passed for application, route, and database PHP files.
- The login page successfully booted and returned HTTP 200 with the Vite stylesheet and Flux markup loaded when tested with portable runtime settings.
