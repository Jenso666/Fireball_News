<ul class="sitemapList" data-object-id="0">
	{assign var=oldDepth value=0}
	{foreach from=$nodeList item='category'}
	{section name=i loop=$oldDepth-$nodeList->getDepth()}</ul></li>{/section}

<li>
	<a href="{link controller='NewsList' application='cms' id=$category->categoryID}{/link}">{$category->getTitle()|language}</a>

	<ul class="categoryList" data-object-id="{@$category->categoryID}">
		{if !$nodeList->current()->hasChildren()}
	</ul>
</li>
{/if}

{assign var=oldDepth value=$nodeList->getDepth()}
{/foreach}
{section name=i loop=$oldDepth}</ul></li>{/section}
</ul>
