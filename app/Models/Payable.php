<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payable extends Model
{
    use HasFactory;
    protected $table='payables';
    protected $fillable=['title','supplier_id','amount','remaining','date','dueDate','status','payment_term','remark','attachment'];
    
    public function supplier():BelongsTo{
        return $this->belongsTo(Supplier::class);
    }
    public function payments():HasMany{
        return $this->hasMany(PayablePayment::class);
    }
}
