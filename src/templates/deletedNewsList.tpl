{include file='newsListing' application='cms'}

<script data-relocate="true">
	require(['Language'], function(Language) {
		WCF.Clipboard.init('wcf\\page\\DeletedContentListPage', {@$objects->getMarkedItems()}, { });
	});
</script>
