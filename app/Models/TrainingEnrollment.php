<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingEnrollment extends Model{protected $guarded=[];protected function casts():array{return['score'=>'decimal:2','due_date'=>'date','started_at'=>'datetime','completed_at'=>'datetime'];}public function course(){return $this->belongsTo(TrainingCourse::class,'training_course_id');}public function employee(){return $this->belongsTo(Employee::class);}}
