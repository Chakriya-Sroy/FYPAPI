<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;
    protected $table="suppliers";
    protected $fillable=["user_id","fullname","email","address","phone","remark"];
    
    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }
    public function payables():HasMany{
        return $this->hasMany(Payable::class);
    }
    public function transactions():HasMany{
        return $this->hasMany(PayableTransaction::class);
    }
}
