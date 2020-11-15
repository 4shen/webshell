<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Http\Controllers;

use AltThree\Validator\ValidationException;
use CachetHQ\Cachet\Bus\Commands\Subscriber\SubscribeSubscriberCommand;
use CachetHQ\Cachet\Bus\Commands\Subscriber\UnsubscribeSubscriberCommand;
use CachetHQ\Cachet\Bus\Commands\Subscriber\UnsubscribeSubscriptionCommand;
use CachetHQ\Cachet\Bus\Commands\Subscriber\UpdateSubscriberSubscriptionCommand;
use CachetHQ\Cachet\Bus\Commands\Subscriber\VerifySubscriberCommand;
use CachetHQ\Cachet\Models\Component;
use CachetHQ\Cachet\Models\ComponentGroup;
use CachetHQ\Cachet\Models\Subscriber;
use CachetHQ\Cachet\Models\Subscription;
use CachetHQ\Cachet\Notifications\Subscriber\ManageSubscriptionNotification;
use GrahamCampbell\Binput\Facades\Binput;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This is the subscribe controller.
 *
 * @author James Brooks <james@alt-three.com>
 */
class SubscribeController extends Controller
{
    /**
     * The illuminate guard instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new subscribe controller instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Show the subscribe by email page.
     *
     * @return \Illuminate\View\View
     */
    public function showSubscribe()
    {
        return View::make('subscribe.subscribe')
            ->withAboutApp(Markdown::convertToHtml(Config::get('setting.app_about')));
    }

    /**
     * Handle the subscribe user.
     *
     * @return \Illuminate\View\View
     */
    public function postSubscribe()
    {
        $email = Binput::get('email');
        $subscriptions = Binput::get('subscriptions');
        $verified = app(Repository::class)->get('setting.skip_subscriber_verification');

        try {
            $subscription = execute(new SubscribeSubscriberCommand($email, $verified));
        } catch (ValidationException $e) {
            return cachet_redirect('status-page')
                ->withInput(Binput::all())
                ->withTitle(sprintf('%s %s', trans('dashboard.notifications.whoops'), trans('cachet.subscriber.email.failure')))
                ->withErrors($e->getMessageBag());
        }

        // Send the subscriber a link to manage their subscription.
        $subscription->notify(new ManageSubscriptionNotification());

        return redirect()->back()->withSuccess(
            sprintf(
                '%s %s',
                trans('dashboard.notifications.awesome'),
                trans('cachet.subscriber.email.manage_subscription')
            )
        );
    }

    /**
     * Handle the verify subscriber email.
     *
     * @param string|null $code
     *
     * @return \Illuminate\View\View
     */
    public function getVerify($code = null)
    {
        if ($code === null) {
            throw new NotFoundHttpException();
        }

        $subscriber = Subscriber::where('verify_code', '=', $code)->first();

        if (!$subscriber) {
            throw new BadRequestHttpException();
        }

        if (!$subscriber->is_verified) {
            execute(new VerifySubscriberCommand($subscriber));
        }

        return redirect()->to(URL::signedRoute(cachet_route_generator('subscribe.manage'), ['code' => $code]))
            ->withSuccess(sprintf('%s %s', trans('dashboard.notifications.awesome'), trans('cachet.subscriber.email.subscribed')));
    }

    /**
     * Handle the unsubscribe.
     *
     * @param string|null $code
     * @param int|null    $subscription
     *
     * @return \Illuminate\View\View
     */
    public function getUnsubscribe($code = null, $subscription = null)
    {
        if ($code === null) {
            throw new NotFoundHttpException();
        }

        $subscriber = Subscriber::where('verify_code', '=', $code)->first();

        if (!$subscriber || !$subscriber->is_verified) {
            throw new BadRequestHttpException();
        }

        if ($subscription) {
            execute(new UnsubscribeSubscriptionCommand(Subscription::forSubscriber($subscriber->id)->firstOrFail()));
        } else {
            execute(new UnsubscribeSubscriberCommand($subscriber));
        }

        return cachet_redirect('status-page')
            ->withSuccess(sprintf('%s %s', trans('dashboard.notifications.awesome'), trans('cachet.subscriber.email.unsubscribed')));
    }

    /**
     * Shows the subscription manager page.
     *
     * @param string|null $code
     *
     * @return \Illuminate\View\View
     */
    public function showManage($code = null)
    {
        if ($code === null) {
            throw new NotFoundHttpException();
        }

        $includePrivate = $this->auth->check();

        $subscriber = Subscriber::where('verify_code', '=', $code)->first();
        $usedComponentGroups = Component::enabled()->authenticated($includePrivate)->where('group_id', '>', 0)->groupBy('group_id')->pluck('group_id');
        $componentGroups = ComponentGroup::whereIn('id', $usedComponentGroups)->orderBy('order')->get();
        $ungroupedComponents = Component::enabled()->authenticated($includePrivate)->where('group_id', '=', 0)->orderBy('order')->orderBy('created_at')->get();

        if (!$subscriber) {
            throw new BadRequestHttpException();
        }

        return View::make('subscribe.manage')
            ->withUngroupedComponents($ungroupedComponents)
            ->withSubscriber($subscriber)
            ->withSubscriptions($subscriber->subscriptions->pluck('component_id')->all())
            ->withComponentGroups($componentGroups);
    }

    /**
     * Updates the subscription manager for a subscriber.
     *
     * @param string|null $code
     *
     * @return \Illuminate\View\View
     */
    public function postManage($code = null)
    {
        if ($code === null) {
            throw new NotFoundHttpException();
        }

        $subscriber = Subscriber::where('verify_code', '=', $code)->first();

        if (!$subscriber) {
            throw new BadRequestHttpException();
        }

        try {
            execute(new UpdateSubscriberSubscriptionCommand($subscriber, Binput::get('subscriptions')));
        } catch (ValidationException $e) {
            return redirect()->to(URL::signedRoute(cachet_route_generator('subscribe.manage'), ['code' => $subscriber->verify_code]))
                ->withInput(Binput::all())
                ->withTitle(sprintf('%s %s', trans('dashboard.notifications.whoops'), trans('cachet.subscriber.email.failure')))
                ->withErrors($e->getMessageBag());
        }

        return redirect()->to(URL::signedRoute(cachet_route_generator('subscribe.manage'), ['code' => $subscriber->verify_code]))
            ->withSuccess(sprintf('%s %s', trans('dashboard.notifications.awesome'), trans('cachet.subscriber.email.updated-subscribe')));
    }
}
