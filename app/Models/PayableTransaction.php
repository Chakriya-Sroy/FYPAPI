<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayableTransaction extends Model
{
    use HasFactory;
    protected $table="payable_transactions";
    protected $fillable=["transaction_type","payable_id","payment_id","transaction_date","amount","supplier_id"];
    public function supplier():BelongsTo{
        return $this->belongsTo(Supplier::class);
    }
}
