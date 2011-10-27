<?php

interface CManager_Controller_Page_Interface {
	public function __construct(CManager_Controller_Router_Config_Page $config,
								CManager_Controller_Request_Abstract $request,
								CManager_Controller_Response_Abstract $response);

	public function init();

	public function tryRedirect();
	public function getRedirectUrl();

	public function getCode();
	public function setCode($code);

	public function getLayout();
	public function setLayout($layout);

	public function getTitle();
	public function setTitle($title);

	public function setVariables(array $variables);
	public function setVariable($name, $value);
	public function getVariables();
	public function getVariable($name);

	public function setRoute(CManager_Controller_Route $route);
	public function getRoute();

	public function runTagsByMode($mode);
	public function runTagsByName($name, $mode = null);
	public function getTagByName($name, $mode = null);
	public function getTagsByName($name, $mode = null);
	public function getTags();

	public function createTag($name, $namespace, $mode, $params = null);
	public function addTag(CManager_Controller_Tag $tag);
	public function unserializeTag($data);
	public function addSessionTag(CManager_Controller_Tag $tag, $path = null);
}