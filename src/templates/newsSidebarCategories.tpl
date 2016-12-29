{if FIREBALL_NEWS_SIDEBAR_CATEGORIES && !$categoryList|empty}
    <section class="box section">
        <h2 class="boxTitle sectionTitle">{lang}cms.news.category.categories{/lang}</h2>

        <div>
            <ol class="sidebarNestedCategoryList newsSidebarCategoryList">
                {foreach from=$categoryList item=categoryItem}
                    {if $categoryItem->isAccessible()}
                        <li{if $category|isset && $category->categoryID == $categoryItem->categoryID} class="active"{/if}>
                            <a href="{link application='cms' controller='NewsList' object=$categoryItem->getDecoratedObject()}{/link}">{$categoryItem->getTitle()}</a>

                            {if $categoryItem->getUnreadNews()}<span class="badge badgeUpdate">
                                <a href="{link application='news' controller='UnreadNewsList'}{/link}" class="jsTooltip">{#$categoryItem->getNews()}</a>
                            {else}
                                <span class="badge">{#$categoryItem->getNews()}</span>
                            {/if}

                            {if $categoryItem->hasChildren() && !FIREBALL_NEWS_SIDEBAR_CATEGORIES_MAIN}
                                <ol>
                                    {foreach from=$categoryItem item=subCategoryItem}
                                        {if $subCategoryItem->isAccessible()}
                                            <li{if $category|isset && $category->categoryID == $subCategoryItem->categoryID} class="active"{/if}>
                                                <a href="{link application='cms' controller='NewsList' object=$subCategoryItem->getDecoratedObject()}{/link}">{$subCategoryItem->getTitle()}</a>
                                                <span class="badge">{#$subCategoryItem->getNews()}</span>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ol>
                            {/if}
                        </li>
                    {/if}
                {/foreach}
            </ol>
        </div>
    </section>
{/if}
