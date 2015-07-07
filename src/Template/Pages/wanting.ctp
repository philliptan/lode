<table>
	<tr>
		<td>Ngay</td>
		<td>KV</td>
		<td>KV GOP</td>
		<td>BU</td>
		<td>BU GOP</td>
		<td>Tien mong muon</td>
		<td>XX</td>
		<td>Thang</td>
		<td>Dau tu</td>
		<td>Von</td>
		<td>doanh thu</td>
		<td>loi nhuan</td>
	</tr>
	<?php foreach ($result as $date => $value) :?>
		<tr>
			<td><?php echo $date; ?></td>
			<td><?php echo $value['kv']; ?></td>
			<td><?php echo ceil($value['kv_gop']); ?></td>
			<td><?php echo ceil($value['hwa_bu']); ?></td>
			<td><?php echo ceil($value['bu_gop']); ?></td>
			<td><?php echo ceil($value['kv_real']); ?></td>
			<td><?php echo $value['xx']; ?></td>
			<td><?php echo $value['number_win']; ?></td>
			<td><?php echo ceil($value['dau_tu']); ?></td>
			<td><?php echo ceil($value['von']); ?></td>
			<td><?php echo ceil($value['doanh_thu']); ?></td>
			<td><?php echo ceil($value['loi_nhuan']); ?></td>
		</tr>
	<?php endforeach; ?>
</table>