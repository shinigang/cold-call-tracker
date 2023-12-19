<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Social;

use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Events\AddingTeamMember;

class SocialController extends Controller
{
    public function redirect($provider)
    {
        if ($provider == 'google') {
            return Socialite::driver('google')->scopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ])->with(["access_type" => "offline", "prompt" => "consent select_account"])->redirect();
        }
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        // uncomment the code below to see the auth code via artisan google-calendar:quickstart
        // dd(request()->code);
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $th) {
            return redirect(route('login'));
        }

        // check if already exists
        $user = User::where('email', $socialUser->getEmail())->first();

        // if doesn't exist
        if (!$user) {
            // create user
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'nickname' => $socialUser->getNickname(),
                'password' => Hash::make(Str::random(7)),
                'current_team_id' => 1,
                'availability' => [
                    'days_of_week' => [
                        'sun' => false,
                        'mon' => true,
                        'tue' => true,
                        'wed' => true,
                        'thu' => true,
                        'fri' => true,
                        'sat' => false
                    ],
                    'shift_start' => '08:00',
                    'shift_end' => '17:00',
                    'meeting_duration' => 60
                ]
            ]);
            // create socials for user
            $user->socials()->create([
                'provider_id' => $socialUser->getId(),
                'provider' => $provider,
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken
            ]);

            // create default team
            // $user->ownedTeams()->save(Team::forceCreate([
            //     'user_id' => $user->id,
            //     'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            //     'personal_team' => true,
            // ]));

            // Check team invitation if email exists
            $invitation = TeamInvitation::where('email', $user->email)->first();

            // Joins the team with invitation or the default team if none
            $team = Team::find($invitation ? $invitation->team_id : 1);
            AddingTeamMember::dispatch($team, $user);
            $team->users()->attach(
                $user,
                ['role' => $invitation ? $invitation->role : 'caller']
            );
            TeamMemberAdded::dispatch($team, $user);

            // Remove team invitation if exists
            if ($invitation) {
                $invitation->delete();
            }
        }

        // if user does exist
        $socials = Social::where('provider', $provider)->where('user_id', $user->id)->first();
        // check if user doesn't have socials
        if (!$socials) {
            // add socials to user
            $user->socials()->create([
                'provider_id' => $socialUser->getId(),
                'provider' => $provider,
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken
            ]);
        }
        // login user
        auth()->login($user);

        if ($provider == 'google') {
            // update user google metadata
            $user->setGoogleMetadata(
                $socialUser->getId(),
                $socialUser->token,
                $socialUser->refreshToken,
                $socialUser->expiresIn,
            );
        }

        // redirect to the app
        return redirect('/dashboard');
    }
}
