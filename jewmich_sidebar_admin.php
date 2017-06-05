<?

global $wpdb;

function getCalendarMonths($calType) {
	$calInfo = cal_info($calType);
	return $calInfo['months'];
}

$message = null;
if (!empty($_POST['delete'])) {
	$id = key($_POST['delete']);
	$wpdb->delete('Sidebar', ['id' => $id]);
	$message = "Successfully deleted row";
} elseif (!empty($_POST['submit'])) {
	foreach ($_POST['id'] as $i => $id) {
		$row = array();
		if ($id) $row['id'] = $id;
		foreach (array('position', 'description', 'url', 'img_src', 'date_type') as $keyName) {
			$row[$keyName] = $_POST[$keyName][$i];
		}

		if (empty($id) && (empty($row['position']) || empty($row['description']) || empty($row['url']))) {
			continue;
		}

		if (empty($row['date_type'])) {
			$row['date_type'] = $row['date_start'] = $row['date_end'] = null;
		} else {
			$row['date_start'] = '0000-' . $_POST["month_start_" . $row['date_type']][$i] . '-' . $_POST["day_start"][$i];
			$row['date_end'] = '0000-' . $_POST["month_end_" . $row['date_type']][$i] . '-' . $_POST["day_end"][$i];
		}

		if (empty($id)) {
			$wpdb->insert('Sidebar', $row);
		} else {
			$wpdb->update('Sidebar', $row, ['id' => $id]);
		}
	}
	$message = "Successfully updated";
}

$postKeys = array('id', 'position', 'description', 'url', 'img_src',  'date_type', 'month_start', 'day_start', 'month_end', 'day_end');
$rows = $wpdb->get_results('
	SELECT
		id,
		position,
		description,
		url,
		img_src,
		date_type,
		MONTH(date_start) AS month_start,
		DAY(date_start) AS day_start,
		MONTH(date_end) AS month_end,
		DAY(date_end) AS day_end
	FROM Sidebar
	ORDER BY
		date_type IS NOT NULL ASC,
		position ASC
', ARRAY_A);

// append blank entry for entering a new row
$rows[] = array_fill_keys($postKeys, '');

$dateTypes = [
	'Gregorian' => getCalendarMonths(CAL_GREGORIAN),
	'Hebrew' => getCalendarMonths(CAL_JEWISH),
];
?>
<h1>Sidebar Editor</h1>
<? if ($message): ?>
<h3><?= $message ?></h3>
<? endif ?>
<form method="post">
	<table>
		<tr>
			<th>Delete</th>
			<th>Position</th>
			<th>Description</th>
			<th>Link URL</th>
			<th>Image URL</th>
			<th>Date type</th>
			<th>Date range start</th>
			<th>Date range end</th>
		</tr>
		<? foreach ($rows as $i => $row): ?>
		<tr>
			<td>
				<? if ($row['id']): ?>
				<input type="submit" name="delete[<?= $row['id'] ?>]" value="Delete"/>
				<? endif ?>
			</td>
			<td>
				<input type="hidden" name="id[<?= $i ?>]" value="<?= $row['id'] ?>"/>
				<select name="position[<?= $i ?>]">
					<? foreach (range(1, 10) as $num): ?>
					<option value="<?= $num ?>"<?=
						($num == $row['position']) ? ' selected' : ''
					?>><?= $num ?></option>
					<? endforeach ?>
				</select>
			</td>
			<td><input type="text" name="description[<?= $i ?>]" value="<?= $row['description'] ?>"/></td>
			<td><input type="text" name="url[<?= $i ?>]" value="<?= $row['url'] ?>"/></td>
			<td><input type="text" name="img_src[<?= $i ?>]" value="<?= $row['img_src'] ?>"/></td>
			<td>
				<select name="date_type[<?= $i ?>]">
					<option value=""<?= is_null($row['date_type']) ? ' selected' : '' ?>>N/A</option>
					<? foreach (array_keys($dateTypes) as $dateType): ?>
					<option value="<?= $dateType ?>"<?= 
						($dateType === $row['date_type']) ? 'selected' : ''
					?>><?= $dateType ?></option>
					<? endforeach ?>
				</select>
			</td>
			<? foreach (array('start', 'end') as $rangeType): ?>
			<td class="date-range">
				<? foreach ($dateTypes as $dateType => $months): ?>
				<select name="month_<?= $rangeType ?>_<?= $dateType ?>[<?= $i ?>]">
					<? foreach ($months as $monthNum => $monthName): ?>
					<option value="<?= $monthNum ?>"<?= 
						($row['month_' . $rangeType] == $monthNum) ? ' selected' : ''
					?>><?= $monthName ?></option>
					<? endforeach ?>
				</select>
				<? endforeach ?>

				<select name="day_<?= $rangeType ?>[<?= $i ?>]">
					<? foreach (range(1, 31) as $dayNum): ?>
					<option value="<?= $dayNum ?>"<?=
						($row['day_'. $rangeType] == $dayNum) ? ' selected' : '' 
					?>><?= $dayNum ?></option>
					<? endforeach ?>
				</select>
			</td>
			<? endforeach ?>
		</tr>
		<? endforeach ?>
	</table>
	<input type="submit" name="submit" value="Submit"/>
</form>
