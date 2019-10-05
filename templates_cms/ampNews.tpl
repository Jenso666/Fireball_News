{capture assign='pageTitle'}{$news->getTitle()}{/capture}

{capture assign='headContent'}
	<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "NewsArticle",
			"mainEntityOfPage": "{$regularCanonicalURL}",
			"headline": "{$news->getTitle()}",
			"datePublished": "{@$news->time|date:'c'}",
			"dateModified": "{@$news->time|date:'c'}",
			"description": "{@$news->getTeaser()}",
			"author": {
				"@type": "Person",
				"name": "{$news->username}"
			},
			"publisher": {
				"@type": "Organization",
				"name": "{PAGE_TITLE|language}",
				"logo": {
					"@type": "ImageObject",
					"url": "{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}",
					"width": {@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoWidth')},
					"height": {@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoHeight')}
				}
			}
			{if $news->getImage()}
			,"image": {
				"@type": "ImageObject",
				"url": "{$news->getImage()->getThumbnailLink('large')}",
				"width": {@$news->getImage()->getThumbnailWidth('large')},
				"height": {@$news->getImage()->getThumbnailHeight('large')}
			}
			{/if}
		}
	</script>
{/capture}

{include file='ampHeader'}

<article class="article">
	<header class="articleHeader">
		<h1 class="articleTitle">{$news->getTitle()}</h1>
		<h2 class="articleAuthor">{$news->username}</h2>
		<time class="articleDate" datetime="{@$news->time|date:'c'}">{@$news->time|plainTime}</time>
	</header>

	{if $news->getImage()}
		<figure class="articleImage">
			<amp-img src="{$news->getImage()->getThumbnailLink()}" alt="{$news->getImage()->title}" height="{@$news->getImage()->heightThumbnail}" width="{@$news->getImage()->widthThumbnail}" layout="responsive"></amp-img>
		</figure>
	{/if}

	{if !$news->teaser|empty}
		<div class="articleTeaser">
			<p>{@$news->getTeaser()}</p>
		</div>
	{/if}

	<div class="articleContent">
		{@$news->getAmpFormattedContent()}
	</div>
</article>

{include file='ampFooter'}
