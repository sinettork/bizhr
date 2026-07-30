<?php

return [
    'qr' => [
        // Short enough to make photographed QR codes impractical to reuse.
        'session_lifetime_seconds' => (int) env('ATTENDANCE_QR_LIFETIME_SECONDS', 45),

        // Prevent a second scan from immediately changing check-in into checkout.
        'minimum_checkout_minutes' => (int) env('ATTENDANCE_QR_MIN_CHECKOUT_MINUTES', 5),

        // Reject coarse network-based location readings.
        'maximum_accuracy_meters' => (float) env('ATTENDANCE_QR_MAX_ACCURACY_METERS', 100),
    ],
];
