<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'translation';
    protected $primaryKey = 'translation_id';

    protected $fillable = [
        'target_language',
        'target_language_code',
        'translated_text',
        'transcription_id'

    ];
}
