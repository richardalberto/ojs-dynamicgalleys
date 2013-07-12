<?php
/**
 * @file ArticleDynamicGalleyDAO.php
 *
 * Copyright (c) 2011-2013 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_dynamicGalleys
 * @brief ArticleDynamicGalley DAO class.
 *
 */

import('classes.article.ArticleGalleyDAO');

class ArticleDynamicGalleyDAO extends ArticleGalleyDAO {

	/**
	 * Constructor.
	 */
	function ArticleDynamicGalleyDAO() {
		parent::DAO();
	}

        /**
	 * Append Dynamic galleys (eg. PDF, HTML) to the list of galleys for an article
	 */
	function appendDynamicGalleys($hookName, $args) {
                $article =& $args[0];
                $row =& $args[1];
                
                $galleys =& $article->getGalleys(); // keep galleys
		$articleId =& $article->getArticleId();

                // get derived galleys from DB for this article
                $result = &$this->retrieve(
                        'SELECT dynamic_galley_id
                        FROM article_dynamic_galleys x
                        WHERE x.article_id = ? ORDER BY dynamic_galley_id DESC',
                        array($articleId)
                );

                $dynamicGalleyPlugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);
                $journal = &Request::getJournal();

                while (!$result->EOF) {
                        $row = $result->GetRowAssoc(false);
                        $galleyId = $row['dynamic_galley_id'];
                        $dynamicGalley = $this->_getDynamicGalleyFromId($galleyId, $articleId);
                        $dynamicGalley->setGalleyId($galleyId);
                        array_unshift($galleys, $dynamicGalley); // add new galley

                        $result->moveNext();
                }

		return true;
	}

        /**
	 * Insert Dynamic galleys into article_dynamic_galleys
	 */
	function insertDynamicGalleys($articleId, $html = false, $pdf = false, $zip = false) {
                if($html){
                    // publish an HTML galley
                    $this->update(
                            sprintf('INSERT INTO article_dynamic_galleys
                                    (article_id, label, galley_type, locale, date_uploaded, date_modified)
                                    VALUES
                                    (?, ?, ?, ?, %s, %s)',
                                    $this->datetimeToDB(date('dmY')), $this->datetimeToDB(date('dmY'))),
                            array(
                                    $articleId,
                                    'HTML',
                                    'text/html',
                                    'es_ES'
                            )
                    );
                }

                if($pdf){
                    // create a PDF galley
                    $this->update(
                            sprintf('INSERT INTO article_dynamic_galleys
                                    (article_id, label, galley_type, locale, date_uploaded, date_modified)
                                    VALUES
                                    (?, ?, ?, ?, %s, %s)',
                                    $this->datetimeToDB(date('dmY')), $this->datetimeToDB(date('dmY'))),
                            array(
                                    $articleId,
                                    'PDF',
                                    'application/pdf',
				    'es_ES'
                            )
                    );
                }
	}


        /**
	 * Delete Dynamic galleys from article_dynamic_galleys
	 */
	function deleteDynamicGalleys($articleId, $html = false, $pdf = false, $zip = false) {
		if($html)
			$this->update(
				'DELETE FROM article_dynamic_galleys WHERE article_id = ? AND label = ?',
				array($articleId, "HTML")
			);

		if($pdf)
			$this->update(
				'DELETE FROM article_dynamic_galleys WHERE article_id = ? AND label = ?',
				array($articleId, "PDF")
			);
	}

        /**
	 * Increment views on Dynamic galleys
	 */
	function incrementDynamicGalleyViews($hookName, $args) {
		$galleyId =& $args[0];

		return $this->update(
			'UPDATE article_dynamic_galleys SET views = views + 1 WHERE dynamic_galley_id = ?',
			$galleyId
		);

	}

        function _getDynamicGalleyFromId($dynamicGalleyId){
            $result = &$this->retrieve('SELECT	x.*,
                                        x.galley_type,
					x.locale,
					x.date_uploaded,
					x.date_modified
				FROM	article_dynamic_galleys x
				WHERE	x.dynamic_galley_id=?',
 				array($dynamicGalleyId)
 			);

            // transform row into an ArticleDynamicGalley object
            if ($result->RecordCount() != 0) {
                    $articleDynamicGalley = &$this->_returnDynamicGalleyFromRow($result->GetRowAssoc(true));

                    return $articleDynamicGalley;
            }
        }

        function &_returnDynamicGalleyFromRow(&$row){
            $dynamicGalleysPlugin = &PluginRegistry::getPlugin('generic', DYNAMIC_GALLEYS_PLUGIN_NAME);
            $dynamicGalleysPlugin->import('classes.ArticleDynamicGalley');

            $galley = new ArticleDynamicGalley();
            $galley->setGalleyId($row['DYNAMIC_GALLEY_ID']);
            $galley->setArticleId($row['ARTICLE_ID']);
            $galley->setLabel($row['LABEL']);
            $galley->setViews($row['VIEWS']);
            $galley->setFileType($row['GALLEY_TYPE']);
            $galley->setDateModified($row['DATE_MODIFIED']);
            $galley->setDateUploaded($row['DATE_MODIFIED']);
            $galley->setLocale($row['LOCALE']);
            
            return $galley;
        }

        function getDynamicGalleysByIssueId($issueId){
            $publishedArticleDao = &DAORegistry::getDAO('PublishedArticleDAO');
            $articles = &$publishedArticleDao->getPublishedArticles($issueId, null, false);

            $galleys = array();

            foreach($articles as $article){
                $result = &$this->retrieve(
				'SELECT * FROM article_dynamic_galleys
				WHERE article_id = ?',
				$article->getArticleId()
			);

                while (!$result->EOF) {
			$galleys[] = &$this->_returnDynamicGalleyFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

            }


            return $galleys;
        }


        function getDynamicGalleysByArticleId($articleId){

            $galleys = array();
            $result = &$this->retrieve(
                            'SELECT * FROM article_dynamic_galleys
                            WHERE article_id = ?',
                            $articleId
                    );

            while (!$result->EOF) {
                    $galleys[] = &$this->_returnDynamicGalleyFromRow($result->GetRowAssoc(true));
                    $result->moveNext();
            }

            return $galleys;
        }

        function getArticleFirstAcceptedDateByArticleId($articleId){
            $result = &$this->retrieve('SELECT `date_decided` FROM `edit_decisions` WHERE
                                    `article_id` = ?
                                    ORDER BY `edit_decisions`.`date_decided` ASC
                                    LIMIT 1',
                                    array($articleId)
                                    );
            return $result->fields['date_decided'];
        }
}
?>
