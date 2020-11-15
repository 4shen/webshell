<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use App\Models\LookupAccount;
use App\Models\LookupContact;
use App\Models\LookupInvitation;
use App\Models\LookupProposalInvitation;
use App\Models\LookupAccountToken;
use App\Models\LookupUser;
use Auth;
use Utils;

class DatabaseLookup
{
    public function handle(Request $request, Closure $next, $guard = 'user')
    {
        if (! env('MULTI_DB_ENABLED')) {
            return $next($request);
        }

        if ($guard == 'user') {
            if ($code = $request->confirmation_code) {
                LookupUser::setServerByField('confirmation_code', $code);
            } elseif (session(SESSION_DB_SERVER)) {
                if (Auth::viaRemember()) {
                    Auth::logout();
                } else {
                    // do nothing
                }
            } elseif (! Auth::check() && $email = $request->email) {
                LookupUser::setServerByField('email', $email);
            } else {
                Auth::logout();
            }
        } elseif ($guard == 'api') {
            if ($token = $request->header('X-Ninja-Token')) {
                LookupAccountToken::setServerByField('token', $token);
            } elseif ($email = $request->email) {
                LookupUser::setServerByField('email', $email);
            }
        } elseif ($guard == 'contact') {
            if ($key = request()->invitation_key) {
                LookupInvitation::setServerByField('invitation_key', $key);
            } elseif ($key = request()->proposal_invitation_key) {
                LookupProposalInvitation::setServerByField('invitation_key', $key);
            } elseif ($key = request()->contact_key ?: session('contact_key')) {
                LookupContact::setServerByField('contact_key', $key);
            } elseif ($key = request()->account_key) {
                LookupAccount::setServerByField('account_key', $key);
            } else {
                $subdomain = Utils::getSubdomain(\Request::server('HTTP_HOST'));
                if ($subdomain != 'app') {
                    LookupAccount::setServerByField('subdomain', $subdomain);
                }
            }
        } elseif ($guard == 'postmark') {
            LookupInvitation::setServerByField('message_id', request()->MessageID);
        } elseif ($guard == 'account') {
            if ($key = request()->account_key) {
                LookupAccount::setServerByField('account_key', $key);
            } else {
                $subdomain = Utils::getSubdomain(\Request::server('HTTP_HOST'));
                if ($subdomain != 'app') {
                    LookupAccount::setServerByField('subdomain', $subdomain);
                }
            }
        } elseif ($guard == 'license') {
            config(['database.default' => DB_NINJA_1]);
        }

        return $next($request);
    }
}
