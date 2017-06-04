<ul class="articleList">
	{foreach from=$objects item='news'}
		<li>
			<a href="{$news->getLink()}">
				<div>
					<div class="containerHeadline">
						<h3 class="articleListTitle">{$news->getTitle()}</h3>
						<ul class="inlineList articleListMetaData">
							<li>
								<span class="icon icon16 fa-clock-o"></span>
								{@$news->time|time}
							</li>

							{if $news->enableComments}
								<li>
									<span class="icon icon16 fa-comments"></span>
									{lang}cms.news.comments.count{/lang}
								</li>
							{/if}

							{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike')}
								<li class="wcfLikeCounter{if $news->cumulativeLikes > 0} likeCounterLiked{elseif $news->cumulativeLikes < 0}likeCounterDisliked{/if}">
									{if $news->likes || $news->dislikes}
									<span class="icon icon16 fa-thumbs-o-{if $news->cumulativeLikes < 0}down{else}up{/if} jsTooltip"
									      title="{lang likes=$news->likes dislikes=$news->dislikes}wcf.like.tooltip{/lang}"></span>{if $news->cumulativeLikes > 0}+{elseif $news->cumulativeLikes == 0}&plusmn;{/if}{#$news->cumulativeLikes}
									{/if}
								</li>
							{/if}
						</ul>
					</div>

					<div class="containerContent articleListTeaser">
						{if !$news->teaser|empty}
							{$news->getTeaser()}
						{else}
							{@$news->getExcerpt()}
						{/if}
					</div>
				</div>
			</a>

			<footer class="messageFooter">
				{event name='messageFooter'}

				<div class="messageFooterNotes">
					{if $news->isDeleted}
						<p class="messageFooterNote newsDeleteNote">{lang}cms.news.deleteNote{/lang}</p>
					{/if}
					{if $news->isDisabled}
						<p class="messageFooterNote newsDisabledNote">{lang}cms.news.moderation.disabledPost{/lang}</p>
					{/if}
					{if $news->comments}
						<p class="messageFooterNote"><a class="newsCommentCount" href="{link application='cms' controller='News' object=$news}{/link}#comments">{lang}cms.news.comments.count{/lang}</a></p>
					{/if}
					{event name='messageFooterNotes'}
				</div>

				<div class="messageFooterGroup">
					<ul class="messageFooterButtons buttonList smallButtons jsMobileNavigation">
						{if $news->canEdit()}
							<li><a href="{link application='cms' controller='NewsEdit' object=$news}{/link}" title="{lang}cms.news.edit{/lang}" class="button jsMessageEditButton jsEntryInlineEditor"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
						{/if}
						{if LOG_IP_ADDRESS && $news->ipAddress && $__wcf->session->getPermission('admin.user.canViewIpAddress')}
							<li class="jsIpAddress jsOnly" data-object-id="{@$news->newsID}"><a href="#" title="{lang}cms.news.ipAddress{/lang}" class="button jsTooltip"><span class="icon icon16 fa-globe"></span> <span class="invisible">{lang}cms.news.ipAddress{/lang}</span></a></li>
						{/if}
						{if $__wcf->session->getPermission('user.profile.canReportContent')}
							<li class="jsReportNews jsOnly" data-object-id="{@$news->newsID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="button jsTooltip"><span class="icon icon16 fa-exclamation-triangle"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>
						{/if}
						{event name='messageFooterButtons'}
					</ul>
				</div>
			</footer>
		</li>
	{/foreach}
</ul>
