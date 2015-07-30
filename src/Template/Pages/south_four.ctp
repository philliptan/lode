<?php 
use Cake\Utility\Hash;
$year = Hash::get($this->request->query, 'search_year');
$month = Hash::get($this->request->query, 'search_month');
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
		var search_year = $('#search_year').val();
		var search_month = $('#search_month').val();
		var search_head = $('#search_head').val();
		var search_trail = $('#search_trail').val();
		location.href = baseUrl + 
						'?search_year=' + search_year + 
						'&search_month=' + search_month +
						'&search_head=' + search_head +
						'&search_trail=' + search_trail;
	}

	$(document).ready(function() {
		$('#search_year').change(function() {
			headTrailChange();
		});
		$('#search_month').change(function() {
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
	}
?>

<table>
	<tr>
		<td><?php echo $this->Form->label('search_year', 'Năm');?></td>
		<td><?php echo $this->Form->year('search', [
    'minYear' => 2008,
    'maxYear' => date('Y'),
    'id' => 'search_year',
    'value' => $year,
]);?></td>
		<td><?php echo $this->Form->label('search_year', 'Tháng');?></td>
		<td><?php echo $this->Form->month('search', [
    'id' => 'search_month',
    'value' => $month,
    'monthNames' => false,
]);?></td>
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
<table>
	<tr>
		<th style="border-bottom: 1px solid #000;">Ngày</th>
		<th style="border-bottom: 1px solid #000;">Kênh 1</th>
		<th style="border-bottom: 1px solid #000;">Kênh 2</th>
		<th style="border-bottom: 1px solid #000;">Kênh 3</th>
		<th style="border-bottom: 1px solid #000;">Kênh 4</th>
	</tr>
	<?php echo implode("\n", $arrHtml) ?>
</table>