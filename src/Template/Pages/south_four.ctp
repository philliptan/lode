<?php 
use Cake\Utility\Hash;
$year = Hash::get($this->request->query, 'search_year');
//$year = $year ? $year : NULL;
$month = Hash::get($this->request->query, 'search_month');
//$month = $month ? $month : NULL;

echo $this->Html->script('http://code.jquery.com/jquery.min.js');
$this->Html->scriptStart(['block' => true]);
	$url = $this->request->url;
	echo "var baseUrl = '/$url';";
$this->Html->scriptEnd();
?>
<script type="text/javascript">
	$(document).ready(function() {
		$('#search_year').change(function() {
			var search_month = $('#search_month').val();
			location.href = baseUrl + '?search_year=' + $(this).val() + '&search_month=' + search_month;
		});
		$('#search_month').change(function() {
			var search_year = $('#search_year').val();
			location.href = baseUrl + '?search_year=' + search_year + '&search_month=' + $(this).val();
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

<?php
echo $this->Form->label('search_year', 'Năm');
echo '<div class="input date">';
echo $this->Form->year('search', [
    'minYear' => 2008,
    'maxYear' => date('Y'),
    'id' => 'search_year',
    'value' => $year,
]);
echo $this->Form->label('search_year', 'Tháng');
echo $this->Form->month('search', [
    'id' => 'search_month',
    'value' => $month,
    'monthNames' => false,
]);
echo '</div>';
?>
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