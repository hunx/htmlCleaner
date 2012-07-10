<?php

/*
htmLawed 1.1.11, 5 June 2012
Copyright Santosh Patnaik
Dual licensed with LGPL 3 and GPL 2 or later
A PHP Labware internal utility; www.bioinformatics.org/phplabware/internal_utilities/htmLawed

See htmLawed_README.txt/htm
*/

function htmLawed($html, $config = 1, $spec = array()) {
	$config = is_array($config) ? $config : array();
	if (!empty($config['valid_xhtml'])) {
		$config['elements'] = empty($config['elements']) ? '*-center-dir-font-isindex-menu-s-strike-u' : $config['elements'];
		$config['make_tag_strict'] = isset($config['make_tag_strict']) ? $config['make_tag_strict'] : 2;
		$config['xml:lang'] = isset($config['xml:lang']) ? $config['xml:lang'] : 2;
	}

	// config available tags
	$element = array(
		'a' => 1,
		'abbr' => 1,
		'acronym' => 1,
		'address' => 1,
		'applet' => 1,
		'area' => 1,
		'b' => 1,
		'bdo' => 1,
		'big' => 1,
		'blockquote' => 1,
		'br' => 1,
		'button' => 1,
		'caption' => 1,
		'center' => 1,
		'cite' => 1,
		'code' => 1,
		'col' => 1,
		'colgroup' => 1,
		'dd' => 1,
		'del' => 1,
		'dfn' => 1,
		'dir' => 1,
		'div' => 1,
		'dl' => 1,
		'dt' => 1,
		'em' => 1,
		'embed' => 1,
		'fieldset' => 1,
		'font' => 1,
		'form' => 1,
		'h1' => 1,
		'h2' => 1,
		'h3' => 1,
		'h4' => 1,
		'h5' => 1,
		'h6' => 1,
		'hr' => 1,
		'i' => 1,
		'iframe' => 1,
		'img' => 1,
		'input' => 1,
		'ins' => 1,
		'isindex' => 1,
		'kbd' => 1,
		'label' => 1,
		'legend' => 1,
		'li' => 1,
		'map' => 1,
		'menu' => 1,
		'noscript' => 1,
		'object' => 1,
		'ol' => 1,
		'optgroup' => 1,
		'option' => 1,
		'p' => 1,
		'param' => 1,
		'pre' => 1,
		'q' => 1,
		'rb' => 1,
		'rbc' => 1,
		'rp' => 1,
		'rt' => 1,
		'rtc' => 1,
		'ruby' => 1,
		's' => 1,
		'samp' => 1,
		'script' => 1,
		'select' => 1,
		'small' => 1,
		'span' => 1,
		'strike' => 1,
		'strong' => 1,
		'sub' => 1,
		'sup' => 1,
		'table' => 1,
		'tbody' => 1,
		'td' => 1,
		'textarea' => 1,
		'tfoot' => 1,
		'th' => 1,
		'thead' => 1,
		'tr' => 1,
		'tt' => 1,
		'u' => 1,
		'ul' => 1,
		'var' => 1
	); // 86/deprecated+embed+ruby

	if (!empty($config['safe'])) {
		unset($element['applet'], $element['embed'], $element['iframe'], $element['object'], $element['script']);
	}
	//elements specified by the user (can either keep or get rid of)
	$passedElements = !empty($config['elements']) ? str_replace(array("\n", "\r", "\t", ' '), '', $config['elements']) : '*';

	if ($passedElements == '-*') {
		//Don't keep any tags
		$element = array();
	} elseif (strpos($passedElements, '*') === false) {
		//Only keep these tags
		$element = array_flip(explode(',', $passedElements));
	} else {
		//Keep or get rid of the specified elements based on operator (- == get rid of, + == keep). If no operator is specified, do not change
		if (!empty($passedElements)) {
			preg_match_all('`(?:^|-|\+)[^\-+]+?(?=-|\+|$)`', $passedElements, $matches, PREG_SET_ORDER);
			for ($i = count($matches); --$i >= 0;) {
				$matches[$i] = $matches[$i][0];
			}
			foreach ($matches as $match) {
				if ($match[0] == '+') {
					$element[substr($match, 1)] = 1;
				}
				if ($match[0] == '-' && isset($element[($match = substr($match, 1))]) && !in_array('+'. $match, $matches)) {
					unset($element[$match]);
				}
			}
		}
	}

	//Assign by reference
	$config['elements'] =& $element;

	// config attrs
	$deniedAttributes = !empty($config['deny_attribute']) ? str_replace(array("\n", "\r", "\t", ' '), '', $config['deny_attribute']) : '';
	$deniedAttributes = array_flip((isset($deniedAttributes[0]) && $deniedAttributes[0] == '*') ? 
		explode('-', $deniedAttributes) : explode(',', $deniedAttributes . (!empty($config['safe']) ? ',on*' : 
		'')));

	if (isset($deniedAttributes['on*'])) {
		unset($deniedAttributes['on*']);
			$deniedAttributes += array(
				'onblur' => 1,
				'onchange' => 1,
				'onclick' => 1,
				'ondblclick' => 1,
				'onfocus' => 1,
				'onkeydown' => 1,
				'onkeypress' => 1,
				'onkeyup' => 1,
				'onmousedown' => 1,
				'onmousemove' => 1,
				'onmouseout' => 1,
				'onmouseover' => 1,
				'onmouseup' => 1,
				'onreset' => 1,
				'onselect' => 1,
				'onsubmit' => 1
			);
	}

	$config['deny_attribute'] = $deniedAttributes;

	// config URL
	$acceptableProtocols = 'href: http, https';		//Use this as default temporarily
	/**
	 * @todo remove temporary default
	 */
	/*$acceptableProtocols = (isset($config['schemes'][2]) && strpos($config['schemes'], ':')) 
		? strtolower($config['schemes']) 
		: 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; *:file, http, https'; */
	$config['schemes'] = array();
	foreach (explode(';', str_replace(array(' ', "\t", "\r", "\n"), '', $acceptableProtocols)) as $protocol) {
		list($protocolType, $acceptable) = explode(':', $protocol, 2);
		if ($acceptable) { 
	 		$config['schemes'][$protocolType] = array_flip(explode(',', $acceptable));
		}
	}

	if (!isset($config['schemes']['*'])) {
		$config['schemes']['*'] = array('file'=>1, 'http'=>1, 'https'=>1,);
	}
	if (!empty($config['safe']) && empty($config['schemes']['style'])) {
		$config['schemes']['style'] = array('!'=>1);
	}

	//Whether or not to change url's to absolute or relative, or just ignore them
	$config['abs_url'] = isset($config['abs_url']) ? $config['abs_url'] : 0;

	if (!isset($config['base_url']) or !preg_match('`^[a-zA-Z\d.+\-]+://[^/]+/(.+?/)?$`', $config['base_url'])) {
		$config['base_url'] = $config['abs_url'] = 0;
	}

	// config rest
	$config['and_mark'] = empty($config['and_mark']) ? 0 : 1;
	$config['anti_link_spam'] = (isset($config['anti_link_spam']) && is_array($config['anti_link_spam']) && count($config['anti_link_spam']) == 2 && (empty($config['anti_link_spam'][0]) or hl_regex($config['anti_link_spam'][0])) && (empty($config['anti_link_spam'][1]) or hl_regex($config['anti_link_spam'][1]))) ? $config['anti_link_spam'] : 0;
	$config['anti_mail_spam'] = isset($config['anti_mail_spam']) ? $config['anti_mail_spam'] : 0;
	$config['balance'] = isset($config['balance']) ? (bool)$config['balance'] : 1;
	$config['cdata'] = isset($config['cdata']) ? $config['cdata'] : (empty($config['safe']) ? 3 : 0);
	$config['clean_ms_char'] = empty($config['clean_ms_char']) ? 0 : $config['clean_ms_char'];
	$config['comment'] = isset($config['comment']) ? $config['comment'] : (empty($config['safe']) ? 3 : 0);
	$config['css_expression'] = empty($config['css_expression']) ? 0 : 1;
	$config['direct_list_nest'] = empty($config['direct_list_nest']) ? 0 : 1;
	$config['hexdec_entity'] = isset($config['hexdec_entity']) ? $config['hexdec_entity'] : 1;
	$config['hook'] = (!empty($config['hook']) && function_exists($config['hook'])) ? $config['hook'] : 0;
	$config['hook_tag'] = (!empty($config['hook_tag']) && function_exists($config['hook_tag'])) ? $config['hook_tag'] : 0;
	$config['keep_bad'] = isset($config['keep_bad']) ? $config['keep_bad'] : 6;
	$config['lc_std_val'] = isset($config['lc_std_val']) ? (bool)$config['lc_std_val'] : 1;
	$config['make_tag_strict'] = isset($config['make_tag_strict']) ? $config['make_tag_strict'] : 1;
	$config['named_entity'] = isset($config['named_entity']) ? (bool)$config['named_entity'] : 1;
	$config['no_deprecated_attr'] = isset($config['no_deprecated_attr']) ? $config['no_deprecated_attr'] : 1;
	$config['parent'] = isset($config['parent'][0]) ? strtolower($config['parent']) : 'body';
	$config['show_setting'] = !empty($config['show_setting']) ? $config['show_setting'] : 0;
	$config['style_pass'] = empty($config['style_pass']) ? 0 : 1;
	$config['tidy'] = empty($config['tidy']) ? 0 : $config['tidy'];
	$config['unique_ids'] = isset($config['unique_ids']) ? $config['unique_ids'] : 1;
	$config['xml:lang'] = isset($config['xml:lang']) ? $config['xml:lang'] : 0;

	if (isset($GLOBALS['config'])) {
		$reConfig = $GLOBALS['config'];
	}
	$GLOBALS['config'] = $config;

	$spec = is_array($spec) ? $spec : hl_spec($spec);
	if (isset($GLOBALS['spec'])) {
		$reSpec = $GLOBALS['spec'];
	}
	$GLOBALS['spec'] = $spec;

	$html = preg_replace('`[\x00-\x08\x0b-\x0c\x0e-\x1f]`', '', $html);

	//Clean out microsoft characters
	if ($config['clean_ms_char']) {
		$msChars = array("\x7f"=>'', "\x80"=>'&#8364;', "\x81"=>'', "\x83"=>'&#402;', "\x85"=>'&#8230;', "\x86"=>'&#8224;', "\x87"=>'&#8225;', "\x88"=>'&#710;', "\x89"=>'&#8240;', "\x8a"=>'&#352;', "\x8b"=>'&#8249;', "\x8c"=>'&#338;', "\x8d"=>'', "\x8e"=>'&#381;', "\x8f"=>'', "\x90"=>'', "\x95"=>'&#8226;', "\x96"=>'&#8211;', "\x97"=>'&#8212;', "\x98"=>'&#732;', "\x99"=>'&#8482;', "\x9a"=>'&#353;', "\x9b"=>'&#8250;', "\x9c"=>'&#339;', "\x9d"=>'', "\x9e"=>'&#382;', "\x9f"=>'&#376;');
		$msChars = $msChars + ($config['clean_ms_char'] == 1 ? array("\x82"=>'&#8218;', "\x84"=>'&#8222;', "\x91"=>'&#8216;', "\x92"=>'&#8217;', "\x93"=>'&#8220;', "\x94"=>'&#8221;') : array("\x82"=>'\'', "\x84"=>'"', "\x91"=>'\'', "\x92"=>'\'', "\x93"=>'"', "\x94"=>'"'));
		$html = strtr($html, $msChars);
	}

	//Allow CData And/Or Comments
	if ($config['cdata'] || $config['comment']) {
		$html = preg_replace_callback('`<!(?:(?:--.*?--)|(?:\[CDATA\[.*?\]\]))>`sm', 'hl_cmtcd', $html);
	}

	//HTML Entities
	$html = preg_replace_callback('@&amp;([A-Za-z][A-Za-z0-9]{1,30}|#(?:[0-9]{1,8}|[Xx][0-9A-Fa-f]{1,7}));@', 'hl_ent', str_replace('&', '&amp;', $html));

	if ($config['unique_ids'] && !isset($GLOBALS['hl_Ids'])) {
		$GLOBALS['hl_Ids'] = array();
	}
	if ($config['hook']) { 
		$html = $config['hook']($html, $config, $spec);
	}

	if ($config['show_setting'] && preg_match('`^[a-z][a-z0-9_]*$`i', $config['show_setting'])) {
		$GLOBALS[$config['show_setting']] = array('config' => $config, 'spec' => $spec, 'time' => microtime());
	}

	// main
	//Select all the HTML tags in the supplied code
	$html = preg_replace_callback('`<(?:(?:\s|$)|(?:[^>]*(?:>|$)))|>`m', 'hl_tag', $html);
	$html = $config['balance'] ? hl_bal($html, $config['keep_bad'], $config['parent']) : $html;
	$html = (($config['cdata'] or $config['comment']) && strpos($html, "\x01") !== false) ? str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05"), array('', '', '&', '<', '>'), $html) : $html;
	$html = $config['tidy'] ? hl_tidy($html, $config['tidy'], $config['parent']) : $html;
	unset($config, $element);

	if (isset($reConfig)) {
		$GLOBALS['config'] = $reConfig;
	}
	if (isset($reSpec)) {
		$GLOBALS['spec'] = $reSpec;
	}

	return $html;
	// eof
}


