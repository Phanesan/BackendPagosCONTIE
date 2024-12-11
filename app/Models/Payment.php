<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_status',
        'amount',
        'article_id'
    ];

    public static $rules = [
        'user_id' => 'required',
        'amount' => 'required|numeric',
        'article_id' => 'required'
    ];

    public static $updateRules = [
        'amount' => 'required|numeric',
        'article_id' => 'required'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function article() {
        return $this->belongsTo(Article::class);
    }
}
