<?php

class icms_route_Handler extends icms_ipf_Handler
{

	/**
	 * Constructor
	 *
	 * @param object $db
	 */
	public function __construct(&$db, $module = 'icms')
	{
		parent::__construct($db, 'routes', 'id', 'name', 'name', $module, 'routes', true);
	}

	public function registerModuleRoutes(icms_module_Object $module)
	{
		$path = $module->getPath();
		$flags = \FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS;
		if (is_dir($npath = $path . DIRECTORY_SEPARATOR . 'controllers')) {
			foreach (new \RecursiveDirectoryIterator($npath, $flags) as $entry) {
				if ($entry->getExtension() != 'php' || !$entry->isFile()) {
					continue;
				}
				$class = '\\' . join(
						'\\',
						array_map('ucfirst',
							array_merge(
								[
									'ImpressCMS',
									'modules',
									$module->modname
								],
								explode(
									DIRECTORY_SEPARATOR,
									mb_substr(
										$entry->getPath(),
										mb_strlen($npath) + 1
									)
								),
								[
									$entry->getBasename('.php')
								]
							)
						)
					);
				if (!class_exists($class) || is_in) {
					continue;
				}
				$reflection = new ReflectionClass($class);
				if ($reflection->isAbstract()) {
					continue;
				}
				foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
					if ($method->isAbstract() || $method->isStatic()) {
						continue;
					}
					foreach ([
								 icms_route_Object::TYPE_HEAD => [
									 icms_route_Object::TYPE_HEAD
								 ],
								 icms_route_Object::TYPE_GET => [
									 icms_route_Object::TYPE_HEAD,
									 icms_route_Object::TYPE_GET
								 ],
								 icms_route_Object::TYPE_DELETE => [
									 icms_route_Object::TYPE_HEAD,
									 icms_route_Object::TYPE_DELETE
								 ],
								 icms_route_Object::TYPE_POST => [
									 icms_route_Object::TYPE_HEAD,
									 icms_route_Object::TYPE_POST
								 ],
								 icms_route_Object::TYPE_PUT => [
									 icms_route_Object::TYPE_HEAD,
									 icms_route_Object::TYPE_PUT
								 ],
								 icms_route_Object::TYPE_PATCH => [
									 icms_route_Object::TYPE_HEAD,
									 icms_route_Object::TYPE_PATCH
								 ],
								 icms_route_Object::TYPE_OPTIONS => [
									 icms_route_Object::TYPE_OPTIONS
								 ],
								 'any' => [
									 icms_route_Object::TYPE_HEAD,
									 icms_route_Object::TYPE_GET,
									 icms_route_Object::TYPE_POST,
									 icms_route_Object::TYPE_PUT,
									 icms_route_Object::TYPE_PATCH,
									 icms_route_Object::TYPE_OPTIONS,
									 icms_route_Object::TYPE_DELETE
								 ]
							 ] as $type => $dtypes) {
						$action_type = strtolower($type);
					}
				}
			}
		} else {
			foreach (new \RecursiveDirectoryIterator($path, $flags) as $entry) {
				if ($entry->getExtension() != 'php' || !$entry->isFile()) {
					continue;
				}
				$basename = $entry->getBasename('.php');
			}
		}
	}

}