<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackMaster extends Model
{
    protected $table = 'feedbackmaster';

    protected $primaryKey = 'sn';

    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'questioncode',
        'question',
        'displayorder',
        'isactive',
    ];
}
