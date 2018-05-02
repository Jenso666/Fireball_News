<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}cms.news.comments{/lang} <span class="badge">{@$commentList->countObjects()}</span></h2>
	</header>

	{include file='__commentJavaScript' commentContainerID='newsCommentList'}

	<ul id="newsCommentList" class="commentList containerList" data-can-add="{if $commentCanAdd}true{else}false{/if}" data-object-id="{@$news->newsID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{@$commentList->countObjects()}" data-last-comment-time="{@$lastCommentTime}">
		{include file='commentListAddComment' wysiwygSelector='newsCommentListAddComment'}
		{include file='commentList'}
	</ul>
</section>
