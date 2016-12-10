<script data-relocate="true">
	new WCF.Action.Delete('cms\\data\\news\\NewsAction', '.jsNews');
</script>

{if $objects|count && $__wcf->session->getPermission('user.cms.news.canViewNews')}
	<ul class="messageList">
		{foreach from=$objects item=news}
			{assign var="attachments" value=$news->getAttachments()}
			<li class="jsNews">
				<article class="message messageReduced marginTop"
				         data-user-id="{$news->userID}"
				         data-object-id="{$news->newsID}"
				         data-is-deleted="{$news->isDeleted}"
				         data-is-disabled="{$news->isDisabled}">
					<div>
						<section class="messageContent">
							<div>
								{if CMS_NEWS_NEWS_IMAGES_ATTACHED && $news->imageID != 0 && CMS_NEWS_NEWS_IMAGES_FULLSCREEN}
									<div class="fullScreenPicture"
									     style="background-image: url({$news->getImage()->getLink()});">
										<header class="messageHeader">
											<div class="messageHeadline">
												<h1>
													<a href="{link controller='News' object=$news application='cms'}{/link}">{$news->getTitle()}</a>
												</h1>
												{if $news->languageID != 0 && CMS_NEWS_LANGUAGEICON}
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
	                                                        <a class="userLink" data-user-id="{$news->userID}"
	                                                           href="{link controller='User' object=$news->getUserProfile()}{/link}">
                                                                {$news->username}
                                                            </a>
                                                        {else}
	                                                        {$news->username}
                                                        {/if}
                                                    </span>
													<a class="permalink"
													   href="{link controller='News' object=$news application='cms'}{/link}">
														{@$news->time|time}
													</a>
													-
													<span>
                                                        {implode from=$news->getCategories() item=category}
	                                                        <a
	                                                        href="{link controller='NewsList' application='cms' object=$category}{/link}">{$category->getTitle()|language}</a>{/implode}
                                                    </span>
													{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $news->likes || $news->dislikes}
														<span class="likesBadge badge jsTooltip {if $news->cumulativeLikes > 0}green{elseif $news->cumulativeLikes < 0}red{/if}"
														      title="{lang likes=$news->likes dislikes=$news->dislikes}wcf.like.tooltip{/lang}">{if $news->cumulativeLikes > 0}+{elseif $news->cumulativeLikes == 0}&plusmn;{/if}{#$news->cumulativeLikes}</span>
													{/if}
												</p>
											</div>
										</header>
									</div>
								{else}
									<header class="messageHeader">
										<div class="messageHeadline">
											<h1>
												<a href="{link controller='News' object=$news application='cms'}{/link}">{$news->getTitle()}</a>
											</h1>
											{if $news->languageID != 0 && CMS_NEWS_LANGUAGEICON}
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
	                                                    <a class="userLink" data-user-id="{$news->userID}"
	                                                       href="{link controller='User' object=$news->getUserProfile()}{/link}">
                                                            {$news->username}
                                                        </a>
                                                    {else}
	                                                    {$news->username}
                                                    {/if}
                                                </span>
												<a class="permalink"
												   href="{link controller='News' object=$news application='cms'}{/link}">
													{@$news->time|time}
												</a>
												-
												<span>
                                                    {implode from=$news->getCategories() item=category}
	                                                    <a
	                                                    href="{link controller='NewsList' application='cms' object=$category}{/link}">{$category->getTitle()|language}</a>{/implode}
                                                </span>
												{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $news->likes || $news->dislikes}
													<span class="likesBadge badge jsTooltip {if $news->cumulativeLikes > 0}green{elseif $news->cumulativeLikes < 0}red{/if}"
													      title="{lang likes=$news->likes dislikes=$news->dislikes}wcf.like.tooltip{/lang}">{if $news->cumulativeLikes > 0}+{elseif $news->cumulativeLikes == 0}&plusmn;{/if}{#$news->cumulativeLikes}</span>
												{/if}
											</p>
										</div>
									</header>
								{/if}

								<div class="messageBody">
									{if CMS_NEWS_NEWS_IMAGES_ATTACHED && $news->imageID != 0 && !CMS_NEWS_NEWS_IMAGES_FULLSCREEN}
										<div class="newsBox128">
											<div class="framed">
												<img src="{@$news->getImage()->getLink()}"
												     alt="{$news->getImage()->getTitle()}" style="width: 128px;"/>
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

										{if CMS_NEWS_COMMENTS}
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
													<a href="{link controller='News' object=$news application='cms'}{/link}"
													   class="button jsTooltip">
														<span class="icon icon16 icon-arrow-right"></span>
														<span>{lang}cms.news.read{/lang}</span>
													</a>
												</li>

												{if $news->canModerate()}
													<li class="jsOnly">
														<a class="button jsDeleteButton jsTooltip pointer"
														   title="{lang}wcf.global.button.delete{/lang}"
														   data-object-id="{@$news->newsID}"
														   data-confirm-message="{lang}cms.news.delete.sure{/lang}">
															<span class="icon icon16 icon-remove"></span>
															<span class="invisible">{lang}wcf.global.button.delete{/lang}</span>
														</a>
													</li>
												{/if}

												{event name='messageOptions'}

												<li class="toTopLink">
													<a href="{@$__wcf->getAnchor('top')}"
													   title="{lang}wcf.global.scrollUp{/lang}"
													   class="button jsTooltip">
														<span class="icon icon16 icon-arrow-up"></span>
														<span class="invisible">{lang}wcf.global.scrollUp{/lang}</span>
													</a>
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
