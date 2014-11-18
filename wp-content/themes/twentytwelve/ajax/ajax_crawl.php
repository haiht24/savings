<?php
	$parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
	require_once ($parse_uri[0] . 'wp-load.php');
	require_once (get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');

	$url = $_POST['url'];
	$strActions = $_POST['find'];
	$html = file_get_html($url);
	$numOfActions = count(explode("=>", $strActions));

	//$source = _doAction($html, $strActions, 0);
	//$each = _each($source, "article[class=AllCategories-cat]");
	//echo $each->find('h2')->innertext();
	//echo _doAction();
	//echo _doAction(_doAction($html, $strActions, 0), $strActions, 1);

    $strAction = $strActions;
    $actionNum = 0;

	if ($strAction && $html) {
	    $arrAction = explode("=>", $strAction);
	    $arrSingleAction = explode(":", $arrAction[$actionNum]);
	    // Find by Class/Id
	    if (trim($arrSingleAction[0]) == "f") {
	        $result = _find($html, $arrSingleAction[1], (int)(trim($arrSingleAction[2])));
	    }
	    // Foreach
	    elseif (trim($arrSingleAction[0]) == "e") {
	        $result = _each($html, $arrSingleAction[1]);
	    }
	    echo $result;
	}

	/**
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 */
	function _find($source = null, $findWhat = null, $pos = 0) {
	    if ($source && $findWhat) {
	        return $source->find($findWhat, (int)$pos);
	    }
	}
	function _each($source = null, $findWhat = null) {
	    if ($findWhat && $source) {
	        foreach ($source->find($findWhat) as $e) {
	            echo $e;
	        }
	    }
	}
	function _doAction($html = null, $strAction = null, $actionNum = 0) {
	    if ($strAction && $html) {
	        $arrAction = explode("=>", $strAction);
	        $arrSingleAction = explode(":", $arrAction[$actionNum]);
	        // Find by Class/Id
	        if (trim($arrSingleAction[0]) == "f") {
	            $result = _find($html, $arrSingleAction[1], (int)(trim($arrSingleAction[2])));
	        }
	        // Foreach
	        elseif (trim($arrSingleAction[0]) == "e") {
	            $result = _each($html, $arrSingleAction[1]);
	        }
	        return $result;
	    }
	}
