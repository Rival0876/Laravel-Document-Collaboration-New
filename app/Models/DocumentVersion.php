<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $fillable = ['document_id', 'user_id', 'content_snapshot', 'change_summary'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}