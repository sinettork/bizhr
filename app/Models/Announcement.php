<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Announcement extends Model{use SoftDeletes;protected $guarded=[];protected function casts():array{return['published_at'=>'datetime','expires_at'=>'datetime','is_pinned'=>'boolean'];}}
