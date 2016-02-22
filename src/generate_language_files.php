<?php

require_once('Config.php');
require_once('ApiCall.php');
require_once('LanguageBatchBo.php');


$ApiCallParams = array('system_api',
						'language_api',
						array(
							'system' => 'LanguageFiles',
							'action' => 'getAppletLanguages'
						)
					);

$arg = 'system.translated_applications';
$applets = array('memberapplet' => 'JSM2_MemberApplet');




new Language\LanguageBatchBo($arg,$ApiCallParams,$applets);
