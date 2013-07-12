<table width="100%" border="0" cellspacing="3" cellpadding="3">

  <tr valign="top">

    <td align="right" style="text-align:right" width="35%">

      <img src="{$logoPath}images/mslogo.png" /><br />

      <div style="font-size: 25px"><strong>RNPS:</strong> 2007.<br />

      <strong>e-ISSN:</strong> 2221-2434<br />

      <strong>URL:</strong>&nbsp;<a href="http://www.revfinlay.sld.cu/" style="color:#0000FF; text-decoration:none">http://www.revfinlay.sld.cu</a><br />

      <strong>e-mail:</strong>&nbsp;<a href="mailto:mikhail@infomed.sld.cu" style="color:#0000FF; text-decoration:none">mikhail@infomed.sld.cu</a><br /></div>

    </td>

    <td width="65%">

        <h2>{$article->getArticleTitle()}</h2>

        {$pdfCreator->getAuthorsHtml($article->getAuthors(), false)}

        <table width="100%" cellpadding="1" cellspacing="1">

            <tr>

                <td colspan="4">&nbsp;</td>

            </tr>

            <tr>

              <td width="18%" style="font-size: 25px; text-align:right; font-weight:bold">Sección:</td>

              <td width="36%" style="font-size: 25px; width:35%; text-align:left;">{$article->getSectionTitle()}</td>

              <td width="14%" style="font-size: 25px; width:28%; text-align:right; font-weight:bold">Enviado:</td>

              <td width="32%" style="font-size: 25px; width:22%; text-align:left;">{$article->getDateSubmitted()}</td>

            </tr>

            <tr>

              <td width="18%" style="font-size: 25px; text-align:right; font-weight:bold">Citar&nbsp;como:</td>

              <td width="36%" style="font-size: 25px; text-align:left;">MS,&nbsp;Vol {$issue->getVolume()}, No {$issue->getNumber()} ({$issue->getYear()}): a{$article->getArticleId()}</td>

              <td width="15%" style="font-size: 25px; text-align:right; font-weight:bold">Publicado:</td>

              <td width="32%" style="font-size: 25px; text-align:left;">{$article->getDatePublished()}</td>

            </tr>

        </table>

    </td>

  </tr>

  <tr>

    <td colspan="2"><hr width="100%" size="1px" color="#7b7979" /></td>

  </tr>

  <tr>

    <td width="100%" colspan="2" align="center">

        <div align="center">

            <div style="font-size: 30px; color: #656262;  line-height: 1.5em; border-bottom: 2px solid #a7a5a6;">Puede encontrar informaci&oacute;n actualizada sobre este art&iacute;culo en:</div>

            <div style="font-size: 25px"><a href="http://www.revfinlay.sld.cu/index.php/finlay/article/view/{$article->getArticleId()}" style="color:#0000FF; text-decoration:none">http://www.revfinlay.sld.cu/index.php/finlay/article/view/{$article->getArticleId()}</a></div>

        </div>

    </td>

  </tr>

  <tr>

    <td colspan="2"><hr width="100%" size="1px" color="#7b7979" /></td>

  </tr>

  <tr valign="top">

    <td align="right" style="text-align:right" width="35%">

    	<div style="font-weight:bold">Información&nbsp;bibliográfica:</div>

     </td>

    <td width="65%">

    	<div style="font-size: 30px; color: #656262;  line-height: 1.5em; border-bottom: 2px solid #a7a5a6;">Vancouver</div>

    	<div style="font-size: 25px">{$reference}</div><br />

        <div style="font-size: 30px; color: #656262; line-height: 1.5em; border-bottom: 2px solid #a7a5a6;">Otros estilos para citar este artículo pueden ser consultados en:</div>

        <div style="font-size: 25px"><a href="http://www.revfinlay.sld.cu/index.php/finlay/rt/captureCite/{$article->getArticleId()}/0" style="color:#0000FF; text-decoration:none">http://www.revfinlay.sld.cu/index.php/finlay/rt/captureCite/{$article->getArticleId()}/0</a></div>

    </td>

  </tr>



  <tr>

    <td colspan="2"><hr width="100%" size="1px" color="#7b7979" /></td>

  </tr>



  <tr valign="top">

    <td align="right" style="text-align:right" width="35%"><div style="font-weight:bold">Referencias:</div></td>

    <td width="65%">

      <div style="font-size: 25px">Este artículo cita {$refCount} artículo(s), pueden tener acceso aqu&iacute;:</div>

      <div style="font-size: 25px"><a href="http://www.revfinlay.sld.cu/index.php/finlay/dynamicGalleys/view/{$article->getArticleId()}/html#ref-list" style="color:#0000FF; text-decoration:none">http://www.recvfinlay.sld.cu/index.php/finlay/dynamicGalleys/view/{$article->getArticleId()}/html#ref-list</a></div>

    </td>

  </tr>

  <tr valign="top">

    <td align="right" style="text-align:right;" width="35%"><div style="font-weight:bold">Respuesta(s) R&aacute;pida(s):</div></td>

    <td width="65%">

        <div style="font-size: 25px">Este art&iacute;culo tiene {$commCount} respuesta(s) r&aacute;pida(s) a la(s) cual(es) puede tener acceso aqu&iacute;:</div>

        <div style="font-size: 25px"><a href="http://www.revfinlay.sld.cu/index.php/finlay/comment/view/{$article->getArticleId()}/0" style="color:#0000FF; text-decoration:none">http://www.revfinlay.sld.cu/index.php/finlay/comment/view/{$article->getArticleId()}/0</a></div>

    </td>

  </tr>

  <tr>

    <td colspan="2"><hr width="100%" size="1" color="#7b7979" /></td>

  </tr>

  <tr valign="top">

    <td align="right" style="text-align:right;" width="35%"><div style="font-weight:bold">Notas:</div></td>

    <td width="65%">&nbsp;</td>

  </tr>

</table>