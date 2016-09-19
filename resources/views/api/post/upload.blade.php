<div class="about-section">
   <div class="text-content">
     <div class="span7 offset1">
        @if(Session::has('success'))
          <div class="alert-box success">
          <h2>{!! Session::get('success') !!}</h2>
          </div>
        @endif
        <div class="secure">Upload form</div>
        {!! Form::open(array('url'=>'/api/post/upload','method'=>'POST', 'files'=>true)) !!}
         <div class="control-group">
          <div class="controls">
          {!! Form::file('attachments') !!}
	  <p class="errors">{!!$errors->first('attachments')!!}</p>
	@if(Session::has('error'))
	<p class="errors">{!! Session::get('error') !!}</p>
	@endif
        </div>
        <input type='hidden' name='user_id' value='6' >
        <input type='hidden' name='username' value='Công Mập' >
        <input type='hidden' name='user_avatar' value='http://usport.vn/app/images/avatar.png' >
        <input type='hidden' name='category_id' value="6">
        <input type='hidden' name='content' value="test upload video">
        <input type='hidden' name='type' value="video">

        </div>
        <div id="success"> </div>
      {!! Form::submit('Submit', array('class'=>'send-btn')) !!}
      {!! Form::close() !!}
      </div>
   </div>
</div>