<h1>Lập Lệnh</h1>
<?php
    echo $this->Form->create($command);
    echo $this->Form->input('date_command', ['type' => 'date']);
    echo $this->Form->input('xx');
    echo $this->Form->input('number_win');
    echo $this->Form->button(__('Thực thi'));
    echo $this->Form->end();
?>

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
	<?php
		$prevCommand = NULL;
		foreach ($commands as $key => $command) :
			//$prevProfit = $prevCommand ? $prevCommand->wanting_increase - $prevCommand->prev_profit_increase : 0;
			$profit = $command->revenue - $command->investment;
	?>
		<tr>
			<td><?php echo $command->date_command->i18nFormat('yyyy-MM-dd'); ?></td>
			<td><?php echo $command->wanting; ?></td>
			<td><?php echo $this->Number->format($command->wanting_increase, ['precision' => 0]); ?></td>
			<td><?php echo $this->Number->format($command->prev_profit, ['precision' => 0]); ?></td>
			<td><?php echo $this->Number->format($command->prev_profit_increase, ['precision' => 0]); ?></td>
			<td><?php echo $this->Number->format($command->wanting_increase - $command->prev_profit_increase, ['precision' => 0]); ?></td>
			<td><?php echo $command->xx; ?></td>
			<td><?php echo $command->number_win; ?></td>
			<td><?php echo $this->Number->format($command->money_on_one, ['precision' => 0]); ?></td>
			<td><?php echo $this->Number->format($command->investment, ['precision' => 0]); ?></td>
			<td><?php echo $this->Number->format($command->revenue, ['precision' => 0]); ?></td>
			<td><?php echo $this->Number->format($profit); ?></td>
		</tr>
	<?php $prevCommand = $command; endforeach; ?>
</table>