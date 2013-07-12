{**
 * articles.tpl
 *
 *
 * List of  articles on selected issue to potentially export
 *
 * $Id: articles.tpl,v 1.7 2007/09/04 16:31:43 damnpoet Exp $
 *}
{assign var="pageTitle" value="plugins.generic.dynamicGalleys.selectArticle.name"}
{assign var="pageCrumbTitle" value="plugins.generic.dynamicGalleys.selectArticle.name"}
{include file="common/header.tpl"}

<br/>

<a name="issues"></a>

<table width="100%" class="listing">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="65%">{translate key="article.title"}</td>
		<td width="5%" align="center">{translate key="plugins.generic.dynamicGalleys.html"}</td>
                <td width="5%" align="center">{translate key="plugins.generic.dynamicGalleys.pdf"}</td>
                <td width="5%" align="center">{translate key="plugins.generic.dynamicGalleys.all"}</td>
	</tr>
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	
{foreach from=$articles item=article}
        {assign var=galleys value=$dynamicGalleysDao->getDynamicGalleysByArticleId($article->getArticleId())}
	<tr valign="top">
		<td><a href="{url page="article" op="view" path=$article->getArticleId()}" class="action">{$article->getArticleTitle()|escape}</a></td>

                {assign var='html' value='publishArticle'}
                {assign var='pdf' value='publishArticle'}
                {assign var='zip' value='publishArticle'}
                {assign var='all' value='publishArticle'}
                {foreach from=$galleys item="galley"}
                    {if $galley->getLabel() eq "HTML"}{assign var='html' value='unPublishArticle'}{/if}
                    {if $galley->getLabel() eq "PDF"}{assign var='pdf' value='unPublishArticle'}{/if}
                {/foreach}
                {if $html eq 'unPublishArticle' && $pdf eq 'unPublishArticle' && $zip eq 'unPublishArticle'}
                    {assign var='all' value='unPublishArticle'}
                {/if}
                
                <td>
                    <a href="{url page="DynamicGalleysPlugin" op=$html path=$article->getArticleId()|to_array:"html":$issueId}" class="action">{if $html eq 'unPublishArticle'}{translate key="plugins.generic.dynamicGalleys.unpublish"}{else}{translate key="plugins.generic.dynamicGalleys.publish"}{/if}</a><br />
                    <a href="{url page="DynamicGalleysPlugin" op="view" path=$article->getArticleId()|to_array:"html"}">Ver Prueba</a>
                </td>
                <td>
                    <a href="{url page="DynamicGalleysPlugin" op=$pdf path=$article->getArticleId()|to_array:"pdf":$issueId}" class="action">{if $pdf eq 'unPublishArticle'}{translate key="plugins.generic.dynamicGalleys.unpublish"}{else}{translate key="plugins.generic.dynamicGalleys.publish"}{/if}</a>
                    <a href="{url page="DynamicGalleysPlugin" op="view" path=$article->getArticleId()|to_array:"pdf"}">Ver Prueba</a>
                </td>
                <td>
                    <a href="{url page="DynamicGalleysPlugin" op=$all path=$article->getArticleId()|to_array:"all":$issueId}" class="action">{if $html eq 'unPublishArticle' && $pdf eq 'unPublishArticle' && $zip eq 'unPublishArticle'}{translate key="plugins.generic.dynamicGalleys.unpublish"}{else}{translate key="plugins.generic.dynamicGalleys.publish"}{/if}</a>
                </td>
	</tr>
	<tr>
		<td colspan="5" class="separator">&nbsp;</td>
	</tr>
{/foreach}
{if !$articles}
	<tr>
		<td colspan="5" class="nodata">{translate key="article.noArticles"}</td>
	</tr>
	<tr>
		<td colspan=5" class="endseparator">&nbsp;</td>
	</tr>
{/if}
</table>
{include file="common/footer.tpl"}
