<?php

class ServiceProvider_Stripe extends Extension_ServiceProvider implements IServiceProvider_HttpRequestSigner {
	const ID = 'wgm.stripe.service.provider';
	
	function renderConfigForm(Model_ConnectedAccount $account) {
		$tpl = DevblocksPlatform::getTemplateService();
		$active_worker = CerberusApplication::getActiveWorker();
		
		$params = $account->decryptParams($active_worker);
		$tpl->assign('params', $params);
		
		$tpl->display('devblocks:wgm.stripe::provider/edit_params.tpl');
	}
	
	function saveConfigForm(Model_ConnectedAccount $account, array &$params) {
		@$edit_params = DevblocksPlatform::importGPC($_POST['params'], 'array', array());
		
		$active_worker = CerberusApplication::getActiveWorker();
		
		if(!isset($edit_params['publishable_key']) || empty($edit_params['publishable_key']))
			return "The 'Publishable Key' is required.";
		
		if(!isset($edit_params['secret_key']) || empty($edit_params['secret_key']))
			return "The 'Secret Key' is required.";
		
		foreach($edit_params as $k => $v)
			$params[$k] = $v;
		
		return true;
	}
	
	function authenticateHttpRequest(Model_ConnectedAccount $account, &$ch, &$verb, &$url, &$body, &$headers) {
		$credentials = $account->decryptParams();
		
		if(!isset($credentials['secret_key']))
			return false;
		
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, sprintf("%s:", $credentials['secret_key']));
		
		curl_setopt($ch, CURLOPT_URL, $url);
		
		return true;
	}
};