<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcription extends Model
{
    protected $table = 'transcription';
    protected $primaryKey = 'transcription_id';
    protected $fillable = [
        'audio_path',
        'text_content',
        'user_id',
        'screen_id'
    ];


    public function getAudioPathAttribute($value)
    {
        $actual_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
        return ($value == null ? '' : $actual_link . 'audio/' . $value);
    }





}
