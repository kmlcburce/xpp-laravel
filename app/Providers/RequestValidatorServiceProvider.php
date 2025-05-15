<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class RequestValidatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

    static function registerValidator($data){
        $validate = Validator::make($data, [
            // 'username' => 'required|string|unique:accounts',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:16|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
        ]);

        if($validate->fails()){
            return [
                'error' => $validate->errors()->first()
            ];
        }

        if($validate->passes()){
            return true;
        }
    }
    static function forgotPasswordValidator($data){
        $validate = Validator::make($data, [
            'email' => 'required|email',
        ]);
        if($validate->passes()){
            return true;
        } else{
            return false;
        }

    }
}
