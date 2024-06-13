<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivableTransaction extends Model
{
    use HasFactory;
    protected $table="receivable_transactions";
    protected $fillable=["transaction_type","receivable_id","receivableCreated","transaction_date","amount","customer_id"];
    
    public function customer():BelongsTo{
        return $this->belongsTo(Customer::class);
    }
 
}
