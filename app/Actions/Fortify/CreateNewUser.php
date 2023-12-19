<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Events\AddingTeamMember;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
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
            ]), function (User $user) {
                // $this->createTeam($user);

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
            });
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]));
    }
}
