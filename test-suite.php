<?php
include 'htmLawed.class.php';
include 'original-src/htmLawed.php';
$samplePath = dirname(__FILE__).'/samples/';
$sampleFiles = scandir($samplePath);
$results = array();
$config = array(
	//'and_mark' => 1,
	'cdata' => 1,
	'clean_ms_char' => 2,
	'comment' => 1,
	'deny_attribute' => 'style,class',
	'keep_bad' => 6,
	'make_tag_strict' => 1,
	'tidy' => 1,
	'elements' => '* -script -style -o -xml -span -form -input -select -textarea -button -img',
);
$spec = 'a=id(match="/[a-z][a-z\d.:\-`"]*/i"/minval=2), href(maxlen=100/minlen=34); img=-width,-alt';
foreach ($sampleFiles AS $file) {
	if (strlen($file) < 3) continue;

	$results[$file] = array(
		'new' => array(
			'time' => -1,
			'result' => '',
			'memory' => -1,
		),
		'original' => array(
			'time' => -1,
			'result' => '',
			'memory' => -1,
		),
		'match' => false,
	);
	$html = file_get_contents($samplePath.$file);

	$versions = array('original', 'new');
	shuffle($versions);
	foreach ($versions AS $version) {
		if ($version == 'original') {
			/*
			 * Run tests against the original version
			 */
			$startMemory = memory_get_usage();
			$startTime = microtime(true);
			$oldOutput = htmLawed($html, $config, $spec);
			$endTime = microtime(true);
			$endMemory = memory_get_usage();
			$results[$file]['original']['memory'] = $endMemory - $startMemory;
			$results[$file]['original']['time'] = $endTime - $startTime;
			$results[$file]['original']['result'] = $oldOutput;
		}
		if ($version == 'new') {
			/*
			 * Run tests against the OOP-based method
			 */
			$startMemory = memory_get_usage();
			$startTime = microtime(true);
			$newOutput = htmLawed::create()->setHTML($html)->setAllSettings($config)->setSpec($spec)->clean();
			$endTime = microtime(true);
			$endMemory = memory_get_usage();
			$results[$file]['new']['memory'] = $endMemory - $startMemory;
			$results[$file]['new']['time'] = $endTime - $startTime;
			$results[$file]['new']['result'] = $newOutput;
		}
	}
	$results[$file]['source'] = $html;
	$results[$file]['match'] = ($newOutput === $oldOutput);
}

foreach ($results AS $file => $result) {
	echo '<div style="border:1px solid black;background:#f0f0f0;padding:5px;margin:10px 0">';
	echo '<h2 style="margin:0">Run-time results for <em>'.$file.'</em></h2>';
	if ($result['match'] === true) {
		echo '<div style="color:green">Success - Both results match</div>';
	} else {
		echo '<div style="color:red">Failure - Results do not match</div>';
	}
	echo '<div><strong>OOP-version:</strong> Run-time: '.round($result['new']['time'], 6).' seconds. Memory: '.round($result['new']['memory'] / 1000, 3).'kB.</div>';
	echo '<div><strong>Original version:</strong> Run-time: '.round($result['original']['time'], 6).' seconds. Memory: '.round($result['original']['memory'] / 1000, 3).'kB.</div>';
	$timeDiff = round($result['new']['time'] - $result['original']['time'], 6);
	if ($timeDiff < 0) {
		echo '<div><strong>OOP-version</strong> was '.abs($timeDiff).' seconds faster.</div>';
	} else {
		echo '<div><strong>Original version</strong> was '.$timeDiff.' seconds faster.</div>';		
	}
	$memDiff = $result['new']['memory'] - $result['original']['memory'];
	if ($memDiff < 0) {
		echo '<div><strong>OOP-version</strong> used '.abs($memDiff).' fewer bytes.</div>';
	} else {
		echo '<div><strong>Original version</strong> used '.$memDiff.' fewer bytes.</div>';		
	}
	echo '<h3 style="margin:0">Original Input</h3>';
	echo '<div style="border:1px solid #666;background:#fff;padding:2px;max-height:100px;overflow:auto"><code>'.htmlspecialchars($result['source']).'</code></div>';
	if ($result['match'] === true) {
		echo '<h3 style="margin:0">Resulting Code</h3>';
		echo '<div style="border:1px solid #666;background:#fff;padding:2px;max-height:100px;overflow:auto"><code>'.htmlspecialchars($result['new']['result']).'</code></div>';
	} else {
		echo '<h3 style="margin:0">New-Version Code</h3>';
		echo '<div style="border:1px solid #666;background:#fff;padding:2px;max-height:100px;overflow:auto"><code>'.htmlspecialchars($result['new']['result']).'</code></div>';
		echo '<h3 style="margin:0">Original-Version Code</h3>';
		echo '<div style="border:1px solid #666;background:#fff;padding:2px;max-height:100px;overflow:auto"><code>'.htmlspecialchars($result['original']['result']).'</code></div>';
	}
	echo '</div>';
}
?>
<ul>
	<li>All tests are run after both the class and function-collection are loaded.</li>
	<li>Both versions are isolated from each other and run in random order.</li>
	<li>Subsequent calls intentionally don't unset values to demonstrate performance benefits of multiple executions within a single script.</li>
	<li>Memory-usage is crudely performed with PHP's <em>memory_get_usage</em> function.</li>
</ul>