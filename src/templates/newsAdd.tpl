{capture assign='headContent'}
	<script data-relocate="true" src="{@$__wcf->getPath('cms')}js/Fireball.News{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@LAST_UPDATE_TIME}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath('cms')}acp/js/Fireball.ACP.js?v={@LAST_UPDATE_TIME}"></script>
{/capture}

{include file='header'}

<form id="messageContainer" class="jsFormGuard" method="post" action="{if $action == 'add'}{link controller='NewsAdd' application='cms'}{/link}{else}{link controller='NewsEdit' application='cms' id=$newsID}{/link}{/if}">
	<section class="section">
		<h2 class="sectionTitle">{lang}cms.news.category.categories{/lang}</h2>
		<small>{lang}cms.news.category.categories.description{/lang}</small>

		<ol class="nestedCategoryList doubleColumned jsCategoryList">
			{foreach from=$categoryList item=categoryItem}
				{if $categoryItem->isAccessible()}
					<li>
						<div>
							<div class="containerHeadline">
								<h3>
									<label{if $categoryItem->getDescription()} class="jsTooltip" title="{$categoryItem->getDescription()}"{/if}>
										<input type="checkbox" name="categoryIDs[]" value="{@$categoryItem->categoryID}" class="jsCategory"{if $categoryItem->categoryID|in_array:$categoryIDs} checked="checked"{/if}/>
										{$categoryItem->getTitle()}
									</label>
								</h3>
							</div>

							{if $categoryItem->hasChildren()}
								<ol>
									{foreach from=$categoryItem item=subCategoryItem}
										{if $subCategoryItem->isAccessible()}
											<li>
												<label{if $subCategoryItem->getDescription()} class="jsTooltip" title="{$subCategoryItem->getDescription()}"{/if}>
													<input type="checkbox" name="categoryIDs[]" value="{@$subCategoryItem->categoryID}" class="jsChildCategory" {if $subCategoryItem->categoryID|in_array:$categoryIDs}checked="checked" {/if} />
													{$subCategoryItem->getTitle()}
												</label>
											</li>
										{/if}
									{/foreach}
								</ol>
							{/if}
						</div>
					</li>
				{/if}
			{/foreach}
		</ol>

		{if $errorField == 'categoryIDs'}
			<small class="innerError">
				{if $errorType == 'empty'}
					{lang}wcf.global.form.error.empty{/lang}
				{else}
					{lang}cms.news.categories.error.{@$errorType}{/lang}
				{/if}
			</small>
		{/if}

		{event name='categoryFields'}
	</section>

	<section class="section">
		<h2 class="sectionTitle">{lang}cms.news.label{/lang}</h2>

		<div id="newsAddabelSelectionContainer">
			{include file='newsAddLabelSelection' application='cms'}
		</div>
	</section>

	<section class="section">
		<h2 class="sectionTitle">{lang}cms.news.general{/lang}</h2>

		{if $action =='add'}{include file='messageFormMultilingualism'}{/if}

		<dl{if $errorField == 'subject'} class="formError"{/if}>
			<dt><label for="subject">{lang}wcf.global.title{/lang}</label></dt>
			<dd>
				<input type="text" id="subject" name="subject" value="{$subject}" required="required" maxlength="255" class="long" />
				{if $errorField == 'subject'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'censoredWordsFound'}
							{lang}wcf.message.error.censoredWordsFound{/lang}
						{else}
							{lang}cms.news.subject.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>

		{if MODULE_TAGGING}{include file='tagInput'}{/if}

		<dl{if $errorField == 'teaser'} class="formError"{/if}>
			<dt><label for="teaser">{lang}cms.news.teaser{/lang}</label></dt>
			<dd>
				<textarea id="teaser" name="teaser" rows="5" cols="40">{$teaser}</textarea>
				<small>{lang}cms.news.teaser.description{/lang}</small>
				{if $errorField == 'teaser'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}cms.news.teaser.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>

		<dl class="newsImageSelect">
			<dt><label for="image">{lang}cms.news.image{/lang}</label></dt>
			<dd>
				<div id="filePicker">
					<ul class="formAttachmentList clearfix"></ul>
					<span class="button small">{lang}cms.acp.file.picker{/lang}</span>
				</div>
			</dd>
		</dl>

		<dl{if $errorField == 'authors'} class="formError"{/if}>
			<dt><label for="authors">{lang}cms.news.authors{/lang}</label></dt>
			<dd>
				<input type="text" id="authors" name="authors" value="{$authors}" class="long" />
				{if $errorField == 'authors'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'censoredWordsFound'}
							{lang}wcf.message.error.censoredWordsFound{/lang}
						{else}
							{lang}cms.news.authors.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>

			{event name='informationFields'}
	</section>

	<section class="section jsOnly">
		<h2 class="sectionTitle">{lang}cms.news.time.toPublish{/lang}</h2>

		<dl{if $errorField == 'time'} class="formError"{/if}>
			<dt><label for="time">{lang}cms.news.time.toPublish{/lang}</label></dt>
			<dd>
				<input data-ignore-timezone="1" type="datetime" class="medium" id="time" name="time" value="{$time}" />
			</dd>
		</dl>
	</section>

	<section class="section">
		<h2 class="sectionTitle">{lang}cms.news.message{/lang}</h2>

		<dl class="wide{if $errorField == 'text'} formError{/if}">
			<dt><label for="text">{lang}cms.news.message{/lang}</label></dt>
			<dd>
				<textarea id="text" name="text" rows="20" cols="40">{$text}</textarea>
				{include file='messageFormTabs' wysiwygContainerID='text'}
				{if $errorField == 'text'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'tooLong'}
							{lang}wcf.message.error.tooLong{/lang}
						{elseif $errorType == 'censoredWordsFound'}
							{lang}wcf.message.error.censoredWordsFound{/lang}
						{else}
							{lang}cms.news.message.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>

		{event name='messageFields'}
	</section>

	{event name='sections'}

	{if FIREBALL_NEWS_DISCLAIMER && ((FIREBALL_NEWS_DISCLAIMER_GUESTS && !$__wcf->user->userID) || (FIREBALL_NEWS_DISCLAIMER_USERS && $__wcf->user->userID))}
		<section class="section">
			<h2 class="sectionTitle">{lang}cms.news.add.disclaimer{/lang}</h2>

			{@FIREBALL_NEWS_DISCLAIME|nl2br}

			<dl class="marginTop">
				<dt></dt>
				<dd>
					<label> <input type="checkbox" id="disclaimerAccepted" name="disclaimerAccepted" value="1" required />
						{lang}cms.news.add.disclaimer.optIn{/lang}
					</label>
					{if $errorField == 'disclaimerAccepted'}
						<small class="innerError">
							{lang}cms.news.add.disclaimer.error.notAccepted{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		</section>
	{/if}

	<div class="section">
		{include file='captcha'}
	</div>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
		{if !$newsID|empty}
			{include file='messageFormPreviewButton' previewMessageObjectType='de.codequake.cms.news' previewMessageObjectID=$newsID}
		{else}
			{include file='messageFormPreviewButton' previewMessageObjectType='de.codequake.cms.news'}
		{/if}
	</div>
</form>

<script data-relocate="true">
	require(['Language'], function (Language) {
		Language.addObject({
			'wcf.global.button.upload': '{lang}wcf.global.button.upload{/lang}'
		});

		new WCF.Category.NestedList();
		new WCF.Message.FormGuard();

		new WCF.Search.User('#authors', null, false, [ ], true);

		// use acp file picker
		new Fireball.ACP.File.Picker($('#filePicker').children('.button'), 'imageID', {
			{if $image|isset}
				{@$image->fileID}: {
					fileID: {@$image->fileID},
					title: '{$image->getTitle()}',
					formattedFilesize: '{@$image->filesize|filesize}'
				}
			{/if}
		}, {
			fileType: 'image'
		});

		new Fireball.ACP.File.Preview();
		new Fireball.News.LabelSelection({@$labelGroupIDsByCategory});
		new WCF.Label.Chooser({ {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '#messageContainer');

		WCF.Message.Submit.registerButton('text', $('#messageContainer').find('> .formSubmit > input[type=submit]'));
	});
</script>

{include file='footer'}
{include file='wysiwyg'}
