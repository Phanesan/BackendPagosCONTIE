<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
    ];

    protected $casts = [
        'publication_date' => 'datetime',
    ];

    public static $rules = [
        'title' => 'required',
        'content' => 'required',
    ];

    public function authors() {
        return $this->belongsToMany(User::class);
    }
}
