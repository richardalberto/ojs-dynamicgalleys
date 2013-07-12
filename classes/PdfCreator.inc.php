<?php
// extend TCPF with custom functions
class PdfCreator {
	var $article;
	var $plugin;
	var $pluginVancouver;

	function PdfCreator(){
		$this->pluginVancouver = & PluginRegistry::loadPlugin('citationFormats', 'vancouver');
		$this->journal = &Request::getJournal();

	}

	//Page header
	public function getHeader() {
		$onlineIssn = $this->journal->getSetting('onlineIssn');
		$journalTitle = $this->journal->getLocalizedTitle();
		$header = '<table width="100%" style="border-bottom: 1px solid #9f0d0d; vertical-align: top; font-family: sans; font-size: 7pt; color: #303030;"><tr>
		<td width="33%">Descargado el: {DATE j-m-Y}</td>
		<td style="text-align: center;"></span></td>
		<td width="33%" style="text-align: right;">ISSN '.$onlineIssn.'</td>
		</tr></table>';
		return $header;
	}

	// Page footer
	public function getFooter() {
		//var_dump($this->journal->getPath());
		//die();
		//var_dump($this->journal->getUrl());
		//die();
		$url = $this->journal->getUrl();
		//$path = $this->journal->getPath();
		//$url = str_replace('/'.$path.'/index.php/'.$path, "", $url);

		$issueDao = &DAORegistry::getDAO('IssueDAO');
		$issue = &$issueDao->getIssueByArticleId($this->article->getArticleId(), $this->article->getJournalId());
		
		list($mes, $year) = explode("/", date("m/Y", strtotime($issue->getDatePublished())));
		$meses = $this->getMonthName($mes);

		$journalTitle = $this->journal->getLocalizedTitle();
		
		$footer = '<table width="100%" style="border-top: 1px solid #9f0d0d; vertical-align: top; font-family: sans; font-size: 7pt; color: #303030;"><tr>
		<td width="45%"><a href="'.$url.'">'.$journalTitle.'</a></td>
		<td style="text-align: center;">{PAGENO}</td>
		<td width="45%" style="text-align: right;">'.$meses.' '.$year.' | Volumen '.$issue->getVolume().' | Numero '.$issue->getNumber().'</td>
		</tr></table>';
		
		return $footer;
	}

	// article first page
	public function getFirstPageHtml(){
		$authors = $this->article->getAuthors();
		$keywords = $this->article->getArticleSubject();		
		$otherKeywords = $this->article->getSubject("en_US");

		// Section            
		$sectionDao = &DAORegistry::getDAO('SectionDAO');            
		$section = &$sectionDao->getSection($this->article->getSectionId());
		$articleSection = $section->getLocalizedIdentifyType();

		$firstPageHtml = '<div class="section" align="left">'.$articleSection.'</div>
							<div class="title"><h2 class="title">'.$this->article->getArticleTitle().'</h2></div>
							<div class="enTitle"><h2 class="enTitle">'.$this->article->getTitle("en_US").'</h2></div>
							<div class="authors">'.$this->getAuthorsHtml($authors).'</div><br />
							<div class="authorsBiography">'.$this->getAuthorsBiographyHtml($authors).'</div>
							<div class="articleCitation">'.$this->getArticleCitationHtml().'</div>
							<div class ="abstract">'.$this->getAbstractHtml("Resumen", $this->article->getArticleAbstract(), "Abstract", $this->article->getAbstract("en_US"), $keywords, $otherKeywords).'</div>
							<div class ="articleData">'.$this->getFirstPageBottomHtml("citation").'</div>
							
		';

		return $firstPageHtml;
	}

	// article body
	public function getBodyHtml() {
		
		$articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');            
		$bodyPart = $articlesExtrasDao->getArticleBody($this->article->getArticleId());            
		// Images
		$images = unserialize($articlesExtrasDao->getArticleImages($this->article->getArticleId()));            
		$body = DynamicGalleysHandler::correctImagesPath($this->journal, $bodyPart, $this->article->getArticleId(), $images);
		// complete body
		$fp = fopen('/tmp/data.txt', 'w');
		fwrite($fp, $body);
		fclose($fp);
		$substitutions = array(
#			"<br/>" => "", 		// hack for correct pdf visualization
#			"<br />" => "",		// hack for correct pdf visualization
			'<p> </p>' => "",	// delete empty paragraps for properly two colummn pdf visualization
			"<p></p>" => ""		// delete empty paragraps for properly two colummn pdf visualization
			);
		$body = str_replace(
			array_keys($substitutions),
			array_values($substitutions),
			$body
			);
		
		// remove links to images
		$body = preg_replace('/\<([Aa]) href="#(.*?)>(.*?)\<\/([Aa])\>/','${3}',$body);
		
		// remove links to images
		//$text = preg_replace('/\<([Aa]) href="#(.*?)>(.*?)\<\/([Aa])\>/','${3}',$text);

		return $body;
	}

	// article citations
	public function getCitationsHtml() {
		$articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');
		$citationsHtml = "";
		$citations = unserialize($articlesExtrasDao->getCitationsByArticleId($this->article->getArticleId()));            
		if($citations){                
			$citationsHtml .= '<br /><div class="references"><strong>REFERENCIAS BIBLIOGR&Aacute;FICAS</strong><br />';                                
			foreach($citations as $index => $citation){                    
				$citationText = DynamicGalleysHandler::assemblyCitation($citation);                    
				$citationsHtml .= '<p>'.($index+1).".\t".$citationText."</p>";
			}
			$citationsHtml .= '</div>';
		}

		return $citationsHtml;
	}


