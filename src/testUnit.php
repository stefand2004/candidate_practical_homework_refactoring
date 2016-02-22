<?php


require_once('Config.php');
require_once('ApiCall.php');
require_once('LanguageBatchBo.php');





class LanguageBatchBoTest extends PHPUnit_Framework_TestCase
{
    

    public function testCanBeNegated()
    {
			
		$ApiCallParams = array('system_api',
						'language_api',
						array(
							'system' => 'LanguageFiles',
							'action' => 'getAppletLanguages'
						)
					);

		$arg = 'system.translated_applications';
		$applets = array('memberapplet' => 'JSM2_MemberApplet');	
        
        $a = new Language\LanguageBatchBo($arg,$ApiCallParams,$applets);

        
    }

    // ...
}
