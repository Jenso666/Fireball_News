<header class="boxHeadline">
    <h2>{lang}wcf.dashboard.box.de.codequake.cms.latestNews.content{/lang}</h2>
</header>

<div class="container marginTop">
    {hascontent}
        <ul class="sidebarBoxList">
            {content}
                {foreach from=$latestNews item=news}
                    <li>
                        <div class="sidebarBoxHeadline">
                            <h3>
                                <a href="{link application='cms' controller='News' object=$news->getDecoratedObject()}{/link}" class="newsLink" data-news-id="{@$news->newsID}" data-sort-order="DESC" title="{$news->subject}">{$news->subject}</a>
                            </h3>
                            <small>
                                {if $news->userID}
                                    <a href="{link controller='User' object=$news->getUserProfile()->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$news->getUserProfile()->userID}">{$news->username}</a>
                                {else}
                                    {$news->username}
                                {/if}
                                - {@$news->time|time}
                            </small>
                        </div>
                    </li>
                {/foreach}
            {/content}
        </ul>
    {/hascontent}
</div>
