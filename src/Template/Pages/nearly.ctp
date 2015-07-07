<table>
	<tr>
		<td>day</td>
		<td>x0</td>
		<td>x1</td>
		<td>x2</td>
		<td>x3</td>
		<td>x4</td>
		<td>x5</td>
		<td>x6</td>
		<td>x7</td>
		<td>x8</td>
		<td>x9</td>
	</tr>
<?php foreach ($nearly as $key => $value) : ?>
	<tr>
		<td><?php echo $key?></td>
		<!-- <td><?php //echo implode('</td><td>', $value)?></td> -->
		<?php for ($i=0; $i<10; $i++) : 
			$text = isset($value[$i]) ? $value[$i] : NULL;
		?>
		<td style="color:<?php echo ($text > 3) ? 'red' : 'black';  ?>;"><?php echo $text;?></td>
		<?php endfor; ?>
	</tr>
<?php endforeach; ?>
</table>