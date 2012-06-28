<?php

/**
 * @package utility
 */

/**
 * Class that, when envoked, will sanitize a supplied HTML string according to 
 * default or specified parameters
 *
 * @package utility
 */

class HtmlCleaner {

	private $tags;

	/**
	 * @todo expand this
	 */
	function __construct() {

	}

	/**
	 * @todo expand this
	 */
	public function cleanHtml($html = '') {
		//strip_comments is not a standard PHP function, use something else not from our codebase
		$html = strip_comments($html);








		$html = str_replace(array('\n', '\r', '\t', ' '), '', $html);
	}

	/**
	 * A complete tag list and whether or not we accept them (true/false)
	 * Includes HTML5, deprecated tags, embed, ruby, isindex, and nobr
	 *
	 * @param array $include - an array of tag names that should be accepted
	 * @param array $exclude - an array of tag names that should not be accepted
	 * @return object - Returns an updated version of the current object
	 */
	public function setTags($include = array(), $exclude = array()) {
		$tags = array(
			'!DOCTYPE' => false,
			'a' => true,
			'abbr' => true,
			'acronym' => true,
			'address' => true,
			'applet' => false,
			'area' => false,
			'b' => true,
			'base' => true,
			'basefont' => false,
			'bdo' => true,
			'big' => true,
			'blockquote' => true,
			'body' => true,
			'br' => true,
			'button' => true,
			'caption' => true,
			'center' => false,
			'cite' => true,
			'code' => true,
			'col' => true,
			'colgroup' => true,
			'dd' => true,
			'del' => true,
			'dfn' => true,
			'dir' => false,
			'div' => true,
			'dl' => true,
			'dt' => true,
			'em' => true,
			'embed' => false,
			'fieldset' => true,
			'font' => false,
			'form' => true,
			'frame' => true,
			'frameset' => true,
			'h1' => true,
			'h2' => true,
			'h3' => true,
			'h4' => true,
			'h5' => true,
			'h6' => true,
			'head' => true,
			'hr' => true,
			'html' => true,
			'i' => true,
			'iframe' => false,
			'img' => false,
			'input' => true,
			'ins' => true,
			'isindex' => false,
			'kbd' => true,
			'label' => true,
			'legend' => true,
			'li' => true,
			'link' => true,
			'map' => false,
			'menu' => false,
			'meta' => true,
			'nobr' => false,
			'noframes' => true,
			'noscript' => true,
			'object' => false,
			'ol' => true,
			'optgroup' => true,
			'option' => true,
			'p' => true,
			'param' => true,
			'pre' => true,
			'q' => true,
			'ruby' => false,
			's' => false,
			'samp' => true,
			'script' => false,
			'select' => true,
			'small' => true,
			'span' => true,
			'strike' => false,
			'strong' => true,
			'style' => false,
			'sub' => true,
			'sup' => true,
			'table' => true,
			'tbody' => true,
			'td' => true,
			'textarea' => true,
			'tfoot' => true,
			'th' => true,
			'thead' => true,
			'title' => true,
			'tr' => true,
			'tt' => true,
			'u' => false,
			'ul' => true,
			'var' => true,
			'xmp' => false);
		
		foreach ($include as $tag) {
			$tags[$tag] = true;
		}
		foreach ($exclude as $tag) {
			$tags[$tag] = false;
		}

		$this->tags = $tags;
		return $this;
	}

	public function getTags() {
		return $this->tags;
	}

	/**
	 * @todo expand this
	 */
	private function settings() {
		$settings = array(
			'abs_url',
			'and_mark',
			'anti_link_spam',
			'anti_mail_spam',
			'balance',
			'base_url',
			'cdata',
			'clean_ms_char',
			'comment',
			'css_expression',
			'keep_attributes' => array('style', 'href'),
			'direct_list_nest',
			'elements',
			'hexdec_entity',
			'hook',
			'hook_tag',
			'keep_bad',
			'lc_std_val',
			'make_tag_strict',
			'named_entity',
			'no_deprecated_attr',
			'parent',
			'safe' => true,
			'schemes' => 'href: http, https',
			'show_setting',
			'style_pass',
			'tidy' => true,
			'unique_ids',
			'valid_xhtml' => true,
			'xml:lang' => false,
		);
	}

