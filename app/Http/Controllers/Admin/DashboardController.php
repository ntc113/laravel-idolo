<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Class DashboardController.
 *
 * @author 
 */
class DashboardController extends Controller
{
    public function index() {
    	$latestUsers = \App\Models\User::whereNotNull('fb_user_id')->orderBy('created_at', 'desc')->limit(8)->get();
    	$latestPosts = \App\Models\Post::whereNull('fb_post_id')->orderBy('created_at', 'desc')->limit(5)->get();

    	// User, Posts chart last 7 days
    	$latestSevenDatePostsQuery = \DB::select("select DATE_FORMAT(created_at, '%d %M') as date, COUNT(id) as number_post from posts 
    		WHERE   created_at BETWEEN NOW() - INTERVAL 7 DAY AND NOW()
			GROUP BY date");
    	$latestSevenDateUsersQuery = \DB::select("select DATE_FORMAT(created_at, '%d %M') as date, COUNT(id) as number_user from users 
    		WHERE   created_at BETWEEN NOW() - INTERVAL 7 DAY AND NOW()
			GROUP BY date");

    	$now = new \DateTime( "7 days ago");
	    $interval = new \DateInterval( 'P1D'); // 1 Day interval
	    $period = new \DatePeriod( $now, $interval, 6); // 7 Days

	    $latestSevenDatePosts = array();
	    $latestSevenDateUsers = array();
	    $startDay = '';
	    $endDay = '';
	    $dateChartData = '[';
	    $userChartData = '[';
	    $postChartData = '[';

	    $count = 0;
	    foreach( $period as $day) {
	    	$str_day = $day->format( 'd M, Y');
	    	($count == 0) ? $startDay = $str_day : $endDay = $str_day;
	        $key = $day->format( 'd M');
	        $latestSevenDatePosts[ $key ] = 0;
	        $latestSevenDateUsers[ $key ] = 0;
	        ($count == 0) ? $dateChartData .= $key : $dateChartData .= ",".$key;

	        $count ++;
	    }
	    $dateChartData .= ']';
	    foreach ($latestSevenDatePostsQuery as $date) {
	    	$latestSevenDatePosts[substr($date->date,0,6)] = $date->number_post;
	    }
	    foreach ($latestSevenDateUsersQuery as $date) {
	    	$latestSevenDateUsers[substr($date->date,0,6)] = $date->number_user;
	    }

	    $count = 0;
	    foreach ($latestSevenDatePosts as $no) {
	        ($count == 0) ? $postChartData .= $no : $postChartData .= ',' . $no;
	        $count ++;
	    }
	    $postChartData .= ']';

	    $count = 0;
	    foreach ($latestSevenDateUsers as $no) {
	        ($count == 0) ? $userChartData .= $no : $userChartData .= ',' . $no;
	        $count ++;
	    }
	    $userChartData .= ']';
	    $latestSevenData = [
	    	'startDay' => $startDay,
	    	'endDay' => $endDay,
	    	'date' => json_encode(array_keys($latestSevenDatePosts)),//$dateChartData,
	    	'posts' => $postChartData,
	    	'users' => $userChartData
	    ];
	    // Device chart
	    $useWebData = \App\Models\User::whereWeb(1)->count();
	    $useAndroidData = \App\Models\User::whereAndroid(1)->count();
	    $useIosData = \App\Models\User::whereIos(1)->count();
	    $useData = [
	    	'web' => $useWebData,
	    	'android' => $useAndroidData,
	    	'ios' => $useIosData,
	    ];

        return view('backend/layout/dashboard', compact('latestUsers', 'latestPosts', 'latestSevenData','useData'));
    }
}
