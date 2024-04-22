<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivablePayment extends Model
{
    use HasFactory;
    protected $table="receivable_payments";
    protected $fillable=["amount","user_id","receivable_id","attachment","remark","date"];
    public function receivable():BelongsTo{
        return $this->belongsTo(Receivable::class);
    }
}
