'use strict';


// Declare app level module which depends on filters, and services
angular.module('myApp', [
	'ui.router',
	'ui.bootstrap',
	'myApp.filters',
	'myApp.services',
	'myApp.directives',
	'myApp.controllers',
    'angularMoment',
    'wu.masonry',
    'infinite-scroll',
    'ngCookies',
    'bsLoadingOverlay',
    'ngProgress',
    'flow',
    'angulartics',
    'angulartics.google.analytics',
    'ui.utils',
    'luegg.directives',
    'ngStorage',
    'ngRoute',
    'youtube-embed'
	])
.config(function ($stateProvider, $urlRouterProvider, $httpProvider, $locationProvider, $routeProvider, $sceDelegateProvider) {

    /*$sceDelegateProvider.resourceUrlWhitelist([
        // Allow same origin resource loads.
        'self',
        // Allow loading from our assets domain.  Notice the difference between * and **.
        'http://demo.catcom.vn/templates/menu.html'
      ]);*/
    $urlRouterProvider.otherwise("error");
    $stateProvider.state("home", {
        url: "/", templateUrl: "/templates/home/home.html",
        controller: "HomeCtrl"
    }).state("hot", {
        url: "/hot", templateUrl: "/templates/home/home.html",
        controller: "HomeCtrl"
    }).state("post", {
        url: "/post/:post_id",
        templateUrl: "/templates/post/detail.html",
        resolve: {
            post: function (Post, $stateParams, $state) {
                return Post.getPost($stateParams.post_id).success(function (data) {
                    if (data.error != 0)return $state.go("error");
                    return data
                })
            }
        },
        controller: "PostDetailCtrl"
    }).state("search", {
        url: "/search?keyword",
        templateUrl: "/templates/home/search.html"
    }).state("users", {
        url: "/users",
        templateUrl: "/templates/home/search.html"
    }).state("profiles", {
        url: "/profiles",
        templateUrl: "/templates/user/profiles.html",
        controller: "ProfilesCtrl"
    }).state("chat", {
        url: "/chat",
        templateUrl: "/templates/chat/chat.html"
    }).state("chat/", {
        url: "/chat/:chatId",
        templateUrl: "/templates/chat/chat.html"
    }).state("user", {
        url: "/user/:userId",
        templateUrl: "/templates/user/index.html",
        "abstract": true,
        controller: "UserCtrl",
        resolve: {
            user: function ($stateParams, $state, User, $rootScope) {
                return User.getUserInfo($stateParams.userId).success(function (data, status, header, config) {
                    //if (!data.status)return $state.go("error");

                    return data
                })
            }
    }}).state("user.activity", {
        url: "", 
        templateUrl: "/templates/user/index.html"
    }).state("category", {
        url: "/category/:slug",
        templateUrl: "/templates/home/home.html",
        "abstract": true,
        controller: "CategoryCtrl",
        resolve: {}
    }).state("category.detail", {
        url: "", 
        templateUrl: "/templates/home/home.html"
    }).state("error", {
        url: "/error", 
        templateUrl: "/templates/error.html"
    });
    $locationProvider.html5Mode({enabled: true, requireBase: false}).hashPrefix("!")

})
.run(function(amMoment, $rootScope, $location, $anchorScroll, $state) {
    amMoment.changeLocale('vi');
    $rootScope.$on('$routeChangeSuccess', function(){
        ga('send', 'pageview', $location.path());
    });
    $rootScope.$on("$locationChangeSuccess", function() {
        $anchorScroll();
    });
    $rootScope.user = {};

});

/*window.fbAsyncInit = function() {
    FB.init({
      appId      : '1184650714881453',
      xfbml      : true,
      version    : 'v2.6'
    });
};

(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));*/
/*(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.6&appId=1184650714881453";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));*/