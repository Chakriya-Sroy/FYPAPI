<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;
    protected $table="subscriptions";
    protected $fillable=["plan","user_id","start","end","active","type","price"];

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }
}
