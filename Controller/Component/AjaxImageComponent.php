<?php
	class AjaxImageComponent extends Component {
		public function save_image($ctr, $id, $path) {
			$ctr->set('_serialize', 'data');

			$path = explode('.', $path);
			$model = null;
			$field = null;
			if (count($path) == 1) {
				$model = $ctr->{Infrector::singularize($ctr->name)};
				$field = $path[0];
			} else if (count($path) == 2) {
				$model = $ctr->{$path[0]};
				$field = $path[1];
			} else {
				throw new InternalErrorException('Invalid Path');
			}

			$model->id = $id;
			if (!$model->exists()) {
				throw new NotFoundException(__('Invalid id'));
			}

			if (method_exists($model, 'isOwnedBy') && !$model->isOwnedBy($id, $ctr->Auth->user('id'))) {
			   	throw new ForbiddenException('Permission Denied');
			}

			$model->read();

			$file = Hash::get($ctr->data, $path);
			if (!$file) {
				throw new ForbiddenException('Image data not found.');
			}

			if (Hash::get($file, 'error')) {
				throw new ForbiddenException('Upload error');
			}

			if (Hash::get($file, 'size') >= 1024 * 512) {
				throw new ForbiddenException('Too large image size');
			}

			$mime = Hash::get($file, 'type');

			if (explode('/', $mime)[0] != 'image') {
				throw new ForbiddenException('Invalid file type');
			}

			$tmp_name = Hash::get($file, 'tmp_name');
			if (!is_uploaded_file($tmp_name)) {
				throw new ForbiddenException('Not an uploaded file');
			}

			$model->set([
			($field . '_data') => file_get_contents($tmp_name),
			($field . '_mime') => $mime
			]);

			if (!$model->save()) {
				throw new InternalErrorException('Save failed');
			}

			$ctr->set('data', [
			'status' => 'OK',
			]);
		}

		// Deprecated
		public function upload_image($id, $model, $path, $controller, $options = array()) {
			$controller->set('_serialize', 'data');

			$model_instance = $controller->{$model};
			$model_instance->id = $id;
			if (!$model_instance->exists()) {
				throw new NotFoundException(__('Invalid id'));
			}
			$model_instance->read();

			$file = Hash::get($controller->data, $path);
			if (!$file) {
				throw new ForbiddenException('Image data not found.');
			}

			if (Hash::get($file, 'error')) {
				throw new ForbiddenException('Upload error');
			}

			$size = Hash::get($file, 'size');
			if ($size == 0 || $size >= 524288) {
				throw new ForbiddenException('Invalid image size');
			}

			$tmp_name = Hash::get($file, 'tmp_name');
			if (!is_uploaded_file($tmp_name)) {
				throw new ForbiddenException('Not an uploaded file');
			}

			$extension = strtolower(pathinfo(Hash::get($file, 'name'), PATHINFO_EXTENSION));
			switch ($extension) {
				case 'png':
				case 'jpg':
				case 'jpeg':
				case 'bmp':
				case 'gif':
					break;
				default:
					throw new ForbiddenException('Invalid image type');
			}

			$filename = $id . '.'. $extension;

			$filepath = array_key_exists('filepath', $options) ? $options['filepath'] : WWW_ROOT . 'img/' ;
			if (substr($filepath, -1) != '/') $filepath .= '/';

			$url = array_key_exists('url', $options) ? $options['url'] : str_replace($_SERVER['DOCUMENT_ROOT'], '', $filepath);

			$field = explode('.', $path)[1];
			foreach (glob($filepath . (+$id) . '.*') as $oldfile) {
				unlink($oldfile);
			}

			$filepath .= $filename;
			$url .= $filename;

			if (move_uploaded_file($tmp_name, $filepath)) {
				$model_instance->set($field, $url);
				if (!$model_instance->save()) {
					unlink($filepath);
					throw new InternalErrorException;
				}
			}
			$controller->set('data', $url);
		}
	}
?>
