<h1>{{{ $post->title }}}</h1>

<p class="muted">Posted by {{{ $post->author }}} {{{ $post->timeAgo }}}.</p>
<p>{{{ $post->content }}}</p>
<p><span class="badge">{{{ $post->comments->count() }}} comments</span></p>

@if($post->commentsEnabled)
	<hr>

	{{ $comments->show('blog-post-'.$post->id) }}
@endif