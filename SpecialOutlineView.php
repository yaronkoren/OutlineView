<?php
/**
 * Class for the page Special:OutlineView
 *
 * @author Yaron Koren
 */

class SpecialOutlineView extends SpecialPage {

	private $tableSchemas = [];
	private $nodeNum = 1;
	private $treeData = [];

	public function __construct() {
		parent::__construct( 'OutlineView' );
	}

	public function execute( $pageName, $fullDisplay = false ) {
		//$this->checkPermissions();

		$out = $this->getOutput();
		//$request = $this->getRequest();
		//$user = $this->getUser();

		$this->setHeaders();
		$out->addModules( [ 'ext.outlineview' ] );

		if ( $pageName == '' ) {
			$text = "No page name specified.";
			$out->addHTML( $text );
			return;
		}

		$pageName = str_replace( '_', ' ', $pageName );
		$out->setPageTitle( $this->msg( 'outlineview-title', $pageName ) );

		$allChildren = $this->getChildren( $pageName, 2 );

		if ( $fullDisplay ) {
			// action=viewoutlinefull - suitable for printing.
			$this->displayAll( $pageName, $allChildren );
			$out->setPageTitle( "$pageName: full outline" );
			return;
		}

		$this->getDataForNode( $pageName, $allChildren, '#' );

                $mainDiv = Html::element( 'div', [
                        'id' => 'OutlineView',
                        //'data-input-name' => $input_name,
                        'data-tree-layout' => json_encode( $this->treeData ),
                        //'style' => 'margin: 1em 0; min-height: 200px;'
                ] );

		$text = '<table><tr><td id="menuPane">' . $mainDiv . '</td><td id="displayPane"></td></tr></table>';

		$title = Title::newFromText( $pageName );
		$fullOutlineURL = $title->getLocalURL( 'action=outlineviewfull' );
		$text .= '<p><a href="' . $fullOutlineURL . '">' . $this->msg( 'printableversion' ) . '</a></p>';

		$out->addHTML( $text );
	}

	private function getChildren( $pageName, $depth ) {
		global $wgOutlineViewMaxDepth;

		if ( $depth > $wgOutlineViewMaxDepth ) {
			// Avoid infinite loops, or just a huge tree.
			return [];
		}

		$pageChildren = [];
		$title = Title::newFromText( $pageName );
		if ( $title == null || !$title->exists() ) {
			return [];
		}

                $dbr = wfGetDB( DB_REPLICA );

                $tableNames = [];

                $res = $dbr->select(
                        'cargo_pages', 'table_name',
                        [ 'page_id' => $title->getArticleID() ]
                );
                foreach ( $res as $row ) {
                        $tableNames[] = $row->table_name;
                }
                $tableSchemas = $this->getTableSchemas( $tableNames );
		foreach( $tableSchemas as $tableName => $tableSchema ) {
                	$fieldDescriptions = $tableSchema->mFieldDescriptions;
			foreach ( $fieldDescriptions as $fieldName => $fieldDescription ) {
				if ( $fieldDescription->mType !== 'Page' || !$fieldDescription->mIsList ) {
					continue;
				}

				// For any field of type "List of Page", get all is values for the current page.
				$fieldTableName = $tableName . '__' . $fieldName;
				$cargoQuery = CargoSQLQuery::newFromValues(
					"$tableName, $fieldTableName", // tables
					"$fieldTableName._value", // fields
					'_pageID = ' . $title->getArticleID(), // where
					"$tableName._ID = $fieldTableName._rowID", // join on
					null, null,
					"_position ASC", // order by
					null, null );
				$res = $cargoQuery->run();
				foreach ( $res as $row ) {
					$childPage = $row['_value'];
					if ( $childPage === null || trim( $childPage ) === '' ) {
						continue;
					}
					$pageChildren[$childPage] = $this->getChildren( $childPage, $depth + 1 );
				}
			}
		}
		return $pageChildren;
	}

	private function getTableSchemas( $tableNames ) {
		$curTableSchemas = [];
		foreach ( $tableNames as $tableName ) {
			if ( !array_key_exists( $tableName, $this->tableSchemas ) ) {
				$newTableSchemas = CargoUtils::getTableSchemas( [ $tableName ] );
				$this->tableSchemas = array_merge( $this->tableSchemas, $newTableSchemas );
			}
			$curTableSchemas[$tableName] = $this->tableSchemas[$tableName];
		}
		return $curTableSchemas;
	}

	private function getDataForNode( $pageName, $nodeArray, $parentID ) {
		$curID = 'node' . $this->nodeNum++;
		$this->treeData[] = [ 'id' => $curID, 'parent' => $parentID, 'text' => $pageName, 'type' => 'page' ];
		foreach ( $nodeArray as $childPage => $childNodeArray ) {
			$this->getDataForNode( $childPage, $childNodeArray, $curID );
		}
	}

	private function displayAll( $pageName, $nodeArray ) {
		$out = $this->getOutput();
		$out->addHTML("<h2>$pageName</h2>\n");
		$title = Title::newFromText( $pageName );
		$article = Article::newFromTitle( $title, $this->getContext() );
		$article->view();
		foreach ( $nodeArray as $childPage => $childNodeArray ) {
			$this->displayAll( $childPage, $childNodeArray );
		}
	}

}
