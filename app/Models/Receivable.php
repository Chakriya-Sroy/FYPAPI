<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receivable extends Model
{
    use HasFactory;
    protected $table='receivables';
    protected $fillable=['customer_id','amount','remaining','date','dueDate','status','payment_term','remark','attachment'];

    public function customer():BelongsTo{
        return $this->belongsTo(Customer::class);
    }
    public function payments():HasMany{
        return $this->hasMany(ReceivablePayment::class);
    }
}
