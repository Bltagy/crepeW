<?php
namespace App\Http\Traits;
use App\PromoCode;
trait LangData {

	public function toLang($lang, $data, $single = false) {
		if (empty($data)) {
			return [];
		}

		if ($single) {
			$langAttrs = $this->getLangKeys($data->getAttributes(), $lang);

			foreach ($langAttrs as $attr) {
				if (str_is('*' . $lang, $attr)) {
					$theKey        = str_replace('_' . $lang, '', $attr);
					$data[$theKey] = $data[$attr];
				}
				unset($data[$attr]);
			}

			return $data;
		}
		$data = $data->map(function ($item, $key) use ($lang) {
			$langAttrs = $this->getLangKeys($item->getAttributes(), $lang);

			foreach ($langAttrs as $attr) {
				if (str_is('*' . $lang, $attr)) {
					$theKey        = str_replace('_' . $lang, '', $attr);
					$item[$theKey] = $item[$attr];
				}
				unset($item[$attr]);
			}

			return $item;

		});
		return $data;
	}

	public function toLangArray($lang, $data) {
		if (empty($data)) {
			return [];
		}

		$langAttrs = $this->getLangKeys(array_keys($data), $lang);

		foreach ($langAttrs as $attr) {
			if (str_is('*' . $lang, $attr)) {
				$theKey        = str_replace('_' . $lang, '', $attr);
				$data[$theKey] = $data[$attr];
			}
			unset($data[$attr]);
		}

		return $data;

	}

	public function getLangKeys($attr) {
		$langKeys = [];
		foreach ($attr as $item_key => $item_value) {
			foreach (config('app.supported_languages') as $env_lang) {
				if (str_is('*' . $env_lang, $item_key)) {
					$langKeys[] = $item_key;
				}
			}
		}
		return $langKeys;
	}

	public function name($lang, $data) {
		if ($lang == 'ar') {
			return $data->name_ar;
		} else {
			return $data->name;
		}
	}

	public function generatePromo() {
		// $str = str_random(5);
		$length = 5;
		$str    = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $length);

		if ($this->promoCodeExists($str)) {
			return $this->generatePromo();
		}

		// otherwise, it's valid and can be used
		return $str;
	}

	public function promoCodeExists($number) {
		// query the database and return a boolean
		// for instance, it might look like this in Laravel
		return PromoCode::wherePromoCode($number)->exists();
	}

	public function convert2english($string) {
		if (is_numeric($string)) {
			return $string;
		}
		$newNumbers = range(0, 9);
		// 1. Persian HTML decimal
		$persianDecimal = array('&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;');
		// 2. Arabic HTML decimal
		$arabicDecimal = array('&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;');
		// 3. Arabic Numeric
		$arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
		// 4. Persian Numeric
		$persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');

		$string = str_replace($persianDecimal, $newNumbers, $string);
		$string = str_replace($arabicDecimal, $newNumbers, $string);
		$string = str_replace($arabic, $newNumbers, $string);
		return str_replace($persian, $newNumbers, $string);
	}
}
