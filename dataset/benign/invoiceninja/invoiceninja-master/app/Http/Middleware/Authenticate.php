<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Invitation;
use App\Models\ProposalInvitation;
use Auth;
use Utils;
use Closure;
use Session;

/**
 * Class Authenticate.
 */
class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'user')
    {
        $authenticated = Auth::guard($guard)->check();
        $invitationKey = $request->invitation_key ?: $request->proposal_invitation_key;

        if ($guard == 'client') {
            if (! empty($request->invitation_key) || ! empty($request->proposal_invitation_key)) {
                $contact_key = session('contact_key');
                if ($contact_key) {
                    $contact = $this->getContact($contact_key);
                    $invitation = $this->getInvitation($invitationKey, ! empty($request->proposal_invitation_key));

                    if (! $invitation) {
                        return response()->view('error', [
                            'error' => trans('texts.invoice_not_found'),
                            'hideHeader' => true,
                        ]);
                    }

                    if ($contact && $contact->id != $invitation->contact_id) {
                        // This is a different client; reauthenticate
                        $authenticated = false;
                        Auth::guard($guard)->logout();
                    }
                    Session::put('contact_key', $invitation->contact->contact_key);
                }
            }

            if (! empty($request->contact_key)) {
                $contact_key = $request->contact_key;
                Session::put('contact_key', $contact_key);
            } else {
                $contact_key = session('contact_key');
            }

            $contact = false;
            if ($contact_key) {
                $contact = $this->getContact($contact_key);
            } elseif ($invitation = $this->getInvitation($invitationKey, ! empty($request->proposal_invitation_key))) {
                $contact = $invitation->contact;
                Session::put('contact_key', $contact->contact_key);
            }
            if (! $contact) {
                return \Redirect::to('client/login');
            }

            $account = $contact->account;

            if (Auth::guard('user')->check() && Auth::user('user')->account_id == $account->id) {
                // This is an admin; let them pretend to be a client
                $authenticated = true;
            }

            // Does this account require portal passwords?
            if ($account && (! $account->enable_portal_password || ! $account->hasFeature(FEATURE_CLIENT_PORTAL_PASSWORD))) {
                $authenticated = true;
            }

            if (! $authenticated && $contact && ! $contact->password) {
                $authenticated = true;
            }

            if (env('PHANTOMJS_SECRET') && $request->phantomjs_secret && hash_equals(env('PHANTOMJS_SECRET'), $request->phantomjs_secret)) {
                $authenticated = true;
            }

            if ($authenticated) {
                $request->merge(['contact' => $contact]);
                $account->loadLocalizationSettings($contact->client);
            }
        }

        if (! $authenticated) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                if ($guard == 'client') {
                    $url = '/client/login';
                    if (Utils::isNinjaProd()) {
                        if ($account && Utils::getSubdomain() == 'app') {
                            $url .= '?account_key=' . $account->account_key;
                        }
                    } else {
                        if ($account && Account::count() > 1) {
                            $url .= '?account_key=' . $account->account_key;
                        }
                    }
                } else {
                    $url = '/login';
                }
                return redirect()->guest($url);
            }
        }

        return $next($request);
    }

    /**
     * @param $key
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    protected function getInvitation($key, $isProposal = false)
    {
        if (! $key) {
            return false;
        }

        // check for extra params at end of value (from website feature)
        list($key) = explode('&', $key);
        $key = substr($key, 0, RANDOM_KEY_LENGTH);

        if ($isProposal) {
            $invitation = ProposalInvitation::withTrashed()->where('invitation_key', '=', $key)->first();
        } else {
            $invitation = Invitation::withTrashed()->where('invitation_key', '=', $key)->first();
        }

        if ($invitation && ! $invitation->is_deleted) {
            return $invitation;
        } else {
            return null;
        }
    }

    /**
     * @param $key
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    protected function getContact($key)
    {
        $contact = Contact::withTrashed()->where('contact_key', '=', $key)->first();
        if ($contact && ! $contact->is_deleted) {
            return $contact;
        } else {
            return null;
        }
    }
}
