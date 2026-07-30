<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Asset extends Model{use SoftDeletes;protected $guarded=[];protected function casts():array{return['purchase_date'=>'date','purchase_cost'=>'decimal:2'];}public function assignments(){return $this->hasMany(AssetAssignment::class);}}
