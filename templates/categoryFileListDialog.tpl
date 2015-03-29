<div class="tabularBox tabularBoxTitle marginTop" data-category-id="{@$category->categoryID}">
	<header>
		<h2>{$category->getTitle()}</h2>
	</header>

	<table class="table">
		<thead>
			<th class="columnMark"></th>
			<th class="columnID columnFileID">{lang}wcf.global.objectID{/lang}</th>
			<th class="columnTitle columnFile" colspan="2">{lang}wcf.global.title{/lang}</th>
			<th class="columnType">{lang}cms.news.image.fileType{/lang}</th>
			<th class="columnDownloads">{lang}cms.news.image.downloads{/lang}</th>

			{event name='columnHeads'}
		</thead>

		<tbody>
			{foreach from=$fileList item=file}
				<tr data-file-id="{@$file->fileID}">
					<td class="columnMark"></td>
					<td class="columnID columnFileID">{@$file->fileID}</td>
					<td class="columnIcon">{@$file->getIconTag()}</td>
					<td class="columnTitle columnFile"><a class="cmsFileLink" data-file-id="{@$file->fileID}">{$file->getTitle()}</a></td>
					<td class="columnType">{$file->fileType}</td>
					<td class="columnDownloads">{#$file->downloads}</td>

					{event name='columnRows'}
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
