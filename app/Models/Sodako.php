<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sodako extends Model
{
    protected $fillable = [
        'id_user',
        'price',
        'created_by',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    /**
     * Get the user that owns the deposit
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    /**
     * Get the user creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
