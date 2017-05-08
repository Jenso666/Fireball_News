<script data-relocate="true">
	//<![CDATA[
	$(function () {
		new WCF.Action.Delete('cms\\data\\news\\NewsAction', '.jsNews');
	});
	//]]>
</script>

{if $objects|count && $__wcf->session->getPermission('user.fireball.news.canViewNews')}
	<ul class="messageList jsClipboardContainer" data-type="de.codequake.cms.news">
		{foreach from=$objects item=news}
			{assign var="attachments" value=$news->getAttachments()}
			<li class="jsNews jsClipboardObject" data-news-id="{@$news->newsID}" data-element-id="{@$news->newsID}">
				<article class="message messageReduced marginTop{if $news->isDisabled} messageDisabled{/if}{if $news->isDeleted} messageDeleted{/if}"
					data-user-id="{$news->userID}"
					data-object-id="{$news->newsID}"
					data-is-deleted="{$news->isDeleted}"
					data-is-disabled="{$news->isDisabled}"
					data-is-delayed="{$news->isDelayed}">
					<div>
						<section class="messageContent">
							<div>
								{if FIREBALL_NEWS_NEWS_IMAGES_ATTACHED && $news->imageID != 0 && FIREBALL_NEWS_NEWS_IMAGES_FULLSCREEN}
									<div class="fullScreenPicture" style="background-image: url({$news->getImage()->getLink()});">
										<header class="messageHeader">
											{if $news->canModerate() && $hasMarkedItems|isset}
												<ul class="messageQuickOptions">
													<li class="jsOnly"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$news->newsID}" /></li>
												</ul>
											{/if}

											<div class="messageHeadline">
												<h1>
													<a href="{link controller='News' object=$news application='cms'}{/link}">{$news->getTitle()}</a>
												</h1>
												{if $news->hasLabels()}
													<ul class="labelList">
														{foreach from=$news->getLabels() item=label}
															<li><a href="{if $templateName == 'newsList'}{link application='cms' controller='NewsList' object=$category}labelIDs[{@$label->groupID}]={@$label->labelID}{/link}{else}{link application='cms' controller='NewsOverview'}labelIDs[{@$label->groupID}]={@$label->labelID}{/link}{/if}" class="badge label{if $label->getClassNames()} {$label->getClassNames()}{/if} jsTooltip" title="{lang}cms.news.newsByLabel{/lang}">{lang}{$label->label}{/lang}</a></li>
														{/foreach}
													</ul>
												{/if}
												{if $news->languageID && FIREBALL_NEWS_LANGUAGEICON}
													<p class="newMessageBadge" style="margin-top: 30px">
														{@$news->getLanguageIcon()}
													</p>
												{/if}
												{if $news->isNew()}
													<p class="newMessageBadge">{lang}wcf.message.new{/lang}</p>
												{/if}
												<p>
													<span class="username">
														{if $news->userID}
															<a class="userLink" data-user-id="{$news->userID}" href="{link controller='User' object=$news->getUserProfile()}{/link}">{$news->username}</a>
														{else}
															{$news->username}
														{/if}
													</span>
													<a class="permalink" href="{link controller='News' object=$news application='cms'}{/link}">
														{@$news->time|time}
													</a>
													- <span>{implode from=$news->getCategories() item=category}<a href="{link controller='NewsList' application='cms' object=$category}{/link}">{$category->getTitle()|language}</a>{/implode}</span>
													{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $news->likes || $news->dislikes}
														<span class="likesBadge badge jsTooltip {if $news->cumulativeLikes > 0}green{elseif $news->cumulativeLikes < 0}red{/if}" title="{lang likes=$news->likes dislikes=$news->dislikes}wcf.like.tooltip{/lang}">{if $news->cumulativeLikes > 0}+{elseif $news->cumulativeLikes == 0}&plusmn;{/if}{#$news->cumulativeLikes}</span>
													{/if}
												</p>
											</div>
										</header>
									</div>
								{else}
									<header class="messageHeader">
										{if $news->canModerate()}
											<ul class="messageQuickOptions">
												<li class="jsOnly"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$news->newsID}" /></li>
											</ul>
										{/if}

										<div class="messageHeadline">
											<h1>
												<a href="{link controller='News' object=$news application='cms'}{/link}">{$news->getTitle()}</a>
											</h1>
											{if $news->hasLabels()}
												<ul class="labelList">
													{foreach from=$news->getLabels() item=label}
														<li><a href="{if $templateName == 'newsList'}{link application='cms' controller='NewsList' object=$category}labelIDs[{@$label->groupID}]={@$label->labelID}{/link}{else}{link application='cms' controller='NewsOverview'}labelIDs[{@$label->groupID}]={@$label->labelID}{/link}{/if}" class="badge label{if $label->getClassNames()} {$label->getClassNames()}{/if} jsTooltip" title="{lang}cms.news.newsByLabel{/lang}">{lang}{$label->label}{/lang}</a></li>
													{/foreach}
												</ul>
											{/if}
											{if $news->languageID && FIREBALL_NEWS_LANGUAGEICON}
												<p class="newMessageBadge" style="margin-top: 30px">
													{@$news->getLanguageIcon()}
												</p>
											{/if}
											{if $news->isNew()}
												<p class="newMessageBadge">{lang}wcf.message.new{/lang}</p>
											{/if}
											<p>
												<span class="username">
													{if $news->userID != 0}
														<a class="userLink" data-user-id="{$news->userID}" href="{link controller='User' object=$news->getUserProfile()}{/link}">{$news->username}</a>
													{else}
														{$news->username}
													{/if}
												</span>
												<a class="permalink" href="{link controller='News' object=$news application='cms'}{/link}">{@$news->time|time}</a>
												- <span>{implode from=$news->getCategories() item=category}<a href="{link controller='NewsList' application='cms' object=$category}{/link}">{$category->getTitle()|language}</a>{/implode}</span>
												{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $news->likes || $news->dislikes}
													<span class="likesBadge badge jsTooltip {if $news->cumulativeLikes > 0}green{elseif $news->cumulativeLikes < 0}red{/if}" title="{lang likes=$news->likes dislikes=$news->dislikes}wcf.like.tooltip{/lang}">{if $news->cumulativeLikes > 0}+{elseif $news->cumulativeLikes == 0}&plusmn;{/if}{#$news->cumulativeLikes}</span>
												{/if}
											</p>
										</div>
									</header>
								{/if}

								<div class="messageBody">
									{if FIREBALL_NEWS_NEWS_IMAGES_ATTACHED && $news->imageID != 0 && !FIREBALL_NEWS_NEWS_IMAGES_FULLSCREEN}
										<div class="newsBox128">
											<div class="framed">
												<img src="{@$news->getImage()->getLink()}" alt="{$news->getImage()->getTitle()}" style="width: 128px;" />
											</div>
											<div class="newsTeaser">
												{if $news->teaser != ""}
													<strong>{$news->teaser}</strong>{else}{@$news->getExcerpt()}
												{/if}
											</div>
										</div>
									{else}
										<div class="newsTeaser">
											{if $news->teaser != ""}
												<strong>{$news->teaser}</strong>{else}{@$news->getExcerpt()}
											{/if}
										</div>
									{/if}

									<div class="messageFooter">
										<p class="messageFooterNote">
											{lang}cms.news.clicks.count{/lang}
										</p>

										{if FIREBALL_NEWS_COMMENTS}
											<p class="messageFooterNote">
												<a href="{link controller='News' object=$news application='cms'}#comments{/link}">
													{lang}cms.news.comments.count{/lang}
												</a>
											</p>
										{/if}
									</div>

									<footer class="messageOptions">
										<nav class="buttonGroupNavigation jsMobileNavigation">
											<ul class="smallButtons buttonGroup">
												<li class="continue">
													<a href="{link controller='News' object=$news application='cms'}{/link}" class="button jsTooltip"> <span class="icon icon16 icon-arrow-right"></span> <span>{lang}cms.news.read{/lang}</span> </a>
												</li>

												{if $news->canModerate()}
													<li class="jsOnly">
														<a class="button jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$news->newsID}" data-confirm-message="{lang}cms.news.delete.sure{/lang}"> <span class="icon icon16 icon-remove"></span> <span class="invisible">{lang}wcf.global.button.delete{/lang}</span> </a>
													</li>
												{/if}

												{event name='messageOptions'}

												<li class="toTopLink">
													<a href="{@$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="button jsTooltip"> <span class="icon icon16 icon-arrow-up"></span> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span> </a>
												</li>
											</ul>
										</nav>
									</footer>
								</div>
							</div>
						</section>
					</div>
				</article>
			</li>
		{/foreach}
	</ul>
{/if}
