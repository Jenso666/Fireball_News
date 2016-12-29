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
										<span class="icon icon16 fa-thumbs-o-{if $news->cumulativeLikes < 0}down{else}up{/if} jsTooltip" title="{lang likes=$news->likes dislikes=$news->dislikes}wcf.like.tooltip{/lang}"></span>{if $news->cumulativeLikes > 0}+{elseif $news->cumulativeLikes == 0}&plusmn;{/if}{#$news->cumulativeLikes}
									{/if}
								</li>
							{/if}
						</ul>
					</div>

					<div class="containerContent articleListTeaser">
						{if !$news->teaser|empty}
							{$news->teaser}
						{else}
							{@$news->getExcerpt()}
						{/if}
					</div>
				</div>
			</a>
		</li>
	{/foreach}
</ul>
