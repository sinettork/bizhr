<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class ExpenseClaim extends Model{use SoftDeletes;protected $guarded=[];protected function casts():array{return['expense_date'=>'date','amount'=>'decimal:2','manager_reviewed_at'=>'datetime','accountant_reviewed_at'=>'datetime','paid_at'=>'datetime'];}public function employee(){return $this->belongsTo(Employee::class);}}
