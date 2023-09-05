<?php

namespace App\Validation;

class TracklessRules
{
	public function currencyStatus(string $str, string $fields, array $data)
	{
		if (($str != 'active') || ($str!='disabled')) {
			return false;
		} else {
			return true;
		}
	}
}										