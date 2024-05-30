<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;
    protected $table='password_reset_tokens';
    public $timestamps = false; 
    protected $primaryKey = 'email'; // Specify 'email' as the primary key
    public $incrementing = false; // Set to false to indicate that the primary key is not auto-incrementing
    protected $keyType = 'string'; // Specify the key type as 'string'
    protected $fillable=['email','token','created_at'];
}
