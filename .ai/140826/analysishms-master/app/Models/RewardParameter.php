<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardParameter extends Model
{
    use HasFactory;
    protected $table = 'rwparameter';
    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'code',
        'sno',
        'name',
        'category',
        'rpointonamt',
        'rpoint',
        'rpointvalue',
        'limitlow',
        'limitup',
        'compoperator',
        'u_name',
        'u_ae',
        'validupto',
        'activeyn',
        'restcode',
        'minamtreedem'
    ];
}
