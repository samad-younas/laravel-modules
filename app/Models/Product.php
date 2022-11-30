<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }
    public function likes(){
        return $this->hasMany(Like::class,'likeable_id');
     }
    public function liked()
    {
        // if (is_null($userId)) {
            $userId = Auth::id();
        // }
        return $this->likes()->where('user_id',  auth()->id())->exists();
    }
}
