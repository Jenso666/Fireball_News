{assign var="user" value=$news->getUserProfile()}
<div class="box48">
    {if $news->getImage() != null}
        <a class="framed" href="{link controller='News' object=$news application='cms'}{/link}">
            <img src="{@$news->getImage()->getLink()}" alt="{$news->getImage()->getTitle()}" style="width: 48px;" />
        </a>
    {else}
        <a class="framed" href="{link controller='User' object=$news->getUserProfile()}{/link}">
            {@$news->getUserProfile()->getAvatar()->getImageTag(48)}
        </a>
    {/if}

    <div>
        <div class="containerHeadline">
            <h3>
                <a href="{link controller='News' object=$news application='cms'}{/link}">{$news->getTitle()}</a>
                <small>- {@$news->time|time}</small>
            </h3>
        </div>

        <div>
            {@$news->getExcerpt()|nl2br}
        </div>
    </div>
</div>
