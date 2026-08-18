<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasPublicId;

    protected $fillable = [
        'employee_id', 'document_type', 'document_number', 'original_name',
        'file_path', 'issued_date', 'expiry_date', 'notes',
        'status', 'version', 'verified_by', 'verified_at', 'revoked_by', 'revoked_at', 'revocation_reason',
    ];

    protected function casts(): array
    {
        return ['issued_date' => 'date', 'expiry_date' => 'date', 'verified_at' => 'datetime', 'revoked_at' => 'datetime', 'version' => 'integer'];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
