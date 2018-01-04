{foreach from=$labelGroups item=labelGroup}
	<dl{if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset} class="formError"{/if}>
		<dt><label>{$labelGroup->getTitle()}</label></dt>
		<dd>
			<ul class="labelList jsOnly" data-object-id="{@$labelGroup->groupID}">
				<li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}" data-force-selection="{if $labelGroup->forceSelection}true{else}false{/if}">
					<div class="dropdownToggle" data-toggle="labelGroup{@$labelGroup->groupID}"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
					<div class="dropdownMenu">
						<ul class="scrollableDropdownMenu">
							{foreach from=$labelGroup item=label}
								<li data-label-id="{@$label->labelID}"><span><span class="badge label{if $label->getClassNames()} {@$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></span></li>
							{/foreach}
						</ul>
					</div>
				</li>
			</ul>
			<noscript>
				<select name="labelIDs[{@$labelGroup->groupID}]">
					{foreach from=$labelGroup item=label}
						<option value="{@$label->labelID}">{lang}{$label->label}{/lang}</option>
					{/foreach}
				</select>
			</noscript>
			{if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset}
				<small class="innerError">
					{if $errorType[$labelGroup->groupID] == 'missing'}
						{lang}wcf.label.error.missing{/lang}
					{else}
						{lang}wcf.label.error.invalid{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
{/foreach}
