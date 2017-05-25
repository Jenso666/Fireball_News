{capture assign='headContent'}
	<script data-relocate="true" src="{@$__wcf->getPath('cms')}js/Fireball{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@LAST_UPDATE_TIME}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath('cms')}js/Fireball.News{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@LAST_UPDATE_TIME}"></script>
{/capture}

{capture assign='headerNavigation'}
	{if $__wcf->user->userID}
		<li class="jsOnly"><a title="{lang}wcf.user.objectWatch.manageSubscription{/lang}" class="jsSubscribeButton jsTooltip" data-object-type="de.codequake.cms.news" data-object-id="{@$news->newsID}"><span class="icon icon16 fa-bookmark"></span> <span class="invisible">{lang}wcf.user.objectWatch.manageSubscription{/lang}</span></a></li>
	{/if}
{/capture}

{capture assign='pageTitle'}{$news->getTitle()}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader articleContentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle" itemprop="name headline">{$news->getTitle()}</h1>
			<ul class="inlineList contentHeaderMetaData articleMetaData">
				<li itemprop="author" itemscope itemtype="http://schema.org/Person">
					<span class="icon icon16 fa-user"></span>
					{if $news->userID}
						<a href="{link controller='User' id=$news->userID title=$news->username}{/link}" class="userLink" data-user-id="{@$news->userID}" itemprop="url"> <span itemprop="name">{$news->username}</span> </a>
					{else}
						<span itemprop="name">{$news->username}</span>
					{/if}
				</li>

				<li>
					<span class="icon icon16 fa-clock-o"></span> <span>{@$news->time|time}</span>
					<meta itemprop="datePublished" content="{@$news->time|date:'c'}">
					<meta itemprop="dateModified" content="{@$news->lastChangeTime|date:'c'}">
				</li>

				{if $news->enableComments}
					<li itemprop="interactionStatistic" itemscope itemtype="http://schema.org/InteractionCounter">
						<span class="icon icon16 fa-comments"></span> <span>{lang}cms.news.comments{/lang}</span>
						<meta itemprop="interactionType" content="http://schema.org/CommentAction">
						<meta itemprop="userInteractionCount" content="{@$news->comments}">
					</li>
				{/if}

				<li>
					<span class="icon icon16 fa-eye"></span>
					{lang}cms.news.views.count{/lang}
				</li>

				<li class="newsLikesBadge"></li>
			</ul>

			<meta itemprop="mainEntityOfPage" content="{$canonicalURL}">
			<div itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
				<meta itemprop="name" content="{PAGE_TITLE|language}">
				<div itemprop="logo" itemscope itemtype="http://schema.org/ImageObject">
					<meta itemprop="url" content="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}">
				</div>
			</div>
		</div>

		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						{if $news->canEdit()}
							<li><a href="{link controller='NewsEdit' application='cms' object=$news}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}cms.news.edit{/lang}</span></a></li>
						{/if}
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{capture assign='sidebarRight'}
	{if $news->getCategories()|count}
		<section class="box section">
			<h2 class="sectionTitle">{lang}cms.news.category.categories{/lang}</h2>

			<ul>
				{foreach from=$news->getCategories() item=category}
					<li>
						<a href="{$category->getLink()}" class="jsTooltip" title="{lang}cms.news.categorizedNews{/lang}">{$category->getTitle()}</a>
					</li>
				{/foreach}
			</ul>
		</section>
	{/if}

	{if $tags|count}
		<section class="box section">
			<h2 class="sectionTitle">{lang}wcf.tagging.tags{/lang}</h2>

			<ul class="tagList">
				{foreach from=$tags item=tag}
					<li>
						<a href="{link controller='Tagged' object=$tag}objectType=de.codequake.cms.news{/link}" class="badge tag jsTooltip" title="{lang}wcf.tagging.taggedObjects.de.codequake.cms.news{/lang}">{$tag->name}</a>
					</li>
				{/foreach}
		</section>
	{/if}

	{if $news->hasLabels()}
		<section class="box section">
			<h2 class="sectionTitle">{lang}wcf.label.labels{/lang}</h2>
			<ul class="labelList">
				{foreach from=$news->getLabels() item=label}
					<li><span class="badge label{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></li>
				{/foreach}
			</ul>
		</section>
	{/if}
{/capture}

{capture assign='headContent'}
	<link rel="amphtml" href="{link controller='NewsAmp' object=$news}{/link}">
{/capture}

{include file='header'}

{if $news->isDelayed}
	<p class="warning">{lang}cms.news.publication.delayed{/lang}</p>
{/if}

{if FIREBALL_NEWS_NEWS_IMAGES_ATTACHED && $news->imageID != 0 && FIREBALL_NEWS_NEWS_IMAGES_FULLSCREEN}
	<section class="section">
		<figure class="articleImage" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
			<div class="articleImageWrapper"><img src="{$news->getImage()->getLink()}" /></div>
			<meta itemprop="url" content="{@$news->getImage()->getLink()}">
		</figure>
	</section>
{/if}

