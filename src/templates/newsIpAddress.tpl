<section class="section">
    <h2 class="sectionTitle">{lang}cms.news.ipAddress.news{/lang}</h2>

    <ul>
        <li>{$ipAddress[ipAddress]} ({$ipAddress[hostname]})</li>
        {if $registrationIpAddress|isset}
            <li>
                {lang}cms.news.ipAddress.registration{/lang} {$registrationIpAddress[ipAddress]} ({$registrationIpAddress[hostname]})
            </li>
        {/if}
    </ul>
</section>

{hascontent}
    <section class="section">
        <h2 class="sectionTitle">{lang}cms.news.ipAddress.author{/lang}</h2>

        <ul>
            {content}
                {foreach from=$authorIpAddresses item=authorIpAddress}
                    <li>{$authorIpAddress[ipAddress]} ({$authorIpAddress[hostname]})</li>
                {/foreach}
            {/content}
        </ul>
    </section>
{/hascontent}

{hascontent}
    <section class="section">
        <h2 class="sectionTitle">{lang}cms.news.ipAddress.otherUsers{/lang}</h2>

        <ul>
            {content}
                {foreach from=$otherUsers item=user}
                    {if $user[userID]}
                        <li>
                            <a href="{link controller='User' id=$user[userID] title=$user[username]}{/link}" class="userLink" data-user-id="{@$user[userID]}">{$user[username]}</a>
                        </li>
                    {else}
                        <li>{$user[username]} ({lang}wcf.user.guest{/lang})</li>
                    {/if}
                {/foreach}
            {/content}
        </ul>
    </section>
{/hascontent}
