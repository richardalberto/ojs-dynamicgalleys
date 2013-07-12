<?php
/** 
* @file DynamicGalleysHandler.php 
*
* Copyright (c) 2011 Richard Gonzalez Alberto 
* Distributed under the GNU GPL v2. For full terms see the file docs/COPYING. 
*
* @ingroup plugins_generic_dynamicGalleys 
* @brief Dynamic Galleys generic plugin Handler.
* 
*/

import('classes.handler.Handler');
import('file.ArticleFileManager');

class DynamicGalleysHandler extends Handler {       

    	var $plugin;        

    	function DynamicGalleysHandler(){           
		$this->plugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);            
        	$this->plugin->import("classes.Citation");            
        	$this->plugin->import("classes.Image");            
        	$this->plugin->import("classes.ArticleDynamicGalley");       
	}
	
   	/**	 
      	* Display ArticlesExtras index page.	 
      	*/	
   	function index($args = array()) {		
		$journal = &Request::getJournal();		
        	$editType = array_shift($args);		
		$journal = &Request::getJournal();		
		$issueDao = &DAORegistry::getDAO('IssueDAO');		
		$issues = &$issueDao->getIssues($journal->getJournalId(), Handler::getRangeInfo('issues'));
		$templateMgr = &TemplateManager::getManager();		
		$templateMgr->assign('editType', $editType);		
		$templateMgr->assign_by_ref('issues', $issues);		
		$templateMgr->display($this->plugin->getTemplatePath() . 'issues.tpl');

	}	
	
	/**	 
	* Display a list of articles from the selected issue.	 
	*/	
	function listArticles($args = array()) {		
		DynamicGalleysHandler::validate();		
		DynamicGalleysHandler::setupTemplate();		
		
		if ($this->plugin->getEnabled()) {			
			$issueId = array_shift($args);			
			$publishedArticleDao = &DAORegistry::getDAO('PublishedArticleDAO');			
			$articles = &$publishedArticleDao->getPublishedArticles($issueId, null, false);                        
			
			$dynamicGalleyDao = &DAORegistry::getDAO('ArticleDynamicGalleyDAO');			
			$templateMgr = &TemplateManager::getManager();                        
			$templateMgr->assign('issueId', $issueId);			
			$templateMgr->assign_by_ref('articles', $articles);                        
			$templateMgr->assign_by_ref('dynamicGalleysDao', $dynamicGalleyDao);			
			$templateMgr->display($this->plugin->getTemplatePath() . 'articles.tpl');		
		} else {			
			Request::redirect(null, 'index');		
		}	
	}        
	
	/**	 
	* Publish issue	 
	*/	
	function publishIssue($args = array()) {		
		DynamicGalleysHandler::validate();		
		DynamicGalleysHandler::setupTemplate();		
		
		if ($this->plugin->getEnabled()) {			
			$issueId = array_shift($args);                        
			$galleyType = array_shift($args);			
			// TODO: Publish, but where?		
		} else {			
			Request::redirect(null, 'index');		
		}	
	}        
	
	/**	 
	* Publish article	 
	*/	
	function publishArticle($args = array(), &$request) {		
		DynamicGalleysHandler::validate();		
		DynamicGalleysHandler::setupTemplate();                
		$issueId = array_pop($args);		
		
		if ($this->plugin->getEnabled()) {			
			$articleId = array_shift($args);                        
			$galleyType = array_shift($args);			
			$dynamicGalleyDao = &DAORegistry::getDAO('ArticleDynamicGalleyDAO');                        
			
			$html = false; 
			$pdf = false; 
			$zip = false;                        
			
			switch($galleyType){                            
				case "html": $html = true; break;                            
				case "pdf": $pdf = true; break;                            
				case "zip": $zip = true; break;                            
				default: {                                    
					$html = true; 
					$pdf = true; 
					$zip = true;
				}                        
			}                        
			
			$dynamicGalleyDao->insertDynamicGalleys($articleId, $html, $pdf, $zip);
			$request->redirect(null, 'DynamicGalleysPlugin', 'listArticles', array($issueId));		
		} else {			
			$request->redirect(null, 'index');
		}	
	}        
	
	/**	 
	* UnPublish article	 
	*/	
	function unPublishArticle($args = array()) {		
		DynamicGalleysHandler::validate();		
		DynamicGalleysHandler::setupTemplate();                
		
		$issueId = array_pop($args);		
		if ($this->plugin->getEnabled()) {			
			$articleId = array_shift($args);                        
			$galleyType = array_shift($args);			
			$dynamicGalleyDao = &DAORegistry::getDAO('ArticleDynamicGalleyDAO');                        
			$html = false; $pdf = false; $zip = false;                        
			
			switch($galleyType){                            
				case "html": $html = true; break;                            
				case "pdf": $pdf = true; break;                            
				case "zip": $zip = true; break;                            
				default: { $html = true; $pdf = true; $zip = true; }                        
			}                        
			
			$dynamicGalleyDao->deleteDynamicGalleys($articleId, $html, $pdf, $zip);  
			Request::redirect(null, 'DynamicGalleysPlugin', 'listArticles', array($issueId));		
			die();
		} else {			
			Request::redirect(null, 'index');		
			die();
		}	
	}        
	
	/**	 
	* View Galley	 
	*/	
	function view($args = array()) { 
		/*DynamicGalleysHandler::validate();            
		DynamicGalleysHandler::setupTemplate();      */              
		$articleId = array_shift($args);                    
		$galleyType = array_shift($args);                    
		$fileId = array_shift($args);     
		
		switch($galleyType){                        
			case "pdf": { DynamicGalleysHandler::showPdfGalley($articleId); break; }                        
			case "zip": { break; } // TODO                        
			case "img": { DynamicGalleysHandler::showImage($articleId, $fileId); break; }                        
			default: { DynamicGalleysHandler::showHtmlGalley($articleId); break; }                    
		}	
	}        
	
	function showPdfGalley($articleId){
		$plugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);
		
		require_once("{$plugin->getPluginPath()}/mpdf/mpdf.php");

		$plugin->import('classes.ArticleDynamicGalleyDAO');   
		$plugin->import('classes.Image');  
		$plugin->import('classes.Citation');
		$plugin->import('classes.PdfCreator');

		$articleDao = &DAORegistry::getDAO('ArticleDAO');            
		$article = &$articleDao->getArticle($articleId);		
		$pdfCreator = new PdfCreator();
		$pdfCreator->article = &$articleDao->getArticle($articleId);
		$pdfCreator->plugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);
		

		// Section            
		$sectionDao = &DAORegistry::getDAO('SectionDAO');            
		$section = &$sectionDao->getSection($article->getSectionId());

		// page number
		list($articleFirstPage) = explode("-",$article->getPages());
		if((int)$articleFirstPage <= 0) $articleFirstPage = 0;
		
		
		$page = 2;
		$fontSize = 90;
		$styleSheetPath = $plugin->getStylesPath().'pdf.css';
		$styleSheetPath = str_replace('file:', '', $styleSheetPath);
		$styleSheet = file_get_contents($styleSheetPath);
		while ($page > 1){
			$fontSize -= 5;

			$mpdf=new mPDF('', 'Letter', 0, '', 25, 25, 17, 20, 8, 8);
			
			// set pdf dpi
			$mpdf->dpi = 72;

			// header
			$mpdf->SetHTMLHeader($pdfCreator->getHeader());
			
			// footer
			$mpdf->SetHTMLFooter($pdfCreator->getFooter());
			
			// css
			$mpdf->WriteHTML($styleSheet, 1);

			// page number
			$mpdf->AddPage("", "", $articleFirstPage, "", "");

			// first page
			$mpdf->WriteHTML('<div class="head" style="font-size: '.$fontSize.'%;">'.$pdfCreator->getFirstPageHtml().'</div>');
			
			$page = count($mpdf->pages);

		}

		//$mpdf->WriteHTML($pdfCreator->getArticleCitationHtml());
		// new page
		$mpdf->WriteHTML('<pagebreak>');

		// Body
		$body = $pdfCreator->getBodyHtml();

		// Citations            
		$body = $body.$pdfCreator->getCitationsHtml();

		
		// doble colummn
		$body = preg_replace("/\<([Ii][Mm][Gg])(.*?)\>/",'{TMP_REPLACE}'.'${0}'.'{TMP_REPLACE}',$body);
		$body = preg_replace("/\<[sS][pP][aA][nN](.*?)\>/",'',$body);
		$body = str_replace("</span>",'',$body);		
		$bodyList = explode("{TMP_REPLACE}",$body);		
		//$bodySpanStyle = '<span>';
		$bodySpanStyle = '<span style="text-align: justify; color: #303030; font-family: FreeSans; font-size: 10; line-height: 120%;">';
		$hrStyle = 'style="border: 0 none; height: 1px; color: #ffffff;"';
		$part = 1;

		$bodyText = "";

		foreach ($bodyList as $key => $value) {
			if ($part % 2 != 0){
				$bodyText .= '<div class="body"><p>'.$bodySpanStyle.$value.'</span></div>';


				// // text
				// $mpdf->SetColumns(2,'J',7);
				// $mpdf->WriteHTML('<div class="body">'.$bodySpanStyle.$value.'</span></div>');
			}else{
				//get image width
				$dom = new DOMDocument();
				$dom->loadHTML($value);
				$imgFile = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
				list($width, $height, $type, $attr) = getimagesize($imgFile);

				if ($width > 250){
					$mpdf->SetColumns(2,'J',7);
					if ($bodyText != ""){
						$mpdf->WriteHTML($bodyText);
					}					
					$bodyText = "";
					$mpdf->SetColumns(0);
					$mpdf->WriteHTML('<div class="image" align="center"><hr '.$hrStyle.' />'.$value.'<hr '.$hrStyle.' /></div>');
					$mpdf->SetColumns(2,'J',7);
				}else{
					$bodyText .= '<div class="image" align="center"><hr '.$hrStyle.' />'.$value.'<hr '.$hrStyle.' /></div>';
				}
				// // images				
				// $mpdf->SetColumns(0);
				// $mpdf->WriteHTML('<div class="image" align="center"><hr '.$hrStyle.' />'.$value.'<hr '.$hrStyle.' /></div>');
			}

			$part += 1;
		}
		if ($bodyText != ""){
			$mpdf->SetColumns(2,'J',7);
			$mpdf->WriteHTML($bodyText);
		}
		
		$mpdf->Output("revf-articulo-{$article->getArticleId()}.pdf", 'I');

	}


	function parseBody($body, $columns = 3){            
		$html = "<table border=\"2\"><tr>";            
		$bodytext = array($body);            
		$text = implode(",", $bodytext); //prepare bodytext            
		$length = strlen($text); //determine the length of the text            
		$length = ceil($length/$columns); //divide length by number of columns            
		$words = explode(" ",$text); // prepare text for word count and split the body into columns            
		$c = count($words);            
		$l = 0;            
		for($i=1;$i<=$columns;$i++) {                
			$new_string = "";                
			$html .= "<td style=\"text-align:justify\" valign=\"top\">";                
			for($g=$l;$g<=$c;$g++) {                    
				if(strlen($new_string) <= $length || $i == $columns) $new_string.=$words[$g]." ";                    
				else {                        
					$l = $g;                        
					break;                    
				}                

			}                
			$html .= $new_string;                
			$html .= "</td>";            
		}            
		$html .= "</tr></table>"; // complete the table            
		
		return $html;        
	}        
	
	/**	 
	* return article data
	*/
	private function getArticleDataHtml($article){
		// Published
		$issueDao = &DAORegistry::getDAO('IssueDAO');
		$issue = &$issueDao->getIssueByArticleId($article->getArticleId(), $article->getJournalId());

		// Article
            	$publishedArticleDao = &DAORegistry::getDAO('PublishedArticleDAO');
            	$pubArticle = &$publishedArticleDao->getPublishedArticleByArticleId($article->getArticleId(), $article->getJournalId());
		
		// citation
		//$pluginVancouver = &PluginRegistry::loadPlugin('citationFormats', 'vancouver');
		$citationPlugins =& PluginRegistry::loadCategory('citationFormats');            
		$pluginVancouver = &$citationPlugins['VancouverCitationPlugin'];
		//$pluginaa = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);
		
		$journal = &Request::getJournal();
		
		$html  ="";
		$html  = "<br /><b>C&oacute;mo citar este art&iacute;culo:</b> {$pluginVancouver->fetchCitation($pubArticle, $issue, $journal)}<br />";
		
		// copyleft note
		//$ccImgFile = Config::getVar('files', 'files_dir') . "/by-nc-sa.jpg";
		//$src = "\"file://{$ccImgFile}\""
		$src = "http://i.creativecommons.org/l/by-nc-sa/3.0/88x31.png";
		
		$html  .= "<br /><b>Copyright:</b> Esta revista provee acceso libre inmediato a su contenido bajo el principio de que hacer disponible gratuitamente investigación al publico apoya a un mayor intercambio de conocimiento global. Esto significa que se permite la copia y distribución de sus contenidos científicos por cualquier medio siempre que mantenga el reconocimiento de sus autores, no haga uso comercial de las obras y no se realicen modificaciones de ellas.<br/> <img src={$src} />";

		// Datos envio
		$html  .= "<br /><br /><b>Aprobado: {$article->getDateStatusModified()}</b><br />";
		
		// Correspondencia
		$authors = $article->getAuthors();
		$author = $authors[0];

		$html  .= "<br /><b>Correspondencia:</b>  {$author->getFullName()}. {$author->getLocalizedAffiliation()} <a href=\"mailto:{$author->getEmail()}\">{$author->getEmail()}</a><br />";

		#$html = preg_replace("/\<([Ii][Mm][Gg])(.*?)\>/","<a STRTOREPLACE\${2} rel=\"lightbox\">"."\${0}".'</a>',$html);
		
		return $html;
	}


	function showHtmlGalley($articleId, $show = true){            
		$templateMgr = new TemplateManager();
		//$templateMgr = &TemplateManager::getManager();
		$dynamicGalleysPlugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);            
		$articleDao = &DAORegistry::getDAO('ArticleDAO');            
		$article = &$articleDao->getArticle($articleId);            
		$issueDao = &DAORegistry::getDAO('IssueDAO');            
		$issue = &$issueDao->getIssueByArticleId($articleId);            
		$journal = &Request::getJournal();
		$pluginVancouver = &PluginRegistry::loadPlugin('citationFormats', 'vancouver');
		$articleDataHtml = $this->getArticleDataHtml($article);

		//plugin url
		$pluginUrl = Request::getBaseUrl()."/plugins/generic/dynamicGalleys/";
		$templateMgr->assign_by_ref('pluginUrl', $pluginUrl);

		// Locale            
		$locale = Locale::getLocale();            
		$localeShort =  explode("_", $locale);            
		$localeShort = $localeShort[0];            
		$templateMgr->assign('locale', $locale);            
		$templateMgr->assign('localeShort', $localeShort);            
		
		// Common            
		$templateMgr->assign_by_ref('article', $article);            
		$templateMgr->assign_by_ref('issue', $issue);            
		$templateMgr->assign_by_ref('journal', $journal);
		$templateMgr->assign_by_ref('pluginVancouver', $pluginVancouver);
		//$templateMgr->assign_by_ref('articleDataHtml', $articleDataHtml);
		$templateMgr->assign_by_ref('articleDataHtml', $articleDataHtml);
		
		// Article            
		$templateMgr->assign('articleTitle', $article->getArticleTitle());            
		$templateMgr->assign('articleAbstract', $article->getAbstract($locale));            
		
		// Section            
		$sectionDao = &DAORegistry::getDAO('SectionDAO');            
		$section = &$sectionDao->getSection($article->getSectionId());            
		$templateMgr->assign_by_ref('section', $section);            
		
		// Authors            
		$authorsDao = &DAORegistry::getDAO('AuthorDAO');            
		//$authors = &$authorsDao->getAuthorsByArticle($article->getArticleId());       
		$authors = $article->getAuthors();
		$templateMgr->assign_by_ref('authors', $authors);            
		
		// Keywords            
		$keywords = $article->getSubject($locale);            
		$otherKeywords = $article->getSubject("en_US");            
		$templateMgr->assign('keywordsCount', count(explode(";",$keywords)));            
		$templateMgr->assign('otherKeywordsCount', count( explode(";", $otherKeywords)));            
		if(count($keywords) > 0) $templateMgr->assign('keywords', explode(";", $keywords));            
		if(count($otherKeywords) > 0) $templateMgr->assign('otherKeywords', explode(";", $otherKeywords));            
		
		$articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');            
		
		// Images            
		$images = unserialize($articlesExtrasDao->getArticleImages($articleId));            
		
		// Body            
		$body = $articlesExtrasDao->getArticleBody($article->getArticleId());            
		$body = DynamicGalleysHandler::correctImagesPath($journal, $body, $articleId, $images, false);
		//add zoom to images
		$zoomIconUrl = Request::getBaseUrl().'/plugins/generic/dynamicGalleys/images/zoom-in-icon.png';
		$body = preg_replace("/\<([Ii][Mm][Gg])(.*?)\>/",'<a STRTOREPLACE${2} rel="lightbox" id="zoom" >'.'<div style="margin-bottom:-50px; margin-top:15px; overflow:hidden;">'.'${0}'.'<br/><img style="border:1px none; position:relative; top:-45px; right:20px; width:30px; height:auto; margin-right:120px; margin-left:auto;" src="'.$zoomIconUrl.'"/>'.'</div>'.'</a>',$body);
		$body = str_replace("STRTOREPLACE src", "href", $body);
		$body = str_replace(' alt="" / ', ' ', $body);

		$templateMgr->assign('body', $body);            
		
		// Citations            
		$citations = unserialize($articlesExtrasDao->getCitationsByArticleId($article->getArticleId()));            
		$refCount = count($citations);            
		$templateMgr->assign('refCount', $refCount);            
		$templateMgr->assign_by_ref('citations', $citations);            
		if($show) $templateMgr->display($dynamicGalleysPlugin->getTemplatePath() . 'html.tpl');
		else return $templateMgr->fetch($dynamicGalleysPlugin->getTemplatePath() . 'html.tpl');  
		//if($show) $templateMgr->display($dynamicGalleysPlugin->getTemplatePath() . 'iframe.tpl');            
		//else return $templateMgr->fetch($dynamicGalleysPlugin->getTemplatePath() . 'iframe.tpl');   
	}        
	
	function correctImagesPath($journal, $body, $articleId, &$images, $isPdf = true){

		$path = $journal->getPath();

		$articleFileManager = new ArticleFileManager($articleId);            
		if($images){                
			foreach($images as $image){
				$name = preg_quote(rawurlencode((string)$image->getName()));                        
				$desc = preg_quote(rawurlencode((string)$image->getDescription()));                        
				$file =& $articleFileManager->getFile($image->getFileId());                        
				
				// Find images && links                        
				$linkPattern = "|<a href=\"({$name}[^>])\">$desc<\/a>|Ui";                        
				$src = $file->getFilePath();                        
				$url = $isPdf ? 'file://'.$src.'"' : Request::getBaseUrl().'/index.php/'.$path.'/DynamicGalleysPlugin/view/'.$articleId.'/img/'.$image->getFileId().'"';
				#$url = $isPdf ? 'file://'.$src.'"' : Request::getBaseUrl().'/index.php/finlay/DynamicGalleysPlugin/view/'.$articleId.'/img/'.$image->getFileId().'"'; // TODO: change "finlay" to full url to make it fully dynamic!                        
				//$url = Request::getBaseUrl().'/index.php/finlay/article/viewFile/'.$articleId.'/'.$image->getFileId().'"'; 
				$body = preg_replace('/([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $name . ')"/', '\1="'.$url, $body);                
			}            
		}           
		
		return $body;        
	}        
	
	function showImage($articleId, $fileId){  
		$articleFileManager = new ArticleFileManager($articleId);            
		$articleFileManager->viewFile($fileId);
	}	

	/**	 
	* Setup common template variables.	 
	* @param $subclass boolean set to true if caller is below this handler in the hierarchy	 
	*/	
	function setupTemplate($subclass = false) {		
		parent::validate();		
		$templateMgr = &TemplateManager::getManager();		
		$templateMgr->assign('pageHierachy', array(array(Request::url(null, DYNAMIC_GALLEYS_PLUGIN_NAME), 'plugins.generic.plugins.generic.dynamicGalleys.displayName')));
	}	
	
	/**	 
	* Validate that user is an editor/admin/manager/layout_editor in the selected journal.	 
	* Redirects to user index page if not properly authenticated.	 
	*/	
	function validate() {		
		$journal = &Request::getJournal();		
		if (!isset($journal) || ( !Validation::isEditor($journal->getJournalId()) && !Validation::isSiteAdmin() && !Validation::isJournalManager($journal->getJournalId()) && !Validation::isLayoutEditor($journal->getJournalId()) )) {			
			Validation::redirectLogin();		
		}	
	}        

	/**         
	* Makes a citation         
	* TODO: Delete this added to Citation Class         
	*/        
	function assemblyCitation(&$citation){            
		$templateMgr = &TemplateManager::getManager();            
		$templateMgr->assign_by_ref('citation', $citation);            
		$dynamicGalleysPlugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);            
		
		return $templateMgr->fetch($dynamicGalleysPlugin->getTemplatePath() . 'citation.tpl');        
	}        
	
	/**         
	* Makes a citation         
	*/        
	function getFirstPage(&$article, &$pdfCreator){            
		$templateMgr = &TemplateManager::getManager();            
		
		// Article            
		$publishedArticleDao = &DAORegistry::getDAO('PublishedArticleDAO');            
		$pubArticle = &$publishedArticleDao->getPublishedArticleByArticleId($article->getArticleId(), $article->getJournalId());            
		$templateMgr->assign_by_ref('article', $pubArticle);            
		
		// Published            
		$issueDao = &DAORegistry::getDAO('IssueDAO');            
		$issue = &$issueDao->getIssueByArticleId($article->getArticleId(), $article->getJournalId());            
		$templateMgr->assign_by_ref('issue', $issue);            
		
		// Journal            
		$journalDao = &DAORegistry::getDAO('JournalDAO');            
		$journal = $journalDao->getJournal($article->getJournalId());
		$templateMgr->assign_by_ref('journal', $journal);           
		 
		// Reference            
		$reference = NULL;            
		$citationPlugins =& PluginRegistry::loadCategory('citationFormats');            
		$vancouverPlugin = &$citationPlugins['VancouverCitationPlugin'];            
		$templateMgr->assign_by_ref('citationPlugin', $vancouverPlugin);            
		$reference = $templateMgr->fetch($vancouverPlugin->getTemplatePath() . 'citation.tpl');            
		$templateMgr->assign('reference', $reference);            
		
		// Img path            
		$dynamicGalleysPlugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);            
		$templateMgr->assign('logoPath', Request::getBaseUrl()."/plugins/generic/dynamicGalleys/");            
		
		// PdfCreator            
		$templateMgr->assign_by_ref('pdfCreator', $pdfCreator);            
		
		// References count            
		$articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');            
		$citations = unserialize($articlesExtrasDao->getCitationsByArticleId($article->getArticleId()));            
		$count = 0;            
		if($citations) $count = count($citations);            
		$templateMgr->assign('refCount', $count);            
		
		// Comments count            
		$commCount = 0;            
		$commentDao = &DAORegistry::getDAO('CommentDAO');            
		$comments = &$commentDao->getRootCommentsByArticleId($article->getArticleId());            
		$templateMgr->assign('commCount', count($comments));            
		$dynamicGalleysPlugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);            
		$output = $templateMgr->fetch($dynamicGalleysPlugin->getTemplatePath() . 'title.tpl');            
		
		return $output;        
	}
}
?>