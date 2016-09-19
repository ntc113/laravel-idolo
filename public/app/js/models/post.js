(function () {
    "use strict";
    angular.module("myApp").factory("Post", postFactory);
    function postFactory($http) {
        var Post = {
            nextPage: nextPage,
            postStatus: postStatus,
            getComment: getComment,
            getReplyComment: getReplyComment,
            getPostByUser: getPostByUser,
            leaveComment: leaveComment,
            getPost: getPost,
            likePost: likePost,
            unlikePost: unlikePost,
            getListLiked: getListLiked,
            likeComment: likeComment,
            unLikeComment: unLikeComment,
        };
        return Post;
        function nextPage(url) {

            return $http.get(url)
        }

        function postStatus(postUrl, params) {
            delete params.limit;
            delete params.cursor;
            return $http.post(postUrl, serializeData(params))
        }

        function getComment(postId, limit, offset) {
            var params = {device_id: 'pc'};
            var url = "/api/post/getcommentsbypostid/"+postId+"/"+offset+"/"+limit;
            return $http.get(url, {params: params})
        }
        
        function getReplyComment(commentId, limit) {
            var params = {device_id: 'pc'};
            var url = "/api/post/api_get_replies_by_comment_id/"+commentId+"/comment_id/asc/0/"+limit;
            return $http.get(url, {params: params})
        }

        function leaveComment(params) {
            params.method = "post";
            var url = "/api/post/commentpost";
            return $http.post(url, params)
        }

        function getPost(postId) {
            var url = "/api/post/getpostbyid/"+postId;
            return $http.get(url)
        }
        function getPostByUser(userId, cateId) {
            var url = "/api/post/getpostsbyuserid/"+userId+"/0/6/"+cateId+"?device_id=pc";
            return $http.get(url)
        }

        function likePost(postId) {
            var params = {post_id: postId};
            var url = "/api/post/likepost";
            return $http.post(url, params)
        }

        function unlikePost(postId) {
            var params = {post_id: postId};
            var url = "/api/post/unlikepost";
            return $http.post(url, params)
        }

        function getListLiked(postId, offset, numPerPage) {
            var url = "/api/post/getlistlike/"+postId+"/"+offset+"/"+numPerPage;
            return $http.get(url)
        }

        function likeComment(commentId) {
            var url = "/api/post/likecomment";
            return $http.post(url, {comment_id: commentId})
        }

        function unLikeComment(commentId) {
            var url = "/api/post/unlikecomment";
            return $http.post(url, {comment_id: commentId})
        }

    }
})();