{assign var='templates' value=','|explode:"news,newsOverview,newsList,newsArchive"}
{if $__cms->isActiveApplication() && $templateName|in_array:$templates && $__searchAreaInitialized|empty}
    {capture assign='__searchInputPlaceholder'}{lang}cms.news.searchNews{/lang}{/capture}
    {capture assign='__searchHiddenInputFields'}
        <input type="hidden" name="types[]" value="de.codequake.cms.news" />
    {/capture}
{/if}
