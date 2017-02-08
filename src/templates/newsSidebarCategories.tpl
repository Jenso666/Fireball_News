{if FIREBALL_NEWS_SIDEBAR_CATEGORIES && !$categoryList|empty}
	<section class="box section">
		<h2 class="boxTitle sectionTitle">{lang}cms.news.category.categories{/lang}</h2>

		<div class="boxContent">
			<ol class="sidebarNestedCategoryList boxMenu forceOpen newsSidebarCategoryList">
				{foreach from=$categoryList item=categoryItem}
					{if $categoryItem->isAccessible()}
						<li{if $category|isset && $category->categoryID == $categoryItem->categoryID} class="active"{/if}>
							<a href="{$categoryItem->getLink()}" class="boxMenuLink"> <span class="boxMenuLinkTitle">{$categoryItem->getTitle()}</span> <span class="badge{if $categoryItem->getUnreadNews()} badgeUpdate{/if} newsCounter">{if $categoryItem->getUnreadNews()}{#$categoryItem->getUnreadNews()}/{/if}{#$categoryItem->getNews()}</span> </a>

							{if $categoryItem->hasChildren() && !FIREBALL_NEWS_SIDEBAR_CATEGORIES_MAIN}
								<ol class="boxMenuDepth1">
									{foreach from=$categoryItem item=subCategoryItem}
										{if $subCategoryItem->isAccessible()}
											<li{if $category|isset && $category->categoryID == $subCategoryItem->categoryID} class="active"{/if}>
												<a href="{$subCategoryItem->getLink()}" class="boxMenuLink"> <span class="boxMenuLinkTitle">{$subCategoryItem->getTitle()}</span> <span class="badge{if $subCategoryItem->getUnreadNews()} badgeUpdate{/if} newsCounter">{if $subCategoryItem->getUnreadNews()}{#$subCategoryItem->getUnreadNews()}/{/if}{#$subCategoryItem->getNews()}</span></a>
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
