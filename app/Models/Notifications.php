<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifications extends Model
{
    use HasFactory;
    protected $table='notifications';
    protected $fillable=['message','sender_id','user_id','type','invitation_id'];

    public function user():BelongsTo{
        return $this->belongsTo(User::class,'user_id');
    }
}

