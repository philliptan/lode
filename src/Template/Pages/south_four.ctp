<?php 
use Cake\Utility\Hash;
$year1 = Hash::get($this->request->query, 'search_year1');
$month1 = Hash::get($this->request->query, 'search_month1');
$year2 = Hash::get($this->request->query, 'search_year2');
$month2 = Hash::get($this->request->query, 'search_month2');
$head = Hash::get($this->request->query, 'search_head');
$trail = Hash::get($this->request->query, 'search_trail');

echo $this->Html->script('http://code.jquery.com/jquery.min.js');
$this->Html->scriptStart(['block' => true]);
	$url = $this->request->url;
	echo "var baseUrl = '/$url';";
$this->Html->scriptEnd();
?>
<script type="text/javascript">
	function headTrailChange() {
		var search_year1 = $('#search_year1').val();
		var search_month1 = $('#search_month1').val();
		var search_year2 = $('#search_year2').val();
		var search_month2 = $('#search_month2').val();
		var search_head = $('#search_head').val();
		var search_trail = $('#search_trail').val();
		location.href = baseUrl + 
						'?search_year1=' + search_year1 + 
						'&search_month1=' + search_month1 +
						'&search_year2=' + search_year2 + 
						'&search_month2=' + search_month2 +
						'&search_head=' + search_head +
						'&search_trail=' + search_trail;
	}

	$(document).ready(function() {
		$('#search_year1').change(function() {
			headTrailChange();
		});
		$('#search_month1').change(function() {
			headTrailChange();
		});
		$('#search_year2').change(function() {
			headTrailChange();
		});
		$('#search_month2').change(function() {
			headTrailChange();
		});
		$('#search_head').change(function() {
			headTrailChange();
		});
		$('#search_trail').change(function() {
			headTrailChange();
		});
	});
</script>
<?php 
	$style1 = 'style="font-size:1.5em; color:blue;"';
	$style2 = 'style="font-size:1.5em; color:red;"';

	$htmlFormat = "
<tr>
	<td>###DATE###</td>
	<td $style1>###HEAD1###</td>
	<td $style2>###TRAIL1###</td>
	<td $style1>###HEAD2###</td>
	<td $style2>###TRAIL2###</td>
</tr>";

	$arrHtml = [];
	$arrSpace = [];
	foreach ($trails as $key => $trail) {
		$dateFormat = $trail->date_result->i18nFormat('yyyy-MM-dd');

		$htmlTmp = Hash::get($arrHtml, $dateFormat, $htmlFormat);
		$city = $trail->city;
		$city += $city > 10 ? 1 : 0;
		$channelID = $city%2 == 0 ? 2 : 1;
		$channelText = $trail->level == 9 ? 'HEAD' : 'TRAIL';
		$htmlTmp = str_replace("###$channelText" . "$channelID###", $trail->trail, $htmlTmp);
		$htmlTmp = str_replace('###DATE###', $dateFormat, $htmlTmp);
		$arrHtml[$dateFormat] = $htmlTmp;

		if ($head) {

		}
	}
?>

<table>
	<tr>
		<td><?php echo $this->Form->label('search_year1', 'Từ Năm');?></td>
		<td><?php echo $this->Form->year('search', [
    'minYear' => 2008,
    'maxYear' => date('Y'),
    'id' => 'search_year1',
    'value' => $year1,
]);?></td>
		<td><?php echo $this->Form->label('search_month1', 'Tháng');?></td>
		<td><?php echo $this->Form->month('search', [
    'id' => 'search_month1',
    'value' => $month1,
    'monthNames' => false,
]);?></td>
	</tr>
	<tr>
		<td><?php echo $this->Form->label('search_year2', 'Đến Năm');?></td>
		<td><?php echo $this->Form->year('search', [
    'minYear' => 2008,
    'maxYear' => date('Y'),
    'id' => 'search_year2',
    'value' => $year2,
]);?></td>
		<td><?php echo $this->Form->label('search_month2', 'Tháng');?></td>
		<td><?php echo $this->Form->month('search', [
    'id' => 'search_month2',
    'value' => $month2,
    'monthNames' => false,
]);?></td>
	</tr>
	<tr>
		<td><?php  echo $this->Form->label('search_year', 'Xx');?></td>
		<td><?php echo $this->Form->select(
			    'search_head',
			    range(0, 9),
			    ['empty' => 'chọn', 'id' => 'search_head', 'value' => $head]
			);?></td>
		<td><?php  echo $this->Form->label('search_year', 'xX');?></td>
		<td><?php echo $this->Form->select(
			    'search_trail',
			    range(0, 9),
			    ['empty' => 'chọn', 'id' => 'search_trail', 'value' => $trail]
			);?></td>
	</tr>
</table>
<?php if ($htmlSpace): ?>
<table>
	<tr>
		<td>Ngày</td>
<?php
	foreach (array_keys($htmlSpace) as $key => $value) {
		echo "<td>$value</td>";
	}
?>
	</tr>
	<tr>
		<td>Lần</td>
<?php
	foreach ($htmlSpace as $key => $value) {
		echo "<td>" . count($value) . "</td>";
	}
?>
	</tr>
</table>
<?php endif; ?>
<table>
	<tr>
		<th style="border-bottom: 1px solid #000;">Ngày</th>
		<th style="border-bottom: 1px solid #000;">Kênh 1</th>
		<th style="border-bottom: 1px solid #000;">Kênh 2</th>
		<th style="border-bottom: 1px solid #000;">Kênh 3</th>
		<th style="border-bottom: 1px solid #000;">Kênh 4</th>
	</tr>
	<?php 
		$html = implode("\n", $arrHtml);
		$html = str_replace('###HEAD1###', '----', $html);
		$html = str_replace('###HEAD2###', '----', $html);
		$html = str_replace('###TRAIL1###', '----', $html);
		$html = str_replace('###TRAIL2###', '----', $html);
		echo $html;
	?>
</table>