<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id', 'document_type', 'document_number', 'original_name',
        'file_path', 'issued_date', 'expiry_date', 'notes',
    ];

    protected function casts(): array
    {
        return ['issued_date' => 'date', 'expiry_date' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
