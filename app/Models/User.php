<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'careusers';

    public $timestamps = false;

    protected $primaryKey = 'userID';

    protected $fillable = [
        'deleted', 'call_payment_id', 'user_image',
        'consultation_payment_id', 'subscription_payment_id','specializationID',
        'incentive_doctor','qrcode','incentive_percentage','specilizationAreaID',
        'height', 'weight', 'blood_group'
    ];

    public function specialization(){
        return $this->belongsTo(Specialization::class,'specializationID', 'specializationID');
    }

    public function hospital(){
        return $this->belongsTo(Hospital::class, 'specializationAreaID', 'specializationAreaID');
    }

    public function selcomOrder(){
        return $this->hasOne(SelcomModel::class);
    }

    public function posts(){
        return $this->hasMany(Forum::class);
    }

    public function abuseReport(){
        return $this->hasMany(Abuse::class);
    }

    public function articles(){
        return $this->hasMany(Article::class);
    }

    public function topics(){
        return $this->hasMany(Topics::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function patientList(){
        return $this->hasOne(DoctorPatientList::class);
    }

    public function services(){
        return $this->belongsToMany(HomeService::class, 'doctor_services', 'doctor_id', 'service_id')->withPivot('approved');
    }

    public function visits(){
        return $this->hasMany(ServiceConfirmation::class);
    }

    public function link(){
        return $this->hasOne(ReferralLink::class,'doctor_id','userID');
    }

    public function patientSubscriptions(){
        return $this->hasMany(Subscription::class,'subscriber_id','userID',);
    }

    public function doctorSubscriptions(){
        return $this->hasMany(Subscription::class,'doctor_id','userID');
    }

    public function onlineTime(){
        return $this->hasMany(OnlineTime::class,'doctor_id','userID');
    }

    public function kpis(){
        return $this->belongsToMany(Kpi::class,'incentives','doctor_id','kpi_id')->withPivot('paid');
    }

    public function tracking(){
        return $this->hasMany(KpiTracking::class,'doctor_id','userID');
    }

    public function consultationPayments(){
        return $this->hasMany(ConsultationPayment::class,'doctor_id','userID');
    }

    public function subscriptionPayments(){
        return $this->hasMany(SubscriptionPayment::class, 'doctor_id', 'userID');
    }

    public function doctorChats(){
        return $this->hasMany(Chat::class,'specialist_id', 'userID');
    }

    public function patientChats(){
        return $this->hasMany(Chat::class,'patient_id','userID');
    }
    
    public function kpiPrices(){
        return $this->belongsToMany(Kpi::class,'kpi_prices','doctor_id','kpi_id')->withPivot('unit_amount');
    }

    public function prices(){
        return $this->hasMany(KpiPrice::class,'doctor_id','userID');
    }

    public function doctorConsultationHistory(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }

    public function patientConsultationHistory(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }
}
