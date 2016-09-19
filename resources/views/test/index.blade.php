<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Title Page</title>

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
	<script>
		// [1] Load lên các thành phần cần thiết
	  window.fbAsyncInit = function() {
	    FB.init({
	      appId      : '234920570236221',
	      xfbml      : true,
	      version    : 'v2.7'
	    });
	  };

	  (function(d, s, id){
	     var js, fjs = d.getElementsByTagName(s)[0];
	     if (d.getElementById(id)) {return;}
	     js = d.createElement(s); js.id = id;
	     js.src = "//connect.facebook.net/en_US/sdk.js";
	     fjs.parentNode.insertBefore(js, fjs);
	   }(document, 'script', 'facebook-jssdk'));

	  // [2] Xử lý trạng thái đăng nhập
        function statusChangeCallback(response) {
            // Người dùng đã đăng nhập FB và đã đăng nhập vào ứng dụng
            if (response.status === 'connected') {
                ShowWelcome();
            }
                // Người dùng đã đăng nhập FB nhưng chưa đăng nhập ứng dụng
            else if (response.status === 'not_authorized') {
                ShowLoginButton();
            }
                // Người dùng chưa đăng nhập FB
            else {
                ShowLoginButton();
            }
        }

        // [3] Yêu cầu đăng nhập FB
        function RequestLoginFB() {
            window.location = 'http://graph.facebook.com/oauth/authorize?client_id=234920570236221&scope=public_profile,email,user_likes,user_birthday,user_education_history,user_work_history,user_posts,user_photos,user_videos,user_location,publish_actions,publish_pages,pages_manage_instant_articles&redirect_uri=http://usport.vn/test&response_type=token';
        }

        // [4] Hiển thị nút đăng nhập
        function ShowLoginButton() {
            document.getElementById('btb').setAttribute('style', 'display:block');
            document.getElementById('lbl').setAttribute('style', 'display:none');
        }

        // [5] Chào mừng người dùng đã đăng nhập
        function ShowWelcome() {
            document.getElementById('btb').setAttribute('style', 'display:none');
            FB.api('/me', function (response) {
                var name = response.name;
                var username = response.username;
                var id = response.id;
                document.getElementById('lbl').innerHTML = 'Tên=' + name + ' | username=' + username + ' | id=' + id;
                document.getElementById('lbl').setAttribute('style', 'display:block');
            });
        }
	</script>

		<h1 class="text-center">Facebook API</h1>
<div class="container">
	<!-- NÚT ĐĂNG NHẬP -->
    <input id="btb" type="button" value="ĐĂNG NHẬP" onclick="RequestLoginFB()" />
    <p id="lbl" style="display:none">BẠN ĐÃ ĐĂNG NHẬP THÀNH CÔNG!</p>
    <br />

    <!-- HIỂN THỊ NÚT LUIKE -->
    <div class="fb-like" data-href="http://usport.vn" data-layout="box_count" data-action="like" data-size="small" data-show-faces="false" data-share="false"></div>
    <br />
    <!-- HIỂN THỊ PAGE -->
    <div class="fb-page" data-href="https://www.facebook.com/dancing.channel" data-tabs="timeline" data-width="300" data-height="70" data-small-header="true" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/dancing.channel" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/dancing.channel">Thư Viện Lập Trình</a></blockquote></div>
    <br />
    <!-- COMMENT -->
    <div class="fb-comments" data-href="http://usport.vn" data-numposts="10"></div>

    <p>POST VIDEO</p>
    <form action="https://graph-video.facebook.com/583524488486094/videos" method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload_phase" value="start" />
        <br /><br />
        file_size <br />
        <input type="text" name="file_size" value="152043520" />
        <br /><br />
        <!-- link<br />
        <input type="text" name="link" />
        <br /><br /> -->
        access_token<br />
        <input type="text" name="access_token" />
        <input type="submit" />

    </form>
    <br /><br />
    <br /><br />

    <p>POST MESSAGE</p>
    <form action="https://graph.facebook.com/583524488486094/feed" method="post" enctype="multipart/form-data">
        message<br />
        <input type="text" name="message" />
        <br /><br />
        link<br />
        <input type="text" name="link" />
        <br /><br />
        picture<br />
        <input type="text" name="picture" />
        <br /><br />
        description<br />
        <input type="text" name="description" />
        <br /><br />
        caption<br />
        <input type="text" name="caption" />
        <br /><br />
        name<br />
        <input type="text" name="name" />
        <br /><br />
        access_token<br />
        <input type="text" name="access_token" />
        <input type="submit" />

    </form>
</div>
		

    <!-- #################################################################### -->

		<!-- jQuery -->
		<script src="//code.jquery.com/jquery.js"></script>
		<!-- Bootstrap JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
		<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
 		<script src="Hello World"></script>
	</body>
</html>