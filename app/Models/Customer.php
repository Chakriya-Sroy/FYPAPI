<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    protected $table="customers";
    protected $fillable=["user_id","fullname","phone","gender","address","email","remark"];
    //Eloquent Relationship with other table
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }  
    public function receivables():HasMany{
        return $this->hasMany(Receivable::class);
    }
    
}