	/**
	 * @todo Expand this maybe?
	 */
	public function strictTag($tag, $attribute) {
		// transform tag
		if ($tag == 'center') {
			$tag = 'div';
			return 'text-align: center;';
		}
		
		if ($tag == 'dir' or $tag == 'menu') {
			$tag = 'ul'; 
			return '';
		}

		if ($tag == 's' or $tag == 'strike') {
			$tag = 'span'; 
			return 'text-decoration: line-through;';
		}

		if ($tag == 'u') {
			$tag = 'span'; 
			return 'text-decoration: underline;';
		}
		$fontSizes = array(
			'0'=>'xx-small',
			'1'=>'xx-small',
			'2'=>'small',
			'3'=>'medium',
			'4'=>'large',
			'5'=>'x-large',
			'6'=>'xx-large',
			'7'=>'300%',
			'-1'=>'smaller',
			'-2'=>'60%',
			'+1'=>'larger',
			'+2'=>'150%',
			'+3'=>'200%',
			'+4'=>'300%'
		);
		if ($tag == 'font') {
			$attr = '';
			if (
				preg_match('@face\s*=\s*(\'|")([^=]+?)\\1@i', $attribute, $match) || 
				preg_match('@face\s*=\s*([^"])(\S+)@i', $attribute, $match)
			) {
				$attr .= ' font-family: ' . str_replace('"', "'", trim($match[2])) . ';';
			}

			if (preg_match('`color\s*=\s*(\'|")?(.+?)(\\1|\s|$)`i', $attribute, $match)) {
				$attr .= ' color: ' . trim($match[2]) . ';';
			}
			if (
				preg_match('`size\s*=\s*(\'|")?(.+?)(\\1|\s|$)`i', $attribute, $match) && 
				isset($fontSizes[($match = trim($match[2]))])) {
				$attr .= ' font-size: ' . $fontSizes[$match] . ';';
			}
			$tag = 'span'; 
			return ltrim($attr);
		}
		return array('tag' => $tag, 'attribute' => $attribute);
	}


