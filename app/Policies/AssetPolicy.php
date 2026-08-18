<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function view(User $user, Asset $asset): bool
    {
        return $asset->company_id === $user->companyId() && $user->can('asset.view');
    }

    public function manage(User $user, Asset $asset): bool
    {
        return $asset->company_id === $user->companyId() && $user->can('asset.manage');
    }
}
