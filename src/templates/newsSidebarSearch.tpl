<section class="box section">
	<h2 class="boxTitle sectionTitle">{lang}cms.news.search.filter{/lang}</h2>

	<div class="boxContent">
		<form method="get">
			<dl>
				<dd>{lang}cms.news.search.user{/lang}</dd>
				<dt>
					<input type="text" id="newsFilterUsername" name="username" value="{if $username|isset}{$username}{/if}" class="long" />
				</dt>
			</dl>

			<dl>
				<dd>{lang}cms.news.search.time{/lang}</dd>
				<dt>
					<input type="date" name="time" value="{if !$time|empty}{$time}{/if}" class="long" />
				</dt>
			</dl>

			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
			</div>
		</form>
	</div>
</section>

<script data-relocate="true">
	$(function () {
		new WCF.Search.User('#newsFilterUsername', null, false, [ ], false);
	})
</script>
