<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $table = "images";
    public $timestamps = false;

    protected $fillable = [
        'name',
        'url',
        'owner_id'
    ];

    protected $attributes = array(
        'name' => 'Untitled',
        'users' => '[]'
    );

    protected $casts = [
        'users' => 'array'
    ];
}
