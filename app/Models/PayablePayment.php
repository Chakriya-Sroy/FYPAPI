<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayablePayment extends Model
{
    use HasFactory;
    protected $table="payable_payments";
    protected $fillable=["amount","payable_id","attachment","remark","date"];
    
    public function payable():BelongsTo{
        return $this->belongsTo(Payable::class);
    }
}
