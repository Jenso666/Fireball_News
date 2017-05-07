<fieldset>
	<legend>{lang}cms.news.category.appearance{/lang}</legend>

	<dl>
		<dt><label for="additionalData[defaultNewsImage]">{lang}cms.news.category.defaultNewsImage{/lang}</label></dt>
		<dt>
			<div id="defaultNewsImage">
				<ul class="formAttachmentList clearfix"></ul>
				<span class="button small">{lang}cms.acp.file.picker{/lang}</span>
			</div>
		</dt>
	</dl>
</fieldset>

<script data-relocate="true" src="{@$__wcf->getPath('cms')}js/CMS.ACP.js?v={@$__wcfVersion}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function () {
		WCF.Language.addObject({
			'wcf.global.button.upload': '{lang}wcf.global.button.upload{/lang}'
		});

		// use acp file picker
		new CMS.ACP.File.Picker($('#defaultNewsImage').children('.button'), 'additionalData[defaultNewsImage]', {
		{if !$defaultNewsImage|empty}
		{@$defaultNewsImage->fileID}: {
			fileID: {@$defaultNewsImage->fileID},
			title: '{$defaultNewsImage->getTitle()}',
				formattedFilesize: '{@$defaultNewsImage->filesize|filesize}'
		}
		{/if}
	}, { fileType: 'image' });
		new CMS.ACP.File.Preview();
	})
	//]]>
</script>
