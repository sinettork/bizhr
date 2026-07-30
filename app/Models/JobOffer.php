<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JobOffer extends Model{protected $guarded=[];protected function casts():array{return['salary_amount'=>'decimal:2','proposed_start_date'=>'date','expires_at'=>'date','approved_at'=>'datetime','responded_at'=>'datetime'];}public function applicant(){return $this->belongsTo(JobApplicant::class);}public function creator(){return $this->belongsTo(User::class,'created_by');}public function approver(){return $this->belongsTo(User::class,'approved_by');}}
