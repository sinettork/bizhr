<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JobInterview extends Model{protected $guarded=[];protected function casts():array{return['scheduled_at'=>'datetime','completed_at'=>'datetime'];}public function applicant(){return $this->belongsTo(JobApplicant::class);}public function interviewer(){return $this->belongsTo(User::class,'interviewer_id');}}
