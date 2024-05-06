<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Collator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

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
    
    public function collector(): HasOneThrough //not yet finalize
    {
        return $this->hasOneThrough(CustomerAndCollector::class, Collector::class);
    }

    public function transactions():HasMany{
        return $this->hasMany(ReceivableTransaction::class);
    }


    // how to get customer and collector
    /**
     * CustomerandCollector has customer_id that can link to customer 
     * Collector has collector_id that can link to user
     * Customer has user_id
     */
    
}
