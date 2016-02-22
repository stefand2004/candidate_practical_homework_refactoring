<?php

namespace Language;




/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo{	
	protected  $applications = array();
		
	public function __construct($arg,$ApiCallParams,$applets){
		$this->applications = Config::get($arg);
		$this->generateLanguageFiles($ApiCallParams);
		$this->generateAppletLanguageXmlFiles($applets,$ApiCallParams);
	}
	private function output($application,$language,$success){
		$string = "\nGenerating language files\n[APPLICATION: " . $application . "]\n\t[LANGUAGE: " . $language . "]";
		if ($success) $string .= " OK\n";
		else $string .="Unable to generate language file!";
		echo $string;
	}
	private function generateLanguageFiles($ApiCallParams){
		foreach ($this->applications as $application => $languages) {
			foreach ($languages as $language) {
				if ($this->getLanguageFile($application, $language,$ApiCallParams)) {
					$this->output($application,$language,true);
				}
				else {
					$this->output($application,$language,false);
				}
			}
		}
	}
	private function getLanguageFile($application, $language,$ApiCallParams){
		$result = false;
		$languageResponse = ApiCall::call($ApiCallParams[0],$ApiCallParams[1],$ApiCallParams[2],array('language' => $language));
		try {
			$this->checkForApiErrorResult($languageResponse);
		}
		catch (\Exception $e) {
			throw new \Exception('Error during getting language file: (' . $application . '/' . $language . ')');
		}
		// If we got correct data we store it.
		$destination = $this->getLanguageCachePath($application) . $language . '.php';
		var_dump($destination);
		return  (bool)$this->writeLanguageResponse($destination,$languageResponse);
	}
	
	protected  function getLanguageCachePath($application)
	{
		return Config::get('system.paths.root') . '/cache/' . $application. '/';
		
	}
	protected function writeLanguageResponse($destination,$languageResponse){
		if (!is_dir(dirname($destination))) {
			mkdir(dirname($destination), 0755, true);
		}
		$result = file_put_contents($destination, $languageResponse['data']);
		return (bool)$result;
	}
	
	protected  function checkForApiErrorResult($result)
	{
		// Error during the api call.
		if ($result === false || !isset($result['status'])) {
			throw new \Exception('Error during the api call');
		}
		// Wrong response.
		if ($result['status'] != 'OK') {
			throw new \Exception('Wrong response: '
				. (!empty($result['error_type']) ? 'Type(' . $result['error_type'] . ') ' : '')
				. (!empty($result['error_code']) ? 'Code(' . $result['error_code'] . ') ' : '')
				. ((string)$result['data']));
		}
		// Wrong content.
		if ($result['data'] === false) {
			throw new \Exception('Wrong content!');
		}
	}
	public function generateAppletLanguageXmlFiles($applets,$ApiCallParams){		
		foreach ($applets as $appletDirectory => $appletLanguageId) {
			$languages = $this->getAppletLanguages($appletLanguageId,$ApiCallParams);
			if (empty($languages)) $this->output2($appletLanguageId,$appletDirectory,$languages,false);
			else $this->output2($appletLanguageId,$appletDirectory,$languages,true);
			$path = Config::get('system.paths.root') . '/cache/flash';
			foreach ($languages as $language) {
				$xmlContent = $this->getAppletLanguageFile($appletLanguageId, $language);
				$xmlFile    = $path . '/lang_' . $language . '.xml';
				if (strlen($xmlContent) == file_put_contents($xmlFile, $xmlContent)) {
					echo " OK saving $xmlFile was successful.\n";
				}
				else {
					throw new \Exception('Unable to save applet: (' . $appletLanguageId . ') language: (' . $language
						. ') xml (' . $xmlFile . ')!');
				}
			}
			echo " < $appletLanguageId ($appletDirectory) language xml cached.\n";
		}

		echo "\nApplet language XMLs generated.\n";
	}
	private function output2($appletLanguageId,$appletDirectory,$languages,$success){
		$string = "\nGetting applet language XMLs..\n";
		$string .= " Getting > $appletLanguageId ($appletDirectory) language xmls..\n";
		if($success) $string .= ' - Available languages: ' . implode(', ', $languages) . "\n";
		else $string .= 'There is no available languages for the ' . $appletLanguageId . ' applet.';
	}
	protected  function getAppletLanguages($applet,$ApiCallParams)
	{
		$result = ApiCall::call($ApiCallParams[0],$ApiCallParams[1],$ApiCallParams[2],array('applet' => $applet));
		try {
			$this->checkForApiErrorResult($result);
		}
		catch (\Exception $e) {
			throw new \Exception('Getting languages for applet (' . $applet . ') was unsuccessful ' . $e->getMessage());
		}

		return $result['data'];
	}
	protected  function getAppletLanguageFile($applet, $language)
	{
		$result = ApiCall::call(
			'system_api',
			'language_api',
			array(
				'system' => 'LanguageFiles',
				'action' => 'getAppletLanguageFile'
			),
			array(
				'applet' => $applet,
				'language' => $language
			)
		);

		try {
			$this->checkForApiErrorResult($result);
		}
		catch (\Exception $e) {
			throw new \Exception('Getting language xml for applet: (' . $applet . ') on language: (' . $language . ') was unsuccessful: '
				. $e->getMessage());
		}

		return $result['data'];
	}
	
}