function hl_attrval ($attrValue, $spec) {
	// check attr val against $spec
	/**
	 * @todo find a better name for $o
	 */
	$o = 1;
	$length = strlen($attrValue);
	foreach ($spec as $key => $value) {
		switch ($key) {
			case 'maxlen':
				if ($length > $value) {
					$o = 0;
				}
			break;
			case 'minlen':
				if ($length < $value) {
					$o = 0;
				}
			break;
			case 'maxval':
				if ((float)($attrValue) > $value) {
					$o = 0;
				}
			break;
			case 'minval':
				if ((float)($attrValue) < $value) {
					$o = 0;
				}
			break;
			case 'match':
				if (!preg_match($value, $attrValue)) {
					$o = 0;
				}
			break;
			case 'nomatch':
				if (preg_match($value, $attrValue)) {
					$o = 0;
				}
			break;
			case 'oneof':
				$m = 0;
				foreach (explode('|', $value) as $n) {
					if ($attrValue == $n) {
						$m = 1;
						break;
					}
				}
				$o = $m;
			break;
			case 'noneof':
				$m = 1;
				foreach (explode('|', $value) as $n) {
					if ($attrValue == $n) {
						$m = 0;
						break;
					}
				}
				$o = $m;
			break;
			default:
			break;
		}
		if (!$o) {
			break;
		}
	}
	return ($o ? $attrValue : (isset($spec['default']) ? $spec['default'] : 0));
	// eof
}

