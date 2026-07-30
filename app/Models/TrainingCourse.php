<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class TrainingCourse extends Model{use SoftDeletes;protected $guarded=[];protected function casts():array{return['is_mandatory'=>'boolean','is_active'=>'boolean'];}public function enrollments(){return $this->hasMany(TrainingEnrollment::class);}}