{if ($news->isDelayed && $news->canSeeDelayed()) || !$news->isDelayed}
	<section class="section articleContent newsContent"
		data-user-id="{$news->userID}"
		data-object-id="{$news->newsID}"
		data-news-id="{$news->newsID}"
		data-is-deleted="{$news->isDeleted}"
		data-is-disabled="{$news->isDisabled}"
		data-is-delayed="{$news->isDelayed}"
		data-object-type="de.codequake.cms.likeableNews"
		data-like-liked="{if $newsLikeData[$news->newsID]|isset}{@$newsLikeData[$news->newsID]->liked}{/if}"
		data-like-likes="{if $newsLikeData[$news->newsID]|isset}{@$newsLikeData[$news->newsID]->likes}{else}0{/if}"
		data-like-dislikes="{if $newsLikeData[$news->newsID]|isset}{@$newsLikeData[$news->newsID]->dislikes}{else}0{/if}"
		data-like-users='{ {if $newsLikeData[$news->newsID]|isset}{implode from=$newsLikeData[$news->newsID]->getUsers() item=likeUser}"{@$likeUser->userID}": "{$likeUser->username|encodeJSON}"{/implode}{/if} }'
	>
		<div class="htmlContent">
			{if !$news->teaser|empty && FIREBALL_NEWS_NEWS_IMAGES_FULLSCREEN}
				<p class="articleTeaser">{$news->teaser}</p>
			{/if}

			{@$news->getFormattedMessage()}
		</div>

		{if $news->getPoll()}
			<div>
				{include file='poll' poll=$news->getPoll()}
			</div>
		{/if}

		{assign var=objectID value=$news->newsID}
		{include file='attachments'}

		{if !$tags|empty}
			<ul class="tagList articleTagList section">
				{foreach from=$tags item=tag}
					<li><a href="{link controller='Tagged' object=$tag}objectType=de.codequake.cms.news{/link}" class="tag">{$tag->name}</a></li>
				{/foreach}
			</ul>
		{/if}

		<div class="section row newsLikeSection">
			<div class="col-xs-12 col-md-6">
				<div class="newsLikesSummery"></div>
			</div>
			<div class="col-xs-12 col-md-6">
				<ul class="newsLikeButtons buttonGroup"></ul>
			</div>
		</div>
	</section>

	{if ENABLE_SHARE_BUTTONS}
		<section class="section jsOnly">
			<h2 class="sectionTitle">{lang}wcf.message.share{/lang}</h2>

			{include file='shareButtons'}
		</section>
	{/if}

	{if FIREBALL_NEWS_COMMENTS && ($commentList|count || $commentCanAdd)}
		{include file='newsCommentList' application='cms'}
	{/if}
{/if}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	require(
		['Language', 'WoltLabSuite/Core/Ui/Like/Handler'], function (Language, UiLikeHandler) {
			Language.addObject({
				'wcf.message.share': '{lang}wcf.message.share{/lang}',
				'wcf.message.share.facebook': '{lang}wcf.message.share.facebook{/lang}',
				'wcf.message.share.google': '{lang}wcf.message.share.google{/lang}',
				'wcf.message.share.permalink': '{lang}wcf.message.share.permalink{/lang}',
				'wcf.message.share.permalink.bbcode': '{lang}wcf.message.share.permalink.bbcode{/lang}',
				'wcf.message.share.permalink.html': '{lang}wcf.message.share.permalink.html{/lang}',
				'wcf.message.share.reddit': '{lang}wcf.message.share.reddit{/lang}',
				'wcf.message.share.twitter': '{lang}wcf.message.share.twitter{/lang}',
				'cms.news.ipAddress.title': '{lang}cms.news.ipAddress.title{/lang}',
				'cms.news.ipAddress.news': '{lang}cms.news.ipAddress.news{/lang}',
				'cms.news.ipAddress.otherUsers': '{lang}cms.news.ipAddress.otherUsers{/lang}',
				'cms.news.ipAddress.author': '{lang}cms.news.ipAddress.author{/lang}'
			});

			{if ($news->isDelayed && $news->canSeeDelayed()) || !$news->isDelayed}
				new WCF.Action.Delete('cms\\data\\news\\NewsAction', '.jsNews');
				new WCF.Message.Share.Content();

				{if LOG_IP_ADDRESS && $__wcf->session->getPermission('admin.user.canViewIpAddress')}
					new Fireball.News.IPAddressHandler();
				{/if}

				{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike')}
					new UiLikeHandler(
						'de.codequake.cms.likeableNews', {
							// settings
							isSingleItem: true,

							// permissions
							canDislike: {if LIKE_ENABLE_DISLIKE}1{else}0{/if},
							canLike: {if $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canLike')}1{else}0{/if},
							canLikeOwnContent: {if LIKE_ALLOW_FOR_OWN_CONTENT}1{else}0{/if},
							canViewSummary: {if LIKE_SHOW_SUMMARY}1{else}0{/if},

							// selectors
							badgeContainerSelector: '.newsLikesBadge',
							buttonAppendToSelector: '.newsLikeButtons',
							containerSelector: '.newsContent',
							summarySelector: '.newsLikesSummery'
						}
					);
				{/if}
			{/if}
		}
	);
</script>

{include file='footer'}
