'use strict';

/* Controllers */

angular.module('myApp.controllers', [])
.controller('rootCtrl', ['$scope', '$rootScope', '$uibModal', 'User', '$timeout', '$http', 'ngProgressFactory', '$window', '$localStorage',  function($scope, $rootScope, $uibModal, User, $timeout, $http, ngProgressFactory, $window, $localStorage) {

    /* variable */
    $rootScope.baseURL = window.location.protocol + "//" + window.location.host + "/";
    $rootScope.txtLength = 300;
    $rootScope.isMobile = false;
    $rootScope.authenticationAzCompleted = false;
    $rootScope.recentChats = {};
    $rootScope.boxChats = {};
    $rootScope.requestedUser = [];
    var isMobile = false;
    if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
        || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;
    if(isMobile){
        $rootScope.isMobile = true;
    }
    $rootScope.page = 'IDolo';
    $rootScope.showLoginBox = function () {
        if ($rootScope.loggedIn)return;
        $rootScope.remind = false;
        var modalInstance = $uibModal.open({
            size: "lg",
            windowTemplateUrl: "/templates/post/window_login.html",
            templateUrl: "/templates/home/login.html",
            windowClass: "modal-border",
            controller: "LoginCtrl"

        });
        modalInstance.result.then(function (data) {
            if (typeof data != "undefined" && data == "success")$rootScope.$broadcast("login-success", {})
        });
        return
    };
    $rootScope.loadingComment = false;
    $rootScope.showRegistrationBox = function () {
        if ($rootScope.loggedIn)return;
        $rootScope.remind = false;
        var modalInstance = $uibModal.open({
            size: "lg",
            windowTemplateUrl: "/templates/post/window_login.html",
            templateUrl: "/templates/home/registration.html",
            windowClass: "modal-border",
            controller: "LoginCtrl"

        });
        modalInstance.result.then(function (data) {
            if (typeof data != "undefined" && data == "success")$rootScope.$broadcast("login-success", {})
        });
        return
    };
    $rootScope.getApp = function () {
        var modalInstance = $uibModal.open({
            size: "m",
            templateUrl: "/templates/home/getapp.html",
            controller: "ComposeCtrl",
            windowTemplateUrl: "/templates/home/window.html",
        });

    };
    $rootScope.rules = function () {
        var modalInstance = $uibModal.open({
            size: "lg",
            windowTemplateUrl: "/templates/home/windowPolicy.html",
            templateUrl: "/templates/home/rules.html",
            controller: "ComposeCtrl"
        });
    };
    $rootScope.contact = function () {
        var modalInstance = $uibModal.open({
            size: "m",
            windowTemplateUrl: "/templates/home/window.html",
            templateUrl: "/templates/home/contact.html",
            controller: "ComposeCtrl"
        });
    };
    $rootScope.idoloReport = function () {
        var modalInstance = $uibModal.open({
            size: "m",
            templateUrl: "/templates/home/report.html",
            controller: "ComposeCtrl",
            windowTemplateUrl: "/templates/home/window.html",
        });

    };
    $rootScope.idoloHelp = function () {
        var modalInstance = $uibModal.open({
            size: "lg",
            templateUrl: "/templates/home/help.html",
            controller: "ComposeCtrl",
            windowTemplateUrl: "/templates/home/windowPolicy.html",
        });

    };
    $rootScope.idoloAbout = function () {
        var modalInstance = $uibModal.open({
            size: "m",
            templateUrl: "/templates/home/about.html",
            controller: "ComposeCtrl",
            windowTemplateUrl: "/templates/home/window.html",
        });

    };
    $rootScope.sendContact = function () {
        var params = {
            name: $scope.contact.username,
            email: $scope.contact.email,
            subject: $scope.contact.subject,
            message: $scope.contact.message,
        };
        $http.post('/api/contact', params )
        .success(function(data, status, headers, config) {
            if (data.error == 0) {
                alert ('your content is sent successfully');
            } else {
                alert ('cannot send. please try again later.');
            }
        });
    }
    $scope.logout = function () {
        $http.get("/api/user/logout").success(function (data, status, header, config) {
            if (!data.error) {
                $rootScope.loggedIn = false;
                $rootScope.me = {};
                $window.location.reload();
            }
        }).error(function (data, status, header, config) {

        })
    };
    $rootScope.openBoxChat = function (chatId, type, name, senderId) {
        if (!$rootScope.loggedIn) {
            $scope.showLoginBox();
            return
        }
        if(!chatId){
            alert('Data is updating, please wait.');
            return;
        }
        if(Object.keys($rootScope.boxChats).length > 2){
            for (var first in $rootScope.boxChats) break;
            delete $rootScope.boxChats[first];
        }
        $rootScope.boxChats[chatId] = {messages: {}, mini: false, focus: true, name: name, senderId: senderId}

        azstack.azGetListUnreadMessages(type, chatId);
        azstack.azGetListModifiedMessages(0, type, chatId);
    };
    $scope.show = false;
    $scope.init = function () {
        User.getUserInfo("me").success(function (data) {
            if (data.error == 0) {
                $rootScope.loggedIn = true;
                $rootScope.$broadcast("get-user-info-success", {});
                $rootScope.me = data.data;
            } else {
                $rootScope.loggedIn = false;
                $rootScope.appReady = true
            }
        }).error(function () {
            $rootScope.appReady = true
        });

        $rootScope.setTimeout = function(func, time) {
            var interval = setInterval(function() {
                clearInterval(interval);
                setTimeout(func, time);
            });
        };
        $rootScope.types = ['text', 'image', 'video'];
        $rootScope.menus = [];
        $http.get('/api/category/getlistcategories')
            .success(function(data, status, headers, config) {
                $rootScope.menus = data.data;
            });

        $timeout(function(){
            $scope.show = true;
        }, 2000);
        $scope.listUsers = [];
        $scope.preLoadingUsers = true;

        $http.get('/api/user/getlistusers/0/11')
            .success(function(data, status, headers, config) {
                $scope.listUsers = data.data;
                $scope.preLoadingUsers = false;
            });
    };
    $scope.shareFb = function (id) {

    };
    $scope.selectCate = function(){
        if (!$rootScope.loggedIn) {
            $scope.showLoginBox();
            return
        }
        // $scope.closeModal();
        var modalInstance = $uibModal.open({
            size: "m",
            windowTemplateUrl: "/templates/home/window.html",

            templateUrl: "/templates/post/compose.html",
            windowClass: "modal-border",
            controller: "ComposeCtrl"
        });
    };
    $scope.$on("login-success", function (event, args) {
        $scope.init();

    });
    $scope.init();
}])
.controller('ComposeCtrl', ['$scope','$http','$location','$rootScope', '$uibModal', '$uibModalInstance', '$state', function($scope,$http,$location,$rootScope, $uibModal, $uibModalInstance, $state) {
    $scope.closeModal = function () {
        $uibModalInstance.close()
    };
    $scope.attachments = [];
    $scope.is_anonymous = 0;
    $scope.privacy = 'Anonymous';
    $scope.uploadFileStatus = false;
    $scope.uploadFileSuccess = function(data){
        // console.log(data)
        $scope.uploadFileStatus = true;
        $scope.attachments.push(data);
    };
    $scope.changePrivacy = function(privacy){
        if(privacy == 'Anonymous'){
            $scope.privacy = 'Anonymous';
            $scope.is_anonymous = 1;
        }else{
            $scope.is_anonymous = 0;
            $scope.privacy = 'Every body';
        }
    };
    $scope.error = 0;
    $scope.error_msg = '';
    $scope.formData = {};
    var extWhiteList = ['mov', 'mp4', '3gp', 'wmv', 'flv', 'avi', 'flv', 'm4v', 'amv'];

    $scope.fileAdd = function (file, event, flow) {
        $scope.error = 0;
        $scope.error_msg = '';
        for (var i = 0; i < file.length; i++) {
            if (extWhiteList.indexOf(file[i].getExtension()) < 0) {
                file[i].pause();
                file[i].cancel();
                $scope.error = 1;
                $scope.error_msg = "incorrect file format.";
                $scope.uploadFileStatus = false;
                return;
            }
        }
    }
    $scope.save = function(){
        $scope.error = 0;
        $scope.error_msg = '';
        var params = {
            category_id: 6,
            attachments: $scope.attachments,
            content: $scope.formData.content,
            is_anonymous: $scope.is_anonymous,
            user_id: $rootScope.me.id,
            type: $scope.formData.type || 'video',
            // url: $scope.formData.url || '',

        };
        if(params.attachments == ''){
            $scope.error = 1;
            $scope.error_msg = 'Please attach a video.';
            console.log(params)
            return;
        }
        if(params.content == ''){
            $scope.error = 1;
            $scope.error_msg = 'Please type the caption';
            console.log(params)
            return;
        }
        $http.post('/api/post/newpost', params )
            .success(function(data, status, headers, config) {
                console.log(data);
                if(data.error == 0){
                    $state.go('post', {post_id: data.data.id}, {
                        location: "replace"
                    });
                    $scope.closeModal();
                }else {
                    $scope.error = 1;
                    $scope.error_msg = data.message;
                }
            })
    }
    $scope.compose = function(index){
        if (!$rootScope.loggedIn)return;
        $rootScope.create_category = $rootScope.menus[index];

        $scope.closeModal();
        var modalInstance = $uibModal.open({
            size: "m",
            windowTemplateUrl: "/templates/home/window.html",

            templateUrl: "/templates/post/compose.html",
            windowClass: "modal-border",
            controller: "ComposeCtrl"
        });
    };
}])
.controller('LoginCtrl', ['$scope','$http','$location','headerFooterData','$uibModalInstance','$uibModal','AuthService', '$rootScope', '$localStorage', function($scope, $http, $location, headerFooterData, $uibModalInstance, $uibModal, AuthService, $rootScope, $localStorage) {
    $scope.error = "";
    $scope.state = "";
    $scope.login = function () {
        if ($scope.state == "loading")return false;
        $scope.error = "";
        $http.post("/api/user/api_login/pc", {
            email: $scope.email,
            password: $scope.password
        }).success(function (data, status, header, config) {

            if (data.error == 0) {

                $scope.state = "success";
                $rootScope.loggedIn = true;
                $rootScope.$broadcast("login-success", {});
                $uibModalInstance.close("success");
                $rootScope.me = data.data;
                $rootScope.access_token = data.token;
            } else {
                $scope.error = data.error;
                $scope.state = "error"
            }
        }).error(function (data, status, header, config) {
            $scope.error = "System busy now, please try again later!";
            $scope.state = "error"
        })
    };
    $scope.signup = function () {
        if ($scope.state == "loading")return false;
        $scope.error = "";
        $http.post("/api/user/api_register/pc", {
            display_name: $scope.display_name,
            phone: $scope.phone,
            email: $scope.email,
            password: $scope.password
        }).success(function (data, status, header, config) {

            if (data.error == 0) {
                $scope.state = "success";
                $rootScope.loggedIn = true;
                $rootScope.$broadcast("login-success", {});
                $uibModalInstance.close("success");
                $rootScope.me = data.data;
                $rootScope.access_token = data.token;
            } else {
                $scope.error = data.error;
                $scope.state = "error"
            }
        }).error(function (data, status, header, config) {
            $scope.error = "System busy now, please try again later!";
            $scope.state = "error"
        })
    };
    $scope.loginFacebook = function (token) {
        FB.login(function(response) {
            if (response.authResponse) {
                $scope.token = FB.getAuthResponse()['accessToken'];
                 $http.get("/api/user/sociallogin/"+$scope.token).success(function (data, status, header, config) {
                    if (data.error == 0) {
                        $scope.state = "success";
                        $rootScope.loggedIn = true;
                        $rootScope.$broadcast("login-success", {});
                        $uibModalInstance.close("success");
                        $rootScope.me = data.data;
                        $rootScope.access_token = data.token;
                    } else {
                        $scope.error = data.error;
                        $scope.state = "error"
                    }
                }).error(function (data, status, header, config) {
                    $scope.error = "System busy now, please try again later!";
                    $scope.state = "error"
                })
            } else {
                console.log('User cancelled login or did not fully authorize.');
                $scope.error = "";//data.error;
                $scope.state = "error"
            }
        }, {
            scope: 'user_birthday,user_friends,email,public_profile,user_likes,user_posts,publish_actions,publish_pages', 
            return_scopes: true
        });
    };
    $scope.closeModal = function () {
        $uibModalInstance.close()
    };
    $scope.switchForm = function (to) {
        $scope.closeModal();
        var modalInstance = $uibModal.open({
            windowTemplateUrl: "/templates/post/window_login.html",
            templateUrl: "/view/home/" + to + ".html",
            windowClass: "modal-border",
            controller: "LoginCtrl",
            size: "lg",
        });
        modalInstance.result.then(function (data) {
        })
    };
}])
.controller('HomeCtrl', ['$scope','$http','$location', 'Post', '$timeout', '$rootScope', 'ngProgressFactory', '$state', function($scope,$http,$location, Post, $timeout, $rootScope, ngProgressFactory, $state) {
    $scope.posts = [];
    $scope.busy = false;
    $scope.numPerPage = 10;
    $scope.offset = 0;
    $scope.progressbar = ngProgressFactory.createInstance();
    $scope.preLoading = false;

    $rootScope.page = 'fb';
    $scope.nextPage = function() {
        if ($scope.busy) return;
        $scope.busy = true;
        $scope.progressbar.start();
        var apiUrl = '';
        if ($state.current.name === 'home') {
            apiUrl = '/api/post/getlistposts?';
        } else {
            apiUrl = '/api/post/gettopposts?';
        }
        $http.get(apiUrl + 'offset=' + $scope.offset+'&limit='+$scope.numPerPage)
            .success(function(data, status, headers, config) {
                if(data.error == 0){
                    var items = data.data;
                    for (var i = 0; i < items.length; i++) {
                        $scope.posts.push(items[i]);
                    }
                    $scope.busy = false;

                    //$timeout(function () {
                    //    $rootScope.$broadcast('masonry.reload');
                    //}, 500);
                    $timeout(function () {
                        $scope.preLoading = true;
                        $('.preLoading').remove();
                        $scope.progressbar.complete();

                    }, 100);
                    $scope.offset += $scope.numPerPage;
                }
            });
    };

}])
.controller('SearchBarCtrl', ['$scope','$http','$location', '$timeout', '$rootScope', '$state', function($scope,$http,$location, $timeout, $rootScope, $state) {
    $scope.keyword = "";
    $scope.search = function () {
        if (!$scope.keyword)return;
        var keyword = $scope.keyword;
        $scope.keyword = "";
        return $state.go("search", {keyword: keyword})
    }

}])
.controller('CategoryCtrl', ['$scope','$http','$location', 'Post', '$timeout', '$rootScope', '$stateParams', 'ngProgressFactory',
    function($scope,$http,$location, Post, $timeout, $rootScope, $stateParams, ngProgressFactory) {
    $scope.progressbar = ngProgressFactory.createInstance();
    $scope.progressbar.complete();
    $scope.progressbar.start();
    $rootScope.page = 'cateogory';
    var slug = $stateParams.slug;
    $scope.posts = [];
    $scope.busy = false;
    $scope.preLoading = false;

    $scope.numPerPage = 10;
    $scope.offset = 0;
    $scope.nextPage = function() {
        if ($scope.busy) return;
        $scope.busy = true;
        $http.get('/api/post/getpostsbycategoryslug/'+slug+'/'+$scope.offset+'/'+$scope.numPerPage)
            .success(function(data, status, headers, config) {
                var items = data.data;
                for (var i = 0; i < items.length; i++) {
                     $scope.posts.push(items[i]);
                }

                $scope.busy = false;
                $timeout(function () {
                    $scope.preLoading = true;
                    $('.preLoading').remove();
                    $scope.progressbar.complete();

                }, 100);
                $scope.offset += 10;
            });
    };

}]).controller('ListFeaturedCtrl', ['$scope','$http','$location','$stateParams', '$rootScope', function($scope,$http,$location,$stateParams, $rootScope) {
    $scope.featured = [];
        var slug = '';
        if($stateParams.slug){
            slug = $stateParams.slug;
        }
    $http.get('/api/post/gethotposts/0/4')
	.success(function(data, status, headers, config) {
        $scope.featured = data.data;
	});
}]).controller('ProfilesCtrl', ['$scope','$http','$state','$rootScope', function($scope,$http,$state,$rootScope) {

    if (!$rootScope.loggedIn) {
        $state.go("home");
        $scope.showLoginBox();
        return
    }
    $scope.tabSelected = "#tab-basic";
    $scope.tabChange = function(e){
        if (e.target.nodeName === 'A') {
            $scope.tabSelected = e.target.getAttribute("href");
            e.preventDefault();
        }
    }

    $scope.error = 0;
    $scope.error_msg = '';

    $scope.updateProfiles = function(){
        var params = {
            display_name: $scope.me.display_name,
            username: $scope.me.username,
            email: $scope.me.email,
            phone: $scope.me.phone
        };
        $http.post('/api/user/api_update_profile?device_id=pc', params )
            .success(function(data, status, headers, config) {
                if(data.error == 0){
                    $scope.error = 0;
                    $scope.error_msg = '';
                }else if(data.error == 2){
                    $state.go("home");
                    $scope.showLoginBox();
                }else{
                    $scope.error = 1;
                    $scope.error_msg = data.message;
                }
            })
    }
}]);