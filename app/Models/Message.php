<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
      protected $fillable=[
        'conversation_id',
        'role',
        'message'
    ];



    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
