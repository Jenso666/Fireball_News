{if $category|isset}
	{capture assign='pageTitle'}{$category->getTitle()}{/capture}
	{capture assign='contentTitle'}{$category->getTitle()}{/capture}
{/if}

{capture assign='headContent'}
	{if $pageNo < $pages}
        <link rel="next" href="{link controller='NewsOverview' application='cms'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
        <link rel="prev" href="{link controller='NewsOverview' application='cms'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}

	{if $__wcf->getUser()->userID}
        <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='NewsFeed' application='cms'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
	{else}
        <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='NewsFeed' application='cms'}{/link}">
	{/if}
{/capture}

{capture assign='headerNavigation'}
    <li>
        <a rel="alternate" href="{if $__wcf->getUser()->userID}{link controller='NewsFeed' application='cms' appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link application='cms' controller='NewsFeed' appendSession=false}{/link}{/if}" title="{lang}wcf.global.button.rss{/lang}" class="jsTooltip">
            <span class="icon icon16 fa-rss"></span>
            <span class="invisible">{lang}wcf.global.button.rss{/lang}</span>
        </a>
    </li>
    <li class="jsOnly">
        <a title="{lang}cms.news.markAllAsRead{/lang}" class="markAllAsReadButton jsTooltip">
            <span class="icon icon16 fa-check"></span>
            <span class="invisible">{lang}cms.news.markAllAsRead{/lang}</span>
        </a>
    </li>
{/capture}

{if $__wcf->getSession()->getPermission('user.fireball.news.canAddNews')}
	{capture assign='contentHeaderNavigation'}
        <li>
            <a href="{link application='cms' controller='NewsAdd'}{/link}" title="{lang}cms.news.add{/lang}" class="button">
                <span class="icon icon16 fa-pencil"></span>
                <span>{lang}cms.news.add{/lang}</span>
            </a>
        </li>
	{/capture}
{/if}

{capture assign='sidebarRight'}
	{include file='newsSidebarCategories' application='cms'}
{/capture}

{include file='header'}

{hascontent}
    <div class="paginationTop">
		{content}
		    {pages print=true assign='pagesLinks' controller='NewsOverview' application='cms' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
    </div>
{/hascontent}

{if $objects|count}
    <div class="section">
	    {if FIREBALL_NEWS_VIEW == 'article'}
			{include file='newsListItemsArticle' application='cms'}
	    {else}
		    {include file='newsListItemsMessage' application='cms'}
	    {/if}
    </div>
{else}
    <p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
        <div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
        </div>
	{/hascontent}

	{hascontent}
        <nav class="contentFooterNavigation">
            <ul>
				{content}{event name='contentFooterNavigation'}{/content}
            </ul>
        </nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	//<![CDATA[
	$(function () {
		new Fireball.News.MarkAllAsRead();
	});
	//]]>
</script>

{include file='footer'}
