{if !$share|isset}{assign var='share' value=false}{/if}

<ul class="articleList messageList jsClipboardContainer" data-type="de.codequake.cms.news">
	{foreach from=$objects item=news}
		{assign var='objectID' value=$news->newsID}
		{assign var='userProfile' value=$news->getUserProfile()}
		<li>
			<div id="news{$news->newsID}"
			     class="message jsMessage{if $news->isDeleted} messageDeleted{/if}{if $news->isDisabled} messageDisabled{/if}{if $news->getUserProfile()->userOnlineGroupID} userOnlineGroupMarking{@$news->getUserProfile()->userOnlineGroupID}{/if} marginTop jsClipboardObject jsEntry guestbookEntry"
			     data-object-id="{$news->newsID}"
			     data-is-deleted="{if $news->isDeleted}1{else}0{/if}"
			     data-is-disabled="{if $news->isDisabled}1{else}0{/if}"
			     data-can-delete="{@$news->canDelete()}"
			     data-can-restore="{@$news->canModerate()}"
			     data-can-delete-completely="{@$news->canModerate()}"
			     data-can-enable="{@$news->canModerate()}"
			     data-can-edit="{@$news->canEdit()}"
			     data-object-type="de.codequake.cms.news"
			     data-user-id="{@$news->userID}"
			     data-edit-url="{link controller='NewsEdit' application='cms' id=$news->newsID}{/link}"
			>

				{include file='messageSidebar' enableMicrodata=true}

				<div class="messageContent">
					<h3 class="sectionTitle"><a href="{$news->getLink()}">{$news->getTitle()}</a></h3>
					<header class="contentHeader messageHeader">
						<div class="contentHeaderTitle messageHeaderBox">
							<ul class="inlineList contentHeaderMetaData articleMetaData">
								<li itemprop="author" itemscope itemtype="http://schema.org/Person">
									<span class="icon icon16 fa-user"></span>
									{if $news->userID}
										<a href="{link controller='User' id=$news->userID title=$news->username}{/link}" class="userLink" data-user-id="{@$news->userID}" itemprop="url">
											<span itemprop="name">{$news->username}</span>
										</a>
									{else}
										<span itemprop="name">{$news->username}</span>
									{/if}
								</li>

								<li>
									<span class="icon icon16 fa-clock-o"></span>
									<span>{@$news->time|time}</span>
									<meta itemprop="datePublished" content="{@$news->time|date:'c'}">
									<meta itemprop="dateModified" content="{@$news->lastChangeTime|date:'c'}">
								</li>
							</ul>

							<ul class="messageStatus">
								{if $news->isDeleted}
									<li><span class="badge label red jsIconDeleted">{lang}wcf.message.status.deleted{/lang}</span></li>
								{/if}
								{if $news->isDisabled}
									<li><span class="badge label green jsIconDisabled">{lang}wcf.message.status.disabled{/lang}</span></li>
								{/if}
								{event name='messageStatus'}
							</ul>
						</div>

						<ul class="messageQuickOptions">
							{if $news->reportQueueID}
								<li><a href="{link controller='ModerationReport' id=$news->reportQueueID}{/link}"><span class="icon icon16 fa-exclamation-triangle jsTooltip" title="{lang}cms.news.reported{/lang}"></span></a></li>
							{/if}
							{if $share}
								<li><a href="{link application='cms' controller='News' object=$news appendSession=false}{/link}" class="jsTooltip jsButtonShare" title="{lang}wcf.message.share{/lang}" data-link-title="{$news->getTitle()}">#{#$startIndex}</a></li>
							{/if}

							{event name='messageQuickOptions'}
						</ul>

						{event name='messageHeader'}
					</header>

					<div class="messageBody">
						<div class="messageText" itemprop="text">
							{if !$news->teaser|empty}
								{$news->teaser}
							{else}
								{@$news->getExcerpt()}
							{/if}
						</div>

						{event name='afterMessageText'}
					</div>

					<footer class="messageFooter">
						{if $news->showSignature && $news->getUserProfile() !== null && $news->getUserProfile()->showSignature()}
							<div class="messageSignature">
								<div>{@$news->getUserProfile()->getSignature()}</div>
							</div>
						{/if}

						{event name='messageFooter'}

						<div class="messageFooterNotes">
							{if $news->isDeleted}
								<p class="messageFooterNote guestbookEntryDeleteNote">{lang}cms.news.deleteNote{/lang}</p>
							{/if}
							{if $news->isDisabled}
								<p class="messageFooterNote guestbookEntryDisabledNote">{lang}cms.news.moderation.disabledPost{/lang}</p>
							{/if}
							{if $news->comments}
								<p class="messageFooterNote"><a class="blogCommentCount" href="{link application='cms' controller='News' object=$news}{/link}#comments">{lang}cms.news.comments.count{/lang}</a></p>
							{/if}
							{event name='messageFooterNotes'}
						</div>

						<div class="messageFooterGroup">
							<ul class="messageFooterButtons buttonList smallButtons jsMobileNavigation">
								{if $news->canEdit()}
									<li><a href="{link application='cms' controller='NewsEdit' object=$news}{/link}" title="{lang}cms.news.edit{/lang}" class="button jsMessageEditButton jsEntryInlineEditor"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
								{/if}
								{if LOG_IP_ADDRESS && $news->ipAddress && $__wcf->session->getPermission('admin.user.canViewIpAddress')}
									<li class="jsIpAddress jsOnly" data-object-id="{@$news->newsID}"><a href="#" title="{lang}cms.news.ipAddress{/lang}" class="button jsTooltip"><span class="icon icon16 fa-globe"></span> <span class="invisible">{lang}cms.news.ipAddress{/lang}</span></a></li>
								{/if}
								{if $__wcf->session->getPermission('user.profile.canReportContent')}
									<li class="jsReportEntry jsOnly" data-object-id="{@$news->newsID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="button jsTooltip"><span class="icon icon16 fa-exclamation-triangle"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>
								{/if}
								{event name='messageFooterButtons'}
							</ul>
						</div>
					</footer>
				</div>
			</div>
		</li>

		{if $share}
			{if $sortOrder == 'DESC'}
				{assign var="startIndex" value=$startIndex - 1}
			{else}
				{assign var="startIndex" value=$startIndex + 1}
			{/if}
		{/if}
	{/foreach}
</ul>
