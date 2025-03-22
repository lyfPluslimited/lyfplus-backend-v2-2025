<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model schema",
 *     @OA\Property(property="userID", type="integer", example=352),
 *     @OA\Property(property="firstName", type="string", example="Emmanuel"),
 *     @OA\Property(property="lastName", type="string", example="Sanga"),
 *     @OA\Property(property="dateOfBirth", type="string", format="date", nullable=true),
 *     @OA\Property(property="email", type="string", format="email", example="esanga530@gmail.com"),
 *     @OA\Property(property="password", type="string", example="$2y$10$..."),
 *     @OA\Property(property="phone", type="string", example="+255742559448"),
 *     @OA\Property(property="gender", type="string", nullable=true),
 *     @OA\Property(property="age", type="integer", nullable=true),
 *     @OA\Property(property="regionID", type="integer", nullable=true),
 *     @OA\Property(property="street", type="string", nullable=true),
 *     @OA\Property(property="lat", type="number", format="float", nullable=true),
 *     @OA\Property(property="longt", type="number", format="float", nullable=true),
 *     @OA\Property(property="geo", type="string", nullable=true),
 *     @OA\Property(property="timeSt", type="string", format="date-time", example="2024-11-26 22:21:43"),
 *     @OA\Property(property="doctorsIDnumber", type="string", nullable=true),
 *     @OA\Property(property="doctorsIDverificationStatus", type="string", example="Not Verified"),
 *     @OA\Property(property="status", type="string", example="PENDING"),
 *     @OA\Property(property="userRole", type="integer", example=1),
 *     @OA\Property(property="userNhifNumber", type="string", nullable=true),
 *     @OA\Property(property="onlineStatus", type="integer", example=0),
 *     @OA\Property(property="specializationID", type="integer", nullable=true),
 *     @OA\Property(property="lastOnlineTime", type="string", format="date-time", example="2024-11-26 19:21:43"),
 *     @OA\Property(property="user_image", type="string", format="uri", example="https://lyfplus.com/lyfPlus/images/profile/doctor.jpg"),
 *     @OA\Property(property="height", type="number", format="float", nullable=true),
 *     @OA\Property(property="weight", type="number", format="float", nullable=true),
 *     @OA\Property(property="blood_group", type="string", nullable=true),
 *     @OA\Property(property="doctorPromotionCode", type="string", nullable=true),
 *     @OA\Property(property="userPromotionCode", type="string", nullable=true),
 *     @OA\Property(property="allergy", type="string", nullable=true),
 *     @OA\Property(property="doctor_bio", type="string", nullable=true),
 *     @OA\Property(property="specilizationAreaID", type="integer", nullable=true),
 *     @OA\Property(property="experience", type="string", nullable=true),
 *     @OA\Property(property="consultation_fee", type="integer", example=0),
 *     @OA\Property(property="call_fee", type="number", format="float", nullable=true),
 *     @OA\Property(property="no_initial_consultation", type="string", example="0"),
 *     @OA\Property(property="no_remain_consulatation", type="integer", nullable=true),
 *     @OA\Property(property="consultation_availabiliy", type="string", example="OFF"),
 *     @OA\Property(property="phone_availability", type="string", example="OFF"),
 *     @OA\Property(property="patient_no", type="integer", nullable=true),
 *     @OA\Property(property="registration_source", type="string", example="mobile"),
 *     @OA\Property(property="ip_address", type="string", format="ipv4", example="197.250.96.211"),
 *     @OA\Property(property="deleted", type="integer", example=0),
 *     @OA\Property(property="country", type="string", example="Tanzania"),
 *     @OA\Property(property="currency", type="string", example="TZS"),
 *     @OA\Property(property="rate", type="number", format="float", example=0.00043)
 * )
 */
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
