<?php
	class AjaxImageHelper extends AppHelper {
		public $helpers = array('Html');

		private $scriptWritten = false;

		// Deprecated
		public function image($data, $path, $upload_url, $options = array()) {
			if (!$this->scriptWritten) {
				$this->Html->script('AjaxImage.script', array('inline' => false));
			}

			$url = Hash::get($data, $path);

			$options['src'] = $url;

			$img = $this->Html->tag('img', null, $options);

			return $this->Html->tag('div', $img . $this->Html->tag(
				'div',
				__('Drag and drop image to upload.')
			), array(
				'data-name' => 'data' . join(array_map(function ($str) {
					return '[' . $str . ']';
				}, explode('.', $path))),
				'data-upload' => $this->Html->url($upload_url),
				'class' => 'ajax-image',
			));
		}

		public function img($data, $path, $url, $options = []) {
			if (!$this->scriptWritten) {
				$this->Html->script('AjaxImage.script', ['inline' => false]);
				$this->scriptWritten = true;
			}

			$src = Hash::get($options, 'src');
			if ($src == null) $src = $url;
			$options['src'] = $src;

			$class = Hash::get($options, 'class');
			if ($class === null) {
				$class = [];
			} else if (is_string($class)) {
				$class = [$class];
			}
			$class[] = 'ai-image';
			$options['class'] = $class;

			if (Hash::get($options, 'alt') === null) {
				$options['alt'] = __('Drag and drop image to upload.');
			}

			return $this->Html->tag(
				'div',
				$this->Html->tag('img', null, $options),
				[
				'data-src' => $src,
				'data-name' => 'data' . join(array_map(function ($str) {
					return '[' . $str . ']';
				}, explode('.', $path))),
				'data-url' => $url,
				'class' => 'ai-drop'
				]);
		}
	}
?>
