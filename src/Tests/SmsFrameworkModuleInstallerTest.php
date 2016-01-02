<?php

/**
 * @file
 * Contains \Drupal\sms\Tests\SmsFrameworkModuleInstallerTest.
 */

namespace Drupal\sms\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the UI module installation of smsframework.
 *
 * @group SMS Framework
 */
class SmsFrameworkModuleInstallerTest extends WebTestBase {


  public function testUiInstallModule() {
    $edit = [
      'modules[SMS Framework][sms][enable]' => TRUE,
    ];
    $this->drupalLogin($this->rootUser);
    $this->drupalPostForm('/admin/modules', $edit, 'Install');
    $this->assertText('Module SMS Framework has been enabled.');
  }

}
