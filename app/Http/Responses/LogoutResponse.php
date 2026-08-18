<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

final class LogoutResponse implements LogoutResponseContract
{
    public function toResponse(mixed $request): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->with('status', 'អ្នកបានចាកចេញពីគណនីដោយសុវត្ថិភាព។');
    }
}
