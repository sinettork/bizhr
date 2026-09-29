<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
        ]);

        $request->session()->put('active_company_id', (int) $data['company_id']);

        $company = Company::query()->findOrFail((int) $data['company_id']);

        return redirect()
            ->route('dashboard')
            ->with('status', "Workspace switched to {$company->name}.");
    }
}