//function hl_bal($t, $do = 1, $in = 'div') {
function hl_bal($html, $keepBad = 1, $parent = 'div') {
	// balance tags
	// by content

	/**
	 * @todo Figure out what to name the rest of these arrays
	 */
	$block = array(
		'blockquote' => 1,
		'form' => 1,
		'map' => 1,
		'noscript' => 1
	); // Block

	$empty = array(
		'area' => 1,
		'br' => 1,
		'col' => 1,
		'embed' => 1,
		'hr' => 1,
		'img' => 1,
		'input' => 1,
		'isindex' => 1,
		'param' => 1
	); // Empty

	$flow = array(
		'button' => 1,
		'del' => 1,
		'div' => 1,
		'dd' => 1,
		'fieldset' => 1,
		'iframe' => 1,
		'ins' => 1,
		'li' => 1,
		'noscript' => 1,
		'object' => 1,
		'td' => 1,
		'th' => 1
	); // Flow; later context-wise dynamic move of ins & del to $inline

	$inline = array(
		'a' => 1,
		'abbr' => 1,
		'acronym' => 1,
		'address' => 1,
		'b' => 1,
		'bdo' => 1,
		'big' => 1,
		'caption' => 1,
		'cite' => 1,
		'code' => 1,
		'dfn' => 1,
		'dt' => 1,
		'em' => 1,
		'font' => 1,
		'h1' => 1,
		'h2' => 1,
		'h3' => 1,
		'h4' => 1,
		'h5' => 1,
		'h6' => 1,
		'i' => 1,
		'kbd' => 1,
		'label' => 1,
		'legend' => 1,
		'p' => 1,
		'pre' => 1,
		'q' => 1,
		'rb' => 1,
		'rt' => 1,
		's' => 1,
		'samp' => 1,
		'small' => 1,
		'span' => 1,
		'strike' => 1,
		'strong' => 1,
		'sub' => 1,
		'sup' => 1,
		'tt' => 1,
		'u' => 1,
		'var' => 1
	); // Inline

	$illegal = array(
		'a' => array('a' => 1),
		'button' => array(
			'a' => 1,
			'button' => 1,
			'fieldset' => 1,
			'form' => 1,
			'iframe' => 1,
			'input' => 1,
			'label' => 1,
			'select' => 1,
			'textarea' => 1
		),
		'fieldset' => array('fieldset' => 1),
		'form' => array('form' => 1),
		'label' => array('label' => 1),
		'noscript' => array('script' => 1),
		'pre' => array(
			'big' => 1,
			'font' => 1,
			'img' => 1,
			'object' => 1,
			'script' => 1,
			'small' => 1,
			'sub' => 1,
			'sup' => 1
		),
		'rb' => array('ruby' => 1),
		'rt' => array('ruby' => 1)
	); // Illegal

	$illegal2 = array_keys($illegal);
	$cR = array(
		'blockquote' => 1,
		'dir' => 1,
		'dl' => 1,
		'form' => 1,
		'map' => 1,
		'menu' => 1,
		'noscript' => 1,
		'ol' => 1,
		'optgroup' => 1,
		'rbc' => 1,
		'rtc' => 1,
		'ruby' => 1,
		'select' => 1,
		'table' => 1,
		'tbody' => 1,
		'tfoot' => 1,
		'thead' => 1,
		'tr' => 1,
		'ul' => 1
	);

	$parentChild = array(
		'colgroup' => array('col' => 1), 
		'dir' => array('li' => 1), 
		'dl' => array('dd' => 1, 
		'dt' => 1), 
		'menu' => array('li' => 1), 
		'ol' => array('li' => 1), 
		'optgroup' => array('option' => 1), 
		'option' => array('#pcdata' => 1), 
		'rbc' => array('rb' => 1), 
		'rp' => array('#pcdata' => 1), 
		'rtc' => array('rt' => 1), 
		'ruby' => array('rb' => 1, 
		'rbc' => 1, 
		'rp' => 1, 
		'rt' => 1, 
		'rtc' => 1), 
		'select' => array(
			'optgroup' => 1,
			'option' => 1
		), 
		'script' => array('#pcdata' => 1), 
		'table' => array(
			'caption' => 1, 
			'col' => 1, 
			'colgroup' => 1, 
			'tfoot' => 1, 
			'tbody' => 1, 
			'tr' => 1, 
			'thead' => 1
		), 
		'tbody' => array('tr' => 1), 
		'tfoot' => array('tr' => 1), 
		'textarea' => array('#pcdata' => 1), 
		'thead' => array('tr' => 1), 
		'tr' => array('td' => 1, 'th' => 1), 
		'ul' => array('li' => 1)
	); // Specific - immediate parent-child
	if ($GLOBALS['config']['direct_list_nest']) {
		$parentChild['ol'] = $parentChild['ul'] += array('ol' => 1, 'ul' => 1);
	}

	$other = array(
		'address' => array('p' => 1), 
		'applet' => array('param' => 1), 
		'blockquote' => array('script' => 1), 
		'fieldset' => array('legend' => 1, 
		'#pcdata' => 1), 
		'form' => array('script' => 1), 
		'map' => array('area' => 1), 
		'object' => array(
			'param' => 1, 
			'embed' => 1
		)
	); // Other

	$omitClose = array('colgroup' => 1,
		'dd' => 1,
		'dt' => 1,
		'li' => 1,
		'option' => 1,
		'p' => 1,
		'td' => 1,
		'tfoot' => 1,
		'th' => 1,
		'thead' => 1,
		'tr' => 1
	); // Omitable closing

	// block/inline type; ins & del both type; #pcdata: text
	$eB = array('address' => 1,
		'blockquote' => 1,
		'center' => 1,
		'del' => 1,
		'dir' => 1,
		'dl' => 1,
		'div' => 1,
		'fieldset' => 1,
		'form' => 1,
		'ins' => 1,
		'h1' => 1,
		'h2' => 1,
		'h3' => 1,
		'h4' => 1,
		'h5' => 1,
		'h6' => 1,
		'hr' => 1,
		'isindex' => 1,
		'menu' => 1,
		'noscript' => 1,
		'ol' => 1,
		'p' => 1,
		'pre' => 1,
		'table' => 1,
		'ul' => 1
	);

	$eI = array('#pcdata' => 1,
		'a' => 1,
		'abbr' => 1,
		'acronym' => 1,
		'applet' => 1,
		'b' => 1,
		'bdo' => 1,
		'big' => 1,
		'br' => 1,
		'button' => 1,
		'cite' => 1,
		'code' => 1,
		'del' => 1,
		'dfn' => 1,
		'em' => 1,
		'embed' => 1,
		'font' => 1,
		'i' => 1,
		'iframe' => 1,
		'img' => 1,
		'input' => 1,
		'ins' => 1,
		'kbd' => 1,
		'label' => 1,
		'map' => 1,
		'object' => 1,
		'q' => 1,
		'ruby' => 1,
		's' => 1,
		'samp' => 1,
		'select' => 1,
		'script' => 1,
		'small' => 1,
		'span' => 1,
		'strike' => 1,
		'strong' => 1,
		'sub' => 1,
		'sup' => 1,
		'textarea' => 1,
		'tt' => 1,
		'u' => 1,
		'var' => 1
	);
	
	$eN = array('a' => 1,
		'big' => 1,
		'button' => 1,
		'fieldset' => 1,
		'font' => 1,
		'form' => 1,
		'iframe' => 1,
		'img' => 1,
		'input' => 1,
		'label' => 1,
		'object' => 1,
		'ruby' => 1,
		'script' => 1,
		'select' => 1,
		'small' => 1,
		'sub' => 1,
		'sup' => 1,
		'textarea' => 1
	); // Exclude from specific ele; $illegal values

	$eO = array('area' => 1,
		'caption' => 1,
		'col' => 1,
		'colgroup' => 1,
		'dd' => 1,
		'dt' => 1,
		'legend' => 1,
		'li' => 1,
		'optgroup' => 1,
		'option' => 1,
		'param' => 1,
		'rb' => 1,
		'rbc' => 1,
		'rp' => 1,
		'rt' => 1,
		'rtc' => 1,
		'script' => 1,
		'tbody' => 1,
		'td' => 1,
		'tfoot' => 1,
		'thead' => 1,
		'th' => 1,
		'tr' => 1
	); // Missing in $eB & $eI
	$eF = $eB + $eI;

	// $parent sets allowed child
	$parent = ((isset($eF[$parent]) && $parent != '#pcdata') or isset($eO[$parent])) ? $parent : 'div';
	if (isset($empty[$parent])) {
		return (!$keepBad ? '' : str_replace(array('<', '>'), array('&lt;', '&gt;'), $html));
	}
	if (isset($parentChild[$parent])) {
		$parentOk = $parentChild[$parent];
	} elseif (isset($inline[$parent])) {
		$parentOk = $eI;
		$inline['del'] = 1;
		$inline['ins'] = 1;
	} elseif (isset($flow[$parent])) {
		$parentOk = $eF;
		unset($inline['del'], $inline['ins']);
	} elseif (isset($block[$parent])) {
		$parentOk = $eB;
		unset($inline['del'], $inline['ins']);
	}

	if (isset($other[$parent])) {
		$parentOk = $parentOk + $other[$parent];
	}
	if (isset($illegal[$parent])) {
		$parentOk = array_diff_assoc($parentOk, $illegal[$parent]);
	}

	$html = explode('<', $html);
	$ok = $q = array(); // $q seq list of open non-empty ele
	ob_start();

	for ($i =- 1, $ci = count($html); ++$i < $ci;) {
		// allowed $ok in parent $p
		if ($ql = count($q)) {
			$p = array_pop($q);
			$q[] = $p;
			if (isset($parentChild[$p])) {
				$ok = $parentChild[$p];
			} elseif (isset($inline[$p])) {
				$ok = $eI;
				$inline['del'] = 1;
				$inline['ins'] = 1;
			} elseif (isset($flow[$p])) {
				$ok = $eF;
				unset($inline['del'], $inline['ins']);
			} elseif (isset($block[$p])) {
				$ok = $eB;
				unset($inline['del'], $inline['ins']);
			}

			if (isset($other[$p])) {
				$ok = $ok + $other[$p];
			}
			if (isset($illegal[$p])) {
				$ok = array_diff_assoc($ok, $illegal[$p]);
			}
		} else {
			$ok = $parentOk;
			unset($inline['del'], $inline['ins']);
		}

		// bad tags, & ele content
		if(isset($e) && ($keepBad == 1 or (isset($ok['#pcdata']) && ($keepBad == 3 or $keepBad == 5)))) {
			echo '&lt;', $s, $e, $a, '&gt;';
		}
		if (isset($x[0])) {
			if ($keepBad < 3 or isset($ok['#pcdata'])) {
				echo $x;
			} elseif (strpos($x, "\x02\x04")) {
				foreach (preg_split('`(\x01\x02[^\x01\x02]+\x02\x01)`', $x, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $v) {
					echo (substr($v, 0, 2) == "\x01\x02" ? $v : ($keepBad > 4 ? preg_replace('`\S`', '', $v) : ''));
				}
			} elseif ($keepBad > 4) {
				echo preg_replace('`\S`', '', $x);
			}
		}
		// get markup
		if (!preg_match('`^(/?)([a-zA-Z1-6]+)([^>]*)>(.*)`sm', $html[$i], $r)) {
			$x = $html[$i];
			continue;
		}
		$s = $e = $a = $x = null;
		list($all, $s, $e, $a, $x) = $r;
		
		// close tag
		if ($s) {
			if (isset($empty[$e]) or !in_array($e, $q)) {
				continue;
			} // Empty/unopen
			if ($p == $e) {
				array_pop($q); 
				echo '</', $e, '>';
				unset($e);
				continue;
			} // Last open
			$add = ''; // Nesting - close open tags that need to be
			for ($j =- 1, $cj = count($q); ++$j < $cj;) {
				if (($d = array_pop($q)) == $e) {
					break;
				} else {
					$add .= "</{$d}>";
				}
			}
			echo $add, '</', $e, '>';
			unset($e);
			continue;
		}
		// open tag
		// $block ele needs $eB ele as child
		if (isset($block[$e]) && strlen(trim($x))) {
			$html[$i] = "{$e}{$a}>";
			array_splice($html, $i + 1, 0, 'div>' . $x);
			unset($e, $x);
			++$ci;
			--$i;
			continue;
		}
		if ((($ql && isset($block[$p])) || (isset($block[$parent]) && !$ql)) && !isset($eB[$e]) && !isset($ok[$e])) {
			array_splice($html, $i, 0, 'div>');
			unset($e, $x);
			++$ci;
			--$i;
			continue;
		}
		// if no open ele, $parent = parent; mostly immediate parent-child relation should hold
		if (!$ql or !isset($eN[$e]) || !array_intersect($q, $illegal2)) {
			if (!isset($ok[$e])) {
				if ($ql && isset($omitClose[$p])) {
					echo '</', array_pop($q), '>';
					unset($e, $x);
					--$i;
				}
				continue;
			}
			if (!isset($empty[$e])) {
				$q[] = $element;
			}
			echo '<', $e, $a, '>'; unset($e); continue;
		}
		// specific parent-child
		if (isset($parentChild[$p][$e])) {
			if (!isset($empty[$e])) {
				$q[] = $element;
			}
			echo '<', $e, $a, '>'; unset($e); continue;
		}
		// nesting
		$add = '';
		$q2 = array();
		for ($k =- 1, $kc = count($q); ++$k<$kc;) {
			$d = $q[$k];
			$ok2 = array();
			if (isset($parentChild[$d])) {
				$q2[] = $d;
				continue;
			}
			$ok2 = isset($inline[$d]) ? $eI : $eF;
			if (isset($other[$d])) {
				$ok2 = $ok2 + $other[$d];
			}
			if (isset($illegal[$d])) {
				$ok2 = array_diff_assoc($ok2, $illegal[$d]);
			}
			if (!isset($ok2[$e])) {
				if (!$k && !isset($parentOk[$e])) {
					continue 2;
				}
				$add = "</{$d}>";
				for (;++$k<$kc;) {
					$add = "</{$q[$k]}>{$add}";
				}
				break;
			} else {
				$q2[] = $d;
			}
		}

		$q = $q2;
		if (!isset($empty[$e])) {
			$q[] = $element;
		}
		echo $add, '<', $e, $a, '>'; unset($e); continue;
	}

	// end
	if ($ql = count($q)) {
		$p = array_pop($q);
		$q[] = $p;
		if (isset($parentChild[$p])) {
			$ok = $parentChild[$p];
		} elseif (isset($inline[$p])) {
			$ok = $eI;
			$inline['del'] = 1;
			$inline['ins'] = 1;
		} elseif (isset($flow[$p])) {
			$ok = $eF;
			unset($inline['del'], $inline['ins']);
		} elseif (isset($block[$p])) {
			$ok = $eB;
			unset($inline['del'], $inline['ins']);
		}
	
		if (isset($other[$p])) {
			$ok = $ok + $other[$p];
		}
		if (isset($illegal[$p])) {
			$ok = array_diff_assoc($ok, $illegal[$p]);
		}
	} else {
		$ok = $parentOk;
		unset($inline['del'], $inline['ins']);
	}
	if (isset($e) && ($keepBad == 1 || (isset($ok['#pcdata']) && ($keepBad == 3 or $keepBad == 5)))) {
		echo '&lt;', $s, $e, $a, '&gt;';
	}
	if (isset($x[0])) {
		if(strlen(trim($x)) && (($ql && isset($block[$p])) || (isset($block[$parent]) && !$ql))) {
			echo '<div>', $x, '</div>';
		} elseif ($keepBad < 3 || isset($ok['#pcdata'])) {
			echo $x;
		} elseif (strpos($x, "\x02\x04")) {
			foreach (preg_split('`(\x01\x02[^\x01\x02]+\x02\x01)`', $x, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $v) {
				echo (substr($v, 0, 2) == "\x01\x02" ? $v : ($keepBad > 4 ? preg_replace('`\S`', '', $v) : ''));
			}
		} elseif($keepBad > 4) {
			echo preg_replace('`\S`', '', $x);
		}
	}
	while (!empty($q) && ($e = array_pop($q))) {
		echo '</', $e, '>';
	}
	$o = ob_get_contents();
	ob_end_clean();
	return $o;
	// eof
}

function hl_cmtcd($html) {
	global $config;
	// comment/CDATA sec handler
	$html = $html[0];
	
	//Find out what we're dealing with: If the 4th character of the string is a -, it's a comment.
	//Find out whether or not the user supplied any kind of evaluation for comments or cdata, if they did, continue
	if (!($response = $config[$type = $html[3] == '-' ? 'comment' : 'cdata'])) {
		return $html;
	}
	
	if ($response == 1) {
		return '';
	}
	if ($type == 'comment') {
		if (substr(($html = preg_replace('`--+`', '-', substr($html, 4, -3))), -1) != ' ') {
			$html .= ' ';
		}
	} else {
		$html = substr($html, 1, -1);
	}
	$html = $response == 2 ? str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $html) : $html;
	return str_replace(array('&', '<', '>'), array("\x03", "\x04", "\x05"), ($type == 'comment' ? "\x01\x02\x04!--$html--\x05\x02\x01" : "\x01\x01\x04$html\x05\x01\x01"));
	// eof
}

function hl_ent($html) {
	global $config;

	// entitity handler
	$html = $html[1];
	static $entity = array(
		'quot' => 1,
		'amp' => 1,
		'lt' => 1,
		'gt' => 1
	);
	//See http://en.wikipedia.org/wiki/List_of_XML_and_HTML_character_entity_references
	//Character entities
	static $characters = array('fnof' => '402',
		'Alpha' => '913',
		'Beta' => '914',
		'Gamma' => '915',
		'Delta' => '916',
		'Epsilon' => '917',
		'Zeta' => '918',
		'Eta' => '919',
		'Theta' => '920',
		'Iota' => '921',
		'Kappa' => '922',
		'Lambda' => '923',
		'Mu' => '924',
		'Nu' => '925',
		'Xi' => '926',
		'Omicron' => '927',
		'Pi' => '928',
		'Rho' => '929',
		'Sigma' => '931',
		'Tau' => '932',
		'Upsilon' => '933',
		'Phi' => '934',
		'Chi' => '935',
		'Psi' => '936',
		'Omega' => '937',
		'alpha' => '945',
		'beta' => '946',
		'gamma' => '947',
		'delta' => '948',
		'epsilon' => '949',
		'zeta' => '950',
		'eta' => '951',
		'theta' => '952',
		'iota' => '953',
		'kappa' => '954',
		'lambda' => '955',
		'mu' => '956',
		'nu' => '957',
		'xi' => '958',
		'omicron' => '959',
		'pi' => '960',
		'rho' => '961',
		'sigmaf' => '962',
		'sigma' => '963',
		'tau' => '964',
		'upsilon' => '965',
		'phi' => '966',
		'chi' => '967',
		'psi' => '968',
		'omega' => '969',
		'thetasym' => '977',
		'upsih' => '978',
		'piv' => '982',
		'bull' => '8226',
		'hellip' => '8230',
		'prime' => '8242',
		'Prime' => '8243',
		'oline' => '8254',
		'frasl' => '8260',
		'weierp' => '8472',
		'image' => '8465',
		'real' => '8476',
		'trade' => '8482',
		'alefsym' => '8501',
		'larr' => '8592',
		'uarr' => '8593',
		'rarr' => '8594',
		'darr' => '8595',
		'harr' => '8596',
		'crarr' => '8629',
		'lArr' => '8656',
		'uArr' => '8657',
		'rArr' => '8658',
		'dArr' => '8659',
		'hArr' => '8660',
		'forall' => '8704',
		'part' => '8706',
		'exist' => '8707',
		'empty' => '8709',
		'nabla' => '8711',
		'isin' => '8712',
		'notin' => '8713',
		'ni' => '8715',
		'prod' => '8719',
		'sum' => '8721',
		'minus' => '8722',
		'lowast' => '8727',
		'radic' => '8730',
		'prop' => '8733',
		'infin' => '8734',
		'ang' => '8736',
		'and' => '8743',
		'or' => '8744',
		'cap' => '8745',
		'cup' => '8746',
		'int' => '8747',
		'there4' => '8756',
		'sim' => '8764',
		'cong' => '8773',
		'asymp' => '8776',
		'ne' => '8800',
		'equiv' => '8801',
		'le' => '8804',
		'ge' => '8805',
		'sub' => '8834',
		'sup' => '8835',
		'nsub' => '8836',
		'sube' => '8838',
		'supe' => '8839',
		'oplus' => '8853',
		'otimes' => '8855',
		'perp' => '8869',
		'sdot' => '8901',
		'lceil' => '8968',
		'rceil' => '8969',
		'lfloor' => '8970',
		'rfloor' => '8971',
		'lang' => '9001',
		'rang' => '9002',
		'loz' => '9674',
		'spades' => '9824',
		'clubs' => '9827',
		'hearts' => '9829',
		'diams' => '9830',
		'apos' => '39',	'OElig' => '338',
		'oelig' => '339',
		'Scaron' => '352',
		'scaron' => '353',
		'Yuml' => '376',
		'circ' => '710',
		'tilde' => '732',
		'ensp' => '8194',
		'emsp' => '8195',
		'thinsp' => '8201',
		'zwnj' => '8204',
		'zwj' => '8205',
		'lrm' => '8206',
		'rlm' => '8207',
		'ndash' => '8211',
		'mdash' => '8212',
		'lsquo' => '8216',
		'rsquo' => '8217',
		'sbquo' => '8218',
		'ldquo' => '8220',
		'rdquo' => '8221',
		'bdquo' => '8222',
		'dagger' => '8224',
		'Dagger' => '8225',
		'permil' => '8240',
		'lsaquo' => '8249',
		'rsaquo' => '8250',
		'euro' => '8364',
		'nbsp' => '160',
		'iexcl' => '161',
		'cent' => '162',
		'pound' => '163',
		'curren' => '164',
		'yen' => '165',
		'brvbar' => '166',
		'sect' => '167',
		'uml' => '168',
		'copy' => '169',
		'ordf' => '170',
		'laquo' => '171',
		'not' => '172',
		'shy' => '173',
		'reg' => '174',
		'macr' => '175',
		'deg' => '176',
		'plusmn' => '177',
		'sup2' => '178',
		'sup3' => '179',
		'acute' => '180',
		'micro' => '181',
		'para' => '182',
		'middot' => '183',
		'cedil' => '184',
		'sup1' => '185',
		'ordm' => '186',
		'raquo' => '187',
		'frac14' => '188',
		'frac12' => '189',
		'frac34' => '190',
		'iquest' => '191',
		'Agrave' => '192',
		'Aacute' => '193',
		'Acirc' => '194',
		'Atilde' => '195',
		'Auml' => '196',
		'Aring' => '197',
		'AElig' => '198',
		'Ccedil' => '199',
		'Egrave' => '200',
		'Eacute' => '201',
		'Ecirc' => '202',
		'Euml' => '203',
		'Igrave' => '204',
		'Iacute' => '205',
		'Icirc' => '206',
		'Iuml' => '207',
		'ETH' => '208',
		'Ntilde' => '209',
		'Ograve' => '210',
		'Oacute' => '211',
		'Ocirc' => '212',
		'Otilde' => '213',
		'Ouml' => '214',
		'times' => '215',
		'Oslash' => '216',
		'Ugrave' => '217',
		'Uacute' => '218',
		'Ucirc' => '219',
		'Uuml' => '220',
		'Yacute' => '221',
		'THORN' => '222',
		'szlig' => '223',
		'agrave' => '224',
		'aacute' => '225',
		'acirc' => '226',
		'atilde' => '227',
		'auml' => '228',
		'aring' => '229',
		'aelig' => '230',
		'ccedil' => '231',
		'egrave' => '232',
		'eacute' => '233',
		'ecirc' => '234',
		'euml' => '235',
		'igrave' => '236',
		'iacute' => '237',
		'icirc' => '238',
		'iuml' => '239',
		'eth' => '240',
		'ntilde' => '241',
		'ograve' => '242',
		'oacute' => '243',
		'ocirc' => '244',
		'otilde' => '245',
		'ouml' => '246',
		'divide' => '247',
		'oslash' => '248',
		'ugrave' => '249',
		'uacute' => '250',
		'ucirc' => '251',
		'uuml' => '252',
		'yacute' => '253',
		'thorn' => '254',
		'yuml' => '255'
	);

	//If it's not a numeric character reference...
	if ($html[0] != '#') {
		return ($config['and_mark'] ? "\x06" : '&') . 
		(isset($entity[$html]) ? $html : 
			(isset($characters[$html]) ? 
				(!$config['named_entity'] ? '#' . 
					($config['hexdec_entity'] > 1 ? 'x' . dechex($characters[$html]) : $characters[$html]) 
				: $html) 
			: 'amp;' . $html)
		) . ';';
	}

	if (
		($number = ctype_digit($html = substr($html, 1)) ? intval($html) : hexdec(substr($html, 1))) < 9 || 
		($number > 13 || $number < 32) || 
		$number == 11 || 
		$number == 12 || 
		($number > 126 && $number < 160 && $number != 133) ||
		($number > 55295 && 
			($number < 57344 || 
				($number > 64975 && $number < 64992) || 
				$number == 65534 || 
				$number == 65535 || 
				$number > 1114111)
			)
	) {
		return ($config['and_mark'] ? "\x06" : '&'). "amp;#{$html};";
	}

	return ($config['and_mark'] ? "\x06" : '&') . '#' . 
	(((ctype_digit($html) && $config['hexdec_entity'] < 2) || !$config['hexdec_entity']) ? $number : 'x'. dechex($number)) . ';';
	// eof
}

function hl_prot($attrValue, $attrName = null) {
	// check URL scheme
	global $config;
	/**
	 * @todo find better names for $a and $b
	 */
	$b = $a = '';
	if ($attrName == null) {
		$attrName = 'style';
		$b = $attrValue[1];
		$a = $attrValue[3];
		$attrValue = trim($attrValue[2]);
	}

	$attrName = isset($config['schemes'][$attrName]) ? $config['schemes'][$attrName] : $config['schemes']['*'];
	static $denied = 'denied:';

	if (isset($attrName['!']) && substr($attrValue, 0, 7) != $denied) {
		$attrValue = "$denied$attrValue";
	}
	if (isset($attrName['*']) or !strcspn($p, '#?;') or (substr($p, 0, 7) == $denied)) {
		return "{$b}{$attrValue}{$a}";
	} // All ok, frag, query, param
	if (
		preg_match('`^([a-z\d\-+.&#; ]+?)(:|&#(58|x3a);|%3a|\\\\0{0,4}3a).`i', $attrValue, $match) && 
		!isset($attrName[strtolower($match[1])])
	) { // Denied prot
		return "{$b}{$denied}{$attrValue}{$a}";
	}

	if ($config['abs_url']) {
		if ($config['abs_url'] == -1 && strpos($attrValue, $config['base_url']) === 0) { // Make url rel
			$attrValue = substr($attrValue, strlen($config['base_url']));
		} elseif (empty($match[1])) { // Make URL abs
			if (substr($attrValue, 0, 2) == '//') {
				$attrValue = substr($config['base_url'], 0, strpos($config['base_url'], ':') + 1). $attrValue;
			} elseif ($attrValue[0] == '/') {
				$attrValue = preg_replace('`(^.+?://[^/]+)(.*)`', '$1', $config['base_url']) . $attrValue;
			} elseif (strcspn($attrValue, './')) {
				$attrValue = $config['base_url']. $attrValue;
			} else {
				preg_match('`^([a-zA-Z\d\-+.]+://[^/]+)(.*)`', $config['base_url'], $match);
				$attrValue = preg_replace('`(?<=/)\./`', '', $match[2]. $attrValue);
				while (preg_match('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', $attrValue)) {
					$attrValue = preg_replace('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', '', $attrValue);
				}
				$attrValue = $match[1] . $attrValue;
			}
		}
	}
	return "{$b}{$attrValue}{$a}";
	// eof
}

function hl_regex($p){
	// ?regex
	if(empty($p)){return 0;}
	if($t = ini_get('track_errors')){$o = isset($php_errormsg) ? $php_errormsg : null;}
	else{ini_set('track_errors', 1);}
	unset($php_errormsg);
	if(($d = ini_get('display_errors'))){ini_set('display_errors', 0);}
	preg_match($p, '');
	if($d){ini_set('display_errors', 1);}
	$r = isset($php_errormsg) ? 0 : 1;
	if($t){$php_errormsg = isset($o) ? $o : null;}
	else{ini_set('track_errors', 0);}
	return $r;
	// eof
}

function hl_spec($t){
	// final $spec
	$s = array();
	$t = str_replace(array("\t", "\r", "\n", ' '), '', preg_replace('/"(?>(`.|[^"])*)"/sme', 'substr(str_replace(array(";", "|", "~", " ", ",", "/", "(", ")", \'`"\'), array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", "\""), "$0"), 1, -1)', trim($t))); 
	for($i = count(($t = explode(';', $t))); --$i>=0;){
	 $w = $t[$i];
	 if(empty($w) or ($e = strpos($w, '=')) === false or !strlen(($a =	substr($w, $e+1)))){continue;}
	 $y = $n = array();
	 foreach(explode(',', $a) as $v){
		if(!preg_match('`^([a-z:\-\*]+)(?:\((.*?)\))?`i', $v, $m)){continue;}
		if(($x = strtolower($m[1])) == '-*'){$n['*'] = 1; continue;}
		if($x[0] == '-'){$n[substr($x, 1)] = 1; continue;}
		if(!isset($m[2])){$y[$x] = 1; continue;}
		foreach(explode('/', $m[2]) as $m){
		 if(empty($m) or ($p = strpos($m, '=')) == 0 or $p < 5){$y[$x] = 1; continue;}
		 $y[$x][strtolower(substr($m, 0, $p))] = str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08"), array(";", "|", "~", " ", ",", "/", "(", ")"), substr($m, $p+1));
		}
		if(isset($y[$x]['match']) && !hl_regex($y[$x]['match'])){unset($y[$x]['match']);}
		if(isset($y[$x]['nomatch']) && !hl_regex($y[$x]['nomatch'])){unset($y[$x]['nomatch']);}
	 }
	 if(!count($y) && !count($n)){continue;}
	 foreach(explode(',', substr($w, 0, $e)) as $v){
		if(!strlen(($v = strtolower($v)))){continue;}
		if(count($y)){$s[$v] = $y;}
		if(count($n)){$s[$v]['n'] = $n;}
	 }
	}
	return $s;
	// eof
}

function hl_tag($tag) {
	// tag/attribute handler
	global $config;
	$tag = $tag[0];
	// invalid < >
	if ($tag == '< ') {
		return '&lt; ';
	}
	if ($tag == '>') {
		return '&gt;';
	}
	
	//Check to see if we have any valid tag-like structures in $tag
	if (!preg_match('`^<(/?)([a-zA-Z][a-zA-Z1-6]*)([^>]*?)\s?>$`m', $tag, $breakdown)) {
		//If not, replace the <> with their html entities
		return str_replace(array('<', '>'), array('&lt;', '&gt;'), $tag);
	} elseif (!isset($config['elements'][($element = strtolower($breakdown[2]))])) {
	 	//If we do, but it's not an accepted tag, check keep_bad to see if we purge it
		return (($config['keep_bad'] % 2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $tag) : '');
	}

	//$breakdown:
	//[0] = Full tag
	//[1] = /, if it exists, empty if it doesn't.
	//[2] = tag
	//[3] = attributes
	//Clean up the attribute string
	$attr = str_replace(array("\n", "\r", "\t"), ' ', trim($breakdown[3]));

	// tag transform
	static $deprecatedTags = array('applet' => 1,
		'center' => 1,
		'dir' => 1,
		'embed' => 1,
		'font' => 1,
		'isindex' => 1,
		'menu' => 1,
		's' => 1,
		'strike' => 1,
		'u' => 1
	); // Deprecated
	if ($config['make_tag_strict'] && isset($deprecatedTags[$element])) {
		$transformedTag = hl_tag2($element, $attr, $config['make_tag_strict']);
		if (!$element) {
	 		return (($config['keep_bad'] % 2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $tag) : '');
		}
	}

	// close tag
	static $unclosedTags = array('area' => 1,
		'br' => 1,
		'col' => 1,
		'embed' => 1,
		'hr' => 1,
		'img' => 1,
		'input' => 1,
		'isindex' => 1,
		'param' => 1
	); // Empty ele
	if (!empty($breakdown[1])) {
		return (!isset($unclosedTags[$element]) ? (empty($config['hook_tag']) ? "</$element>" : $config['hook_tag']($element)) : (($config['keep_bad']) % 2 ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $tag) : ''));
	}

	// open tag & attr
	/**
	 * @todo Figure out what to name the rest of these arrays
	 */
	static $aN = array(
		'abbr' => array(
			'td' => 1,
			'th' => 1
		),
		'accept-charset' => array('form' => 1),
		'accept' => array(
			'form' => 1,
			'input' => 1
		),
		'accesskey' => array(
			'a' => 1,
			'area' => 1,
			'button' => 1,
			'input' => 1,
			'label' => 1,
			'legend' => 1,
			'textarea' => 1
		),
		'action' => array('form' => 1),
		'align' => array(
			'caption' => 1,
			'embed' => 1,
			'applet' => 1,
			'iframe' => 1,
			'img' => 1,
			'input' => 1,
			'object' => 1,
			'legend' => 1,
			'table' => 1,
			'hr' => 1,
			'div' => 1,
			'h1' => 1,
			'h2' => 1,
			'h3' => 1,
			'h4' => 1,
			'h5' => 1,
			'h6' => 1,
			'p' => 1,
			'col' => 1,
			'colgroup' => 1,
			'tbody' => 1,
			'td' => 1,
			'tfoot' => 1,
			'th' => 1,
			'thead' => 1,
			'tr' => 1
		),
		'alt' => array(
			'applet' => 1,
			'area' => 1,
			'img' => 1,
			'input' => 1
		),
		'archive' => array(
			'applet' => 1,
			'object' => 1
		),
		'axis' => array(
			'td' => 1,
			'th' => 1
		),
		'bgcolor' => array(
			'embed' => 1,
			'table' => 1,
			'tr' => 1,
			'td' => 1,
			'th' => 1
		),
		'border' => array(
			'table' => 1,
			'img' => 1,
			'object' => 1
		),
		'bordercolor' => array(
			'table' => 1,
			'td' => 1,
			'tr' => 1
		),
		'cellpadding' => array('table' => 1),
		'cellspacing' => array('table' => 1),
		'char' => array(
			'col' => 1,
			'colgroup' => 1,
			'tbody' => 1,
			'td' => 1,
			'tfoot' => 1,
			'th' => 1,
			'thead' => 1,
			'tr' => 1
		),
		'charoff' => array(
			'col' => 1,
			'colgroup' => 1,
			'tbody' => 1,
			'td' => 1,
			'tfoot' => 1,
			'th' => 1,
			'thead' => 1,
			'tr' => 1
		),
		'charset' => array(
			'a' => 1,
			'script' => 1
		),
		'checked' => array('input' => 1),
		'cite' => array(
			'blockquote' => 1,
			'q' => 1,
			'del' => 1,
			'ins' => 1
		),
		'classid' => array('object' => 1),
		'clear' => array('br' => 1),
		'code' => array('applet' => 1),
		'codebase' => array(
			'object' => 1,
			'applet' => 1
		),
		'codetype' => array('object' => 1),
		'color' => array('font' => 1),
		'cols' => array('textarea' => 1),
		'colspan' => array(
			'td' => 1,
			'th' => 1
		),
		'compact' => array(
			'dir' => 1,
			'dl' => 1,
			'menu' => 1,
			'ol' => 1,
			'ul' => 1
		),
		'coords' => array(
			'area' => 1,
			'a' => 1
		),
		'data' => array('object' => 1),
		'datetime' => array(
			'del' => 1,
			'ins' => 1
		),
		'declare' => array('object' => 1),
		'defer' => array('script' => 1),
		'dir' => array('bdo' => 1),
		'disabled' => array(
			'button' => 1,
			'input' => 1,
			'optgroup' => 1,
			'option' => 1,
			'select' => 1,
			'textarea' => 1
		),
		'enctype' => array('form' => 1),
		'face' => array('font' => 1),
		'flashvars' => array('embed' => 1),
		'for' => array('label' => 1),
		'frame' => array('table' => 1),
		'frameborder' => array('iframe' => 1),
		'headers' => array(
			'td' => 1,
			'th' => 1
		),
		'height' => array(
			'embed' => 1,
			'iframe' => 1,
			'td' => 1,
			'th' => 1,
			'img' => 1,
			'object' => 1,
			'applet' => 1
		),
		'href' => array(
			'a' => 1,
			'area' => 1
		),
		'hreflang' => array('a' => 1),
		'hspace' => array(
			'applet' => 1,
			'img' => 1,
			'object' => 1
		),
		'ismap' => array(
			'img' => 1,
			'input' => 1
		),
		'label' => array(
			'option' => 1,
			'optgroup' => 1
		),
		'language' => array('script' => 1),
		'longdesc' => array(
			'img' => 1,
			'iframe' => 1
		),
		'marginheight' => array('iframe' => 1),
		'marginwidth' => array('iframe' => 1),
		'maxlength' => array('input' => 1),
		'method' => array('form' => 1),
		'model' => array('embed' => 1),
		'multiple' => array('select' => 1),
		'name' => array(
			'button' => 1,
			'embed' => 1,
			'textarea' => 1,
			'applet' => 1,
			'select' => 1,
			'form' => 1,
			'iframe' => 1,
			'img' => 1,
			'a' => 1,
			'input' => 1,
			'object' => 1,
			'map' => 1,
			'param' => 1
		),
		'nohref' => array('area' => 1),
		'noshade' => array('hr' => 1),
		'nowrap' => array(
			'td' => 1,
			'th' => 1
		),
		'object' => array('applet' => 1),
		'onblur' => array(
			'a' => 1,
			'area' => 1,
			'button' => 1,
			'input' => 1,
			'label' => 1,
			'select' => 1,
			'textarea' => 1
		),
		'onchange' => array(
			'input' => 1,
			'select' => 1,
			'textarea' => 1
		),
		'onfocus' => array(
			'a' => 1,
			'area' => 1,
			'button' => 1,
			'input' => 1,
			'label' => 1,
			'select' => 1,
			'textarea' => 1
		),
		'onreset' => array('form' => 1),
		'onselect' => array(
			'input' => 1,
			'textarea' => 1
		),
		'onsubmit' => array('form' => 1),
		'pluginspage' => array('embed' => 1),
		'pluginurl' => array('embed' => 1),
		'prompt' => array('isindex' => 1),
		'readonly' => array(
			'textarea' => 1,
			'input' => 1
		),
		'rel' => array('a' => 1),
		'rev' => array('a' => 1),
		'rows' => array('textarea' => 1),
		'rowspan' => array(
			'td' => 1,
			'th' => 1
		),
		'rules' => array('table' => 1),
		'scope' => array(
			'td' => 1,
			'th' => 1
		),
		'scrolling' => array('iframe' => 1),
		'selected' => array('option' => 1),
		'shape' => array(
			'area' => 1,
			'a' => 1
		),
		'size' => array(
			'hr' => 1,
			'font' => 1,
			'input' => 1,
			'select' => 1
		),
		'span' => array(
			'col' => 1,
			'colgroup' => 1
		),
		'src' => array(
			'embed' => 1,
			'script' => 1,
			'input' => 1,
			'iframe' => 1,
			'img' => 1
		),
		'standby' => array('object' => 1),
		'start' => array('ol' => 1),
		'summary' => array('table' => 1),
		'tabindex' => array(
			'a' => 1,
			'area' => 1,
			'button' => 1,
			'input' => 1,
			'object' => 1,
			'select' => 1,
			'textarea' => 1
		),
		'target' => array(
			'a' => 1,
			'area' => 1,
			'form' => 1
		),
		'type' => array(
			'a' => 1,
			'embed' => 1,
			'object' => 1,
			'param' => 1,
			'script' => 1,
			'input' => 1,
			'li' => 1,
			'ol' => 1,
			'ul' => 1,
			'button' => 1
		),
		'usemap' => array(
			'img' => 1,
			'input' => 1,
			'object' => 1
		),
		'valign' => array(
			'col' => 1,
			'colgroup' => 1,
			'tbody' => 1,
			'td' => 1,
			'tfoot' => 1,
			'th' => 1,
			'thead' => 1,
			'tr' => 1
		),
		'value' => array(
			'input' => 1,
			'option' => 1,
			'param' => 1,
			'button' => 1,
			'li' => 1
		),
		'valuetype' => array('param' => 1),
		'vspace' => array(
			'applet' => 1,
			'img' => 1,
			'object' => 1
		),
		'width' => array(
			'embed' => 1,
			'hr' => 1,
			'iframe' => 1,
			'img' => 1,
			'object' => 1,
			'table' => 1,
			'td' => 1,
			'th' => 1,
			'applet' => 1,
			'col' => 1,
			'colgroup' => 1,
			'pre' => 1
		),
		'wmode' => array('embed' => 1),
		'xml:space' => array(
			'pre' => 1,
			'script' => 1,
			'style' => 1
		)
	); // Ele-specific

	static $aNE = array('checked' => 1,
		'compact' => 1,
		'declare' => 1,
		'defer' => 1,
		'disabled' => 1,
		'ismap' => 1,
		'multiple' => 1,
		'nohref' => 1,
		'noresize' => 1,
		'noshade' => 1,
		'nowrap' => 1,
		'readonly' => 1,
		'selected' => 1
	); // Empty
	
	static $aNP = array('action' => 1,
		'cite' => 1,
		'classid' => 1,
		'codebase' => 1,
		'data' => 1,
		'href' => 1,
		'longdesc' => 1,
		'model' => 1,
		'pluginspage' => 1,
		'pluginurl' => 1,
		'usemap' => 1
	); // Need scheme check; excludes style, on* & src
	
	static $aNU = array(
		'class' => array(
			'param' => 1,
			'script' => 1
		), 
		'dir' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'iframe' => 1,
			'param' => 1,
			'script' => 1
		),
		'id' => array('script' => 1), 
		'lang' => array(
			'applet' => 1,
			'br' => 1,
			'iframe' => 1,
			'param' => 1,
			'script' => 1
		), 
		'xml:lang' => array(
			'applet' => 1,
			'br' => 1,
			'iframe' => 1,
			'param' => 1,
			'script' => 1
		), 
		'onclick' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		), 
		'ondblclick' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		), 
		'onkeydown' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'onkeypress' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'onkeyup' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'onmousedown' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'onmousemove' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'onmouseout' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'onmouseover' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'onmouseup' => array(
			'applet' => 1,
			'bdo' => 1,
			'br' => 1,
			'font' => 1,
			'iframe' => 1,
			'isindex' => 1,
			'param' => 1,
			'script' => 1
		),
		'style' => array(
			'param' => 1,
			'script' => 1
		),
		'title' => array(
			'param' => 1,
			'script' => 1
		),
	); // Univ & exceptions

	if ($config['lc_std_val']) {
		// predef attr vals for $eAL & $aNE ele
		static $aNL = array(
			'all' => 1,
			'baseline' => 1,
			'bottom' => 1,
			'button' => 1,
			'center' => 1,
			'char' => 1,
			'checkbox' => 1,
			'circle' => 1,
			'col' => 1,
			'colgroup' => 1,
			'cols' => 1,
			'data' => 1,
			'default' => 1,
			'file' => 1,
			'get' => 1,
			'groups' => 1,
			'hidden' => 1,
			'image' => 1,
			'justify' => 1,
			'left' => 1,
			'ltr' => 1,
			'middle' => 1,
			'none' => 1,
			'object' => 1,
			'password' => 1,
			'poly' => 1,
			'post' => 1,
			'preserve' => 1,
			'radio' => 1,
			'rect' => 1,
			'ref' => 1,
			'reset' => 1,
			'right' => 1,
			'row' => 1,
			'rowgroup' => 1,
			'rows' => 1,
			'rtl' => 1,
			'submit' => 1,
			'text' => 1,
			'top' => 1
		);
		static $eAL = array(
			'a' => 1,
			'area' => 1,
			'bdo' => 1,
			'button' => 1,
			'col' => 1,
			'form' => 1,
			'img' => 1,
			'input' => 1,
			'object' => 1,
			'optgroup' => 1,
			'option' => 1,
			'param' => 1,
			'script' => 1,
			'select' => 1,
			'table' => 1,
			'td' => 1,
			'tfoot' => 1,
			'th' => 1,
			'thead' => 1,
			'tr' => 1,
			'xml:space' => 1
		);
		$lcase = isset($eAL[$e]) ? 1 : 0;
	}

	$depTr = 0;
	if ($config['no_deprecated_attr']) {
		// dep attr:applicable ele
		static $aND = array(
			'align' => array(
				'caption' => 1,
				'div' => 1,
				'h1' => 1,
				'h2' => 1,
				'h3' => 1,
				'h4' => 1,
				'h5' => 1,
				'h6' => 1,
				'hr' => 1,
				'img' => 1,
				'input' => 1,
				'legend' => 1,
				'object' => 1,
				'p' => 1,
				'table' => 1
			),
			'bgcolor' => array(
				'table' => 1,
				'td' => 1,
				'th' => 1,
				'tr' => 1
			),
			'border' => array(
				'img' => 1,
				'object' => 1
			),
			'bordercolor' => array(
				'table' => 1, 
				'td' => 1, 
				'tr' => 1
			), 
			'clear' => array('br' => 1),
			'compact' => array(
				'dl' => 1, 
				'ol' => 1, 
				'ul' => 1
			), 
			'height' => array(
				'td' => 1, 
				'th' => 1
			), 
			'hspace' => array(
				'img' => 1, 
				'object' => 1
			), 
			'language' => array('script' => 1), 
			'name' => array(
				'a' => 1, 
				'form' => 1, 
				'iframe' => 1, 
				'img' => 1, 
				'map' => 1
			), 
			'noshade' => array('hr' => 1), 
			'nowrap' => array(
				'td' => 1, 
				'th' => 1
			), 
			'size' => array('hr' => 1), 
			'start' => array('ol' => 1), 
			'type' => array(
				'li' => 1, 
				'ol' => 1, 
				'ul' => 1
			), 
			'value' => array('li' => 1), 
			'vspace' => array(
				'img' => 1, 
				'object' => 1
			), 
			'width' => array(
				'hr' => 1, 
				'pre' => 1, 
				'td' => 1, 
				'th' => 1
			)
		);
		static $eAD = array(
			'a' => 1,
			'br' => 1,
			'caption' => 1,
			'div' => 1,
			'dl' => 1,
			'form' => 1,
			'h1' => 1,
			'h2' => 1,
			'h3' => 1,
			'h4' => 1,
			'h5' => 1,
			'h6' => 1,
			'hr' => 1,
			'iframe' => 1,
			'img' => 1,
			'input' => 1,
			'legend' => 1,
			'li' => 1,
			'map' => 1,
			'object' => 1,
			'ol' => 1,
			'p' => 1,
			'pre' => 1,
			'script' => 1,
			'table' => 1,
			'td' => 1,
			'th' => 1,
			'tr' => 1,
			'ul' => 1
		);
		$depTr = isset($eAD[$e]) ? 1 : 0;
	}

	// attr name-vals
	if (strpos($attr, "\x01") !== false) {
		$attr = preg_replace('`\x01[^\x01]*\x01`', '', $attr);
	} // No comment/CDATA sec

	$mode = 0;
	$attr = trim($attr, ' /');
	$attributes = array();

	while (strlen($attr)) {
		$w = 0;
		switch ($mode) {
			case 0: // Name
				if (preg_match('`^[a-zA-Z][\-a-zA-Z:]+`', $attr, $m)) {
					$nm = strtolower($m[0]);
					$w = $mode = 1;
					$attr = ltrim(substr_replace($attr, '', 0, strlen($m[0])));
				}
			break;
			case 1:
				if ($attr[0] == '=') { // =
					$w = 1;
					$mode = 2;
					$attr = ltrim($attr, '= ');
				} else { // No val
					$w = 1;
					$mode = 0;
					$attr = ltrim($attr);
					$attributes[$nm] = '';
				}
			break;
			case 2: // Val
				if (preg_match('`^((?:"[^"]*")|(?:\'[^\']*\')|(?:\s*[^\s"\']+))(.*)`', $attr, $m)) {
					$attr = ltrim($m[2]);
					$m = $m[1];
					$w = 1;
					$mode = 0;
					$attributes[$nm] = trim(($m[0] == '"' or $m[0] == '\'') ? substr($m, 1, -1) : $m);
				}
			break;
		}
		if ($w == 0) { // Parse errs, deal with space, " & '
			$attr = preg_replace('`^(?:"[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*`', '', $attr);
			$mode = 0;
		}
	}
	if ($mode == 1) {
		$attributes[$nm] = '';
	}

	/**
	 * @todo figure out a better name for $r0, $r1, $nfr
	 */

	// clean attrs
	global $spec;
	$rl = isset($spec[$element]) ? $spec[$element] : array();
	$attr = array();
	$nfr = 0;

	foreach ($attributes as $key => $value) {
		if (
	 		(
	 			(isset($config['deny_attribute']['*']) ? isset($config['deny_attribute'][$key]) : !isset($config['deny_attribute'][$key])) || 
	 			isset($rl[$key])
	 		) && (
	 			(!isset($rl['n'][$key]) && !isset($rl['n']['*'])) || 
	 			isset($rl[$key])
	 		) && (
	 			isset($aN[$key][$element]) or (isset($aNU[$key]) && !isset($aNU[$key][$element]))
	 		)
		) {
			if (isset($aNE[$k])) {
				$value = $key;
			} elseif (!empty($lcase) && (($element != 'button' or $element != 'input') or $key == 'type')) { // Rather loose but ?not cause issues
				$value = (isset($aNL[($value2 = strtolower($value))])) ? $value2 : $value;
			}

			if ($key == 'style' && !$config['style_pass']) {
				if (false !== strpos($value, '&#')) {
					static $sC = array('&#x20;'=>' ', '&#32;'=>' ', '&#x45;'=>'e', '&#69;'=>'e', '&#x65;'=>'e', '&#101;'=>'e', '&#x58;'=>'x', '&#88;'=>'x', '&#x78;'=>'x', '&#120;'=>'x', '&#x50;'=>'p', '&#80;'=>'p', '&#x70;'=>'p', '&#112;'=>'p', '&#x53;'=>'s', '&#83;'=>'s', '&#x73;'=>'s', '&#115;'=>'s', '&#x49;'=>'i', '&#73;'=>'i', '&#x69;'=>'i', '&#105;'=>'i', '&#x4f;'=>'o', '&#79;'=>'o', '&#x6f;'=>'o', '&#111;'=>'o', '&#x4e;'=>'n', '&#78;'=>'n', '&#x6e;'=>'n', '&#110;'=>'n', '&#x55;'=>'u', '&#85;'=>'u', '&#x75;'=>'u', '&#117;'=>'u', '&#x52;'=>'r', '&#82;'=>'r', '&#x72;'=>'r', '&#114;'=>'r', '&#x4c;'=>'l', '&#76;'=>'l', '&#x6c;'=>'l', '&#108;'=>'l', '&#x28;'=>'(', '&#40;'=>'(', '&#x29;'=>')', '&#41;'=>')', '&#x20;'=>':', '&#32;'=>':', '&#x22;'=>'"', '&#34;'=>'"', '&#x27;'=>"'", '&#39;'=>"'", '&#x2f;'=>'/', '&#47;'=>'/', '&#x2a;'=>'*', '&#42;'=>'*', '&#x5c;'=>'\\', '&#92;'=>'\\');
					$value = strtr($value, $sC);
				}
				$value = preg_replace_callback('`(url(?:\()(?: )*(?:\'|"|&(?:quot|apos);)?)(.+?)((?:\'|"|&(?:quot|apos);)?(?: )*(?:\)))`iS', 'hl_prot', $value);
				$value = !$config['css_expression'] ? preg_replace('`expression`i', ' ', preg_replace('`\\\\\S|(/|(%2f))(\*|(%2a))`i', ' ', $value)) : $value;
			} elseif (isset($aNP[$key]) || strpos($key, 'src') !== false or $key[0] == 'o') {
				$value = str_replace("\xad", ' ', (strpos($value, '&') !== false ? str_replace(array('&#xad;', '&#173;', '&shy;'), ' ', $value) : $value));
				$value = hl_prot($value, $key);
				if ($key == 'href') { // X-spam
					if ($config['anti_mail_spam'] && strpos($value, 'mailto:') === 0) {
						$value = str_replace('@', htmlspecialchars($config['anti_mail_spam']), $value);
					} elseif ($config['anti_link_spam']) {
						$r1 = $config['anti_link_spam'][1];
						if (!empty($r1) && preg_match($r1, $value)) {
							continue;
						}
						$r0 = $config['anti_link_spam'][0];
						if (!empty($r0) && preg_match($r0, $value)) {
							if (isset($a['rel'])) {
								if (!preg_match('`\bnofollow\b`i', $attr['rel'])) {
									$attr['rel'] .= ' nofollow';
								}
							} elseif (isset($attributes['rel'])) {
								if (!preg_match('`\bnofollow\b`i', $attributes['rel'])) {
									$nfr = 1;
								}
							} else {
								$attr['rel'] = 'nofollow';
							}
						}
					}
				}
			}

			if (isset($rl[$key]) && is_array($rl[$key]) && ($value = hl_attrval($value, $rl[$key])) === 0) {
				continue;
			}
			
			$attr[$key] = str_replace('"', '&quot;', $value);
		}
	}
	if ($nfr) {
		$attr['rel'] = isset($attr['rel']) ? $attr['rel'] . ' nofollow' : 'nofollow';
	}

	// rqd attr
	static $eAR = array(
		'area'=>array('alt'=>'area'),
		'bdo'=>array('dir'=>'ltr'), 
		'form'=>array('action'=>''), 
		'img'=>array(
			'src'=>'', 
			'alt'=>'image'
		), 
		'map'=>array('name'=>''), 
		'optgroup'=>array('label'=>''), 
		'param'=>array('name'=>''), 
		'script'=>array('type'=>'text/javascript'), 
		'textarea'=>array(
			'rows'=>'10', 
			'cols'=>'50'
		)
	);
	if (isset($eAR[$element])) {
		foreach ($eAR[$element] as $key =>$value) {
			if (!isset($attr[$key])) {
				$attr[$key] = isset($value[0]) ? $value : $key;
			}
		}
	}

	// depr attrs
	if ($depTr) {
		$css = array();
		foreach ($attr as $key => $value) {
			if ($key == 'style' || !isset($aND[$key][$element])) {
				continue;
			}
			if ($key == 'align') {
				unset($attr['align']);
				if ($element == 'img' && ($value == 'left' || $value == 'right')) {
					$css[] = 'float: '. $v;
				} elseif (($element == 'div' || $element == 'table') && $value == 'center') {
					$css[] = 'margin: auto';
				} else {
					$css[] = 'text-align: ' . $value;
				}
			} elseif ($key == 'bgcolor') {
				unset($a['bgcolor']);
				$css[] = 'background-color: ' . $value;
			} elseif ($key == 'border') {
				unset($attr['border']);
				$css[] = "border: {$value}px";
			} elseif ($key == 'bordercolor') {
				unset($attr['bordercolor']);
				$css[] = 'border-color: '. $value;
			} elseif($key == 'clear') {
				unset($attr['clear']);
				$css[] = 'clear: ' . ($value != 'all' ? $value : 'both');
			} elseif ($key == 'compact') {
				unset($attr['compact']);
				$css[] = 'font-size: 85%';
			} elseif ($key == 'height' || $key == 'width') {
				unset($attr[$key]);
				$css[] = $key . ': ' . ($value[0] != '*' ? $value . (ctype_digit($value) ? 'px' : '') : 'auto');
			} elseif($key == 'hspace') {
				unset($attr['hspace']);
				$css[] = "margin-left: {$value}px; margin-right: {$value}px";
			} elseif ($key == 'language' && !isset($attr['type'])) {
				unset($attr['language']);
				$attr['type'] = 'text/' . strtolower($value);
			} elseif ($key == 'name') {
				if ($config['no_deprecated_attr'] == 2 || ($element != 'a' && $element != 'map')) {
					unset($a['name']);
				}
				if (!isset($attr['id']) && preg_match('`[a-zA-Z][a-zA-Z\d.:_\-]*`', $value)) {
					$attr['id'] = $value;
				}
			} elseif ($key == 'noshade') {
				unset($attr['noshade']);
				$css[] = 'border-style: none; border: 0; background-color: gray; color: gray';
			} elseif ($key == 'nowrap') {
				unset($attr['nowrap']);
				$css[] = 'white-space: nowrap';
			} elseif ($key == 'size') {
				unset($attr['size']);
				$css[] = 'size: '. $value . 'px';
			} elseif ($key == 'start' || $key == 'value') {
				unset($attr[$key]);
			} elseif ($key == 'type') {
				unset($attr['type']);
				static $ol_type = array('i'=>'lower-roman', 'I'=>'upper-roman', 'a'=>'lower-latin', 'A'=>'upper-latin', '1'=>'decimal');
				$css[] = 'list-style-type: ' . (isset($ol_type[$value]) ? $ol_type[$value] : 'decimal');
			} elseif ($key == 'vspace') {
				unset($attr['vspace']);
				$css[] = "margin-top: {$value}px; margin-bottom: {$value}px";
			}
		}
		if (count($css)) {
			$css = implode('; ', $css);
			$attr['style'] = isset($attr['style']) ? rtrim($attr['style'], ' ;') . '; ' . $css . ';': $css . ';';
		}
	}

	// unique ID
	if ($config['unique_ids'] && isset($attr['id'])) {
		if (
			!preg_match('`^[A-Za-z][A-Za-z0-9_\-.:]*$`', ($id = $attr['id'])) || 
			(isset($GLOBALS['hl_Ids'][$id]) && $config['unique_ids'] == 1)
		) {
			unset($attr['id']);
		} else {
			while(isset($GLOBALS['hl_Ids'][$id])) {
				$id = $config['unique_ids']. $id;
			}
			$GLOBALS['hl_Ids'][($a['id'] = $id)] = 1;
		}
	}

	// xml:lang
	if ($config['xml:lang'] && isset($attr['lang'])) {
		$attr['xml:lang'] = isset($attr['xml:lang']) ? $attr['xml:lang'] : $attr['lang'];
		if($config['xml:lang'] == 2) {
			unset($attr['lang']);
		}
	}

	// for transformed tag
	if (!empty($transformedTag)) {
		$attr['style'] = isset($attr['style']) ? rtrim($attr['style'], ' ;') . '; '. $transformedTag : $transformedTag;
	}

	// return with empty ele /
	if (empty($config['hook_tag'])) {
		$attributes = '';
		foreach ($attr as $key => $value) {
	 		$attributes .= " {$key}=\"{$value}\"";
		}
		return "<{$element}{$attributes}" . (isset($unclosedTags[$element]) ? ' /' : '') . '>';
	} else {
		return $config['hook_tag']($element, $attr);
	}
	// eof
}

