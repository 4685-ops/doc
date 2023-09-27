<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    // UserAddress user_addresses
    // protected $table = 'products';
    // protected $connection = 'mysql';
    // protected $primaryKey = 'id';

    // created_at updated_at
    // public $timestamps = true;
    // const CREATED_AT = 'add_time';
    // const UPDATED_AT = 'update_time';

    protected $casts = [
        'attr' => 'array'
    ];

    protected $fillable = [
        'title',
        'category_id',
        'is_on_sale',
        'pic_url',
        'price',
        'attr'
    ];

    //protected $guarded = [];


}
