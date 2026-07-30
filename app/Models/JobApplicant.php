<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class JobApplicant extends Model{use SoftDeletes;protected $guarded=[];protected function casts():array{return['applied_at'=>'datetime'];}public function vacancy(){return $this->belongsTo(JobVacancy::class,'job_vacancy_id');}public function interviews(){return $this->hasMany(JobInterview::class);}public function offers(){return $this->hasMany(JobOffer::class);}}
