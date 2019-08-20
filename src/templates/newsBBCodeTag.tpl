<div class="container containerPadding marginTop">
	{if FIREBALL_NEWS_NEWS_IMAGES_ATTACHED && $_news->imageID != 0}
		<div class="box96">
			<div class="framed">
				<img src="{@$_news->getImage()->getLink()}" alt="{$_news->getImage()->getTitle()}" style="width: 96px;" />
			</div>
	{/if}

			<div class="containerHeadline">
				<h3>
					<a href="{$_news->getLink()}" data-news-id="{$_news->newsID}" class="newsLink cmsNewsLink" title="{$_news->getTitle()}">{$_news->getTitle()}</a>
				</h3>

				<p>
					<small>
						<span class="username">
							{if $_news->userID != 0}
								<a class="userLink" data-user-id="{$_news->userID}" href="{link controller='User' object=$_news->getUserProfile()}{/link}">{$_news->username}</a>
							{else}
								{$_news->username}
							{/if}
						</span>
						- <a class="permalink" href="{link controller='News' object=$_news application='cms'}{/link}">{@$_news->time|time}</a>
						-
						<span>
							{implode from=$_news->getCategories() item=category}
								<a href="{link controller='NewsList' application='cms' object=$category}{/link}">{$category->getTitle()}</a>
							{/implode}
						</span>
					</small>
				</p>
			</div>

	{if FIREBALL_NEWS_NEWS_IMAGES_ATTACHED && $_news->imageID != 0}
		</div>
	{/if}
</div>