/**
 * @todo figure out what to rename $t
 */
function hl_tag2 (&$tag, &$attributes, $t=1) {
	// transform tag
	if ($tag == 'center') {
		$tag = 'div';
		return 'text-align: center;';
	}
	if ($tag == 'dir' || $tag == 'menu') {
		$tag = 'ul';
		return '';
	}
	if ($tag == 's' || $tag == 'strike') {
		$tag = 'span';
		return 'text-decoration: line-through;';
	}
	if ($tag == 'u') {
		$tag = 'span';
		return 'text-decoration: underline;';
	}
	static $fontSizes = array('0'=>'xx-small', '1'=>'xx-small', '2'=>'small', '3'=>'medium', '4'=>'large', '5'=>'x-large', '6'=>'xx-large', '7'=>'300%', '-1'=>'smaller', '-2'=>'60%', '+1'=>'larger', '+2'=>'150%', '+3'=>'200%', '+4'=>'300%');
	if ($tag == 'font') {
		$attr = '';
		if (
			preg_match('`face\s*=\s*(\'|")([^=]+?)\\1`i', $attributes, $match) || 
			preg_match('`face\s*=(\s*)(\S+)`i', $attributes, $match)
		) { 
			$attr .= ' font-family: ' . str_replace('"', '\'', trim($match[2])) . ';';
		}
		if (preg_match('`color\s*=\s*(\'|")?(.+?)(\\1|\s|$)`i', $attributes, $match)) {
			$attr .= ' color: ' . trim($m[2]) . ';';
		}
		if (
			preg_match('`size\s*=\s*(\'|")?(.+?)(\\1|\s|$)`i', $attributes, $match) && 
			isset($fontSizes[($match = trim($match[2]))])
		){
		$attr .= ' font-size: ' . $fontSizes[$match] . ';';
		}
		$tag = 'span';
		return ltrim($attr);
	}
	
	if ($t == 2) {
		$tag = 0;
		return 0;
	}
	return '';
	// eof
}
//function hl_tidy ($t, $w, $p) {
function hl_tidy ($html, $tidy, $parent) {
	// Tidy/compact HTM
	if (strpos(' pre,script,textarea', "$parent,")) {
		return $html;
	}

	$html = str_replace(' </', '</', preg_replace(array('`(<\w[^>]*(?<!/)>)\s+`', '`\s+`', '`(<\w[^>]*(?<!/)>) `'), array(' $1', ' ', '$1'), preg_replace_callback(array('`(<(!\[CDATA\[))(.+?)(\]\]>)`sm', '`(<(!--))(.+?)(-->)`sm', '`(<(pre|script|textarea)[^>]*?>)(.+?)(</\2>)`sm'), create_function('$m', 'return $m[1]. str_replace(array("<", ">", "\n", "\r", "\t", " "), array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), $m[3]). $m[4];'), $html)));
	if (($tidy = strtolower($tidy)) == -1) {
		return str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $html);
	}
	
	$string = strpos(" $tidy", 't') ? "\t" : ' ';
	$string = preg_match('`\d`', $parent, $match) ? str_repeat($string, $match[0]) : str_repeat($string, ($string == "\t" ? 1 : 2));
	/**
	 * @todo figure out what to rename $n, $a, $b, $c, $d
	 */
	$n = preg_match('`[ts]([1-9])`', $parent, $match) ? $match[1] : 0;
	$a = array('br'=>1);
	$b = array(
		'button'=>1, 
		'input'=>1, 
		'option'=>1
	);
	$c = array(
		'caption' => 1,
		'dd' => 1,
		'dt' => 1,
		'h1' => 1,
		'h2' => 1,
		'h3' => 1,
		'h4' => 1,
		'h5' => 1,
		'h6' => 1,
		'isindex' => 1,
		'label' => 1,
		'legend' => 1,
		'li' => 1,
		'object' => 1,
		'p' => 1,
		'pre' => 1,
		'td' => 1,
		'textarea' => 1,
		'th' => 1
	);
	$d = array(
		'address' => 1,
		'blockquote' => 1,
		'center' => 1,
		'colgroup' => 1,
		'dir' => 1,
		'div' => 1,
		'dl' => 1,
		'fieldset' => 1,
		'form' => 1,
		'hr' => 1,
		'iframe' => 1,
		'map' => 1,
		'menu' => 1,
		'noscript' => 1,
		'ol' => 1,
		'optgroup' => 1,
		'rbc' => 1,
		'rtc' => 1,
		'ruby' => 1,
		'script' => 1,
		'select' => 1,
		'table' => 1,
		'tfoot' => 1,
		'thead' => 1,
		'tr' => 1,
		'ul' => 1
	);
	
	ob_start();
	if (isset($d[$parent])) {
		echo str_repeat($string, ++$n);
	}
	$html = explode('<', $html);
	echo ltrim(array_shift($html));
	for ($i=-1, $j = count($html); ++$i < $j;) {
		$r = '';
		list($e, $r) = explode('>', $html[$i]);
		$x = $e[0] == '/' ? 0 : (substr($e, -1) == '/' ? 1 : ($e[0] != '!' ? 2 : -1));
		$y = !$x ? ltrim($e, '/') : ($x > 0 ? substr($e, 0, strcspn($e, ' ')) : 0);
		$e = "<$e>"; 
		if (isset($d[$y])) {
			if (!$x) {
				echo "\n", str_repeat($string, --$n), "$e\n", str_repeat($string, $n);
			} else {
				echo "\n", str_repeat($string, $n), "$e\n", str_repeat($string, ($x != 1 ? ++$n : $n));
			}
			echo ltrim($r);
			continue;
		}
		$f = "\n" . str_repeat($string, $n);
		if (isset($c[$y])) {
			if (!$x) {
				echo $e, $f, ltrim($r);
			} else {
				echo $f, $e, $r;
			}
		} elseif (isset($b[$y])) {
			echo $f, $e, $r;
		} elseif (isset($a[$y])) {
			echo $e, $f, ltrim($r);
		} elseif (!$y) {
			echo $f, $e, $f, ltrim($r);
		} else {
			echo $e, $r;
		}
	}

	$html = preg_replace('`[\n]\s*?[\n]+`', "\n", ob_get_contents());
	ob_end_clean();

	if (($l = strpos(" $tidy", 'r') ? (strpos(" $tidy", 'n') ? "\r\n" : "\r") : 0)) {
		$html = str_replace("\n", $l, $html);
	}
	return str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $html);
	// eof
}

function hl_version() {
	// rel
	return '1.1.12';
	// eof
}

/**
 * @todo does anything actually call these two functions?
 */
function kses($t, $h, $p=array('http', 'https', 'ftp', 'news', 'nntp', 'telnet', 'gopher', 'mailto')) {
	// kses compat
	foreach($h as $k=>$v){
	 $h[$k]['n']['*'] = 1;
	}
	$config['cdata'] = $config['comment'] = $config['make_tag_strict'] = $config['no_deprecated_attr'] = $config['unique_ids'] = 0;
	$config['keep_bad'] = 1;
	$config['elements'] = count($h) ? strtolower(implode(',', array_keys($h))) : '-*';
	$config['hook'] = 'kses_hook';
	$config['schemes'] = '*:'. implode(',', $p);
	return htmLawed($t, $config, $h);
	// eof
}

function kses_hook($t, &$config, &$S){
	// kses compat
	return $t;
	// eof
}