	/**
	 * @todo finish conversion on this, right now it just a copy/paste.
	 */
	function hl_tag($t) {
		// tag/attribute handler
		$t = $t[0];
		// invalid < >
		if($t == '< '){return '&lt; ';}
		if($t == '>'){return '&gt;';}
		if(!preg_match('`^<(/?)([a-zA-Z][a-zA-Z1-6]*)([^>]*?)\s?>$`m', $t, $m)){
		 return str_replace(array('<', '>'), array('&lt;', '&gt;'), $t);
		}elseif(!isset($config['elements'][($e = strtolower($m[2]))])){
		 return (($config['keep_bad']%2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');
		}
		// attr string
		$a = str_replace(array("\n", "\r", "\t"), ' ', trim($m[3]));
		// tag transform
		static $eD = array('applet'=>1, 'center'=>1, 'dir'=>1, 'embed'=>1, 'font'=>1, 'isindex'=>1, 'menu'=>1, 's'=>1, 'strike'=>1, 'u'=>1); // Deprecated
		if($config['make_tag_strict'] && isset($eD[$e])){
		 $trt = hl_tag2($e, $a, $config['make_tag_strict']);
		 if(!$e){return (($config['keep_bad']%2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');}
		}
		// close tag
		static $eE = array('area'=>1, 'br'=>1, 'col'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'param'=>1); // Empty ele
		if(!empty($m[1])){
		 return (!isset($eE[$e]) ? (empty($config['hook_tag']) ? "</$e>" : $config['hook_tag']($e)) : (($config['keep_bad'])%2 ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : ''));
		}

		// open tag & attr
		static $aN = array('abbr'=>array('td'=>1, 'th'=>1), 'accept-charset'=>array('form'=>1), 'accept'=>array('form'=>1, 'input'=>1), 'accesskey'=>array('a'=>1, 'area'=>1, 'button'=>1, 'input'=>1, 'label'=>1, 'legend'=>1, 'textarea'=>1), 'action'=>array('form'=>1), 'align'=>array('caption'=>1, 'embed'=>1, 'applet'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'legend'=>1, 'table'=>1, 'hr'=>1, 'div'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'p'=>1, 'col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'alt'=>array('applet'=>1, 'area'=>1, 'img'=>1, 'input'=>1), 'archive'=>array('applet'=>1, 'object'=>1), 'axis'=>array('td'=>1, 'th'=>1), 'bgcolor'=>array('embed'=>1, 'table'=>1, 'tr'=>1, 'td'=>1, 'th'=>1), 'border'=>array('table'=>1, 'img'=>1, 'object'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'cellpadding'=>array('table'=>1), 'cellspacing'=>array('table'=>1), 'char'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charoff'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charset'=>array('a'=>1, 'script'=>1), 'checked'=>array('input'=>1), 'cite'=>array('blockquote'=>1, 'q'=>1, 'del'=>1, 'ins'=>1), 'classid'=>array('object'=>1), 'clear'=>array('br'=>1), 'code'=>array('applet'=>1), 'codebase'=>array('object'=>1, 'applet'=>1), 'codetype'=>array('object'=>1), 'color'=>array('font'=>1), 'cols'=>array('textarea'=>1), 'colspan'=>array('td'=>1, 'th'=>1), 'compact'=>array('dir'=>1, 'dl'=>1, 'menu'=>1, 'ol'=>1, 'ul'=>1), 'coords'=>array('area'=>1, 'a'=>1), 'data'=>array('object'=>1), 'datetime'=>array('del'=>1, 'ins'=>1), 'declare'=>array('object'=>1), 'defer'=>array('script'=>1), 'dir'=>array('bdo'=>1), 'disabled'=>array('button'=>1, 'input'=>1, 'optgroup'=>1, 'option'=>1, 'select'=>1, 'textarea'=>1), 'enctype'=>array('form'=>1), 'face'=>array('font'=>1), 'flashvars'=>array('embed'=>1), 'for'=>array('label'=>1), 'frame'=>array('table'=>1), 'frameborder'=>array('iframe'=>1), 'headers'=>array('td'=>1, 'th'=>1), 'height'=>array('embed'=>1, 'iframe'=>1, 'td'=>1, 'th'=>1, 'img'=>1, 'object'=>1, 'applet'=>1), 'href'=>array('a'=>1, 'area'=>1), 'hreflang'=>array('a'=>1), 'hspace'=>array('applet'=>1, 'img'=>1, 'object'=>1), 'ismap'=>array('img'=>1, 'input'=>1), 'label'=>array('option'=>1, 'optgroup'=>1), 'language'=>array('script'=>1), 'longdesc'=>array('img'=>1, 'iframe'=>1), 'marginheight'=>array('iframe'=>1), 'marginwidth'=>array('iframe'=>1), 'maxlength'=>array('input'=>1), 'method'=>array('form'=>1), 'model'=>array('embed'=>1), 'multiple'=>array('select'=>1), 'name'=>array('button'=>1, 'embed'=>1, 'textarea'=>1, 'applet'=>1, 'select'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'a'=>1, 'input'=>1, 'object'=>1, 'map'=>1, 'param'=>1), 'nohref'=>array('area'=>1), 'noshade'=>array('hr'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'object'=>array('applet'=>1), 'onblur'=>array('a'=>1, 'area'=>1, 'button'=>1, 'input'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'onchange'=>array('input'=>1, 'select'=>1, 'textarea'=>1), 'onfocus'=>array('a'=>1, 'area'=>1, 'button'=>1, 'input'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'onreset'=>array('form'=>1), 'onselect'=>array('input'=>1, 'textarea'=>1), 'onsubmit'=>array('form'=>1), 'pluginspage'=>array('embed'=>1), 'pluginurl'=>array('embed'=>1), 'prompt'=>array('isindex'=>1), 'readonly'=>array('textarea'=>1, 'input'=>1), 'rel'=>array('a'=>1), 'rev'=>array('a'=>1), 'rows'=>array('textarea'=>1), 'rowspan'=>array('td'=>1, 'th'=>1), 'rules'=>array('table'=>1), 'scope'=>array('td'=>1, 'th'=>1), 'scrolling'=>array('iframe'=>1), 'selected'=>array('option'=>1), 'shape'=>array('area'=>1, 'a'=>1), 'size'=>array('hr'=>1, 'font'=>1, 'input'=>1, 'select'=>1), 'span'=>array('col'=>1, 'colgroup'=>1), 'src'=>array('embed'=>1, 'script'=>1, 'input'=>1, 'iframe'=>1, 'img'=>1), 'standby'=>array('object'=>1), 'start'=>array('ol'=>1), 'summary'=>array('table'=>1), 'tabindex'=>array('a'=>1, 'area'=>1, 'button'=>1, 'input'=>1, 'object'=>1, 'select'=>1, 'textarea'=>1), 'target'=>array('a'=>1, 'area'=>1, 'form'=>1), 'type'=>array('a'=>1, 'embed'=>1, 'object'=>1, 'param'=>1, 'script'=>1, 'input'=>1, 'li'=>1, 'ol'=>1, 'ul'=>1, 'button'=>1), 'usemap'=>array('img'=>1, 'input'=>1, 'object'=>1), 'valign'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'value'=>array('input'=>1, 'option'=>1, 'param'=>1, 'button'=>1, 'li'=>1), 'valuetype'=>array('param'=>1), 'vspace'=>array('applet'=>1, 'img'=>1, 'object'=>1), 'width'=>array('embed'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'object'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'applet'=>1, 'col'=>1, 'colgroup'=>1, 'pre'=>1), 'wmode'=>array('embed'=>1), 'xml:space'=>array('pre'=>1, 'script'=>1, 'style'=>1)); // Ele-specific
		static $aNE = array('checked'=>1, 'compact'=>1, 'declare'=>1, 'defer'=>1, 'disabled'=>1, 'ismap'=>1, 'multiple'=>1, 'nohref'=>1, 'noresize'=>1, 'noshade'=>1, 'nowrap'=>1, 'readonly'=>1, 'selected'=>1); // Empty
		static $aNP = array('action'=>1, 'cite'=>1, 'classid'=>1, 'codebase'=>1, 'data'=>1, 'href'=>1, 'longdesc'=>1, 'model'=>1, 'pluginspage'=>1, 'pluginurl'=>1, 'usemap'=>1); // Need scheme check; excludes style, on* & src
		static $aNU = array('class'=>array('param'=>1, 'script'=>1), 'dir'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'iframe'=>1, 'param'=>1, 'script'=>1), 'id'=>array('script'=>1), 'lang'=>array('applet'=>1, 'br'=>1, 'iframe'=>1, 'param'=>1, 'script'=>1), 'xml:lang'=>array('applet'=>1, 'br'=>1, 'iframe'=>1, 'param'=>1, 'script'=>1), 'onclick'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'ondblclick'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onkeydown'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onkeypress'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onkeyup'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onmousedown'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onmousemove'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onmouseout'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onmouseover'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'onmouseup'=>array('applet'=>1, 'bdo'=>1, 'br'=>1, 'font'=>1, 'iframe'=>1, 'isindex'=>1, 'param'=>1, 'script'=>1), 'style'=>array('param'=>1, 'script'=>1), 'title'=>array('param'=>1, 'script'=>1)); // Univ & exceptions

		if($config['lc_std_val']){
		 // predef attr vals for $eAL & $aNE ele
		 static $aNL = array('all'=>1, 'baseline'=>1, 'bottom'=>1, 'button'=>1, 'center'=>1, 'char'=>1, 'checkbox'=>1, 'circle'=>1, 'col'=>1, 'colgroup'=>1, 'cols'=>1, 'data'=>1, 'default'=>1, 'file'=>1, 'get'=>1, 'groups'=>1, 'hidden'=>1, 'image'=>1, 'justify'=>1, 'left'=>1, 'ltr'=>1, 'middle'=>1, 'none'=>1, 'object'=>1, 'password'=>1, 'poly'=>1, 'post'=>1, 'preserve'=>1, 'radio'=>1, 'rect'=>1, 'ref'=>1, 'reset'=>1, 'right'=>1, 'row'=>1, 'rowgroup'=>1, 'rows'=>1, 'rtl'=>1, 'submit'=>1, 'text'=>1, 'top'=>1);
		 static $eAL = array('a'=>1, 'area'=>1, 'bdo'=>1, 'button'=>1, 'col'=>1, 'form'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'optgroup'=>1, 'option'=>1, 'param'=>1, 'script'=>1, 'select'=>1, 'table'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1, 'xml:space'=>1);
		 $lcase = isset($eAL[$e]) ? 1 : 0;
		}

		$depTr = 0;
		if($config['no_deprecated_attr']){
		 // dep attr:applicable ele
		 static $aND = array('align'=>array('caption'=>1, 'div'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'object'=>1, 'p'=>1, 'table'=>1), 'bgcolor'=>array('table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1), 'border'=>array('img'=>1, 'object'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'clear'=>array('br'=>1), 'compact'=>array('dl'=>1, 'ol'=>1, 'ul'=>1), 'height'=>array('td'=>1, 'th'=>1), 'hspace'=>array('img'=>1, 'object'=>1), 'language'=>array('script'=>1), 'name'=>array('a'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'map'=>1), 'noshade'=>array('hr'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'size'=>array('hr'=>1), 'start'=>array('ol'=>1), 'type'=>array('li'=>1, 'ol'=>1, 'ul'=>1), 'value'=>array('li'=>1), 'vspace'=>array('img'=>1, 'object'=>1), 'width'=>array('hr'=>1, 'pre'=>1, 'td'=>1, 'th'=>1));
		 static $eAD = array('a'=>1, 'br'=>1, 'caption'=>1, 'div'=>1, 'dl'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'li'=>1, 'map'=>1, 'object'=>1, 'ol'=>1, 'p'=>1, 'pre'=>1, 'script'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1, 'ul'=>1);
		 $depTr = isset($eAD[$e]) ? 1 : 0;
		}

		// attr name-vals
		if(strpos($a, "\x01") !== false){$a = preg_replace('`\x01[^\x01]*\x01`', '', $a);} // No comment/CDATA sec
		$mode = 0; $a = trim($a, ' /'); $aA = array();
		while(strlen($a)){
		 $w = 0;
		 switch($mode){
			case 0: // Name
			 if(preg_match('`^[a-zA-Z][\-a-zA-Z:]+`', $a, $m)){
				$nm = strtolower($m[0]);
				$w = $mode = 1; $a = ltrim(substr_replace($a, '', 0, strlen($m[0])));
			 }
			break; case 1:
			 if($a[0] == '='){ // =
				$w = 1; $mode = 2; $a = ltrim($a, '= ');
			 }else{ // No val
				$w = 1; $mode = 0; $a = ltrim($a);
				$aA[$nm] = '';
			 }
			break; case 2: // Val
			 if(preg_match('`^((?:"[^"]*")|(?:\'[^\']*\')|(?:\s*[^\s"\']+))(.*)`', $a, $m)){
				$a = ltrim($m[2]); $m = $m[1]; $w = 1; $mode = 0;
				$aA[$nm] = trim(($m[0] == '"' or $m[0] == '\'') ? substr($m, 1, -1) : $m);
			 }
			break;
		 }
		 if($w == 0){ // Parse errs, deal with space, " & '
			$a = preg_replace('`^(?:"[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*`', '', $a);
			$mode = 0;
		 }
		}
		if($mode == 1){$aA[$nm] = '';}

		// clean attrs
		global $S;
		$rl = isset($S[$e]) ? $S[$e] : array();
		$a = array(); $nfr = 0;
		foreach($aA as $k=>$v){
		 if(((isset($config['deny_attribute']['*']) ? isset($config['deny_attribute'][$k]) : !isset($config['deny_attribute'][$k])) or isset($rl[$k])) && ((!isset($rl['n'][$k]) && !isset($rl['n']['*'])) or isset($rl[$k])) && (isset($aN[$k][$e]) or (isset($aNU[$k]) && !isset($aNU[$k][$e])))){
			if(isset($aNE[$k])){$v = $k;}
			elseif(!empty($lcase) && (($e != 'button' or $e != 'input') or $k == 'type')){ // Rather loose but ?not cause issues
			 $v = (isset($aNL[($v2 = strtolower($v))])) ? $v2 : $v;
			}
			if($k == 'style' && !$config['style_pass']){
			 if(false !== strpos($v, '&#')){
				static $sC = array('&#x20;'=>' ', '&#32;'=>' ', '&#x45;'=>'e', '&#69;'=>'e', '&#x65;'=>'e', '&#101;'=>'e', '&#x58;'=>'x', '&#88;'=>'x', '&#x78;'=>'x', '&#120;'=>'x', '&#x50;'=>'p', '&#80;'=>'p', '&#x70;'=>'p', '&#112;'=>'p', '&#x53;'=>'s', '&#83;'=>'s', '&#x73;'=>'s', '&#115;'=>'s', '&#x49;'=>'i', '&#73;'=>'i', '&#x69;'=>'i', '&#105;'=>'i', '&#x4f;'=>'o', '&#79;'=>'o', '&#x6f;'=>'o', '&#111;'=>'o', '&#x4e;'=>'n', '&#78;'=>'n', '&#x6e;'=>'n', '&#110;'=>'n', '&#x55;'=>'u', '&#85;'=>'u', '&#x75;'=>'u', '&#117;'=>'u', '&#x52;'=>'r', '&#82;'=>'r', '&#x72;'=>'r', '&#114;'=>'r', '&#x4c;'=>'l', '&#76;'=>'l', '&#x6c;'=>'l', '&#108;'=>'l', '&#x28;'=>'(', '&#40;'=>'(', '&#x29;'=>')', '&#41;'=>')', '&#x20;'=>':', '&#32;'=>':', '&#x22;'=>'"', '&#34;'=>'"', '&#x27;'=>"'", '&#39;'=>"'", '&#x2f;'=>'/', '&#47;'=>'/', '&#x2a;'=>'*', '&#42;'=>'*', '&#x5c;'=>'\\', '&#92;'=>'\\');
				$v = strtr($v, $sC);
			 }
			 $v = preg_replace_callback('`(url(?:\()(?: )*(?:\'|"|&(?:quot|apos);)?)(.+?)((?:\'|"|&(?:quot|apos);)?(?: )*(?:\)))`iS', 'hl_prot', $v);
			 $v = !$config['css_expression'] ? preg_replace('`expression`i', ' ', preg_replace('`\\\\\S|(/|(%2f))(\*|(%2a))`i', ' ', $v)) : $v;
			}elseif(isset($aNP[$k]) or strpos($k, 'src') !== false or $k[0] == 'o'){
			 $v = str_replace("\xad", ' ', (strpos($v, '&') !== false ? str_replace(array('&#xad;', '&#173;', '&shy;'), ' ', $v) : $v));
			 $v = hl_prot($v, $k);
			 if($k == 'href'){ // X-spam
				if($config['anti_mail_spam'] && strpos($v, 'mailto:') === 0){
				 $v = str_replace('@', htmlspecialchars($config['anti_mail_spam']), $v);
				}elseif($config['anti_link_spam']){
				 $r1 = $config['anti_link_spam'][1];
				 if(!empty($r1) && preg_match($r1, $v)){continue;}
				 $r0 = $config['anti_link_spam'][0];
				 if(!empty($r0) && preg_match($r0, $v)){
					if(isset($a['rel'])){
					 if(!preg_match('`\bnofollow\b`i', $a['rel'])){$a['rel'] .= ' nofollow';}
					}elseif(isset($aA['rel'])){
					 if(!preg_match('`\bnofollow\b`i', $aA['rel'])){$nfr = 1;}
					}else{$a['rel'] = 'nofollow';}
				 }
				}
			 }
			}
			if(isset($rl[$k]) && is_array($rl[$k]) && ($v = hl_attrval($v, $rl[$k])) === 0){continue;}
			$a[$k] = str_replace('"', '&quot;', $v);
		 }
		}
		if($nfr){$a['rel'] = isset($a['rel']) ? $a['rel']. ' nofollow' : 'nofollow';}

		// rqd attr
		static $eAR = array('area'=>array('alt'=>'area'), 'bdo'=>array('dir'=>'ltr'), 'form'=>array('action'=>''), 'img'=>array('src'=>'', 'alt'=>'image'), 'map'=>array('name'=>''), 'optgroup'=>array('label'=>''), 'param'=>array('name'=>''), 'script'=>array('type'=>'text/javascript'), 'textarea'=>array('rows'=>'10', 'cols'=>'50'));
		if(isset($eAR[$e])){
		 foreach($eAR[$e] as $k=>$v){
			if(!isset($a[$k])){$a[$k] = isset($v[0]) ? $v : $k;}
		 }
		}

		// depr attrs
		if($depTr){
		 $c = array();
		 foreach($a as $k=>$v){
			if($k == 'style' or !isset($aND[$k][$e])){continue;}
			if($k == 'align'){
			 unset($a['align']);
			 if($e == 'img' && ($v == 'left' or $v == 'right')){$c[] = 'float: '. $v;}
			 elseif(($e == 'div' or $e == 'table') && $v == 'center'){$c[] = 'margin: auto';}
			 else{$c[] = 'text-align: '. $v;}
			}elseif($k == 'bgcolor'){
			 unset($a['bgcolor']);
			 $c[] = 'background-color: '. $v;
			}elseif($k == 'border'){
			 unset($a['border']); $c[] = "border: {$v}px";
			}elseif($k == 'bordercolor'){
			 unset($a['bordercolor']); $c[] = 'border-color: '. $v;
			}elseif($k == 'clear'){
			 unset($a['clear']); $c[] = 'clear: '. ($v != 'all' ? $v : 'both');
			}elseif($k == 'compact'){
			 unset($a['compact']); $c[] = 'font-size: 85%';
			}elseif($k == 'height' or $k == 'width'){
			 unset($a[$k]); $c[] = $k. ': '. ($v[0] != '*' ? $v. (ctype_digit($v) ? 'px' : '') : 'auto');
			}elseif($k == 'hspace'){
			 unset($a['hspace']); $c[] = "margin-left: {$v}px; margin-right: {$v}px";
			}elseif($k == 'language' && !isset($a['type'])){
			 unset($a['language']);
			 $a['type'] = 'text/'. strtolower($v);
			}elseif($k == 'name'){
			 if($config['no_deprecated_attr'] == 2 or ($e != 'a' && $e != 'map')){unset($a['name']);}
			 if(!isset($a['id']) && preg_match('`[a-zA-Z][a-zA-Z\d.:_\-]*`', $v)){$a['id'] = $v;}
			}elseif($k == 'noshade'){
			 unset($a['noshade']); $c[] = 'border-style: none; border: 0; background-color: gray; color: gray';
			}elseif($k == 'nowrap'){
			 unset($a['nowrap']); $c[] = 'white-space: nowrap';
			}elseif($k == 'size'){
			 unset($a['size']); $c[] = 'size: '. $v. 'px';
			}elseif($k == 'start' or $k == 'value'){
			 unset($a[$k]);
			}elseif($k == 'type'){
			 unset($a['type']);
			 static $ol_type = array('i'=>'lower-roman', 'I'=>'upper-roman', 'a'=>'lower-latin', 'A'=>'upper-latin', '1'=>'decimal');
			 $c[] = 'list-style-type: '. (isset($ol_type[$v]) ? $ol_type[$v] : 'decimal');
			}elseif($k == 'vspace'){
			 unset($a['vspace']); $c[] = "margin-top: {$v}px; margin-bottom: {$v}px";
			}
		 }
		 if(count($c)){
			$c = implode('; ', $c);
			$a['style'] = isset($a['style']) ? rtrim($a['style'], ' ;'). '; '. $c. ';': $c. ';';
		 }
		}
		// unique ID
		if($config['unique_ids'] && isset($a['id'])){
		 if(!preg_match('`^[A-Za-z][A-Za-z0-9_\-.:]*$`', ($id = $a['id'])) or (isset($GLOBALS['hl_Ids'][$id]) && $config['unique_ids'] == 1)){unset($a['id']);
		 }else{
			while(isset($GLOBALS['hl_Ids'][$id])){$id = $config['unique_ids']. $id;}
			$GLOBALS['hl_Ids'][($a['id'] = $id)] = 1;
		 }
		}
		// xml:lang
		if($config['xml:lang'] && isset($a['lang'])){
		 $a['xml:lang'] = isset($a['xml:lang']) ? $a['xml:lang'] : $a['lang'];
		 if($config['xml:lang'] == 2){unset($a['lang']);}
		}
		// for transformed tag
		if(!empty($trt)){
		 $a['style'] = isset($a['style']) ? rtrim($a['style'], ' ;'). '; '. $trt : $trt;
		}
		// return with empty ele /
		if(empty($config['hook_tag'])){
		 $aA = '';
		 foreach($a as $k=>$v){$aA .= " {$k}=\"{$v}\"";}
		 return "<{$e}{$aA}". (isset($eE[$e]) ? ' /' : ''). '>';
		}
		else{return $config['hook_tag']($e, $a);}
		// eof
	}





	/* All the helper functions that will instantiate parts of the settings */

	/** 
	 * Set up a list of attributes that we either will or will not accept inside tags.
	 * This list is independent of whether or not we want to keep them or get rid of them
	 *
	 * @param array $attributes - Optional parameter list of attributes.
	 * @return array - A list of HTML Tag attributes
	 */
	private function setAttributeList($attributes = array()) {
		if (empty($attributes)) {
			return array();
		}

		//Clean up the list, make sure there are no extra spaces or newlines, and make everything lowercase
		foreach ($attributes as $key => $attr) {
			$attr = strtolower($attr);

			if (strpos($attr, 'on*') !== false) {
				unset($attributes[$key]);
				$addOn = true;
				continue;	//Skip to the next one
			}
			$attributes[$key] = str_replace(array("\n", "\r", "\t", ' ', '*'), '', strtolower($attr));
		}

		if (isst($addOn) && $addOn) {
			$attributes = array_merge(
				$attributes, 
				array(	//JavaScript event handlers
					'onblur',
					'onchange',
					'onclick',
					'ondblclick',
					'onfocus',
					'onkeydown',
					'onkeypress',
					'onkeyup',
					'onmousedown',
					'onmousemove',
					'onmouseout',
					'onmouseover',
					'onmouseup',
					'onreset',
					'onselect',
					'onsubmit'
				),
			);
		}
		return $attributes;
	}

	/** 
	 * @todo set up input to an easier-to-remember format than type: name, name; type: name name
	 * 		Last type list does not recieve a trailing ;
	 */
	private function setAvaialbleProtocols($protocols = 'href: http, https') {
		if (empty($protocols)) {
			//Defaults from htmLawed, but we don't need that many for testing.
			//$protocols = 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; *:file, http, https';
			$protocols = 'href: http, https';
		}

		//Figure out a better name for this
		$return = array();

		foreach (explode(';', str_replace(array(' ', "\t", "\r", "\n"), '', $protocols)) as $protocol) {
			list($type, $acceptable) = explode(':', $protocol, 2);
			if ($acceptable) { 
		 		$return[$type] = array_flip(explode(',', $acceptable));
			}
		}

		if (!isset($return['*'])) {
			$return['*'] = array('file' => 1, 'http' => 1, 'https' => 1);
		}
		if (!empty($this->settings['safe']) && empty($return['style'])) {
			$return['style'] = array('!' => 1);
		}

		return $return;
	}
}