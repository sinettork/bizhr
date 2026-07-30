<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class JobVacancy extends Model{use SoftDeletes;protected $guarded=[];protected function casts():array{return['open_date'=>'date','close_date'=>'date'];}public function applicants(){return $this->hasMany(JobApplicant::class);}public function position(){return $this->belongsTo(Position::class);}public function branch(){return $this->belongsTo(Branch::class);}}
