{include file='documentHeader'}
<head>
    <title>{$category->getTitle()|language} - {PAGE_TITLE|language}</title>
    <link rel="canonical" href="{link controller='NewsList' application='cms' object=$category}{/link}" />
    {include file='headInclude' application='wcf'}

    <script data-relocate="true">
        //<![CDATA[
        $(function () {
            new CMS.News.MarkAllAsRead();
        });
        //]]>
    </script>
</head>

<body id="tpl_{$templateNameApplication}_{$templateName}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{capture assign='headerNavigation'}
    <li>
        <a rel="alternate" href="{if $__wcf->getUser()->userID}{link application='cms' controller='NewsFeed' id=$category->categoryID appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link application='cms' controller='NewsFeed' id=$category->categoryID appendSession=false}{/link}{/if}" title="{lang}wcf.global.button.rss{/lang}" class="jsTooltip">
            <span class="icon icon16 icon-rss"></span> <span class="invisible">{lang}wcf.global.button.rss{/lang}</span>
        </a>
    </li>
    <li class="jsOnly">
        <a title="{lang}cms.news.markAllAsRead{/lang}" class="markAllAsReadButton jsTooltip">
            <span class="icon icon16 icon-ok"></span>
            <span class="invisible">{lang}cms.news.markAllAsRead{/lang}</span>
        </a>
    </li>
{/capture}

{capture assign='sidebar'}
    {include file='newsSidebarCategories' application='cms'}

    {event name='boxes'}

    {@$__boxSidebar}
{/capture}

{include file='header' sidebarOrientation='right'}

<header class="boxHeadline">
    <h1>{$category->getTitle()|language}</h1>
    {hascontent}<h2>{content}{$category->description|language}{/content}</h2>{/hascontent}
</header>

{include file='userNotice'}

<div class="contentNavigation">
    {pages print=true assign=pagesLinks controller="NewsList" application="cms" id=$categoryID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}

    {if $category->getPermission('canAddNews')}
        <nav>
            <ul>
                <li>
                    <a href="{link application='cms' controller='NewsAdd' id=$categoryID}{/link}" title="{lang}cms.news.add{/lang}" class="button">
                        <span class="icon icon16 icon-asterisk"></span>
                        <span>{lang}cms.news.add{/lang}</span>
                    </a>
                </li>

                {event name='contentNavigationButtonsTop'}
            </ul>
        </nav>
    {/if}
</div>

{include file='newsListing' application='cms'}

{if $objects|count}
    <div class="contentNavigation">
        {pages print=true assign=pagesLinks controller="NewsList" application="cms" id=$categoryID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}

        {if $category->getPermission('canAddNews')}
            <nav>
                <ul>
                    <li>
                        <a href="{link application='cms' controller='NewsAdd' id=$categoryID}{/link}" title="{lang}cms.news.add{/lang}" class="button">
                            <span class="icon icon16 icon-asterisk"></span>
                            <span>{lang}cms.news.add{/lang}</span>
                        </a>
                    </li>
                    {event name='contentNavigationButtonsTop'}
                </ul>
            </nav>
        {/if}
    </div>
{/if}

{include file='footer'}
