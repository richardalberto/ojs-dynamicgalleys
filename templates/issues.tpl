{**
 * issues.tpl
 *
 *
 * List of issues to potentially export
 *
 * $Id: issues.tpl,v 1.7 2009/08/04 16:31:43 damnpoet Exp $
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.dynamicGalleys.selectIssue.name"}
{url|assign:"currentUrl" page="DynamicGalleysPlugin"}
{include file="common/header.tpl"}
{/strip}

<br />

<a name="issues"></a>

<table width="100%" class="listing">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="65%">{translate key="issue.issue"}</td>
		<td width="5%" align="center">{translate key="plugins.generic.dynamicGalleys.html"}</td>
                <td width="5%" align="center">{translate key="plugins.generic.dynamicGalleys.pdf"}</td>
                <td width="5%" align="center">{translate key="plugins.generic.dynamicGalleys.all"}</td>
                <td width="5%" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	
	{iterate from=issues item=issue}
	<tr valign="top">
		<td><a href="{url page="issue" op="view" path=$issue->getIssueId()}" class="action">{$issue->getIssueIdentification()|escape}</a></td>
                <td><a href="{url page="DynamicGalleysPlugin" op="publishIssue" path=$issue->getIssueId()|to_array:"html"}" class="action">{translate key="plugins.generic.dynamicGalleys.publish"}</a></td>
                <td><a href="{url page="DynamicGalleysPlugin" op="publishIssue" path=$issue->getIssueId()|to_array:"pdf"}" class="action">{translate key="plugins.generic.dynamicGalleys.publish"}</a></td>
                <td><a href="{url page="DynamicGalleysPlugin" op="publishIssue" path=$issue->getIssueId()|to_array:"all"}" class="action">{translate key="plugins.generic.dynamicGalleys.publish"}</a></td>
		<td align="right"><a href="{url page="DynamicGalleysPlugin" op="listArticles" path=$issue->getIssueId()}" class="action">{translate key="article.articles"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="{if $issues->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $issues->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="issue.noIssues"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td colspan="1" align="left">{page_info iterator=$issues}</td>
		<td colspan="3" align="right">{page_links anchor="issues" name="issues" iterator=$issues}</td>
	</tr>
{/if}
</table>

{include file="common/footer.tpl"}