	public function getAuthorsHtml($authors, $showIndex = true){
		// Authors
		$html = "";
		foreach($authors as $index => $author){
			$num = $index+1;
			$html .= $author->getFullName();
			if($showIndex) $html .= "<sup>".$num."</sup>";

			if($num!= count($authors)) $html .= ", ";
		}

		return $html;            
        }

	public function getAuthorsBiographyHtml($authors){
		//
		$html = "";
		foreach($authors as $index => $author){
			$num = $index+1;
			$html .= "<sup>".$num."</sup>".$author->getAuthorBiography();
			if($num!= count($authors)) $html .= ", ";
		}

		return $html;
         }

	

    public function getAbstractHtml($title, $abstract, $titleEN, $abstractEN, $keywords, $keywordsEN){
		
		//remove paragrap tags for properly visualization
		$abstract = str_replace("<p>", "", $abstract);
		$abstract = str_replace("</p>", "", $abstract);
		$abstractEN = str_replace("<p>", "", $abstractEN);
		$abstractEN = str_replace("</p>", "", $abstractEN);

		$html = '<div class="abstract">';
		$html .= '<div id="resumen" class="abstractEsBlock" style="width:225px;float:left;">';
		$html .= "<div class=\"abstractEsHeader\"><h3 class=\"abstractEsHead\">{$title}</h3></div>";
		$html .= "<div class=\"abstractEs\">{$abstract}";
		if ($keywords != NULL){
			$html .= "<br /><br /><b>Palabras clave:</b> {$keywords}";
		}
		$html .= '</div></div>';

		if(trim($abstractEN) != ""){
			$html .= '<div id="abstract" class="abstractEnBlock" style="width:225px;float:right;">';
			$html .= "<div class=\"abstractEnHeader\"><h3 class=\"abstractEnHead\">{$titleEN}</h3></div>";
			$html .= "<div class=\"abstractEn\">{$abstractEN}";
			if ($keywordsEN != NULL){
				$html .= "<br /><br /><b>Key words:</b> {$keywordsEN}";
			}
			$html .= "</div></div>";
		}

		$html .= "</div>";
		//var_dump($html);
		//die();
		return $html;
	}


	// public function getAbstractHtml($title, $abstract, $titleEN, $abstractEN, $keywords, $keywordsEN){
	// 	//
	// 	$html  = "<div class=\"abstractEsHeader\"><h3 class=\"abstractEsHead\">{$title}</h3></div>";
	// 	$html .= "<div class=\"abstractEs\">{$abstract}";
	// 	if ($keywords != NULL){
	// 		$html .= "<br /><br /><b>Palabras clave:</b> {$keywords}</div>";
	// 	}
	// 	if(trim($abstractEN) != ""){
	// 		$html .= "<div class=\"abstractEnHeader\"><h3 class=\"abstractEnHead\">{$titleEN}</h3></div>";
	// 		$html .= "<div class=\"abstractEn\">{$abstractEN}";
	// 		if ($keywordsEN != NULL){
	// 			$html .= "<br /><br /><b>Key words:</b> {$keywordsEN}</div>";
	// 		}
	// 	}
	// 	return $html;
	// }

	public function getArticleCitationHtml(){
		// Published
		$issueDao = &DAORegistry::getDAO('IssueDAO');
		$issue = &$issueDao->getIssueByArticleId($this->article->getArticleId(), $this->article->getJournalId());
		// Article
		$publishedArticleDao = &DAORegistry::getDAO('PublishedArticleDAO');		
        $pubArticle = &$publishedArticleDao->getPublishedArticleByArticleId($this->article->getArticleId(), $this->article->getJournalId());
		// citation
		$citation = $this->pluginVancouver->fetchCitation($pubArticle, $issue, $this->journal);
		//$citation = str_replace('<div class="separator"></div>', '', $citation);
		$articleCitationHtml  = "<br /><b>C&oacute;mo citar este art&iacute;culo:</b>".$citation."<br />";
		
		return $articleCitationHtml;
	}

	public function getFirstPageBottomHtml($citation){
		
		//uncomment the following lines if the boss
		//changes her mind again this week
		// // copyleft note
		// $src = Config::getVar('files', 'files_dir') . "/by-nc-sa.jpg";
		// $html  .= "<br /><b>Copyleft:</b> Esta revista provee acceso libre inmediato a su contenido bajo el principio de que hacer disponible gratuitamente investigación al publico apoya a un mayor intercambio de conocimiento global. Esto significa que se permite la copia y distribución de sus contenidos científicos por cualquier medio siempre que mantenga el reconocimiento de sus autores, no haga uso comercial de las obras y no se realicen modificaciones de ellas. <img width=65 style=\"vertical-align: text-top;\" src=\"file://{$src}\" />";

		// Datos envio
		$ArticleDynamicGalleyDAO = &DAORegistry::getDAO('ArticleDynamicGalleyDAO');
		$firstAcceptedDatetime = $ArticleDynamicGalleyDAO->getArticleFirstAcceptedDateByArticleId($this->article->getArticleId());
		$html  .= "<br /><br /><b>Aprobado: ".$firstAcceptedDatetime."</b><br />";
		
		// Correspondencia
		$authors = &$this->article->getAuthors();
		$author = $authors[0];

		$html  .= "<br /><b>Correspondencia:</b>  {$author->getFullName()}. {$author->getLocalizedAffiliation()} <a href=\"mailto:{$author->getEmail()}\">{$author->getEmail()}</a><br />";

		return $html;
	}

	public function getMonthName($monthNum){
		$months = array("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre");

		return $months[$monthNum-1];
	}
}
?>