<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';

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
	
	public static function boot()
	{
		parent::boot();
		
		static::creating(function ($user) {
			$user->activation_token = str_random(30);
		});
	}
	
	public function gravatar($size = '100')
	{
		$hash = md5(strtolower(trim($this->attributes['email'])));
		return "http://www.gravatar.com/avatar/$hash?s=$size";
	}
	
	public function sendPasswordResetNotification($token)
	{
		$this->notify(new ResetPassword($token));
	}
	//一个用户有多条微博
	public function statuses()
	{
		return $this->hasMany(Status::class);
	}
	//获取多条微博并排序
	public function feed()
	{
		$user_ids = Auth::user()->followings->pluck('id')->toArray();
		array_push($user_ids, Auth::user()->id);
		return Status::whereIn('user_id', $user_ids)
			->with('user')
			->orderBy('created_at', 'desc');
	}
	//用户与粉丝是多对多的关系
	public function followers()
	{
		return $this->belongsToMany(User::Class,'followers','user_id','follower_id');
	}
	public function followings()
	{
		return $this->belongsToMany(User::Class,'followers','follower_id','user_id');
	}
	//关注
	public function follow($user_ids)
	{
		if(!is_array($user_ids)){
			$user_ids=compact('user_ids');
		}
		return $this->followings()->sync($user_ids,false);
	}
	//取消关注
	public function unfollow($user_ids)
	{
		if(!is_array($user_ids))
		{
			$user_ids = compact('user_ids');
		}
		return $this->followings()->detach($user_ids);
	}
	//是否关注判断
	public function isFollowing($user_ids)
	{
//		if(!is_array($user_ids))
//		{
//			$user_ids = compact('user_ids');
//		}
//		var_dump($user_ids);exit;
		return $this->followings->contains($user_ids);
	}
}
