{if FIREBALL_NEWS_NEWS_IMAGES_ATTACHED && $news->imageID != 0 && FIREBALL_NEWS_NEWS_IMAGES_FULLSCREEN}
	<section class="section">
		<figure class="articleImage" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
			<div class="articleImageWrapper">{@$news->getImage()->getImageTag()}</div>
			<meta itemprop="url" content="{@$news->getImage()->getLink()}">
		</figure>
	</section>
{/if}

<section class="section articleContent newsContent"
         data-user-id="{$news->userID}"
         data-object-id="{$news->newsID}"
         data-news-id="{$news->newsID}"
         data-is-deleted="{$news->isDeleted}"
         data-is-disabled="{$news->isDisabled}"
>
	<div class="htmlContent">
		{if !$news->teaser|empty && FIREBALL_NEWS_NEWS_IMAGES_FULLSCREEN}
			<p class="articleTeaser">{$news->getTeaser()}</p>
		{/if}

		{@$news->getFormattedMessage()}
	</div>

	{if $news->getPoll()}
		<div>
			{include file='poll' poll=$news->getPoll()}
		</div>
	{/if}

	{if !$news->getTags()|empty}
		<ul class="tagList articleTagList section">
			{foreach from=$news->getTags() item=tag}
				<li><a href="{link controller='Tagged' object=$tag}objectType=de.codequake.cms.news{/link}" class="tag">{$tag->name}</a></li>
			{/foreach}
		</ul>
	{/if}
</section>
