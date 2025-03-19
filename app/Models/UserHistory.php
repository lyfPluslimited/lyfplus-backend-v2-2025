<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    protected $table = 'userHistoryForm';

    protected $primaryKey = 'historyFormID';

    protected $fillable = [
        'consultation_for', 'the_name_consultation_for', 'reason_consultation',
        'currently_on_medication', 'pregnant_woman', 'diagnostic_findings_attachment',
        'identity_card_attachment', 'userID', 'specialistID', 'history_status',
        'history_time', 'medications_list', 'other', 'symptoms'
    ];

    public function user(){
        return $this->hasOne(User::class, 'userID', 'userID');
    }
}
