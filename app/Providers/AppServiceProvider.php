<?php

namespace App\Providers;

use View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $segment1 = \Request::segment(1);
        $segment2 = \Request::segment(2);
        $segment3 = \Request::segment(3);
        if ($segment1 == 'admin' && $segment2 != 'login' && $segment2 != 'logout') {
            \Log::info($segment1 . '::' . $segment2 . '::' . $segment3);
            /* user */
            // not admin and not active
            $userNotActive = \App\Models\User::whereNotNull('fb_user_id')->where('status', '!=', 1)->count();
            View::share('userNotActive', $userNotActive);
            // all user
            $allUsers = \App\Models\User::whereNotNull('fb_user_id')->count();
            View::share('allUsers', $allUsers);
            // all user joined today
            $todayUsers = \App\Models\User::where('created_at','>=', date('Y-m-d 0:0:0'))->whereNotNull('fb_user_id')->count();
            View::share('todayUsers', $todayUsers);
            // rank user with number post
            $topUsers = \App\Models\User::where('posted', '>', 0)->orderBy('posted', 'DESC')->limit(5);
            View::share('topUsers', $topUsers);
            
            /* post */
            // all post
            $allPosts = \App\Models\Post::count();
            View::share('allPosts', $allPosts);
            // post has been published
            $postNotApprove = \App\Models\Post::whereNull('fb_post_id')->count();
            View::share('postNotApprove', $postNotApprove);
            // post not publish in day
            $newPostNotApprove = \App\Models\Post::where('created_at','>=',date('Y-m-d 0:0:0'))->whereNull('fb_post_id')->count();
            View::share('newPostNotApprove', $newPostNotApprove);

            /*dashboard*/
            $fanCount = 0;
            $tokenPage = getPageAccessToken();
            if ($tokenPage) {
                $url = config('cc.facebook_api.graph_url') . config('cc.facebook_api.fanpage_id') . '?fields=fan_count&access_token=' . $tokenPage;
                $pageData = getData($url);
                if ($pageData['error'] != 0) {
                    \Log::error('AppServiceProvider|pageData|'.json_decode($pageData));
                }
                $fanCount = $pageData['data']->fan_count;
            }
            
            View::share('fanCount', $fanCount);

            /*contact*/
            $allContacts = \App\Models\Contact::count();
            View::share('allContacts', $allContacts);
            $contactNotView = \App\Models\Contact::where('is_viewed', 0)->count();
            View::share('contactNotView', $contactNotView);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
