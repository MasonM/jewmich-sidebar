function dateTypeChange() {
	$(this).closest('tr')
		.find('.date-range')
			.toggle($(this).val() !== '')
			.end()
		.find('select[name^=month_start_<?= CAL_GREGORIAN ?>], select[name^=month_end_<?= CAL_GREGORIAN ?>]')
			.toggle($(this).val() === 'Gregorian')
			.end()
		.find('select[name^=month_start_<?= CAL_JEWISH ?>], select[name^=month_end_<?= CAL_JEWISH ?>]')
			.toggle($(this).val() === 'Hebrew');
}
$('select[name^=date_type]').on('change', dateTypeChange).trigger('change');
