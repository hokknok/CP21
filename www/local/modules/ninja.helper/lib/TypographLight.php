<?php

/*
 * Класс реализует минимальный набор функций и методов для
 * типографирования текстов.
 *
 * Планируется для типографирования названий, коротких текстовых фраз, которые
 * или неудобно заводить в админке или клиент не будет этим заниматься.
 *
 */

namespace Ninja\Helper;

class TypographLight {
	// =========================================================================
	// === Параметры объекта ===================================================
	// =========================================================================

	// Обрабатываемый текст
	private $text;

	// Язык текста
	private $lang = 'ru';

	// Рег. выражения для установки неразрывных пробелов
	private $constToNbsp = Array(
		'ru' => Array(
			'name' => 'Russian',
			'words_pattern' => Array(
                '/(\s|^|;|\()(и|в|во|не|на|из|за|под|над|от|до|об|с|у|что|а|как|то|по|он|все|это|о|его|к|еще|для|ко|со|бы|же)(\s)/iu',
				'/(\s|^)(ул.|д.|стр.|г.|п.|оф.)(\s)/iu',
				'/(\s|^)(of|and|or|the|on|in|to)(\s)/',
			),
		),
	);

	// Рег. выражения для установки nobr
	private $constToNobr = Array(
		'ru' => Array(
			'name' => 'Russian',
			'words_pattern' => Array(
				'/((\+\d\s|\d)?(\(?\d{3,4}\)?[\- ]?)?[\d\-]{7,10}\d)/',
				'/(по\-[А-яЁ\w]+)/iu',
			),
		),
	);

	// Установка неразрывных пробелов перед единицами измерений
	private $constToNbspSi = Array(
		'ru' => Array(
			'name' => 'Russian',
			'words_pattern' => Array(
				'/(\d+)\s([A-zА-яЁё\w])/',
			),
		),
	);

	// Установка тонких пробелов в числах
	private $constToThinsp = Array(
		'ru' => Array(
			'name' => 'Russian',
			'words_pattern' => Array(
				'/(\d{1,3})\s(\d{3})/',
			),
		),
	);


	// =========================================================================
	// === КОНСТРУКТОР, ГЕТТЕРЫ и СЕТТЕРЫ ======================================
	// =========================================================================

	public function __construct() {
	}


	// =========================================================================
	// === CRUD ================================================================
	// =========================================================================

	/**
	 * Метод возвращает типографированный текст
	 * @param string $text
	 * @return string Типографированный текст
	 */
	public function getResult($text) {
		if (!is_string($text) || !$text) { return $text; }

		$this->text = $text;


		// Добавляем тонкие пробелы
		$this->setThinsp();


		// Добавляем неразрывные пробелы
		$this->setNbsp();


		// Запретим переносы строк, где нужно
		$this->setNobr();


		// Ставим тире
		$this->setDash();


		// Возвращаем результат
		return $this->text;

	}


	/**
	 * Метод возвращает типографированную строку с номерами телефонов
	 * @param string $text
	 * @return string Типографированный текст
	 */
	public function getPhones($text) {
		if (!is_string($text) || !$text) { return $text; }

		$phonePattern = '/(\+\d)([\(\)\d\s\-]+)(\d\d)/';

		return preg_replace($phonePattern, '<span class="nobr">$1$2$3</span>', $text);
	}


	// =========================================================================
	// === ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ ==============================================
	// =========================================================================

	private function setDash() {
		$pattern = '/([A-zА-яЁё])\s+([—–-])/ui';

		$this->text = preg_replace($pattern, '$1&nbsp;&mdash;', $this->text);

	}


	/**
	 * Метод добавляет неразырвные пробелы в текст
	 */
	private function setNbsp() {
		// Предлоги и адреса
		$patternList = $this->constToNbsp[$this->lang]['words_pattern'];

		foreach ($patternList as $pattern) {
			// Две строки для кейса:
			// Как в московских школах -> Как&nbsp;в&nbsp;московских школах
			$this->text = preg_replace($pattern, '$1$2&nbsp;', $this->text);
			$this->text = preg_replace($pattern, '$1$2&nbsp;', $this->text);
		}


		// Единицы измерения
		$patternListSi = $this->constToNbspSi[$this->lang]['words_pattern'];

		foreach ($patternListSi as $pattern) {
			$this->text = preg_replace($pattern, '$1&nbsp;$2', $this->text);
		}
	}


	private function setNobr() {
		$patternList = $this->constToNobr[$this->lang]['words_pattern'];

		foreach ($patternList as $pattern) {
			$this->text = preg_replace($pattern, '<span class="nobr">$1</span>', $this->text);
		}
	}


	private function setThinsp() {
		$patternList = $this->constToThinsp[$this->lang]['words_pattern'];

		foreach ($patternList as $pattern) {
			$this->text = preg_replace($pattern, '$1&thinsp;$2', $this->text);
		}
	}

}
