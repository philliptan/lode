<table>
	<tr>
		<td>XX</td>
		<td>day</td>
		<td>0</td>
		<td>1</td>
		<td>2</td>
		<td>3</td>
		<td>4</td>
		<td>5</td>
		<td>6</td>
		<td>7</td>
		<td>8</td>
		<td>9</td>
		<td>10</td>
		<td>11</td>
		<td>12</td>
		<td>13</td>
		<td>14</td>
		<td>15</td>
		<td>16</td>
		<td>17</td>
		<td>18</td>
		<td>19</td>
		<td>20</td>
		<td>21</td>
		<td>22</td>
		<td>23</td>
		<td>24</td>
		<td>25</td>
		<td>26</td>
		<td>27</td>
		<td>28</td>
		<td>29</td>
		<td>30</td>
		<td>31</td>
		<td>32</td>
		<td>33</td>
	</tr>
<?php for ($i=0; $i < 10; $i++) : $value = $count[$i];?>
	<tr>
		<td>x<?php echo $i?></td>
		<td><?php echo $day?></td>
		<?php for ($j=0; $j < 34; $j++) : ?>
			<?php if (isset($value[$j])) : ?>
				<td><?php echo $value[$j]['count'];?></td>
			<?php else : ?>
				<td>&nbsp;</td>
			<?php endif; ?>
		<?php endfor; ?>
	</tr>
<?php endfor; ?>

</table>