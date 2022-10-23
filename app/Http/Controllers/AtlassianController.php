<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Socialite;

class AtlassianController extends Controller
{

    public function atlassianRedirect()
    {
        return Socialite::driver('atlassian')->redirect();
    }

    public function atlassianUserRegister()
    {
        $atlassianUser = Socialite::driver('atlassian')->stateless()->user();

        // These are the same fields we will put when creating them via the rake task
        $userFields = [
            'name' => $atlassianUser['name'],
            'email' => $atlassianUser['email'],
            'timezone' => $atlassianUser['zoneinfo'],
            'avatar' => $atlassianUser['picture'],
        ];
        $userKey = [
            'account_id' => $atlassianUser['account_id'],
        ];
        $user = User::updateOrCreate($userKey, $userFields);
        Auth::login($user);
        return redirect('/');
    }
}
