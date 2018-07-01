<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Notifications\ResetPassword;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    protected static function boot(){
        parent::boot();
        static::creating(function($user){
            $user->activation_token = str_random(30);
        });
    }

    public function sendPasswordResetNotification($token){
        $this->notify(new ResetPassword($token));
    }

    public function statuses(){

        return $this->hasMany(Status::class);
    }

    public function feed(){
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids,Auth::user()->id);
        return Status::whereIn('user_id',$user_ids)
                ->with('user')
                ->orderBy('created_at','desc');
    }

    //用户的粉丝
    //user_id 表示被关注人，follower_id 表示被关注人的粉丝
    //第一个参数要被关联的模型的类型，即目标模型
    //第三个参数是定义此关联的模型在连接表中的外键，第四个参数是目标模型在连接表中的外键
    public function followers(){
        //当前用户获取属于他的粉丝,目标模型是粉丝,粉丝恰好也是User
        return $this->belongsToMany(User::class,'followers','user_id','follower_id');
    }

    //用户的关注
    public function followings(){
        return $this->belongsToMany(User::class,'followers','follower_id','user_id');
    }

    //是否已关注
    public function isFollowing($user_id){
        return $this->followings->contains($user_id);
    }

    //关注
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

}
